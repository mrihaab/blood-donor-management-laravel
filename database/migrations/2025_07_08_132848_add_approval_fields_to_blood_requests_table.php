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
        Schema::table('blood_requests', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            $table->timestamp('rejected_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('rejected_at');
            $table->unsignedBigInteger('assigned_donor_id')->nullable()->after('rejected_by');
            $table->timestamp('assigned_at')->nullable()->after('assigned_donor_id');
            $table->integer('units_needed')->default(1)->after('assigned_at');
            
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_donor_id')->references('id')->on('donors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blood_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropForeign(['assigned_donor_id']);
            
            $table->dropColumn([
                'approved_at',
                'approved_by',
                'rejected_at', 
                'rejected_by',
                'assigned_donor_id',
                'assigned_at',
                'units_needed'
            ]);
        });
    }
};
