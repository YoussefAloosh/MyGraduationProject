<?php

namespace App\Http\Controllers\Api\Emergency;

use App\Http\Controllers\Controller;
use App\Models\EmergencyGroup;
use App\Models\User;
use App\Services\Emergency\RatingService;
use Illuminate\Http\Request;

class AppRatingController extends Controller
{
    public function __construct(
        private readonly RatingService $ratingService,
    ) {}

    /**
     * POST /emergency/ratings
     * Body: { "group_id": 1, "rated_id": 5, "score": "positive" | "negative" }
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'group_id' => 'required|integer|exists:emergency_groups,id',
            'rated_id' => 'required|integer|exists:users,id',
            'score'    => 'required|in:positive,negative',
        ]);

        $group = EmergencyGroup::findOrFail($data['group_id']);
        $rated = User::findOrFail($data['rated_id']);

        $rating = $this->ratingService->rate($group, $request->user(), $rated, $data['score']);

        return response()->json([
            'message' => 'Rating submitted.',
            'data' => [
                'id'        => $rating->id,
                'score'     => $rating->score,
                'is_edited' => $rating->is_edited,
                'rated_at'  => $rating->rated_at?->format('Y-m-d H:i'),
            ],
        ], 201);
    }
}
