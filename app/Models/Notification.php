<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'title',
        'type',
        'message',
        'data',
        'status'
    ];

    public function notificationSender()
    {
        return $this->hasMany(User::class,'id','sender_id');
    }
}
