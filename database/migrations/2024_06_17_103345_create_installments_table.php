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
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->string('sequentialNumber')->nullable();
            $table->integer('days')->nullable()->default(0);

            $table->date('date')->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('totalDebt', 10, 2)->nullable();
            $table->boolean('state')->default(1);
            $table->string('status', 255)->default('Pendiente')->nullable();
            $table->foreignId('moviment_id')->nullable()->unsigned()->constrained('moviments');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('installments');
    }
};
