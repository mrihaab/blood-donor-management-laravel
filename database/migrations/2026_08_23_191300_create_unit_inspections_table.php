<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_unit_id')->constrained('blood_units')->onDelete('cascade');
            $table->foreignId('inspector_id')->constrained('users')->onDelete('cascade');
            $table->boolean('cold_chain_intact')->default(false);
            $table->boolean('seal_intact')->default(false);
            $table->integer('elapsed_time_minutes')->default(0);
            $table->boolean('visual_inspection_passed')->default(false);
            $table->string('decision'); // certified_safe, discarded
            $table->text('notes')->nullable();
            $table->timestamp('inspected_at');
            $table->timestamps();

            $table->index('blood_unit_id');
            $table->index('decision');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_inspections');
    }
};
