<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('locations', function (Blueprint $table) {
      $table->id();
      $table->string('name');

      $table->decimal('latitude', 10, 7);
      $table->decimal('longitude', 10, 7);
      $table->integer('radius')->default(50); // meter

      $table->timestamps();
      $table->softDeletes();

      $table->index(['latitude', 'longitude']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('locations');
  }
};