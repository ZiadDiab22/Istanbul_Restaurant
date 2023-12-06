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
        Schema::create('requests_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('req_id');
            $table->unsignedInteger('product_id');
            $table->integer('quantity');
            $table->foreign('req_id')->references('id')
                ->on('requests')->onDelete('cascade');
            $table->foreign('product_id')->references('id')
                ->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests_infos');
    }
};
