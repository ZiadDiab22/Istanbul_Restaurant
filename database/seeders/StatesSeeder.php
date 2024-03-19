<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\request_state;

class StatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('request_states')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        request_state::create([
            "name" => "new",
        ]);
        request_state::create([
            "name" => "working",
        ]);
        request_state::create([
            "name" => "ended",
        ]);
        request_state::create([
            "name" => "cancelled",
        ]);
    }
}
