<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;
use App\Models\Emergency;
use App\Models\User;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $users      = User::all();
        $emergencies = Emergency::all();
        $manager    = User::where('email', 'emergency@admin.com')->first();

        $reports = [
            [
                'report_type' => 'false_emergency',
                'details'     => 'المستخدم أرسل إنذار طوارئ وهمي لاختبار النظام.',
                'status'      => 'pending',
            ],
            [
                'report_type' => 'spam_message',
                'details'     => 'المستخدم يرسل رسائل متكررة وغير مفيدة في وقت الطوارئ.',
                'status'      => 'approved',
            ],
            [
                'report_type' => 'fake_rescue',
                'details'     => 'المستخدم ادّعى المشاركة في الإنقاذ دون أن يصل للموقع فعلياً.',
                'status'      => 'pending',
            ],
            [
                'report_type' => 'group_admin_misconduct',
                'details'     => 'مشرف الغروب يسيء استخدام صلاحياته في حظر الأعضاء.',
                'status'      => 'rejected',
            ],
            [
                'report_type' => 'false_emergency',
                'details'     => 'إنذار طوارئ كاذب للمرة الثانية من نفس المستخدم.',
                'status'      => 'approved',
            ],
            [
                'report_type' => 'spam_message',
                'details'     => 'رسائل spam متكررة في الـ chat.',
                'status'      => 'pending',
            ],
        ];

        foreach ($reports as $reportData) {
            $reporter = $users->random();
            $reported = $users->where('id', '!=', $reporter->id)->random();

            Report::create([
                'reporter_id'  => $reporter->id,
                'reported_id'  => $reported->id,
                'report_type'  => $reportData['report_type'],
                'details'      => $reportData['details'],
                'status'       => $reportData['status'],
                'emergency_id' => $emergencies->random()->id,
                'reported_at'  => now()->subDays(rand(1, 15)),
                'processed_at' => in_array($reportData['status'], ['approved', 'rejected'])
                    ? now()->subDays(rand(1, 5))
                    : null,
                'processed_by' => in_array($reportData['status'], ['approved', 'rejected'])
                    ? $manager?->id
                    : null,
            ]);
        }

        $this->command->info('✅ Reports seeded successfully!');
    }
}