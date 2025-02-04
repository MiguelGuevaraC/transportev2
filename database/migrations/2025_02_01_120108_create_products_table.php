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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Campo 'id' autoincremental
            $table->string('description')->nullable(); // Descripción del producto
            $table->integer('stock')->default(0); // Stock del producto
            $table->decimal('weight', 8, 2)->nullable(); // Peso del producto (puede ser decimal)
            $table->string('category')->nullable(); // Categoría del producto
            $table->foreignId('unity_id')->nullable()->unsigned()->constrained('unities');
            $table->foreignId('person_id')->nullable()->unsigned()->constrained('people');
            $table->timestamps(); // created_at y updated_at
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
        Schema::dropIfExists('products');
    }
};
