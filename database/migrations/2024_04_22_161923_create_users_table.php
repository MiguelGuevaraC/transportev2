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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 255)->unique();
            $table->string('password', 255);
            $table->boolean('state')->default(true);
            $table->foreignId('typeofUser_id')->nullable()->unsigned()->constrained('typeof_Users');
            $table->foreignId('worker_id')->nullable()->unsigned()->constrained('workers');
            $table->foreignId('box_id')->nullable()->unsigned()->constrained('boxes');
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
        Schema::dropIfExists('users');
    }
};
