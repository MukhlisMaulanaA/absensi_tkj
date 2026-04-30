<?php

namespace App\Services;

class GeoFenceService
{
  /**
   * Calculate distance between two coordinates (Haversine Formula)
   */
  public function calculateDistance($lat1, $lng1, $lat2, $lng2): float
  {
    $earthRadius = 6371000; // meters

    $latFrom = deg2rad($lat1);
    $lonFrom = deg2rad($lng1);
    $latTo = deg2rad($lat2);
    $lonTo = deg2rad($lng2);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(
      pow(sin($latDelta / 2), 2) +
      cos($latFrom) * cos($latTo) *
      pow(sin($lonDelta / 2), 2)
    ));

    return $angle * $earthRadius;
  }

  /**
   * Check if within radius
   */
  public function isWithinRadius($userLat, $userLng, $officeLat, $officeLng, $radius): bool
  {
    $distance = $this->calculateDistance($userLat, $userLng, $officeLat, $officeLng);

    return $distance <= $radius;
  }
}