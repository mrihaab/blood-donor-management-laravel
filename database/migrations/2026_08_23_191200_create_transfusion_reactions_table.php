<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfusion_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfusion_id')->constrained('transfusions')->onDelete('cascade');
            $table->foreignId('blood_unit_id')->nullable()->constrained('blood_units')->onDelete('set null');
            $table->string('reaction_type');
            $table->string('severity'); // mild, moderate, severe, life_threatening
            $table->text('symptoms');
            $table->timestamp('onset_at');
            $table->timestamp('reported_at');
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->text('action_taken')->nullable();
            $table->string('outcome')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('transfusion_id');
            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfusion_reactions');
    }
};
