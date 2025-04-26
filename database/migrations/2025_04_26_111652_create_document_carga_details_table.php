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
        Schema::create('document_carga_details', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity')->nullable();
          
            $table->foreignId('product_id')->nullable()->unsigned()->constrained('products');
            $table->foreignId('branchOffice_id')->nullable()->unsigned()->constrained('branch_offices');
            $table->foreignId('almacen_id')->nullable()->unsigned()->constrained('almacens');
            $table->foreignId('seccion_id')->nullable()->unsigned()->constrained('seccions');
            $table->foreignId('document_carga_id')->nullable()->unsigned()->constrained('carga_documents');
            $table->text('num_anexo')->nullable();
            $table->text('comment')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_carga_details');
    }
};
