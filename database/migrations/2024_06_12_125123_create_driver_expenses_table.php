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
        Schema::create('driver_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('place')->nullable();
            $table->decimal('km', 10, 2)->nullable();
            $table->decimal('gallons', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();

            $table->decimal('igv', 10, 2)->nullable();
            $table->decimal('exonerado', 10, 2)->nullable();
            $table->decimal('gravado', 10, 2)->nullable();
            $table->string('selectTypePay')->nullable();

            $table->decimal('amount', 10, 2)->nullable();
            $table->boolean('isMovimentCaja')->nullable()->default(0);


            $table->string('operationNumber')->nullable();
            $table->string('routeFact')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('comment')->nullable();
            $table->foreignId('programming_id')->nullable()->unsigned()->constrained('programmings');
            $table->foreignId('expensesConcept_id')->nullable()->unsigned()->constrained('expenses_concepts');
            $table->foreignId('worker_id')->nullable()->unsigned()->constrained('workers');
            $table->foreignId('bank_id')->nullable()->unsigned()->constrained('banks');
            
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
        Schema::dropIfExists('driver_expenses');
    }
};
