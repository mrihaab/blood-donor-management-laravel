<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_request_id')->constrained('blood_requests')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('hospital_id')->constrained('hospitals')->onDelete('cascade');
            $table->foreignId('administered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, issued, started, completed, stopped, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('hospital_id');
            $table->index('patient_id');
            $table->index('blood_request_id');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfusions');
    }
};
