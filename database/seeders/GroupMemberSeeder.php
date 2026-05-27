<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GroupMember;
use App\Models\EmergencyGroup;
use App\Models\User;

class GroupMemberSeeder extends Seeder
{
    public function run(): void
    {
        $groups = EmergencyGroup::all();
        $users  = User::all();

        foreach ($groups as $group) {
            // أضف 3-5 أعضاء عشوائيين لكل غروب
            $randomUsers = $users->random(min(4, $users->count()));

            foreach ($randomUsers as $user) {
                GroupMember::firstOrCreate(
                    ['user_id' => $user->id, 'group_id' => $group->id],
                    [
                        'membership_type'   => 'permanent',
                        'membership_status' => 'active',
                        'joined_at'         => now(),
                        'last_activity_at'  => now()->subDays(rand(1, 30)),
                        'is_active'         => true,
                    ]
                );
            }
        }

        $this->command->info('✅ Group Members seeded successfully!');
    }
}