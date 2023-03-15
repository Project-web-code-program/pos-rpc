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
        Schema::create('customer', function (Blueprint $table) {
            $table->id();
            $table->string('firstName');
            $table->string('middleName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('nickName')->nullable();
            $table->enum('gender',['P', 'W']);
            $table->integer('titleId');
            $table->integer('customerGroupId');
            $table->integer('locationId');
            $table->string('notes')->nullable();
            $table->date('joinDate');
            $table->integer('typeId');
            $table->integer('numberId');
            $table->integer('jobTitleId');
            $table->date('birthDate')->nullable();
            $table->integer('referenceId')->nullable();
            $table->boolean('generalCustomerCanConfigReminderBooking')->nullable();
            $table->boolean('generalCustomerCanConfigReminderPayment')->nullable();
            $table->boolean('isDeleted')->nullable()->default(false);
            $table->string('deletedBy')->nullable();
            $table->timestamp('deletedAt',0)->nullable();
            $table->string('createdBy')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer');
    }
};
