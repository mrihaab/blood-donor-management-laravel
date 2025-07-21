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
        Schema::create('blood_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_group_id')->constrained('blood_groups')->onDelete('cascade');
            $table->integer('quantity'); // in mL
            $table->date('expiry_date');
            $table->string('location')->default('Main Storage');
            $table->enum('status', ['available', 'expired', 'used'])->default('available');
            $table->foreignId('donor_id')->nullable()->constrained('donors')->onDelete('set null');
            $table->date('collection_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blood_inventory');
    }
};
