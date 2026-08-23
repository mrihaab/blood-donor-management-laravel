<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('storage_temperature');
            $table->integer('shelf_life_days');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // Seed default 5 medical components
        DB::table('blood_components')->insert([
            [
                'name' => 'Whole Blood',
                'code' => 'WB',
                'storage_temperature' => '2°C - 6°C',
                'shelf_life_days' => 35,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Packed Red Blood Cells',
                'code' => 'PRBC',
                'storage_temperature' => '2°C - 6°C',
                'shelf_life_days' => 42,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Platelets',
                'code' => 'PLT',
                'storage_temperature' => '20°C - 24°C',
                'shelf_life_days' => 5,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fresh Frozen Plasma',
                'code' => 'FFP',
                'storage_temperature' => '-18°C or colder',
                'shelf_life_days' => 365,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cryoprecipitate',
                'code' => 'CRYO',
                'storage_temperature' => '-18°C or colder',
                'shelf_life_days' => 365,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_components');
    }
};
