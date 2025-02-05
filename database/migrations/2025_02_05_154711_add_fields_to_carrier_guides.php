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
            $table->decimal('costsubcontract', 10, 2)->nullable()->after('subcontract_id');
            $table->json('datasubcontract')->nullable()->after('costsubcontract');
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
            $table->dropColumn(['costsubcontract', 'datasubcontract']);
        });
    }
};
