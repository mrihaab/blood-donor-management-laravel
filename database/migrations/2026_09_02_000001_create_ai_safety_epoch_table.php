<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_safety_epoch', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('epoch_version')->default(1);
            $table->string('global_operational_mode')->default('NORMAL');
            $table->boolean('recovery_mode')->default(false);
            $table->boolean('deployment_mode')->default(false);
            $table->boolean('global_kill_switch')->default(false);
            $table->timestamp('updated_at')->useCurrent();
        });

        DB::table('ai_safety_epoch')->insert([
            'id' => 1,
            'epoch_version' => 1,
            'global_operational_mode' => 'NORMAL',
            'recovery_mode' => false,
            'deployment_mode' => false,
            'global_kill_switch' => false,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_safety_epoch');
    }
};
