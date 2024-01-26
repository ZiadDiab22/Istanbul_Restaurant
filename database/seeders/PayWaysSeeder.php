<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\payment_way;

class PayWaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('payment_ways')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        payment_way::create([
            "name" => "cash",
        ]);
        payment_way::create([
            "name" => "electronic",
        ]);
    }
}
