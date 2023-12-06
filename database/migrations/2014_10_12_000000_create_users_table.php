<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('city_id')->nullable()->default(null);
            $table->unsignedInteger('type_id');
            $table->string('email', 50)->unique();
            $table->string('password')->unique();
            $table->string('phone_no', 18);
            $table->string('img_url')->nullable()->default(null);
            $table->integer('badget')->nullable()->default(0);
            $table->timestamps();
            $table->foreign('city_id')->references('id')
                ->on('cities')->onDelete('cascade');
            $table->foreign('type_id')->references('id')
                ->on('users_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
