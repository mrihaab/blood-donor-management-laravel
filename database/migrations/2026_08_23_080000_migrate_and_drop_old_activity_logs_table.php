<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activity_logs')) {
            $oldLogs = DB::table('activity_logs')->get();

            foreach ($oldLogs as $oldLog) {
                DB::table('activity_log')->insert([
                    'log_name' => 'default',
                    'description' => $oldLog->message ?? $oldLog->description ?? 'Legacy Activity Log Entry',
                    'subject_type' => null,
                    'subject_id' => null,
                    'causer_type' => !empty($oldLog->user_id) ? \App\Models\User::class : null,
                    'causer_id' => $oldLog->user_id ?? null,
                    'properties' => json_encode([]),
                    'event' => 'legacy',
                    'batch_uuid' => null,
                    'created_at' => $oldLog->created_at ?? now(),
                    'updated_at' => $oldLog->updated_at ?? now(),
                ]);
            }

            Schema::dropIfExists('activity_logs');
        }
    }

    public function down(): void
    {
        // Re-create empty legacy table if rolled back
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }
};
