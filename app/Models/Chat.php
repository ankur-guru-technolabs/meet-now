<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'sender_id',
        'receiver_id',
        'message',
        'type',
        'read_status',
    ];

    public function chats()
    {
        return $this->belongsTo(Chat::class, 'match_id', 'match_id');
    }

    public function users()
    {
        return $this->hasMany(User::class,'id','sender_id');
    }

    public function userReceiver()
    {
        return $this->hasMany(User::class,'id','receiver_id');
    }
}
