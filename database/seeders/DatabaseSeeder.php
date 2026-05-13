<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
  use WithoutModelEvents;

  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // 📍 LOCATION (KANTOR)
    $location = Location::create([
      'name' => 'Kantor Pusat',
      'latitude' => -6.200000, // contoh Jakarta
      'longitude' => 106.816666,
      'radius' => 50,
    ]);

    // 🕒 SCHEDULE
    $schedule = Schedule::create([
      'name' => 'Jam Kerja Normal',
      'check_in_time' => '08:00:00',
      'check_out_time' => '17:00:00',
      'is_weekend' => false,
    ]);

    // 👤 USER EMPLOYEE
    User::create([
      'name' => 'Ilham Jawaz',
      'username' => 'jaw.tkj',
      'email' => 'jaw@tkj.co.id',
      'password' => Hash::make('jaw@tkj123'),
      'role' => 'employee',
      'location_id' => $location->id,
      'schedule_id' => $schedule->id,
    ]);

    // 👑 USER ADMIN
    User::create([
      'name' => 'Admin',
      'username' => 'admin.tkj',
      'email' => 'admin@tanjungkaryajaya.co.id',
      'password' => Hash::make('T@njungkaryajaya_123'),
      'role' => 'admin',
      'location_id' => $location->id,
      'schedule_id' => $schedule->id,
    ]);
  }
}
