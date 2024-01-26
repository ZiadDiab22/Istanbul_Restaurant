<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\users_type;


class UsersTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        users_type::create([
            "name" => "normal",
        ]);
        users_type::create([
            "name" => "employee",
        ]);
        users_type::create([
            "name" => "admin",
        ]);
    }
}
