<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();

        $grouped = [];
        foreach ($permissions as $permission) {
            [$table, $action] = explode('.', $permission->name);
            $grouped[$table][] = [
                'id'     => $permission->id,
                'action' => $action,
                'name'   => $permission->name,
            ];
        }

        return response()->json($grouped);
    }
}
