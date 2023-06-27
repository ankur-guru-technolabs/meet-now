<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'expire_date',
        'title',
        'price',
        'currency_code',
        'month',
        'plan_duration',
        'plan_type',
    ];
}
