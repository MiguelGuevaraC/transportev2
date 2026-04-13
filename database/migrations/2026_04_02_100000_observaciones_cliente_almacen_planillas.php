<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carga_documents', function (Blueprint $table) {
            $table->string('billing_month', 7)->nullable()->after('comment');
            $table->string('guide_pdf_path')->nullable()->after('billing_month');
            $table->unsignedBigInteger('carrier_guide_id')->nullable()->after('guide_pdf_path');
        });

        Schema::table('document_carga_details', function (Blueprint $table) {
            $table->string('position_code', 64)->nullable()->after('num_lot');
            $table->string('damaged_photo_path')->nullable()->after('position_code');
        });

        Schema::table('product_stock_by_branches', function (Blueprint $table) {
            $table->string('position_code', 64)->nullable()->after('num_lot');
        });

        Schema::table('workers', function (Blueprint $table) {
            $table->string('contract_type', 32)->nullable()->after('status');
            $table->date('contract_end_date')->nullable()->after('contract_type');
            $table->string('salary_mode', 32)->nullable()->after('contract_end_date');
            $table->boolean('is_paid_intern')->nullable()->after('salary_mode');
            $table->string('path_licencia_photo')->nullable()->after('is_paid_intern');
            $table->string('path_dni_photo')->nullable()->after('path_licencia_photo');
            $table->string('biometric_credential_id', 191)->nullable()->after('path_dni_photo');
        });

        Schema::create('worker_attendance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('checked_out_at')->nullable();
            $table->string('source', 32)->default('manual');
            $table->string('biometric_device_id', 191)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['worker_id', 'attendance_date'], 'uniq_worker_attendance_day');
        });

        Schema::create('worker_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->date('absence_date');
            $table->string('absence_type', 32);
            $table->text('reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['worker_id', 'absence_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_absences');
        Schema::dropIfExists('worker_attendance_events');

        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn([
                'contract_type', 'contract_end_date', 'salary_mode', 'is_paid_intern',
                'path_licencia_photo', 'path_dni_photo', 'biometric_credential_id',
            ]);
        });

        Schema::table('product_stock_by_branches', function (Blueprint $table) {
            $table->dropColumn('position_code');
        });

        Schema::table('document_carga_details', function (Blueprint $table) {
            $table->dropColumn(['position_code', 'damaged_photo_path']);
        });

        Schema::table('carga_documents', function (Blueprint $table) {
            $table->dropColumn(['billing_month', 'guide_pdf_path', 'carrier_guide_id']);
        });
    }
};
