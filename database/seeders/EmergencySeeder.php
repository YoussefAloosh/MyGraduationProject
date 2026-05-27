<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Emergency;
use App\Models\EmergencyNotification;
use App\Models\RescueParticipation;
use App\Models\EmergencyGroup;
use App\Models\User;

class EmergencySeeder extends Seeder
{
    public function run(): void
    {
        $groups = EmergencyGroup::all();
        $users  = User::all();

        $cases = [
            [
                'case_type' => 'حادث سير',
                'severity'  => 'critical',
                'status'    => 'resolved',
                'is_false'  => false,
                'lat'       => 33.5140,
                'lng'       => 36.2770,
            ],
            [
                'case_type' => 'حريق',
                'severity'  => 'high',
                'status'    => 'in_progress',
                'is_false'  => false,
                'lat'       => 33.5110,
                'lng'       => 36.2750,
            ],
            [
                'case_type'   => 'حالة طبية طارئة',
                'severity'    => 'medium',
                'status'      => 'new',
                'is_false'    => false,
                'lat'         => 36.2030,
                'lng'         => 37.1360,
            ],
            [
                'case_type'   => 'custom',
                'custom_text' => 'شخص محاصر في مبنى متصدع',
                'severity'    => 'critical',
                'status'      => 'completed_quota',
                'is_false'    => false,
                'lat'         => 34.7510,
                'lng'         => 36.7050,
            ],
            [
                'case_type' => 'انهيار مبنى',
                'severity'  => 'critical',
                'status'    => 'false',
                'is_false'  => true,
                'lat'       => 33.5160,
                'lng'       => 36.2800,
            ],
            [
                'case_type' => 'غرق',
                'severity'  => 'high',
                'status'    => 'resolved',
                'is_false'  => false,
                'lat'       => 36.1990,
                'lng'       => 37.1280,
            ],
        ];

        foreach ($cases as $caseData) {
            $group    = $groups->random();
            $reporter = $users->random();

            $emergency = Emergency::create([
                'reporter_id'      => $reporter->id,
                'target_group_id'  => $group->id,
                'case_type'        => $caseData['case_type'],
                'custom_text'      => $caseData['custom_text'] ?? null,
                'severity'         => $caseData['severity'],
                'required_rescuers'=> Emergency::requiredRescuers($caseData['severity']),
                'location_lat'     => $caseData['lat'],
                'location_lng'     => $caseData['lng'],
                'status'           => $caseData['status'],
                'is_false'         => $caseData['is_false'],
                'closed_at'        => in_array($caseData['status'], ['resolved', 'false']) ? now() : null,
            ]);

            // إضافة إشعارات وهمية
            $notifUsers = $users->random(min(3, $users->count()));
            foreach ($notifUsers as $i => $user) {
                $responses = ['accepted', 'rejected', 'no_response'];
                EmergencyNotification::create([
                    'emergency_id' => $emergency->id,
                    'receiver_id'  => $user->id,
                    'is_read'      => true,
                    'is_responded' => true,
                    'response'     => $responses[$i % 3],
                    'notif_round'  => 1,
                    'sent_at'      => now()->subMinutes(rand(5, 60)),
                    'responded_at' => now()->subMinutes(rand(1, 4)),
                ]);
            }

            // إضافة مشاركات إنقاذ للحالات المنتهية
            if (in_array($caseData['status'], ['resolved', 'in_progress', 'completed_quota'])) {
                $rescuers = $users->random(min(2, $users->count()));
                foreach ($rescuers as $rescuer) {
                    RescueParticipation::firstOrCreate(
                        ['emergency_id' => $emergency->id, 'user_id' => $rescuer->id],
                        [
                            'is_resolved_by_user' => $caseData['status'] === 'resolved',
                            'is_verified'         => true,
                            'accepted_at'         => now()->subMinutes(rand(10, 30)),
                            'resolved_at'         => $caseData['status'] === 'resolved' ? now() : null,
                        ]
                    );
                }
            }
        }

        $this->command->info('✅ Emergencies seeded successfully!');
    }
}