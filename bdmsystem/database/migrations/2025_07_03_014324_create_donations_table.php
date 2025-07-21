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
      Schema::create('donations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('donor_id')->constrained()->onDelete('cascade');
    $table->foreignId('blood_group_id')->constrained('blood_groups');
    $table->integer('quantity');
    $table->string('status'); // e.g. "available", "used", etc.
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
