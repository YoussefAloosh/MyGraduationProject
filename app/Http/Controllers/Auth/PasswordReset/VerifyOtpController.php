<?php

namespace App\Http\Controllers\Auth\PasswordReset;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\SuspiciousResetAttemptMail;

class VerifyOtpController extends Controller
{
    public function __invoke(VerifyOtpRequest $request)
    {
        $data = $request->get('temp_token_data');
        $user = User::find($data['user_id']);

        // تحقق من الـ lock
        if ($user->otp_locked_until && now()->lt($user->otp_locked_until)) {
            return response()->json([
                'message' => 'Too many attempts. Please wait before trying again.'
            ], 429);
        }

        // تحقق من الكود
        if ($user->otp_code !== $request->otp_code) {
            $user->increment('otp_attempts');

            if ($user->otp_attempts >= 5) {
                $user->otp_locked_until = now()->addMinutes(60);
                $user->otp_attempts     = 0;
                $user->save();
                // Mail::to($user->email)->send(new SuspiciousResetAttemptMail($user));
                return response()->json([
                    'message' => 'Too many attempts. Account locked for 60 minutes.'
                ], 429);
            }

            if ($user->otp_attempts == 4) {
                return response()->json([
                    'message' => 'Your account will be locked next attempt.'
                ], 429);
            }

            return response()->json(['message' => 'Invalid OTP code.'], 400);
        }

        // تحقق من انتهاء الصلاحية
        if (now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP code expired.'], 400);
        }

        // تنظيف
        $user->otp_attempts     = 0;
        $user->otp_locked_until = null;
        $user->otp_code         = null;
        $user->otp_expires_at   = null;
        $user->save();

        $newTempToken = encrypt([
            'user_id'    => $user->id,
            'purpose'    => 'reset_password_confirm',
            'expires_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'message'    => 'OTP verified successfully.',
            'temp_token' => $newTempToken,
        ]);
    }
}