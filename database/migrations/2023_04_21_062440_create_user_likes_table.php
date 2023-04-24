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
        Schema::create('user_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('like_from')->index();
            $table->unsignedBigInteger('like_to')->index();
            $table->unsignedBigInteger('match_id')->nullable()->index();
            $table->tinyInteger('match_status')->default(2)->comment('0: unmatched, 1: matched, 2: nothing')->index();
            $table->tinyInteger('status')->nullable()->comment('0: dislike, 1: like')->index();
            $table->timestamp('matched_at')->nullable();
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
        Schema::dropIfExists('user_likes');
    }
};
