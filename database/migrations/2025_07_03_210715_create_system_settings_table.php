<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, json, boolean, integer
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // Insert default settings
        DB::table('system_settings')->insert([
            ['key' => 'organization_name', 'value' => 'Blood Donation Management System', 'type' => 'string', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'organization_logo', 'value' => null, 'type' => 'string', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'organization_email', 'value' => 'admin@bdms.com', 'type' => 'string', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'organization_phone', 'value' => '+1-234-567-8900', 'type' => 'string', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'organization_address', 'value' => '123 Health St, Medical City', 'type' => 'string', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'low_stock_threshold', 'value' => '10', 'type' => 'integer', 'group' => 'inventory', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'donation_interval_days', 'value' => '56', 'type' => 'integer', 'group' => 'donation', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'email_notifications', 'value' => 'true', 'type' => 'boolean', 'group' => 'notifications', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'sms_notifications', 'value' => 'false', 'type' => 'boolean', 'group' => 'notifications', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'cities', 'value' => json_encode(['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix']), 'type' => 'json', 'group' => 'locations', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
