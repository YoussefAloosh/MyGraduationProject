<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\PendingUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $code = rand(100000, 999999);
        $validated = $request->validated();

        // حذف أي pending_user قديم بنفس الإيميل
        PendingUser::where('email', $validated['email'])->delete();

        $pendingUser = PendingUser::create([
            'name'                         => $validated['name'],
            'email'                        => $validated['email'],
            'password'                     => Hash::make($validated['password']),
            'verification_code'            => $code,
            'verification_code_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($pendingUser->email)->send(new VerificationCodeMail($code));

        $tempToken = encrypt([
            'user_id'    => $pendingUser->id,
            'purpose'    => 'email_verification',
            'expires_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'message'    => 'Verification code sent.',
            'temp_token' => $tempToken,
        ]);
    }
}