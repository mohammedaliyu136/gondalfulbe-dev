<?php

namespace Modules\OSS\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Vender;

class OssSale extends Model
{
    protected $table = 'oss_sales';

    protected $fillable = [
        'sale_id', 'date', 'farmer_id', 'center', 'total_amount',
        'payment_method', 'is_credit', 'credit_settled', 'created_by',
    ];

    protected $casts = [
        'date'           => 'date',
        'total_amount'   => 'decimal:2',
        'is_credit'      => 'boolean',
        'credit_settled' => 'boolean',
    ];

    const PAYMENT_METHODS = ['Cash', 'Credit', 'Mobile Money'];

    public function farmer() { return $this->belongsTo(Vender::class, 'farmer_id'); }
    public function items()  { return $this->hasMany(OssSaleItem::class, 'sale_id'); }

    public static function generateSaleId(): string
    {
        $count = static::count() + 1;
        return 'OSS-SALE-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }
}
