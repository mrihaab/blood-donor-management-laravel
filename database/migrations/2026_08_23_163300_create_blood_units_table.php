<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_number')->unique(); // ISBT-128 barcode serial number
            $table->foreignId('blood_group_id')->constrained('blood_groups')->onDelete('cascade');
            $table->foreignId('component_id')->constrained('blood_components')->onDelete('cascade');
            $table->foreignId('donor_id')->nullable()->constrained('donors')->onDelete('set null');
            $table->foreignId('donation_id')->nullable()->constrained('donations')->onDelete('set null');
            $table->date('collection_date');
            $table->date('expiry_date');
            $table->integer('volume_ml')->default(450);
            $table->string('storage_location')->default('Main Refrigerator Shelf A');
            $table->enum('status', ['available', 'reserved', 'allocated', 'dispensed', 'expired', 'discarded'])->default('available');
            $table->timestamps();

            // Compound performance index for stock queries
            $table->index(['blood_group_id', 'component_id', 'status', 'expiry_date'], 'idx_blood_units_stock_query');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_units');
    }
};
