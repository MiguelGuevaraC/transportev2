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
        Schema::create('pay_installments', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable();

            $table->dateTime('paymentDate')->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('yape', 10, 2)->nullable();
            $table->decimal('deposit', 10, 2)->nullable();
            $table->decimal('cash', 10, 2)->nullable();
            $table->decimal('card', 10, 2)->nullable();
            $table->decimal('plin', 10, 2)->nullable();
            $table->string('comment')->nullable();
            $table->boolean('state')->default(true);
            $table->string('status')->nullable()->default('Pagado');
            $table->foreignId('installment_id')->nullable()->unsigned()->constrained('installments');

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
        Schema::dropIfExists('pay_installments');
    }
};
