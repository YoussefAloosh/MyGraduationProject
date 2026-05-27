<?php

namespace App\Services\Emergency;

use App\Models\EmergencyGroup;
use App\Models\GroupMember;
use App\Models\Rating;
use App\Models\RatingHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RatingService
{
    /**
     * Create or update a rating. Saves history on edit.
     * Only active members of the same group can rate.
     */
    public function rate(EmergencyGroup $group, User $rater, User $rated, string $score): Rating
    {
        if ($rater->id === $rated->id) {
            throw ValidationException::withMessages([
                'rated_id' => ['You cannot rate yourself.'],
            ]);
        }

        if (! $rated->is_ratable) {
            throw ValidationException::withMessages([
                'rated_id' => ['This user has not enabled ratings.'],
            ]);
        }

        $this->assertActiveMember($group, $rater, 'You must be an active member to rate.');
        $this->assertActiveMember($group, $rated, 'The user you are trying to rate is not an active member.');

        return DB::transaction(function () use ($group, $rater, $rated, $score) {

            $existing = Rating::where([
                'group_id' => $group->id,
                'rater_id' => $rater->id,
                'rated_id' => $rated->id,
            ])->first();

            if ($existing) {
                // No change
                if ($existing->score === $score) {
                    return $existing;
                }

                RatingHistory::create([
                    'rating_id'  => $existing->id,
                    'old_score'  => $existing->score,
                    'new_score'  => $score,
                    'changed_at' => now(),
                ]);

                $existing->update([
                    'score'     => $score,
                    'is_edited' => true,
                    'edited_at' => now(),
                ]);

                return $existing->fresh();
            }

            return Rating::create([
                'group_id' => $group->id,
                'rater_id' => $rater->id,
                'rated_id' => $rated->id,
                'score'    => $score,
                'rated_at' => now(),
            ]);
        });
    }

    private function assertActiveMember(EmergencyGroup $group, User $user, string $message): void
    {
        $isMember = GroupMember::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->where('membership_status', 'active')
            ->exists();

        if (! $isMember) {
            throw ValidationException::withMessages(['group' => [$message]]);
        }
    }
}
