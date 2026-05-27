<?php

namespace App\Http\Controllers\Dashboard\Emergency;

use App\Http\Controllers\Controller;
use App\Models\EmergencyGroup;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index(Request $request, EmergencyGroup $emergencyGroup)
    {
        $ratings = Rating::query()
            ->where('group_id', $emergencyGroup->id)
            ->with(['rater', 'rated', 'history'])
            ->when($request->score,    fn($q) => $q->where('score', $request->score))
            ->when($request->rated_id, fn($q) => $q->where('rated_id', $request->rated_id))
            ->latest('rated_at')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $ratings->map(fn($r) => $this->formatRating($r)),
            'meta' => [
                'current_page' => $ratings->currentPage(),
                'last_page'    => $ratings->lastPage(),
                'total'        => $ratings->total(),
            ],
        ]);
    }

    public function stats(EmergencyGroup $emergencyGroup, User $user)
    {
        $ratings = Rating::where('group_id', $emergencyGroup->id)
            ->where('rated_id', $user->id)
            ->get();

        $total    = $ratings->count();
        $positive = $ratings->where('score', 'positive')->count();
        $negative = $ratings->where('score', 'negative')->count();

        return response()->json([
            'data' => [
                'user' => [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'is_ratable' => $user->is_ratable,
                ],
                'total'            => $total,
                'positive'         => $positive,
                'negative'         => $negative,
                'positive_percent' => $total > 0 ? round(($positive / $total) * 100, 1) : 0,
                'qualifies_admin'  => $total > 0 && ($positive / $total) >= 0.75,
            ],
        ]);
    }

    private function formatRating(Rating $r): array
    {
        return [
            'id'        => $r->id,
            'score'     => $r->score,
            'rated_at'  => $r->rated_at?->format('Y-m-d H:i'),
            'is_edited' => $r->is_edited,
            'edited_at' => $r->edited_at?->format('Y-m-d H:i'),

            'rater' => [
                'id'   => $r->rater->id,
                'name' => $r->rater->name,
            ],

            'rated' => [
                'id'   => $r->rated->id,
                'name' => $r->rated->name,
            ],

            'history' => $r->history->map(fn($h) => [
                'old_score'  => $h->old_score,
                'new_score'  => $h->new_score,
                'changed_at' => $h->changed_at?->format('Y-m-d H:i'),
            ]),
        ];
    }
}
