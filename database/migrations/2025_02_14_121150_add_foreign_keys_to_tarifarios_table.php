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
        Schema::table('tarifarios', function (Blueprint $table) {
            $table->foreignId('destination_id')->nullable()->constrained('places');
            $table->foreignId('origin_id')->nullable()->constrained('places');
            $table->decimal('limitweight')->nullable();
            $table->decimal('tarifa_camp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tarifarios', function (Blueprint $table) {
            $table->dropForeign(['destination_id']);
            $table->dropColumn('destination_id');
            $table->dropForeign(['origin_id']);
            $table->dropColumn('origin_id');
            $table->dropColumn('limitweight');
            $table->dropColumn('tarifa_camp');
        });
    }
};
