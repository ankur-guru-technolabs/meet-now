<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_no',
        'location',
        'latitude',
        'longitude',
        'live_latitude',
        'live_longitude',
        'birth_date',
        'age',
        'gender',
        'interested_gender',
        'lastseen',
        'user_type',
        'hobbies',
        'status',
        'email_verified',
        'phone_verified',
        'otp_verified',
        'is_hide_profile',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude'=>'float',
        'longitude'=>'float',
        'live_latitude'=>'float',
        'live_longitude'=>'float',
        'age' => 'int',
        'gender' => 'int',
        'interested_gender' => 'int',
        'status'=> 'int',
        'email_verified' => 'int',
        'phone_verified'=> 'int',
        'otp_verified'=> 'int',
        'email_verified_at' => 'datetime',
    ];

    public function media()
    {
        return $this->hasMany(UserPhoto::class);
    }
}
