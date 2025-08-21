<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('option_menus', function (Blueprint $table) {
            $table->string('action')->default('list')->after('id')->nullable();
         $indexes = DB::select("SHOW INDEXES FROM option_menus WHERE Key_name = 'option_menus_name_guard_name_unique'");

    if (!empty($indexes)) {
        $table->dropUnique('option_menus_name_guard_name_unique');
    }
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('option_menus', function (Blueprint $table) {
            $table->dropColumn('action');
        });
    }
};
