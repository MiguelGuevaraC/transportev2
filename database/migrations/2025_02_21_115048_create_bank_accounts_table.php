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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number', 50)->nullable();
            $table->string('account_type')->nullable();                   //'ahorros', 'corriente', 'credito'
            $table->string('currency', 10)->nullable();                    //PEN
            $table->decimal('balance', 15, 2)->default(0.00)->nullable(); //SALDO
            $table->string('holder_name', 500)->nullable();               //NOMBRE DE LA TARJETA
            $table->foreignId('holder_id')->nullable()->unsigned()->constrained('people');
            $table->foreignId('bank_id')->nullable()->unsigned()->constrained('banks');
            $table->string('status')->default('activa')->nullable();      //'activa', 'inactiva', 'cerrada'
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
        Schema::dropIfExists('bank_accounts');
    }
};
