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
        Schema::table('moviments', function (Blueprint $table) {
            if (!Schema::hasColumn('moviments', 'observation')) {
                $table->text('observation')->nullable();
            }
        });
    }

    public function down()
    {
        // No eliminamos la columna si ya existía previamente
        Schema::table('moviments', function (Blueprint $table) {
            if (Schema::hasColumn('moviments', 'observation')) {
                $table->dropColumn('observation');
            }
        });
    }
};
