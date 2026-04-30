<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('schedules', function (Blueprint $table) {
      $table->id();
      $table->string('name');

      $table->time('check_in_time');
      $table->time('check_out_time');

      $table->boolean('is_weekend')->default(false);

      $table->timestamps();
      $table->softDeletes();

      $table->index(['is_weekend']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('schedules');
  }
};