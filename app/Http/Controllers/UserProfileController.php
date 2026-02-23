<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends WebBaseController
{
    public function edit(Request $request)
    {
        if (!$this->user()) return redirect()->route('login');

        $u = $this->user();
        $profile = $u->profile()->first();

        return view('profile.edit', compact('u','profile'));
    }

    public function update(Request $request)
    {
        if (!$this->user()) return redirect()->route('login');

        $u = $this->user();

        $data = $request->validate([
            'full_name' => ['required','string','max:190'],
            'school_id_number' => ['nullable','string','max:60'],
            'department_id' => ['nullable','integer','exists:departments,id'],
            'contact_no' => ['nullable','string','max:40'],
            'avatar' => ['nullable','file','mimes:jpg,jpeg,png,webp','max:4096'],
        ]);

        DB::transaction(function () use ($request, $u, $data) {

            $payload = [
                'full_name' => $data['full_name'],
                'school_id_number' => $data['school_id_number'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'contact_no' => $data['contact_no'] ?? null,
            ];

            if (!empty($data['avatar'] ?? null)) {
                if ($u->profile && !empty($u->profile->avatar_url)) {
                    $old = $u->profile->avatar_url;
                    $oldPath = str_starts_with($old, 'storage/') ? substr($old, 8) : $old;
                    Storage::disk('public')->delete($oldPath);
                }
                $path = $data['avatar']->store('avatars', 'public');
                $payload['avatar_url'] = 'storage/' . $path;
            }

            if ($u->profile) {
                $u->profile->update($payload);
            } else {
                $payload['user_id'] = $u->id;

                $payload['user_type'] = 'student';

                UserProfile::create($payload);
            }

            $this->audit(
                $request,
                'profiles.update_me',
                'user_profiles',
                (int) $u->id
            );
        });

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profile updated successfully');
    }
}
