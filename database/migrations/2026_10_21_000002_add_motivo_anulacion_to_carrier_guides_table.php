<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('carrier_guides', 'motivo_anulacion')) {
            Schema::table('carrier_guides', function (Blueprint $table) {
                $table->string('motivo_anulacion', 500)
                    ->nullable()
                    ->after('status_facturado');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('carrier_guides', 'motivo_anulacion')) {
            Schema::table('carrier_guides', function (Blueprint $table) {
                $table->dropColumn('motivo_anulacion');
            });
        }
    }
};
