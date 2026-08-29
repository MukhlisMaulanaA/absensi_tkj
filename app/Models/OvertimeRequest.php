<?php

namespace App\Models;

use App\Services\OvertimeCalculationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class OvertimeRequest extends Model
{
  use HasFactory, SoftDeletes;

  // Disable auto-increment because the primary key will be generated manually.
  public $incrementing = false;

  // Set the primary key data type to string.
  protected $keyType = 'string';

  protected $fillable = [
    'id',
    'user_id',
    'start_time',
    'end_time',
    'description',
    'image',
    'status',
    'approved_by',
    'overtime_days',
    'latitude',
    'longitude',
  ];

  protected $casts = [
    'start_time' => 'datetime',
    'end_time' => 'datetime',
    'image' => 'array',
    'latitude' => 'float',
    'longitude' => 'float',
  ];

  protected static function booted()
  {
    static::creating(function ($model) {
      if (empty($model->id)) {
        $year = date('y');
        $monthsMap = [
          1 => 'A',
          2 => 'B',
          3 => 'C',
          4 => 'D',
          5 => 'E',
          6 => 'F',
          7 => 'G',
          8 => 'H',
          9 => 'I',
          10 => 'J',
          11 => 'K',
          12 => 'L'
        ];
        $monthLetter = $monthsMap[(int) date('n')];
        $prefix = 'OV' . $year . $monthLetter;

        // 1. PENTING: Gunakan withTrashed() agar ID data yang di-soft delete tetap terbaca
        $lastRecord = static::withTrashed()
          ->where('id', 'like', $prefix . '%')
          ->orderBy('id', 'desc')
          ->first();

        if ($lastRecord) {
          $lastNumber = (int) substr($lastRecord->id, -4);
          $nextNumber = $lastNumber + 1;
        } else {
          $nextNumber = 1;
        }

        // 2. Loop pengaman: Pastikan candidate ID benar-benar belum terpakai di DB
        do {
          $candidateId = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
          $exists = static::withTrashed()->where('id', $candidateId)->exists();
          if ($exists) {
            $nextNumber++;
          }
        } while ($exists);

        $model->id = $candidateId;
      }
    });
  }
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