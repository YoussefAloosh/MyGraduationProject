<?php

// app/Http/Controllers/Api/RoleController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // جلب كل الأدوار مع صلاحياتها
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    // إضافة دور جديد
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles']);
        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
        return response()->json($role, 201);
    }

    // حذف دور
    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['message' => 'Role deleted']);
    }

    // جلب الـ Matrix (جداول × صلاحيات) لدور معين
    public function matrix(Role $role)
    {
        $allPermissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        // تجميع الصلاحيات حسب الجدول
        $matrix = [];
        foreach ($allPermissions as $permission) {
            [$table, $action] = explode('.', $permission->name);
            $matrix[$table][$action] = in_array($permission->name, $rolePermissions);
        }

        return response()->json([
            'role' => $role->name,
            'matrix' => $matrix,
        ]);
    }

    // تحديث صلاحيات الدور من الـ Matrix
    public function syncPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
        ]);

        $role->syncPermissions($request->permissions);

        return response()->json([
            'message' => 'Permissions updated',
            'role' => $role->load('permissions'),
        ]);
    }

    public function all()
    {
        $role = Role::all();
        return response()->json([
            'message' => 'The Role is ',
            'role' => $role,
        ]);
    }

}

