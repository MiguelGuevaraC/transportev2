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
        Schema::table('payables', function (Blueprint $table) {
            $table->foreignId('person_id')->nullable()->unsigned()->constrained('people');
            $table->foreignId('type_document_id')->nullable()->unsigned()->constrained('type_documents');
            $table->string('correlativo_ref')->nullable();
            $table->string('type_payable')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payables', function (Blueprint $table) {
            $table->dropForeign(['person_id']);
            $table->dropColumn('person_id');

            $table->dropForeign(['type_document_id']);
            $table->dropColumn('type_document_id');

            $table->dropColumn('correlativo_ref');
            $table->dropColumn('type_payable');
        });
    }
};
