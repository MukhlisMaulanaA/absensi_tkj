<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Attendance extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'user_id',
    'location_id',
    'status',
    'reason',

    'check_in_time',
    'check_in_latitude',
    'check_in_longitude',
    'check_in_photo',

    'check_out_time',
    'check_out_latitude',
    'check_out_longitude',
    'check_out_photo',

    'late_minutes',
    'is_within_radius',
  ];

  protected function casts(): array
  {
    return [
      'check_in_time' => 'datetime',
      'check_out_time' => 'datetime',
      'is_within_radius' => 'boolean',
    ];
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

  public function location()
  {
    return $this->belongsTo(Location::class);
  }

  /*
  |--------------------------------------------------------------------------
  | ACCESSORS
  |--------------------------------------------------------------------------
  */

  public function getWorkingHoursAttribute(): ?float
  {
    if (!$this->check_in_time || !$this->check_out_time) {
      return null;
    }

    return round(
      Carbon::parse($this->check_in_time)
        ->diffInMinutes(Carbon::parse($this->check_out_time)) / 60,
      2
    );
  }

  public function getStatusAttribute(): string
  {
    if (!$this->check_in_time) {
      return 'absent';
    }

    if ($this->late_minutes > 0) {
      return 'late';
    }

    return 'on_time';
  }

  /*
  |--------------------------------------------------------------------------
  | BUSINESS LOGIC
  |--------------------------------------------------------------------------
  */

  public function calculateLateMinutes(): int
  {
    if (!$this->user || !$this->user->schedule || !$this->check_in_time) {
      return 0;
    }

    $scheduleTime = Carbon::parse($this->user->schedule->check_in_time);
    $checkIn = Carbon::parse($this->check_in_time);

    return max(0, $scheduleTime->diffInMinutes($checkIn, false));
  }
}