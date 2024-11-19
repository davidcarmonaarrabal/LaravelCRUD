<?php

namespace Database\Seeders;

use App\Models\Diet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DietSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('diets')->insert([
            'title' => 'Dieta 1',
            'description' => 'Descripción de la dieta 1',
            'totalCalories' => 2000,
            'user_id' => 1
        ]);

        Diet::factory(3)->create();
    }
}
