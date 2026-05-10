<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use \Illuminate\Notifications\Notifiable;


class OvertimeRequest extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'user_id',
    'start_time',
    'end_time',
    'description',
    'status',
    'approved_by',
  ];

  protected $casts = [
    'start_time' => 'datetime',
    'end_time' => 'datetime',
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
}