<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use App\Mail\PasswordResetOtpMail;

class ReSendController extends Controller
{
    public function resendOtp(Request $request)
    {
        $data    = $request->get('temp_token_data');
        $purpose = $data['purpose'];

        if ($purpose === 'email_verification') {
            $user = PendingUser::find($data['user_id']);

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $code                                = rand(100000, 999999);
            $user->verification_code             = $code;
            $user->verification_code_expires_at  = now()->addMinutes(10);
            $user->save();

            Mail::to($user->email)->send(new VerificationCodeMail($code));

        } elseif ($purpose === 'reset_password_request') {
            $user = User::find($data['user_id']);

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            // $otp                  = rand(100000, 999999);
            $otp                  = 999999;
            $user->otp_code       = $otp;
            $user->otp_expires_at = now()->addMinutes(5);
            $user->save();

            Mail::to($user->email)->send(new PasswordResetOtpMail($otp));
        }

        return response()->json(['message' => 'OTP sent successfully.']);
    }
}