<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Nullable deliberately preserves historical records without assigning an
            // inaccurate source to a check-out that happened before this feature.
            $table->string('check_out_source')->nullable()->after('check_out_time');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('check_out_source');
        });
    }
};
