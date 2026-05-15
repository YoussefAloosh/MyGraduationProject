<?php

// app/Http/Controllers/Api/UserController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    // جلب كل المستخدمين مع أدوارهم
    public function index()
    {
        $users = User::with('roles')->get();
        return response()->json($users);
    }

    // إضافة مستخدم جديد
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return response()->json($user->load('roles'), 201);
    }

    // تعديل مستخدم
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|required',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|required|exists:roles,name',
        ]);

        $user->update($request->only('name', 'email'));

        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        return response()->json($user->load('roles'));
    }

    // حذف مستخدم
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
}