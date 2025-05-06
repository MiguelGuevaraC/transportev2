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
        Schema::table('check_list_details', function (Blueprint $table) {
            $table->text('observation')->nullable();
            $table->boolean('is_selected')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('check_list_details', function (Blueprint $table) {
            $table->dropColumn('observation');
            $table->dropColumn('is_selected');
        });
    }
};
