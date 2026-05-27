<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rating;
use App\Models\RatingHistory;
use App\Models\EmergencyGroup;
use App\Models\GroupMember;
use App\Models\User;

class RatingSeeder extends Seeder
{
    public function run(): void
    {
        $groups = EmergencyGroup::all();

        foreach ($groups as $group) {
            $members = GroupMember::where('group_id', $group->id)
                ->where('is_active', true)
                ->with('user')
                ->get();

            if ($members->count() < 2) continue;

            foreach ($members as $raterMember) {
                foreach ($members as $ratedMember) {
                    if ($raterMember->user_id === $ratedMember->user_id) continue;

                    // 70% فرصة للتقييم الإيجابي
                    $score = rand(1, 10) <= 7 ? 'positive' : 'negative';

                    $rating = Rating::firstOrCreate(
                        [
                            'group_id' => $group->id,
                            'rater_id' => $raterMember->user_id,
                            'rated_id' => $ratedMember->user_id,
                        ],
                        [
                            'score'    => $score,
                            'rated_at' => now()->subDays(rand(1, 30)),
                        ]
                    );

                    // 30% فرصة للتعديل
                    if ($rating->wasRecentlyCreated && rand(1, 10) <= 3) {
                        $newScore = $score === 'positive' ? 'negative' : 'positive';

                        RatingHistory::create([
                            'rating_id'  => $rating->id,
                            'old_score'  => $score,
                            'new_score'  => $newScore,
                            'changed_at' => now()->subDays(rand(1, 10)),
                        ]);

                        $rating->update([
                            'score'     => $newScore,
                            'is_edited' => true,
                            'edited_at' => now()->subDays(rand(1, 10)),
                        ]);
                    }
                }
            }
        }

        // تفعيل is_ratable لبعض المستخدمين
        User::inRandomOrder()->limit(8)->update(['is_ratable' => true]);

        $this->command->info('✅ Ratings seeded successfully!');
    }
}