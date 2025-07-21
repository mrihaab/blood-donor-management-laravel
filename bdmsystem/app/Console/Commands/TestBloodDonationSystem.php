<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestBloodDonationSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:blood-system';

    /**
     * The console command description.
     */
    protected $description = 'Test the blood donation system functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Blood Donation Management System...');
        
        // Test 1: Check if blood groups exist
        $this->info('\n1. Checking blood groups...');
        $bloodGroups = \App\Models\BloodGroup::all();
        if ($bloodGroups->count() > 0) {
            $this->info('✓ Found ' . $bloodGroups->count() . ' blood groups');
            foreach ($bloodGroups as $group) {
                $this->line('  - ' . $group->name);
            }
        } else {
            $this->error('✗ No blood groups found. Run: php artisan db:seed --class=BloodGroupSeeder');
        }
        
        // Test 2: Check system tables
        $this->info('\n2. Checking database tables...');
        $tables = ['users', 'donors', 'blood_groups', 'donations', 'blood_inventory', 'appointments', 'blood_requests', 'activity_logs'];
        foreach ($tables as $table) {
            try {
                \DB::table($table)->count();
                $this->info('✓ Table: ' . $table);
            } catch (\Exception $e) {
                $this->error('✗ Table missing: ' . $table);
            }
        }
        
        // Test 3: Test service functionality
        $this->info('\n3. Testing BloodDonationService...');
        try {
            $service = new \App\Services\BloodDonationService();
            $availability = $service->getBloodAvailability();
            $this->info('✓ BloodDonationService is working');
            $this->info('Current blood availability: ' . count($availability) . ' types available');
        } catch (\Exception $e) {
            $this->error('✗ BloodDonationService error: ' . $e->getMessage());
        }
        
        // Test 4: Check routes
        $this->info('\n4. Checking important routes...');
        $routes = [
            'admin.dashboard',
            'admin.donors.index',
            'admin.appointments.index',
            'admin.inventory.index',
            'donor.dashboard',
            'donor.profile.edit'
        ];
        
        foreach ($routes as $routeName) {
            if (\Route::has($routeName)) {
                $this->info('✓ Route: ' . $routeName);
            } else {
                $this->error('✗ Route missing: ' . $routeName);
            }
        }
        
        $this->info('\n✅ Blood Donation Management System test completed!');
        $this->info('\nNext steps:');
        $this->info('1. Create admin user: php artisan tinker');
        $this->info('   User::create(["name"=>"Admin", "email"=>"admin@example.com", "password"=>bcrypt("password"), "role"=>"admin"]);');
        $this->info('2. Start development server: php artisan serve');
        $this->info('3. Visit: http://localhost:8000/login-as');
        
        return 0;
    }
}
