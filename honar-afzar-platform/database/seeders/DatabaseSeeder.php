<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed products
        $this->call(ProductSeeder::class);

        // Seed roles and permissions
        $this->call(RoleSeeder::class);

        // Create default organization
        $organization = Organization::create([
            'name' => 'هنر افزار ایرانیان',
            'slug' => 'honar-afzar-iranian',
            'registration_number' => '94014',
            'national_id' => '10320045678',
            'address' => 'تهران، خیابان ولیعصر',
            'city' => 'تهران',
            'state' => 'تهران',
            'phone' => '02112345678',
            'email' => 'info@honarafzar.ir',
            'status' => 'active',
        ]);

        // Create super admin user
        $superAdmin = User::create([
            'name' => 'مدیر سیستم',
            'email' => 'admin@honarafzar.ir',
            'password' => Hash::make('password'),
            'organization_id' => $organization->id,
            'phone' => '09121234567',
            'is_active' => true,
        ]);

        $superAdmin->assignRole('super-admin');

        // Create org admin user
        $orgAdmin = User::create([
            'name' => 'مدیر سازمان',
            'email' => 'orgadmin@honarafzar.ir',
            'password' => Hash::make('password'),
            'organization_id' => $organization->id,
            'phone' => '09129876543',
            'is_active' => true,
        ]);

        $orgAdmin->assignRole('org-admin');

        // Activate all products for the organization
        $products = \App\Models\Product::all();
        foreach ($products as $product) {
            $organization->products()->attach($product->id, [
                'is_active' => true,
                'activated_at' => now(),
            ]);
        }
    }
}
