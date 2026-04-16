<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agent extends Model
{
    use HasFactory;


    protected $fillable   = [
        'agent_id',
        'name',
        'email',
        'contact',
        'image',
        'bank_name',
        'bank_account',
        'account_name',
        'avatar',
        'is_active',
        'collection_centre',
        'created_by',
        'balance',
        'book_balance',
        'billing_name',
        'billing_country',
        'billing_state',
        'billing_city',
        'billing_phone',
        'billing_zip',
        'billing_address',
    ];


    public function payslips()
    {
        return $this->hasMany(PaySlipMcAgentBatchItem::class, 'agent_id', 'id');
    }
}
