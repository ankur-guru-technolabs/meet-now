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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('search_filters')->nullable();
            $table->integer('like_per_day')->nullable();
            $table->string('video_call')->nullable();
            $table->string('who_like_me');
            $table->string('who_view_me');
            $table->string('message_per_match')->nullable();
            $table->string('price');
            $table->string('currency_code');
            $table->integer('month');
            $table->integer('plan_duration')->nullable();
            $table->string('plan_type')->nullable();
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
        Schema::dropIfExists('subscriptions');
    }
};
