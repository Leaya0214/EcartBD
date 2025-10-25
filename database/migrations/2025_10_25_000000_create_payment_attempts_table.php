<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('transaction_id')->nullable()->index();
            $table->string('status')->default('initiated')->index();
            $table->decimal('amount', 12, 2)->nullable();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();

            // optional foreign key if orders table exists
            if (Schema::hasTable('orders')) {
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
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
        Schema::dropIfExists('payment_attempts');
    }
}
