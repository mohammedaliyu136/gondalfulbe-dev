<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ServiceProvider extends Model
{


    protected $fillable   = [
        'vender_id',
        'name',
        'email',
        'password',
        'contact',
        'bank_name',
        'image',
        'bank_account',
        'bank_code',
        'account_name',
        'avatar',
        'is_active',
        'created_by',
        'balance',
        'book_balance',
        'email_verified_at',
        'billing_name',
        'billing_country',
        'billing_state',
        'billing_city',
        'billing_phone',
        'billing_zip',
        'billing_address',
        'shipping_name',
        'shipping_country',
        'shipping_state',
        'shipping_city',
        'shipping_phone',
        'shipping_zip',
        'shipping_address',
    ];

    

}
