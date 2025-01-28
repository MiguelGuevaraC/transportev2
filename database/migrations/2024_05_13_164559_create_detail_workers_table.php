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
        Schema::create('detail_workers', function (Blueprint $table) {
            $table->id();
            $table->string('function')->nullable();
            $table->foreignId('worker_id')->nullable()->unsigned()->constrained('workers');
            $table->foreignId('programming_id')->nullable()->unsigned()->constrained('programmings');

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
        Schema::dropIfExists('detail_workers');
    }
};
