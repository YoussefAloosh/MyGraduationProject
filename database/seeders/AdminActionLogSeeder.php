<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminActionLog;
use App\Models\EmergencyGroup;
use App\Models\User;

class AdminActionLogSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::where('email', 'emergency@admin.com')->first();
        $admin   = User::where('email', 'admin@admin.com')->first();
        $groups  = EmergencyGroup::all();
        $users   = User::whereNotIn('email', [
            'admin@admin.com',
            'emergency@admin.com',
        ])->get();

        $actions = [
            [
                'action_type' => 'grant_extra_messages',
                'extra_value' => '2',
                'admin'       => $manager,
            ],
            [
                'action_type' => 'remove_user',
                'extra_value' => null,
                'admin'       => $manager,
            ],
            [
                'action_type' => 'ban_user',
                'extra_value' => '30 days',
                'admin'       => $admin,
            ],
            [
                'action_type' => 'promote_to_group_admin',
                'extra_value' => null,
                'admin'       => $manager,
            ],
            [
                'action_type' => 'restrict_user',
                'extra_value' => '7 days',
                'admin'       => $manager,
            ],
            [
                'action_type' => 'grant_extra_messages',
                'extra_value' => '1',
                'admin'       => $manager,
            ],
            [
                'action_type' => 'unrestrict_user',
                'extra_value' => null,
                'admin'       => $admin,
            ],
            [
                'action_type' => 'ban_user',
                'extra_value' => 'permanent',
                'admin'       => $admin,
            ],
        ];

        foreach ($actions as $action) {
            AdminActionLog::create([
                'section'        => 'emergency',
                'action_type'    => $action['action_type'],
                'admin_id'       => $action['admin']->id,
                'target_user_id' => $users->random()->id,
                'group_id'       => in_array($action['action_type'], ['grant_extra_messages', 'remove_user', 'promote_to_group_admin', 'restrict_user'])
                    ? $groups->random()->id
                    : null,
                'extra_value'    => $action['extra_value'],
                'action_at'      => now()->subDays(rand(1, 20)),
            ]);
        }

        $this->command->info('✅ Admin Action Logs seeded successfully!');
    }
}