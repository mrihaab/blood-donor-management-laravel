<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_unit_id')->nullable()->constrained('blood_units')->onDelete('cascade');
            $table->foreignId('blood_group_id')->constrained('blood_groups')->onDelete('cascade');
            $table->foreignId('component_id')->nullable()->constrained('blood_components')->onDelete('set null');
            $table->enum('transaction_type', ['received', 'reserved', 'allocated', 'dispensed', 'expired', 'discarded', 'adjusted']);
            $table->integer('previous_quantity');
            $table->integer('quantity_changed');
            $table->integer('resulting_quantity');
            $table->string('reference_type')->nullable(); // Model class name e.g. App\Models\BloodRequest
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('reason');
            $table->timestamps();

            $table->index(['transaction_type', 'created_at'], 'idx_inv_trans_type_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
