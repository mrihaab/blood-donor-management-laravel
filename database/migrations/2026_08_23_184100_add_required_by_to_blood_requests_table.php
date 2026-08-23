<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blood_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('blood_requests', 'urgency_level')) {
                $table->string('urgency_level')->default('routine')->after('status');
            }
            if (!Schema::hasColumn('blood_requests', 'required_by')) {
                $table->dateTime('required_by')->nullable()->after('urgency_level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blood_requests', function (Blueprint $table) {
            if (Schema::hasColumn('blood_requests', 'urgency_level')) {
                $table->dropColumn('urgency_level');
            }
            if (Schema::hasColumn('blood_requests', 'required_by')) {
                $table->dropColumn('required_by');
            }
        });
    }
};
