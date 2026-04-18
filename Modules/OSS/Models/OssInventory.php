<?php

namespace Modules\OSS\Models;

use Illuminate\Database\Eloquent\Model;

class OssInventory extends Model
{
    protected $table = 'oss_inventory';

    protected $fillable = [
        'transaction_id', 'type', 'product_id', 'quantity', 'date',
        'center', 'reference', 'unit_cost', 'batch_number',
        'expiry_date', 'notes', 'created_by',
    ];

    protected $casts = [
        'quantity'    => 'decimal:2',
        'unit_cost'   => 'decimal:2',
        'date'        => 'date',
        'expiry_date' => 'date',
    ];

    const TYPES = ['Stock In', 'Stock Out', 'Adjustment'];

    public function product() { return $this->belongsTo(OssProduct::class, 'product_id'); }

    public static function generateTransactionId(): string
    {
        $count = static::count() + 1;
        return 'OSS-TXN-' . str_pad((string) $count, 7, '0', STR_PAD_LEFT);
    }
}
