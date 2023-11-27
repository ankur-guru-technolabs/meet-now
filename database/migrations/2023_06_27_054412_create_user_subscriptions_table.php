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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('subscription_id')->index();
            $table->string('expire_date')->nullable();
            $table->string('title');
            $table->string('price');
            $table->string('currency_code');
            $table->integer('month');
            $table->integer('plan_duration')->nullable();
            $table->string('plan_type')->nullable();
            $table->string('google_plan_id')->nullable();
            $table->string('apple_plan_id')->nullable();
            $table->text('order_id')->nullable();
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
        Schema::dropIfExists('user_subscriptions');
    }
};
