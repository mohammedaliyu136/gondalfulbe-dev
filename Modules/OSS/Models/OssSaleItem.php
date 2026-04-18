<?php

namespace Modules\OSS\Models;

use Illuminate\Database\Eloquent\Model;

class OssSaleItem extends Model
{
    protected $table = 'oss_sale_items';

    protected $fillable = ['sale_id', 'product_id', 'quantity', 'unit_price', 'subtotal'];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function sale()    { return $this->belongsTo(OssSale::class, 'sale_id'); }
    public function product() { return $this->belongsTo(OssProduct::class, 'product_id'); }
}
