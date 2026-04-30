<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'name',
    'latitude',
    'longitude',
    'radius',
  ];

  protected function casts(): array
  {
    return [
      'latitude' => 'float',
      'longitude' => 'float',
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

  public function attendances()
  {
    return $this->hasMany(Attendance::class);
  }
}