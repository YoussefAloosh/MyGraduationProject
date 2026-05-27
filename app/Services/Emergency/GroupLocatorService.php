<?php

namespace App\Services\Emergency;

use App\Models\EmergencyGroup;

class GroupLocatorService
{
    public function locate(float $lat, float $lng): ?EmergencyGroup
    {
        $groups = EmergencyGroup::active()->get();

        if ($groups->isEmpty()) {
            return null;
        }

        $containing = $groups
            ->filter(fn (EmergencyGroup $group) => $group->containsLocation($lat, $lng))
            ->sortBy(fn (EmergencyGroup $group) => $group->distanceTo($lat, $lng));

        if ($containing->isNotEmpty()) {
            return $containing->first();
        }

        return $groups->sortBy(fn (EmergencyGroup $group) => $group->distanceTo($lat, $lng))->first();
    }
}
