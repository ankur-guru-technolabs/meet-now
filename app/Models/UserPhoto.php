<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class UserPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
    ];

    protected $appends = ['profile_photo','compress_photo'];

    // ACCESSOR

    public function getProfilePhotoAttribute()
    {
        return asset('/user_profile/' . $this->name);
    }
 
    public function getCompressPhotoAttribute()
    {
        $path = public_path('/user_profile/' . 'Compress_'.$this->name);
        if (File::exists($path)) {
          return asset('/user_profile/' . 'Compress_'.$this->name);
        }
        return null;
    }
}
