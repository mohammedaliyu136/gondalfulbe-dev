<?php

namespace Modules\OSS\Models;

use Illuminate\Database\Eloquent\Model;

class OssAgentSaleItem extends Model
{
    protected $table = 'oss_agent_sale_items';

    protected $fillable = ['agent_sale_id', 'product_id', 'quantity', 'unit_price', 'subtotal'];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function agentSale() { return $this->belongsTo(OssAgentSale::class, 'agent_sale_id'); }
    public function product()   { return $this->belongsTo(OssProduct::class, 'product_id'); }
}
