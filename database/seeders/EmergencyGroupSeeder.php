<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmergencyGroup;
use App\Models\User;

class EmergencyGroupSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::where('email', 'emergency@admin.com')->first();

        $groups = [
            [
                'name'       => 'غروب دمشق - المزة',
                'center_lat' => 33.5138,
                'center_lng' => 36.2765,
                'radius_km'  => 5,
            ],
            [
                'name'       => 'غروب دمشق - كفرسوسة',
                'center_lat' => 33.5000,
                'center_lng' => 36.2800,
                'radius_km'  => 5,
            ],
            [
                'name'       => 'غروب دمشق - المالكي',
                'center_lat' => 33.5250,
                'center_lng' => 36.2900,
                'radius_km'  => 5,
            ],
            [
                'name'       => 'غروب حلب - العزيزية',
                'center_lat' => 36.2021,
                'center_lng' => 37.1343,
                'radius_km'  => 5,
            ],
            [
                'name'       => 'غروب حلب - الحمدانية',
                'center_lat' => 36.1800,
                'center_lng' => 37.1200,
                'radius_km'  => 5,
            ],
            [
                'name'       => 'غروب حمص - الوعر',
                'center_lat' => 34.7500,
                'center_lng' => 36.7000,
                'radius_km'  => 5,
            ],
        ];

        foreach ($groups as $group) {
            EmergencyGroup::create([
                ...$group,
                'is_active'  => true,
                'created_by' => $manager->id,
            ]);
        }

        $this->command->info('✅ Emergency Groups seeded successfully!');
    }
}