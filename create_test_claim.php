<?php

use App\Models\User;
use App\Models\ItemReport;
use App\Models\Claim;
use Illuminate\Support\Facades\Hash;

// Ensure users exist
$reporter = User::firstOrCreate(
    ['email' => 'reporter@example.com'],
    ['name' => 'Reporter User', 'password' => Hash::make('password')]
);

$claimant = User::firstOrCreate(
    ['email' => 'claimant@example.com'],
    ['name' => 'Claimant User', 'password' => Hash::make('password')]
);

// Create a Found Report
$report = ItemReport::create([
    'report_type' => 'found',
    'reporter_user_id' => $reporter->id,
    'item_name' => 'Test Found Item',
    'item_description' => 'This is a test item generated to verify claims.',
    'status' => 'pending', // Initially pending
]);

// Create a Claim
$claim = Claim::create([
    'report_id' => $report->id,
    'claimant_user_id' => $claimant->id,
    'proof_text' => 'This is my item, I can prove it.',
    'status' => 'pending',
]);

// Update report status to claimed
$report->update(['status' => 'claimed']);

echo "Created Claim #{$claim->id} for Report #{$report->id}\n";
