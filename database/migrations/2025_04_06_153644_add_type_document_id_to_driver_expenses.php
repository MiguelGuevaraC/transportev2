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
        Schema::table('driver_expenses', function (Blueprint $table) {
            $table->foreignId('type_document_id')->nullable()->unsigned()->constrained('type_documents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_expenses', function (Blueprint $table) {
            if (Schema::hasColumn('driver_expenses', 'type_document_id')) {
                $table->dropColumn('type_document_id');
            }
        });
    }
};
