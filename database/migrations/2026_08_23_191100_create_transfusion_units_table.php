<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfusion_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfusion_id')->constrained('transfusions')->onDelete('cascade');
            $table->foreignId('blood_unit_id')->constrained('blood_units')->onDelete('cascade');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('disposition')->default('issued'); // issued, transfused, returned_unused, discarded
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['transfusion_id', 'blood_unit_id']);
            $table->index('transfusion_id');
            $table->index('blood_unit_id');
            $table->index('disposition');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfusion_units');
    }
};
