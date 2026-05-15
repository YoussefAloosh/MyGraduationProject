<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\RegisterResource;
use App\Models\PendingUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SuspiciousRegisterAttemptMail;

class VerificationController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|digits:6',
        ]);

        $data        = $request->get('temp_token_data');
        $pendingUser = PendingUser::find($data['user_id']);

        if (!$pendingUser) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // تحقق من الكود
        if ($pendingUser->verification_code !== $request->verification_code) {
            $pendingUser->increment('verification_attempts');

            if ($pendingUser->verification_attempts == 4) {
                return response()->json([
                    'message' => 'Too many failed attempts. Next time your account will be deleted.'
                ], 403);
            }

            if ($pendingUser->verification_attempts >= 5) {
                Mail::to($pendingUser->email)->send(new SuspiciousRegisterAttemptMail($pendingUser));
                $pendingUser->delete();
                return response()->json([
                    'message' => 'Too many failed attempts. Account deleted.'
                ], 403);
            }

            return response()->json(['message' => 'Invalid verification code.'], 400);
        }

        // تحقق من انتهاء الصلاحية
        if (now()->greaterThan($pendingUser->verification_code_expires_at)) {
            return response()->json(['message' => 'Verification code expired.'], 400);
        }

        // إنشاء User الحقيقي
        $user = User::create([
            'name'     => $pendingUser->name,
            'email'    => $pendingUser->email,
            'password' => $pendingUser->password,
        ]);

        $user->assignRole('user');

        // حذف الـ pending
        $pendingUser->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully.',
            'user'    => new RegisterResource($user),
            'token'   => $token,
        ]);
    }
}