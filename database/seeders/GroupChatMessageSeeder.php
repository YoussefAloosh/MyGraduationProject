<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GroupChatMessage;
use App\Models\EmergencyGroup;
use App\Models\Emergency;
use App\Models\User;

class GroupChatMessageSeeder extends Seeder
{
    public function run(): void
    {
        $groups     = EmergencyGroup::all();
        $users      = User::all();
        $emergencies = Emergency::all();

        $normalMessages = [
            'هل هناك أي حالات طارئة اليوم؟',
            'الوضع هادئ في المنطقة.',
            'تم التحقق من الموقع، لا يوجد شيء.',
            'شكراً لكم على التعاون.',
            'هل يمكن أحد المرور على الشارع الرئيسي؟',
            'كل شيء على ما يرام هنا.',
        ];

        $emergencyMessages = [
            'أنا في الطريق إلى الموقع.',
            'وصلت إلى الموقع، الوضع تحت السيطرة.',
            'نحتاج مزيداً من المساعدة هنا.',
            'تم التعامل مع الحالة بنجاح.',
            'المصاب بأمان الآن.',
            'نحتاج سيارة إسعاف بسرعة.',
        ];

        foreach ($groups as $group) {
            $groupUsers = $users->random(min(4, $users->count()));

            // رسائل عادية
            foreach ($normalMessages as $i => $content) {
                GroupChatMessage::create([
                    'group_id'          => $group->id,
                    'sender_id'         => $groupUsers->random()->id,
                    'content'           => $content,
                    'sent_at'           => now()->subHours(rand(1, 48)),
                    'is_emergency_mode' => false,
                    'is_reported_spam'  => $i === 0,
                ]);
            }

            // رسائل وقت طوارئ
            $groupEmergency = $emergencies->where('target_group_id', $group->id)->first();
            if ($groupEmergency) {
                foreach ($emergencyMessages as $content) {
                    GroupChatMessage::create([
                        'group_id'          => $group->id,
                        'sender_id'         => $groupUsers->random()->id,
                        'emergency_id'      => $groupEmergency->id,
                        'content'           => $content,
                        'sent_at'           => now()->subMinutes(rand(1, 60)),
                        'is_emergency_mode' => true,
                        'is_reported_spam'  => false,
                    ]);
                }
            }
        }

        $this->command->info('✅ Group Chat Messages seeded successfully!');
    }
}