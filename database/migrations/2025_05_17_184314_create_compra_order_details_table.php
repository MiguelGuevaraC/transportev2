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
        Schema::create('compra_order_details', function (Blueprint $table) {
            $table->id();

            $table->integer('quantity')->nullable();
            $table->foreignId('compra_order_id')->nullable()->unsigned()->constrained('compra_orders');
            $table->foreignId('repuesto_id')->nullable()->unsigned()->constrained('repuestos');
             $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->text('comment')->nullable();

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
        Schema::dropIfExists('compra_order_details');
    }
};
