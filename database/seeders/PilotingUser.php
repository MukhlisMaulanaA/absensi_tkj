<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PilotingUser extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Pilot phase test users
    User::create([
      'name' => 'Rahel Naufal P',
      'username' => 'rahel.tkj',
      'email' => 'rahel.naufal@tkj.co.id',
      'jabatan' => 'Testing Pilot',
      'password' => Hash::make('rahel@tkj123'),
      'role' => 'employee',
    ]);

    User::create([
      'name' => 'Aura Assyifa\'ul Ma\'rifah',
      'username' => 'aura.assyifaul',
      'email' => 'aura.assyifaul@tkj.co.id',
      'jabatan' => 'Testing Pilot',
      'password' => Hash::make('aura@tkj123'),
      'role' => 'employee',
    ]);
  }
}
