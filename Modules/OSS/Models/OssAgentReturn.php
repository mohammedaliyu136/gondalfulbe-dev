<?php

namespace Modules\OSS\Models;

use Illuminate\Database\Eloquent\Model;

class OssAgentReturn extends Model
{
    protected $table = 'oss_agent_returns';

    protected $fillable = [
        'return_id', 'agent_id', 'product_id', 'quantity_returned',
        'return_date', 'reason', 'reference_stock_in_id', 'created_by',
    ];

    protected $casts = [
        'quantity_returned' => 'decimal:2',
        'return_date'       => 'date',
    ];

    public function product() { return $this->belongsTo(OssProduct::class, 'product_id'); }

    public static function generateReturnId(): string
    {
        $count = static::count() + 1;
        return 'RET-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }
}
