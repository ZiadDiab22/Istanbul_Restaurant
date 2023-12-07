<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\products_type;

class ProductsTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('products_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        products_type::create([
            "name" => "Desserts",
        ]);
        products_type::create([
            "name" => "Drinks",
        ]);
        products_type::create([
            "name" => "Appetizers",
        ]);
    }
}
