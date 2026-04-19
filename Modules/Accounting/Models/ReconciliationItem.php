<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;

class ReconciliationItem extends Model
{
    protected $fillable = [
        'reconciliation_id', 'date', 'description', 'type',
        'amount', 'reference', 'transaction_line_id', 'is_matched',
    ];

    protected $casts = ['date' => 'date', 'amount' => 'decimal:2', 'is_matched' => 'boolean'];

    public function reconciliation()
    {
        return $this->belongsTo(Reconciliation::class);
    }
}
