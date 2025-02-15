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
            $table->longText('lote_doc')->nullable();
            $table->date('date_expiration')->nullable();
            $table->string('code_doc',255)->nullable();
            $table->longText('num_anexo')->nullable();
            $table->foreignId('branchOffice_id')->nullable()->unsigned()->constrained('branch_offices');
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
            $table->dropColumn('date_expiration');
            $table->dropColumn('lote_doc');
            $table->dropColumn('code_doc');
            $table->dropColumn('num_anexo');
            $table->dropForeign(['branchOffice_id']);
            $table->dropColumn('branchOffice_id');
        });
    }
};
