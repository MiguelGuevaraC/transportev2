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
        Schema::create('check_list_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_list_item_id')->nullable()->unsigned()->constrained('check_list_items');
            $table->foreignId('check_list_id')->nullable()->unsigned()->constrained('check_lists');
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
        Schema::dropIfExists('check_list_details');
    }
};
