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
            $table->foreignId('user_created_id')->nullable()->unsigned()->constrained('users');
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
            $table->dropForeign(['user_created_id']);
            $table->dropColumn('user_created_id');
        });
    }
};
