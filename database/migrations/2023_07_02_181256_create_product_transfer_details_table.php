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

        Schema::create('productTransferDetails', function (Blueprint $table) {
            $table->id();

            $table->integer('productTransferId');
            $table->integer('productIdOrigin');
            $table->integer('productIdDestination');
            $table->string('productType');
            $table->string('remark');
            $table->integer('quantity');
            $table->decimal('additionalCost', $precision = 18, $scale = 2);

            $table->boolean('isUserReceived')->nullable()->default(false);
            $table->timestamp('receivedAt', 0)->nullable();
            $table->string('reference')->nullable();

            $table->integer('userIdOffice')->nullable();
            $table->integer('isApprovedOffice')->nullable()->default(0);
            $table->string('reasonOffice')->nullable();
            $table->timestamp('officeApprovedAt', 0)->nullable();

            $table->boolean('isAdminApproval');

            $table->integer('userIdAdmin')->nullable();
            $table->integer('isApprovedAdmin')->nullable()->default(0);
            $table->string('reasonAdmin')->nullable();
            $table->timestamp('adminApprovedAt', 0)->nullable();

            $table->boolean('isDeleted')->nullable()->default(false);
            $table->integer('userId');
            $table->integer('userUpdateId')->nullable();
            $table->string('deletedBy')->nullable();
            $table->timestamp('deletedAt', 0)->nullable();
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
        Schema::dropIfExists('productTransferDetails');
    }
};
