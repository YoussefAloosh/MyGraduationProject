<?php

namespace App\Services\Emergency;

/**
 * Geographic circle utilities for official & pending emergency groups.
 */
class GeoCircleHelper
{
    public const MIN_RADIUS_KM = 5.0;

    public const EARTH_RADIUS_KM = 6371.0;

    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public static function pointInsideCircle(
        float $lat,
        float $lng,
        float $centerLat,
        float $centerLng,
        float $radiusKm,
    ): bool {
        $radius = max($radiusKm, self::MIN_RADIUS_KM);

        return self::haversineKm($lat, $lng, $centerLat, $centerLng) <= $radius;
    }

    /**
     * Arithmetic mean of coordinate pairs.
     *
     * @param  array<int, array{lat: float, lng: float}>  $points
     * @return array{lat: float, lng: float}
     */
    public static function centroid(array $points): array
    {
        if ($points === []) {
            return ['lat' => 0.0, 'lng' => 0.0];
        }

        $count = count($points);
        $lat   = array_sum(array_column($points, 'lat')) / $count;
        $lng   = array_sum(array_column($points, 'lng')) / $count;

        return ['lat' => $lat, 'lng' => $lng];
    }

    /**
     * Radius = max distance from center to any point, with minimum 5 km.
     *
     * @param  array<int, array{lat: float, lng: float}>  $points
     */
    public static function radiusFromPoints(float $centerLat, float $centerLng, array $points): float
    {
        $max = self::MIN_RADIUS_KM;

        foreach ($points as $point) {
            $max = max($max, self::haversineKm($centerLat, $centerLng, $point['lat'], $point['lng']));
        }

        return $max;
    }

    /**
     * @param  array<int, array{lat: float, lng: float}>  $points
     */
    public static function allPointsInside(float $centerLat, float $centerLng, float $radiusKm, array $points): bool
    {
        foreach ($points as $point) {
            if (! self::pointInsideCircle($point['lat'], $point['lng'], $centerLat, $centerLng, $radiusKm)) {
                return false;
            }
        }

        return true;
    }
}
