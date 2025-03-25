<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('people', function (Blueprint $table) {
            $table->decimal('amount_anticipado', 10, 2)->default(0)->nullable()->after('branchOffice_id'); // Reemplaza 'ultimo_campo' con el nombre del último campo de la tabla
        });
    }

    public function down()
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn('amount_anticipado');
        });
    }
};
