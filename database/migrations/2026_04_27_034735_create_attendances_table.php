<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('attendances', function (Blueprint $table) {
      $table->id();

      $table->foreignId('user_id')
        ->constrained()
        ->cascadeOnDelete();

      $table->foreignId('location_id')->nullable()
        ->constrained()
        ->nullOnDelete();

      // CHECK IN
      $table->timestamp('check_in_time')->nullable();
      $table->decimal('check_in_latitude', 10, 7)->nullable();
      $table->decimal('check_in_longitude', 10, 7)->nullable();
      $table->string('check_in_photo')->nullable();

      // CHECK OUT
      $table->timestamp('check_out_time')->nullable();
      $table->decimal('check_out_latitude', 10, 7)->nullable();
      $table->decimal('check_out_longitude', 10, 7)->nullable();
      $table->string('check_out_photo')->nullable();

      // VALIDATION
      $table->integer('late_minutes')->default(0);
      $table->boolean('is_within_radius')->default(true);

      $table->timestamps();
      $table->softDeletes();

      $table->index(['user_id', 'check_in_time']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('attendances');
  }
};