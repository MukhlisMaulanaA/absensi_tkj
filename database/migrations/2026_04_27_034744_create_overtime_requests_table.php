<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('overtime_requests', function (Blueprint $table) {
      $table->id();

      $table->foreignId('user_id')
        ->constrained()
        ->cascadeOnDelete();

      $table->timestamp('start_time');
      $table->timestamp('end_time');

      $table->text('description');

      $table->enum('status', ['pending', 'approved', 'rejected'])
        ->default('pending');

      $table->foreignId('approved_by')->nullable()
        ->constrained('users')
        ->nullOnDelete();

      $table->timestamps();
      $table->softDeletes();

      $table->index(['user_id', 'status']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('overtime_requests');
  }
};