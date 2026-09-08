<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediction_snapshot_lineage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('snapshot_id')->unique();
            $table->unsignedBigInteger('parent_snapshot_id')->nullable();
            $table->boolean('is_leaf')->default(true);
            $table->unsignedBigInteger('lineage_version')->default(1);
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED, EXPIRED, SUPERSEDED, ABORTED
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('approval_nonce')->nullable()->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('snapshot_id')->references('id')->on('prediction_snapshots')->onDelete('cascade');
            $table->foreign('parent_snapshot_id')->references('id')->on('prediction_snapshots')->onDelete('set null');
            $table->index(['is_leaf', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_snapshot_lineage');
    }
};
