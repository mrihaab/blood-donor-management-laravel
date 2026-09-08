<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediction_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('snapshot_uuid')->unique();
            $table->string('scope_type')->default('FACILITY'); // FACILITY, REGION, NETWORK, BLOOD_BANK, HOSPITAL
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->string('blood_group');
            $table->integer('predicted_shortage_units');
            $table->decimal('confidence_score', 5, 4)->nullable();
            $table->string('ai_safety_state')->default('CONFIDENT');
            $table->string('decision_hash');
            $table->string('command_envelope_hash')->nullable();
            
            // Reproducibility Metadata & Signed Rule Artifact Hashes
            $table->string('model_artifact_hash')->nullable();
            $table->string('feature_schema_hash')->nullable();
            $table->string('usability_rule_hash')->nullable();
            $table->string('compatibility_rule_hash')->nullable();
            $table->string('sop_rule_hash')->nullable();
            $table->string('rule_set_hash')->nullable();
            $table->string('usability_rule_version')->default('1.0');
            $table->string('compatibility_policy_version')->default('1.0');
            $table->string('sop_version')->default('1.0');
            
            $table->json('features_snapshot')->nullable();
            $table->json('recommendations_payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['scope_type', 'scope_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_snapshots');
    }
};
