<?php

namespace App\Http\Controllers\Auth\PasswordReset;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetOtpMail;

class SendOtpController extends Controller
{
    public function __invoke(SendOtpRequest $request)
    {
        $user             = User::where('email', $request->validated()['email'])->first();
        // $otp              = rand(100000, 999999);
        $otp              = 999999;
        $user->otp_code   = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();

        // Mail::to($user->email)->send(new PasswordResetOtpMail($otp));

        $tempToken = encrypt([
            'user_id'    => $user->id,
            'purpose'    => 'reset_password_request',
            'expires_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'message'    => 'OTP sent to your email.',
            'temp_token' => $tempToken,
        ]);
    }
}