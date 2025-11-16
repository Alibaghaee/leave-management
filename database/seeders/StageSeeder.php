<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stage;

class StageSeeder extends Seeder
{
    public function run(): void
    {

        $hrStage = Stage::create([
            'name' => 'HR Review',
            'role' => 'hr',
            'order' => 1,
            'days_min' => 1,
            'next_stage_id' => null
        ]);
        $managerStage = Stage::create([
            'name' => 'Manager Review',
            'role' => 'manager',
            'order' => 2,
            'days_min' => 0,
            'next_stage_id' => null
        ]);
        $ceoStage = Stage::create([
            'name' => 'CEO Approval',
            'role' => 'ceo',
            'order' => 3,
            'days_min' => 5,
            'next_stage_id' => null
        ]);


        $hrStage->next_stage_id = $managerStage->id;
        $hrStage->save();


        $managerStage->save();

    }
}
