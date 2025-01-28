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
        Schema::create('detail_receptions', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->nullable();
            $table->string('description')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('paymentAmount', 10, 2)->nullable();
            $table->decimal('debtAmount', 10, 2)->nullable();
            // $table->decimal('costFreight', 10, 2)->nullable();
            $table->decimal('comissionAmount', 10, 2)->nullable();
            $table->decimal('costLoad', 10, 2)->nullable();
            $table->decimal('costDownload', 10, 2)->nullable();
            $table->string('comment')->nullable()->nullable();
            $table->string('status')->nullable()->default('Pendiente');
            $table->foreignId('comissionAgent_id')->nullable()->unsigned()->constrained('commision_agents');
            $table->foreignId('reception_id')->unsigned()->constrained('receptions');
            $table->foreignId('programming_id')->nullable()->unsigned()->constrained('programmings');

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
        Schema::dropIfExists('detail_receptions');
    }
};
