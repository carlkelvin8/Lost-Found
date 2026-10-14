<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\UploadedImageStorage;

class UserProfileController extends WebBaseController
{
    public function edit(Request $request)
    {
        if (!$this->user()) return redirect()->route('login');

        $u = $this->user();
        $profile = $u->profile()->first();
        $departments = Department::orderBy('name')->get();

        return view('profile.edit', compact('u','profile','departments'));
    }

    public function update(Request $request)
    {
        if (!$this->user()) return redirect()->route('login');

        $u = $this->user();

        $data = $request->validate([
            'full_name' => ['required','string','min:2','max:190'],
            'school_id_number' => ['nullable','string','max:60'],
            'department_id' => ['nullable','integer','exists:departments,id'],
            'contact_no' => ['nullable','string','max:40'],
            'address' => ['nullable','string','max:255'],
            'avatar' => ['nullable','file','mimes:jpg,jpeg,png,webp','max:4096'],
        ]);

        DB::transaction(function () use ($request, $u, $data) {

            // Only students may hold a School ID / Department binding.
            $isStudent = (string) ($u->profile?->user_type ?? '') === 'student';

            $payload = [
                'full_name' => $data['full_name'],
                'school_id_number' => $isStudent ? ($data['school_id_number'] ?? null) : null,
                'department_id' => $isStudent ? ($data['department_id'] ?? null) : null,
                'contact_no' => $data['contact_no'] ?? null,
                'address' => $data['address'] ?? null,
            ];

            if (!empty($data['avatar'] ?? null)) {
                $images = app(UploadedImageStorage::class);
                $oldAvatar = $u->profile?->avatar_url;
                $payload['avatar_url'] = $images->store($data['avatar'], 'avatars');
                DB::afterCommit(fn () => $images->delete($oldAvatar));
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
                $u->id
            );
        });

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profile updated successfully');
    }
}
