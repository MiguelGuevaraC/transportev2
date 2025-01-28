<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_moviments', function (Blueprint $table) {
            $table->id();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('quantity')->nullable();
            $table->string('product')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->foreignId('moviment_id')->nullable()->unsigned()->constrained('moviments');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_moviments');
    }
};
