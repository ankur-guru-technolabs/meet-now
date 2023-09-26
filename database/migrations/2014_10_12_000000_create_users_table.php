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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone_no')->unique();
            $table->string('location');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('live_latitude')->nullable();
            $table->string('live_longitude')->nullable();
            $table->string('birth_date');
            $table->string('age');
            $table->string('gender');
            $table->string('interested_gender');
            $table->string('lastseen')->nullable();
            $table->string('user_type');
            $table->string('hobbies');
            $table->string('body_type');
            $table->string('education');
            $table->string('exercise');
            $table->string('religion');
            $table->longText('about');
            $table->string('distance_in')->default(0)->comment('0 for yards, 1 for miles');
            $table->string('status')->default(1);
            $table->string('email_verified')->default(0);
            $table->string('phone_verified')->default(0);
            $table->string('otp_verified')->default(0);
            $table->string('is_hide_profile')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('is_notification_mute')->default(0);
            $table->string('fcm_token')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
