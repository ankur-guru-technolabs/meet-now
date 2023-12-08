<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBlock extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'block_from',
        'block_to'
    ];

    public function users()
    {
        return $this->hasMany(User::class,'id','like_from');
    }
}
