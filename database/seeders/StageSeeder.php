<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stage;

class StageSeeder extends Seeder
{
    public function run(): void
    {
        $hr = Stage::create([
            'name' => 'HR Review',
            'role' => 'hr',
            'order' => 1,
            'min_days' => 0,
            'next_stage_id' => null,
        ]);

        $manager = Stage::create([
            'name' => 'Manager Review',
            'role' => 'manager',
            'order' => 2,
            'min_days' => 0,
            'next_stage_id' => null,
        ]);

        $ceo = Stage::create([
            'name' => 'CEO Approval',
            'role' => 'ceo',
            'order' => 3,
            'min_days' => 5,
            'next_stage_id' => null,
        ]);

        $hr->next_stage_id = $manager->id;
        $hr->save();

        $manager->next_stage_id = $ceo->id;
        $manager->save();
    }
}
