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
        Schema::create('receptions', function (Blueprint $table) {
            $table->id();
            $table->string('codeReception');

            $table->decimal('netWeight', 10, 2)->nullable();
            $table->decimal('paymentAmount', 10, 2)->nullable();
            $table->decimal('debtAmount', 10, 2)->nullable();
            $table->string('typeService')->nullable();
            $table->string('type_responsiblePay')->nullable();
            $table->integer('numberDays')->nullable();
            $table->decimal('creditAmount')->nullable();
            $table->string('conditionPay');
            $table->string('typeDelivery');
            $table->date('receptionDate')->nullable();
            $table->date('transferLimitDate')->nullable();
            $table->date('transferStartDate')->nullable();
            $table->date('estimatedDeliveryDate')->nullable();
            $table->date('actualDeliveryDate')->nullable();
            $table->string('comment');

            $table->string('tokenResponsible', 255)->nullable();
            $table->string('address', 255)->nullable();
            

            $table->foreignId('user_id')->nullable()->unsigned()->constrained('users');

            $table->foreignId('origin_id')->nullable()->unsigned()->constrained('places');
            $table->foreignId('sender_id')->nullable()->unsigned()->constrained('people');

            $table->foreignId('destination_id')->nullable()->unsigned()->constrained('places');
            $table->foreignId('recipient_id')->nullable()->unsigned()->constrained('people');

            $table->foreignId('pickupResponsible_id')->nullable()->unsigned()->constrained('contact_infos');
            $table->foreignId('payResponsible_id')->nullable()->unsigned()->constrained('people');

            $table->foreignId('seller_id')->nullable()->unsigned()->constrained('workers');

            $table->foreignId('pointSender_id')->nullable()->unsigned()->constrained('addresses');
            $table->foreignId('pointDestination_id')->nullable()->unsigned()->constrained('addresses');
            $table->foreignId('branchOffice_id')->nullable()->unsigned()->constrained('branch_offices');
            $table->foreignId('office_id')->nullable()->unsigned()->constrained('branch_offices');
            $table->string('status', 255)->default('Generada')->nullable();

            $table->string('state')->nullable()->default(1);
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
        Schema::dropIfExists('receptions');
    }
};
