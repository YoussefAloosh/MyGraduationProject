<?php

// app/Http/Controllers/Api/PermissionController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    // جلب كل الصلاحيات مجمعة حسب الجدول
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