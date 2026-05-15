<?php

namespace App\Http\Controllers\Auth\PasswordReset;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function __invoke(ResetPasswordRequest $request)
    {
        $data         = $request->get('temp_token_data');
        $user         = User::findOrFail($data['user_id']);
        $user->password = Hash::make($request->validated()['password']);
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Password updated successfully.',
            'token'   => $token,
        ]);
    }
}