<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Setting::updateOrCreate(
            ['key' => 'app_name'],
            ['value' => 'Muazara-App']
        );

        \App\Models\Setting::updateOrCreate(
            ['key' => 'address'],
            ['value' => 'Desa Cermee RT. 15 No.1, Cermee, Bondowoso']
        );

        \App\Models\Setting::updateOrCreate(
            ['key' => 'phone_number'],
            ['value' => '0857-0895-4067']
        );
    }
}
