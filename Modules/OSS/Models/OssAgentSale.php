<?php

namespace Modules\OSS\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Vender;
use App\Models\User;

class OssAgentSale extends Model
{
    protected $table = 'oss_agent_sales';

    protected $fillable = [
        'sale_id', 'agent_id', 'farmer_id', 'date', 'total_amount',
        'payment_method', 'is_credit', 'visit_id', 'created_by',
    ];

    protected $casts = [
        'date'         => 'date',
        'total_amount' => 'decimal:2',
        'is_credit'    => 'boolean',
    ];

    public function agent()  { return $this->belongsTo(User::class, 'agent_id'); }
    public function farmer() { return $this->belongsTo(Vender::class, 'farmer_id'); }
    public function items()  { return $this->hasMany(OssAgentSaleItem::class, 'agent_sale_id'); }

    public static function generateSaleId(): string
    {
        $count = static::count() + 1;
        return 'ASALE-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }
}
