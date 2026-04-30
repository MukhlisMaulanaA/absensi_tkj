<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'name',
    'check_in_time',
    'check_out_time',
    'is_weekend',
  ];

  protected function casts(): array
  {
    return [
      'check_in_time' => 'datetime:H:i',
      'check_out_time' => 'datetime:H:i',
      'is_weekend' => 'boolean',
    ];
  }

  /*
  |--------------------------------------------------------------------------
  | RELATIONSHIPS
  |--------------------------------------------------------------------------
  */

  public function users()
  {
    return $this->hasMany(User::class);
  }
}