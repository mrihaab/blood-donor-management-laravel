<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donor_screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('cascade');
            $table->foreignId('donor_id')->constrained('donors')->onDelete('cascade');
            $table->foreignId('screened_by')->constrained('users')->onDelete('cascade');
            $table->string('blood_pressure')->nullable();
            $table->integer('pulse')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->decimal('weight', 5, 1)->nullable();
            $table->decimal('hemoglobin', 4, 1)->nullable();
            $table->enum('status', ['eligible', 'temporarily_deferred', 'permanently_deferred', 'rejected'])->default('eligible');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['appointment_id', 'donor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donor_screenings');
    }
};
