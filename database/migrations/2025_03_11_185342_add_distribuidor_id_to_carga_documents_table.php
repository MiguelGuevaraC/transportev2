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
        Schema::table('carga_documents', function (Blueprint $table) {
            $table->foreignId('distribuidor_id')->nullable()->unsigned()->constrained('people');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carga_documents', function (Blueprint $table) {
            if (Schema::hasColumn('carga_documents', 'distribuidor_id')) {
                $table->dropColumn('distribuidor_id');
            }
        });
    }
};
