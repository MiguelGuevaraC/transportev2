<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('carga_documents', 'carrier_guide_id')) {
            return;
        }

        DB::statement('ALTER TABLE carga_documents CHANGE carrier_guide_id carrier_guide_number VARCHAR(191) NULL');
    }

    public function down(): void
    {
        if (! Schema::hasColumn('carga_documents', 'carrier_guide_number')) {
            return;
        }

        DB::statement('ALTER TABLE carga_documents CHANGE carrier_guide_number carrier_guide_id BIGINT UNSIGNED NULL');
    }
};
