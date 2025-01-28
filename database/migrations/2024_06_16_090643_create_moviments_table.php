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
        Schema::create('moviments', function (Blueprint $table) {
            $table->id();
            $table->string('sequentialNumber')->nullable();
            $table->string('correlative')->nullable();

            $table->dateTime('paymentDate')->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('yape', 10, 2)->nullable();
            $table->decimal('deposit', 10, 2)->nullable();
            $table->decimal('cash', 10, 2)->nullable();
            $table->decimal('card', 10, 2)->nullable();
            $table->decimal('plin', 10, 2)->nullable();
            $table->string('comment')->nullable();
            $table->string('nroTransferencia')->nullable();

            $table->json('productList')->nullable();

            $table->decimal('saldo', 10, 2)->default(0)->nullable();

            $table->string('typeDocument')->nullable();
            $table->string('typePayment')->nullable();
            $table->string('typeSale')->nullable()->default('Normal');
            $table->string('codeDetraction')->nullable();

            $table->boolean('isBankPayment')->nullable()->default(false);
            $table->string('numberVoucher')->nullable();
            $table->string('routeVoucher')->nullable();

            $table->string('status')->nullable()->default('Generada');
            $table->string('movType')->nullable()->default('Caja');
            $table->string('typeCaja')->nullable();
            $table->string('operationNumber')->nullable();

            $table->foreignId('programming_id')->nullable()->unsigned()->constrained('programmings');
            $table->foreignId('paymentConcept_id')->nullable()->unsigned()->constrained('payment_concepts');
            $table->foreignId('box_id')->nullable()->unsigned()->constrained('boxes');
            $table->foreignId('bank_id')->nullable()->unsigned()->constrained('banks');

            $table->foreignId('branchOffice_id')->nullable()->unsigned()->constrained('branch_offices');
            $table->foreignId('reception_id')->nullable()->unsigned()->constrained('receptions');
            $table->foreignId('person_id')->nullable()->unsigned()->constrained('people');
            $table->foreignId('user_id')->nullable()->unsigned()->constrained('users');
            $table->foreignId('mov_id')->nullable()->unsigned()->constrained('moviments');

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
        Schema::dropIfExists('moviments');
    }
};
