<?php

namespace App\Models;

use \Illuminate\Notifications\Notifiable;
use App\Services\OvertimeCalculationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class OvertimeRequest extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'user_id',
    'start_time',
    'end_time',
    'description',
    'image',
    'status',
    'approved_by',
    'overtime_days',
  ];

  protected $casts = [
    'start_time' => 'datetime',
    'end_time' => 'datetime',
    'image' => 'array',
  ];
  /*
  |--------------------------------------------------------------------------
  | RELATIONSHIPS
  |--------------------------------------------------------------------------
  */

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function approver()
  {
    return $this->belongsTo(User::class, 'approved_by');
  }

  /*
  |--------------------------------------------------------------------------
  | ACCESSORS
  |--------------------------------------------------------------------------
  */

  public function getDurationHoursAttribute(): float
  {
    return round(
      Carbon::parse($this->start_time)
        ->diffInMinutes(Carbon::parse($this->end_time)) / 60,
      2
    );
  }

  /*
  |--------------------------------------------------------------------------
  | HELPERS
  |--------------------------------------------------------------------------
  */

  public function isApproved(): bool
  {
    return $this->status === 'approved';
  }

  public function isPending(): bool
  {
    return $this->status === 'pending';
  }

  public function isRejected(): bool
  {
    return $this->status === 'rejected';
  }

  /*
  |--------------------------------------------------------------------------
  | OVERTIME CALCULATION
  |--------------------------------------------------------------------------
  */

  /**
   * Calculate and update overtime_days if not already set
   */
  public function calculateOvertimeDays(): int
  {
    if ($this->start_time && $this->end_time) {
      $days = OvertimeCalculationService::calculateOvertimeDays(
        Carbon::parse($this->start_time),
        Carbon::parse($this->end_time)
      );
      
      if (!$this->overtime_days) {
        $this->overtime_days = $days;
      }
      
      return $days;
    }

    return 1; 
  }

  /**
   * Get formatted overtime days description
   */
  public function getOvertimeDaysLabel(): string
  {
    $days = $this->overtime_days ?? $this->calculateOvertimeDays();
    return OvertimeCalculationService::getOvertimeDayDescription($days);
  }

  /**
   * Get complete overtime calculation details
   */
  public function getCalculationDetails(): array
  {
    return OvertimeCalculationService::getOvertimeCalculationDetails(
      Carbon::parse($this->start_time),
      Carbon::parse($this->end_time)
    );
  }
}