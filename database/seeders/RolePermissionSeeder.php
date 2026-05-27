<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ─── إنشاء الأدوار ────────────────────────────────
        $member           = Role::firstOrCreate(['name' => 'member',            'guard_name' => 'web']);
        $trusted          = Role::firstOrCreate(['name' => 'trusted',           'guard_name' => 'web']);
        $creator          = Role::firstOrCreate(['name' => 'creator',           'guard_name' => 'web']);
        $moderator        = Role::firstOrCreate(['name' => 'moderator',         'guard_name' => 'web']);
        $admin            = Role::firstOrCreate(['name' => 'admin',             'guard_name' => 'web']);
        $rescuer          = Role::firstOrCreate(['name' => 'rescuer',           'guard_name' => 'web']);
        $emergencyManager = Role::firstOrCreate(['name' => 'emergency_manager', 'guard_name' => 'web']);

        // ─── الجداول والصلاحيات ───────────────────────────
        $tables  = ['users', 'roles', 'permissions', 'articles', 'comments', 'reactions'];
        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($tables as $table) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name'       => "{$table}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // ─── صلاحيات كل دور ──────────────────────────────
        $member->syncPermissions([
            'articles.view', 'comments.view', 'comments.create', 'reactions.create',
        ]);
        $trusted->syncPermissions([
            'articles.view', 'articles.create',
            'comments.view', 'comments.create', 'comments.delete',
            'reactions.create',
        ]);
        $creator->syncPermissions([
            'articles.view', 'articles.create', 'articles.edit', 'articles.delete',
            'comments.view', 'comments.create', 'comments.delete',
            'reactions.create',
        ]);
        $moderator->syncPermissions([
            'articles.view', 'articles.create', 'articles.edit', 'articles.delete',
            'comments.view', 'comments.create', 'comments.delete',
            'reactions.create', 'users.view', 'users.edit',
        ]);
        $admin->syncPermissions(Permission::all());

        // ─── المستخدمون الأساسيون ─────────────────────────
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'home_lat' => 33.5138, 'home_lng' => 36.2765]
        );
        $adminUser->syncRoles(['admin']);

        $moderatorUser = User::firstOrCreate(
            ['email' => 'moderator@admin.com'],
            ['name' => 'Moderator', 'password' => Hash::make('password'), 'home_lat' => 33.5200, 'home_lng' => 36.2800]
        );
        $moderatorUser->syncRoles(['moderator']);

        $creatorUser = User::firstOrCreate(
            ['email' => 'creator@admin.com'],
            ['name' => 'Creator', 'password' => Hash::make('password'), 'home_lat' => 33.5100, 'home_lng' => 36.2700]
        );
        $creatorUser->syncRoles(['creator']);

        $trustedUser = User::firstOrCreate(
            ['email' => 'trusted@test.com'],
            ['name' => 'Trusted User', 'password' => Hash::make('password'), 'home_lat' => 36.2021, 'home_lng' => 37.1343]
        );
        $trustedUser->syncRoles(['trusted']);

        $memberUser = User::firstOrCreate(
            ['email' => 'member@test.com'],
            ['name' => 'Member User', 'password' => Hash::make('password'), 'home_lat' => 34.7500, 'home_lng' => 36.7000]
        );
        $memberUser->syncRoles(['member']);

        $emergencyManagerUser = User::firstOrCreate(
            ['email' => 'emergency@admin.com'],
            ['name' => 'Emergency Manager', 'password' => Hash::make('password'), 'home_lat' => 33.5050, 'home_lng' => 36.2900]
        );
        $emergencyManagerUser->syncRoles(['emergency_manager']);

        // ─── مستخدمون إضافيون بمواقع متنوعة ─────────────
        $extraUsers = [
            // دمشق - المزة
            ['name' => 'أحمد الخالد',   'email' => 'ahmad@test.com',   'home_lat' => 33.5120, 'home_lng' => 36.2745, 'role' => 'member'],
            ['name' => 'سارة محمود',    'email' => 'sara@test.com',    'home_lat' => 33.5155, 'home_lng' => 36.2780, 'role' => 'member'],
            ['name' => 'محمد العلي',    'email' => 'mohamad@test.com', 'home_lat' => 33.5095, 'home_lng' => 36.2720, 'role' => 'rescuer'],
            ['name' => 'نور الحسن',     'email' => 'nour@test.com',    'home_lat' => 33.5170, 'home_lng' => 36.2810, 'role' => 'member'],
            // دمشق - كفرسوسة
            ['name' => 'رامي السعيد',   'email' => 'rami@test.com',    'home_lat' => 33.5010, 'home_lng' => 36.2820, 'role' => 'member'],
            ['name' => 'لينا عمر',      'email' => 'lina@test.com',    'home_lat' => 33.4990, 'home_lng' => 36.2790, 'role' => 'trusted'],
            ['name' => 'كريم يوسف',     'email' => 'karim@test.com',   'home_lat' => 33.5030, 'home_lng' => 36.2850, 'role' => 'member'],
            // حلب
            ['name' => 'عمر الأحمد',    'email' => 'omar@test.com',    'home_lat' => 36.2050, 'home_lng' => 37.1380, 'role' => 'member'],
            ['name' => 'ميساء حداد',    'email' => 'maysa@test.com',   'home_lat' => 36.1980, 'home_lng' => 37.1300, 'role' => 'rescuer'],
            ['name' => 'بلال الرشيد',   'email' => 'bilal@test.com',   'home_lat' => 36.2100, 'home_lng' => 37.1420, 'role' => 'member'],
            // حمص
            ['name' => 'ديمة النجار',   'email' => 'dima@test.com',    'home_lat' => 34.7480, 'home_lng' => 36.7100, 'role' => 'member'],
            ['name' => 'طارق السلوم',   'email' => 'tariq@test.com',   'home_lat' => 34.7550, 'home_lng' => 36.6950, 'role' => 'member'],
        ];

        foreach ($extraUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name'     => $userData['name'],
                    'password' => Hash::make('password'),
                    'home_lat' => $userData['home_lat'],
                    'home_lng' => $userData['home_lng'],
                ]
            );
            $user->syncRoles([$userData['role']]);
        }

        $this->command->info('✅ Roles, Permissions & Users seeded successfully!');
    }
}