<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();

        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles']);
        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        return response()->json($role, 201);
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json(['message' => 'Role deleted']);
    }

    public function matrix(Role $role)
    {
        $allPermissions  = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        $matrix = [];
        foreach ($allPermissions as $permission) {
            [$table, $action] = explode('.', $permission->name);
            $matrix[$table][$action] = in_array($permission->name, $rolePermissions);
        }

        return response()->json([
            'role'   => $role->name,
            'matrix' => $matrix,
        ]);
    }

    public function syncPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
        ]);

        $role->syncPermissions($request->permissions);

        return response()->json([
            'message' => 'Permissions updated',
            'role'    => $role->load('permissions'),
        ]);
    }
}
