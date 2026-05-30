<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PendingGroupRequest;
use App\Models\PendingGroupUser;
use App\Models\User;

class PendingGroupRequestSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $pendingGroups = [
            // ─── سوريا ────────────────────────────────────────
            [
                'center_lat'         => 33.4936,
                'center_lng'         => 36.3117,
                'nearby_users_count' => 4,
                'status'             => 'submitted',
                'submitted_at'       => now()->subHours(2),
            ],
            [
                'center_lat'         => 33.5280,
                'center_lng'         => 36.3050,
                'nearby_users_count' => 3,
                'status'             => 'submitted',
                'submitted_at'       => now()->subDay(),
            ],
            [
                'center_lat'         => 36.2100,
                'center_lng'         => 37.1600,
                'nearby_users_count' => 5,
                'status'             => 'submitted',
                'submitted_at'       => now()->subHours(5),
            ],
            [
                'center_lat'         => 34.7300,
                'center_lng'         => 36.7200,
                'nearby_users_count' => 2,
                'status'             => 'pending',
                'submitted_at'       => null,
            ],
            [
                'center_lat'         => 35.9306,
                'center_lng'         => 36.7953,
                'nearby_users_count' => 1,
                'status'             => 'pending',
                'submitted_at'       => null,
            ],
            [
                'center_lat'         => 32.6189,
                'center_lng'         => 36.1021,
                'nearby_users_count' => 3,
                'status'             => 'submitted',
                'submitted_at'       => now()->subDays(2),
            ],
            [
                'center_lat'         => 35.3333,
                'center_lng'         => 36.3167,
                'nearby_users_count' => 4,
                'status'             => 'submitted',
                'submitted_at'       => now()->subHours(10),
            ],
            [
                'center_lat'         => 33.0000,
                'center_lng'         => 36.2833,
                'nearby_users_count' => 2,
                'status'             => 'pending',
                'submitted_at'       => null,
            ],
        ];

        foreach ($pendingGroups as $groupData) {
            $request = PendingGroupRequest::create([
                'center_lat'              => $groupData['center_lat'],
                'center_lng'              => $groupData['center_lng'],
                'radius_km'               => 5.00,
                'nearby_users_count'      => $groupData['nearby_users_count'],
                'status'                  => $groupData['status'],
                'submitted_to_manager_at' => $groupData['submitted_at'],
            ]);

            // أضف مستخدمين عشوائيين بحسب العدد
            $randomUsers = $users->random(
                min($groupData['nearby_users_count'], $users->count())
            );

            foreach ($randomUsers as $user) {
                PendingGroupUser::firstOrCreate(
                    [
                        'pending_group_id' => $request->id,
                        'user_id'          => $user->id,
                    ],
                    [
                        'join_lat' => $user->home_lat ?? $groupData['center_lat'],
                        'join_lng' => $user->home_lng ?? $groupData['center_lng'],
                        'added_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('✅ Pending Group Requests seeded successfully!');
    }
}