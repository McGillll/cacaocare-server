<?php

namespace Database\Seeders;

use DB;
use Hash;
use Illuminate\Database\Seeder;
use Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'id' => 1,
            'uuid' => (string) Str::uuid(),
            'email_verified_at' => now(),
            'username' => 'Admin',
            'email' => 'cacaocare.ph@gmail.com',
            'password' => Hash::make('CacaoCare_123'),
            'region' => 'Region XI (Davao Region)',
            'province' => 'Davao del Sur',
            'city' => 'City of Davao',
            'barangay' => 'Mintal',
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id' => 2,
            'uuid' => (string) Str::uuid(),
            'email_verified_at' => now(),
            'username' => 'JuanDelaCruz',
            'email' => 'juan.delacruz@example.com',
            'password' => Hash::make('CacaoCare_123'),
            'region' => 'Region XI (Davao Region)',
            'province' => 'Davao del Norte',
            'city' => 'Tagum City',
            'barangay' => 'Magugpo Poblacion',
            'profile' => 'https://randomuser.me/api/portraits/men/32.jpg',
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
