<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'location_id',
        'schedule_id',
        'device_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function overtimeRequests()
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    public function approvedOvertimes()
    {
        return $this->hasMany(OvertimeRequest::class, 'approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | FILAMENT ACCESS
    |--------------------------------------------------------------------------
    */

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }
}