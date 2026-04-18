<?php

namespace Modules\OSS\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class OssAgentAllocation extends Model
{
    protected $table = 'oss_agent_allocations';

    protected $fillable = [
        'allocation_id', 'agent_id', 'product_id', 'quantity_allocated',
        'allocated_date', 'allocated_by', 'center', 'reference_stock_out_id', 'notes', 'created_by',
    ];

    protected $casts = [
        'quantity_allocated' => 'decimal:2',
        'allocated_date'     => 'date',
    ];

    public function product()      { return $this->belongsTo(OssProduct::class, 'product_id'); }
    public function agent()        { return $this->belongsTo(User::class, 'agent_id'); }
    public function allocatedBy()  { return $this->belongsTo(User::class, 'allocated_by'); }

    public static function generateAllocationId(): string
    {
        $count = static::count() + 1;
        return 'ALLOC-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }

    public static function getAgentBalance(int $agentId, int $productId): float
    {
        $allocated = static::where('agent_id', $agentId)->where('product_id', $productId)->sum('quantity_allocated');
        $sold      = OssAgentSaleItem::whereHas('agentSale', fn($q) => $q->where('agent_id', $agentId))
                        ->where('product_id', $productId)->sum('quantity');
        $returned  = OssAgentReturn::where('agent_id', $agentId)->where('product_id', $productId)->sum('quantity_returned');
        return max(0, (float) $allocated - (float) $sold - (float) $returned);
    }
}
