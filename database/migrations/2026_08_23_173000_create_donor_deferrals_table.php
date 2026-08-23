<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donor_deferrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained('donors')->onDelete('cascade');
            $table->enum('deferral_type', ['temporary', 'permanent']);
            $table->string('reason');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'expired', 'revoked'])->default('active');
            $table->timestamps();

            $table->index(['donor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donor_deferrals');
    }
};
