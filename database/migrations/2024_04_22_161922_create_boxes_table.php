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
        Schema::create('boxes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 250)->nullable()->unique();
            $table->string('serie', 250)->nullable()->unique();
            $table->string('status', 250)->nullable()->default('Inactiva');
            $table->boolean('state')->nullable()->default(true);
            $table->foreignId('branchOffice_id')->nullable()->unsigned()->constrained('branch_offices');
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
        Schema::dropIfExists('boxes');
    }
};
