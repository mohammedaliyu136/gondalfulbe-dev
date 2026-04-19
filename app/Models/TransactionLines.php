<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionLines extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'reference',
        'reference_id',
        'reference_sub_id',
        'date',
        'debit',
        'credit',
        'created_by',
    ];
}
