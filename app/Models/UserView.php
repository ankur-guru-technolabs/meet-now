<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserView extends Model
{
    use HasFactory;

    protected $fillable = [
        'view_from',
        'view_to',
    ];

    public function users()
    {
        return $this->hasMany(User::class,'id','view_from');
    }
}
