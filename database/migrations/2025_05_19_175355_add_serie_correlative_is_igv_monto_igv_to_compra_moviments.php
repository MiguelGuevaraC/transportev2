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
        Schema::table('compra_moviments', function (Blueprint $table) {
            $table->string('payment_condition')->nullable();
            $table->string('serie_doc')->nullable();
            $table->string('correlative_doc')->nullable();
            $table->decimal('monto_igv')->nullable();
            $table->boolean('is_igv_incluide')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('compra_moviments', function (Blueprint $table) {
             $table->dropColumn('payment_condition');
            $table->dropColumn('serie_doc');
            $table->dropColumn('correlative_doc');
            $table->dropColumn('monto_igv');
            $table->dropColumn('is_igv_incluide');
        });
    }
};
