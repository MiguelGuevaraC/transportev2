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
        Schema::create('carga_documents', function (Blueprint $table) {
            $table->id();
            $table->dateTime('movement_date')->nullable(); // Fecha del movimiento
            $table->decimal('quantity', 10, 2)->nullable(); // Cantidad
            $table->decimal('unit_price', 10, 2)->nullable(); // Precio unitario
            $table->decimal('total_cost', 10, 2)->nullable(); // Costo total
            $table->decimal('weight', 10, 2)->nullable(); // Peso (opcional)
            $table->string('movement_type')->nullable(); // Tipo de movimiento
            $table->decimal('stock_balance_before', 10, 2)->nullable(); // Balance de stock
            $table->decimal('stock_balance_after', 10, 2)->nullable(); // Balance de stock
            $table->text('comment')->nullable();

            $table->foreignId('product_id')->nullable()->unsigned()->constrained('products');
            $table->foreignId('person_id')->nullable()->unsigned()->constrained('people');

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
        Schema::dropIfExists('carga_documents');
    }
};
