<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ClaimDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClaimDocumentController extends WebBaseController
{
    public function store(Request $request, int $claimId)
    {
        if (!$this->user()) return redirect()->route('login');

        $claim = Claim::findOrFail($claimId);

        $isStaff = $this->hasAnyRole(['admin','osa']);
        $isOwner = (int) $claim->claimant_user_id === (int) $this->user()->id;

        if (!$isStaff && !$isOwner) abort(403);
        if ($claim->status !== 'pending') {
            return redirect()->route('claims.show', $claimId)->withErrors(['message' => 'Cannot add documents after review']);
        }

        $data = $request->validate([
            'file' => ['required','file','max:8192'],
        ]);

        $file = $data['file'];
        $mime = $file->getClientMimeType();
        $path = $file->store('claim_documents', 'public');
        $fullPath = Storage::disk('public')->path($path);
        $hash = is_file($fullPath) ? hash_file('sha256', $fullPath) : null;
        
        // Store relative path to be portable across ports/domains
        $url = '/storage/' . $path;

        ClaimDocument::create([
            'claim_id' => $claim->id,
            'file_url' => $url,
            'file_type' => $mime ? substr((string) $mime, 0, 60) : null,
            'file_hash_sha256' => $hash,
            'created_at' => now(),
        ]);

        $this->audit($request, 'claim_docs.create', 'claim_documents', null, ['claim_id' => $claim->id]);

        return redirect()->route('claims.show', $claimId)->with('success', 'Uploaded');
    }

    public function destroy(Request $request, int $id)
    {
        if (!$this->user()) return redirect()->route('login');

        $doc = ClaimDocument::findOrFail($id);
        $claim = Claim::findOrFail((int) $doc->claim_id);

        $isStaff = $this->hasAnyRole(['admin','osa']);
        $isOwner = (int) $claim->claimant_user_id === (int) $this->user()->id;

        if (!$isStaff && !$isOwner) abort(403);
        if ($claim->status !== 'pending') {
            return redirect()->route('claims.show', $claim->id)->withErrors(['message' => 'Cannot delete documents after review']);
        }

        $doc->delete();
        $this->audit($request, 'claim_docs.delete', 'claim_documents', $id, ['claim_id' => $claim->id]);

        return redirect()->route('claims.show', $claim->id)->with('success', 'Deleted');
    }
}
