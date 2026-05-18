<?php

namespace App\Services;

use Carbon\Carbon;

class OvertimeCalculationService
{
  /**
   * Calculate overtime days based on company regulations.
   * 
   * Regulations:
   * 1. Overtime is calculated from 16:00
   * 2. If overtime continues until 02:00 (early hours of following day) = 2 days' overtime
   * 3. If overtime continues until 06:00 (morning of following day) = 3 days' overtime
   * 4. Pattern continues: each subsequent threshold adds 1 more day
   * 
   * @param Carbon $startTime
   * @param Carbon $endTime
   * @return int
   */
  public static function calculateOvertimeDays(Carbon $startTime, Carbon $endTime): int
  {
    // Normalize times to handle multiple days
    $start = $startTime->copy();
    $end = $endTime->copy();

    // If end is before or equal to start, invalid
    if ($end->lte($start)) {
      return 0;
    }

    // Get hour of end time
    $endHour = $end->hour;
    $startHour = $start->hour;
    $startDate = $start->format('Y-m-d');
    $endDate = $end->format('Y-m-d');

    // Calculate difference in days
    $daysDifference = $start->diffInDays($end, false); // false = without absolute

    // Default overtime = 1 day
    $overtimeDays = 0;

    // If it spans to the next day
    if ($daysDifference > 0) {
      // Check the hour of end time on the next day
      if ($endHour >= 6) {
        // Reaches 06:00 or beyond morning = 3 days
        $overtimeDays = 3;
      } elseif ($endHour >= 2) {
        // Reaches 02:00 to 05:59 (early to mid-morning) = 3 days
        $overtimeDays = 2;
      } else {
        // 00:00 to 01:59 (early hours) = 2 days
        $overtimeDays = 0;
      }

      // If it spans multiple days (2+ days difference)
      if ($daysDifference >= 2) {
        // Add 1 day for each additional full day
        $overtimeDays += ($daysDifference - 1);
      }
    }

    return max(1, $overtimeDays);
  }

  /**
   * Get overtime day threshold description
   * 
   * @param int $overtimeDays
   * @return string
   */
  public static function getOvertimeDayDescription(int $overtimeDays): string
  {
    return match ($overtimeDays) {
      1 => '1 Hari',
      2 => '2 Hari',
      3 => '3 Hari',
      default => $overtimeDays . ' Hari',
    };
  }

  /**
   * Format overtime calculation for display
   * 
   * @param Carbon $startTime
   * @param Carbon $endTime
   * @return array
   */
  public static function getOvertimeCalculationDetails(Carbon $startTime, Carbon $endTime): array
  {
    $overtimeDays = self::calculateOvertimeDays($startTime, $endTime);

    return [
      'start_time' => $startTime->format('d M Y H:i'),
      'end_time' => $endTime->format('d M Y H:i'),
      'overtime_days' => $overtimeDays,
      'description' => self::getOvertimeDayDescription($overtimeDays),
      'hours' => $startTime->diffInHours($endTime),
    ];
  }
}