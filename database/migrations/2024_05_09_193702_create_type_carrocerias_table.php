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
        Schema::create('type_carrocerias', function (Blueprint $table) {
            $table->id(); 

            $table->string('description')->nullable(); 
            $table->boolean('state')->default(1);

            $table->timestamps(); 
            $table->foreignId('typecompany_id')->nullable()->unsigned()->constrained('type_companies');
       
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('type_carrocerias');
    }
};
