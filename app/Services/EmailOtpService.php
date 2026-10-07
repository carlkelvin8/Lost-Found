<?php

namespace App\Services;

use App\Mail\OtpVerificationMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Single source of truth for issuing e-mail verification codes.
 * Used by self-registration, login resend and Staff Tools admin creation.
 */
class EmailOtpService
{
    public function send(User $user): void
    {
        // Only one active code per account
        EmailOtp::where('user_id', $user->id)
            ->where('used', false)
            ->update(['used' => true]);

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailOtp::create([
            'user_id'    => $user->id,
            'otp'        => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);

        // A mailer outage must never block the caller — the code can always
        // be re-sent from the verification screen.
        $userName = $user->profile?->full_name ?? 'User';
        try {
            Mail::to($user->email)->send(new OtpVerificationMail($otp, $userName));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('otp_mail_failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'mailer'  => config('mail.default'),
            ]);
        }
    }
}
