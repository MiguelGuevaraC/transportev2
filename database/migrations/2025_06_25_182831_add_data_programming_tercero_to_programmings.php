<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('programmings', function (Blueprint $table) {
            // Indica si la programación es tercerizada
            $table->boolean('is_tercerizar_programming')->default(0)->after('id');

            // Campo JSON que contiene:
            // - driver_names
            // - vehicle_plate
            // - company_names
            // - is_igv
            // - monto
            $table->json('data_tercerizar_programming')->nullable()->after('is_tercerizar_programming');
        });
    }

    public function down()
    {
        Schema::table('programmings', function (Blueprint $table) {
            $table->dropColumn([
                'is_tercerizar_programming',
                'data_tercerizar_programming',
            ]);
        });
    }
};
