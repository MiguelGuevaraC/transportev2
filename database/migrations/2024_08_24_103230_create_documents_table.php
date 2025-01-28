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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('pathFile')->nullable();
            $table->string('description')->nullable();
            $table->string('number')->nullable();
            $table->string('type')->nullable();

            $table->date('dueDate')->nullable();
            $table->boolean('state')->default(1);
            $table->string('status')->nullable()->default('Vigente');

            $table->timestamps();
            $table->foreignId('vehicle_id')->nullable()->unsigned()->constrained('vehicles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
