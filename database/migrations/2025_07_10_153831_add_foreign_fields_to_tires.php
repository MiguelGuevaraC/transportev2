<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tires', function (Blueprint $table) {
            $table->foreignId('material_id')->nullable()->constrained('materials');
            $table->foreignId('design_id')->nullable()->constrained('designs');
            $table->foreignId('brand_id')->nullable()->constrained('brands');
            $table->dropColumn(['material', 'brand', 'design']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tires', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
            $table->dropColumn('material_id');
            $table->dropForeign(['design_id']);
            $table->dropColumn('design_id');
            $table->dropForeign(['brand_id']);
            $table->dropColumn('brand_id');

        });
    }
};
