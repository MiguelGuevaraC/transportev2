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
        Schema::table('carrier_guides', function (Blueprint $table) {
            if (!Schema::hasColumn('carrier_guides', 'modalidad')) {
                $table->string('modalidad', 255)->default('02')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carrier_guides', function (Blueprint $table) {
            if (Schema::hasColumn('carrier_guides', 'modalidad')) {
                $table->dropColumn('modalidad');
            }
        });
    }
    
};
