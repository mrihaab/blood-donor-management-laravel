<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Drop extra columns if they exist
            if (Schema::hasColumn('activity_logs', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('activity_logs', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('activity_logs', 'action')) {
                $table->dropColumn('action');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
