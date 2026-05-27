<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserBan;
use App\Models\User;

class UserBanSeeder extends Seeder
{
    public function run(): void
    {
        $users   = User::whereNotIn('email', [
            'admin@admin.com',
            'emergency@admin.com',
        ])->get();

        $manager = User::where('email', 'emergency@admin.com')->first();

        $bans = [
            [
                'email'        => 'rami@test.com',
                'reason'       => 'إرسال إنذار طوارئ كاذب — المرة الأولى',
                'days'         => 30,
                'is_permanent' => false,
            ],
            [
                'email'        => 'bilal@test.com',
                'reason'       => 'إرسال رسائل spam بشكل متكرر — تجاوز الحد المسموح',
                'days'         => null,
                'is_permanent' => true,
            ],
            [
                'email'        => 'dima@test.com',
                'reason'       => 'إنقاذات وهمية متكررة',
                'days'         => 60,
                'is_permanent' => false,
            ],
        ];

        foreach ($bans as $banData) {
            $user = User::where('email', $banData['email'])->first();
            if (!$user) continue;

            UserBan::create([
                'user_id'      => $user->id,
                'reason'       => $banData['reason'],
                'banned_from'  => now()->subDays(5),
                'banned_until' => $banData['is_permanent']
                    ? null
                    : now()->addDays($banData['days']),
                'is_permanent' => $banData['is_permanent'],
                'banned_by'    => $manager?->id,
            ]);
        }

        $this->command->info('✅ User Bans seeded successfully!');
    }
}