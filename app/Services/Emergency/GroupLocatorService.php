<?php

namespace App\Services\Emergency;

use App\Models\EmergencyGroup;
use Illuminate\Support\Collection;

class GroupLocatorService
{
    /**
     * Official groups whose circle contains the point (strict — no nearest fallback).
     */
    public function officialGroupsContaining(float $lat, float $lng): Collection
    {
        return EmergencyGroup::active()
            ->get()
            ->filter(fn (EmergencyGroup $group) => $group->containsLocation($lat, $lng))
            ->sortBy(fn (EmergencyGroup $group) => $group->distanceTo($lat, $lng))
            ->values();
    }

    /**
     * SOS / emergency targeting: prefer containing group, else nearest active group.
     */
    public function locateForEmergency(float $lat, float $lng): ?EmergencyGroup
    {
        $containing = $this->officialGroupsContaining($lat, $lng);

        if ($containing->isNotEmpty()) {
            return $containing->first();
        }

        $groups = EmergencyGroup::active()->get();

        if ($groups->isEmpty()) {
            return null;
        }

        return $groups->sortBy(fn (EmergencyGroup $group) => $group->distanceTo($lat, $lng))->first();
    }
}
