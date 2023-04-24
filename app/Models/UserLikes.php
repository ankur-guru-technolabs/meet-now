<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLikes extends Model
{
    use HasFactory;

    protected $fillable = [
        'like_from',
        'like_to',
        'match_id',
        'match_status',
        'status',
        'matched_at',
    ];
}
