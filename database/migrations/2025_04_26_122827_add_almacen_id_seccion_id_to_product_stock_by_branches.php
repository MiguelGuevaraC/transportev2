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
        Schema::table('product_stock_by_branches', function (Blueprint $table) {
            $table->foreignId('almacen_id')->nullable()->constrained('almacens');
            $table->foreignId('seccion_id')->nullable()->constrained('seccions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_stock_by_branches', function (Blueprint $table) {
            // Primero elimina las foreign keys
            $table->dropForeign(['almacen_id']);
            $table->dropForeign(['seccion_id']);
            
            // Luego elimina las columnas
            $table->dropColumn('almacen_id');
            $table->dropColumn('seccion_id');
        });
    }
};
