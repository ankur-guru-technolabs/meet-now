<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'match_id',
        'message',
    ];

    // Relationship with the reporter (User model)
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    // Relationship with the reported user (User model)
    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }
    
}
