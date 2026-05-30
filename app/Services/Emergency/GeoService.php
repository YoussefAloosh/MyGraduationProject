<?php

namespace App\Services\Emergency;

/**
 * Geographic helpers — haversine, centroid, radius, point-in-circle.
 */
class GeoService
{
    public const MIN_RADIUS_KM = 5.0;

    public const MIN_PENDING_MEMBERS = 5;

    public static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371.0;
        $dLat        = deg2rad($lat2 - $lat1);
        $dLng        = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public static function pointInsideCircle(
        float $lat,
        float $lng,
        float $centerLat,
        float $centerLng,
        float $radiusKm,
    ): bool {
        return self::haversine($lat, $lng, $centerLat, $centerLng) <= $radiusKm;
    }

    /**
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
     * Max distance from center to any point, enforced minimum radius.
     *
     * @param  array<int, array{lat: float, lng: float}>  $points
     */
    public static function boundingRadiusKm(float $centerLat, float $centerLng, array $points): float
    {
        $max = 0.0;

        foreach ($points as $point) {
            $max = max($max, self::haversine($centerLat, $centerLng, $point['lat'], $point['lng']));
        }

        return max($max, self::MIN_RADIUS_KM);
    }

    /**
     * @param  array<int, array{lat: float, lng: float}>  $points
     */
    public static function allPointsInsideCircle(
        array $points,
        float $centerLat,
        float $centerLng,
        float $radiusKm,
    ): bool {
        foreach ($points as $point) {
            if (! self::pointInsideCircle($point['lat'], $point['lng'], $centerLat, $centerLng, $radiusKm)) {
                return false;
            }
        }

        return true;
    }
}
