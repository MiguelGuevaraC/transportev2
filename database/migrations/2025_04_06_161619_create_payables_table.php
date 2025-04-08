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
        Schema::create('payables', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable();            // Número del pago
            $table->integer('days')->nullable();             // Días
            $table->date('date')->nullable();                // Fecha del pago
            $table->decimal('total', 10, 2)->nullable();     // Monto total
            $table->decimal('totalDebt', 10, 2)->nullable(); // Monto de la deuda total
            $table->string('status')->default('Pendiente')->nullable();
            $table->foreignId('driver_expense_id')->nullable()->unsigned()->constrained('driver_expenses');
            $table->foreignId('user_created_id')->nullable()->unsigned()->constrained('users');
            $table->timestamps();  // created_at y updated_at
            $table->softDeletes(); // Columna de soft delete
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payables');
    }
};
