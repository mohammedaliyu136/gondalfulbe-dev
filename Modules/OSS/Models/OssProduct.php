<?php

namespace Modules\OSS\Models;

use Illuminate\Database\Eloquent\Model;

class OssProduct extends Model
{
    protected $table = 'oss_products';

    protected $fillable = [
        'product_code', 'name', 'category', 'unit', 'price',
        'reorder_level', 'description', 'supplier', 'is_active', 'created_by',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'is_active'     => 'boolean',
    ];

    const CATEGORIES = ['Feed', 'Veterinary', 'Equipment', 'Other'];

    public function inventory()       { return $this->hasMany(OssInventory::class, 'product_id'); }
    public function saleItems()       { return $this->hasMany(OssSaleItem::class, 'product_id'); }
    public function agentSaleItems()  { return $this->hasMany(OssAgentSaleItem::class, 'product_id'); }

    public function getUnitPriceAttribute(): string
    {
        return (string) $this->price;
    }

    public function getReorderQuantityAttribute(): float
    {
        return (float) $this->reorder_level;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->current_stock <= 0) {
            return 'Out of Stock';
        }

        if ($this->isLowStock()) {
            return 'Low Stock';
        }

        return 'OK';
    }

    public function getCurrentStockAttribute(?string $center = null): float
    {
        $query = OssInventory::where('product_id', $this->id);
        if ($center) $query->where('center', $center);
        $in  = (clone $query)->where('type', 'Stock In')->sum('quantity');
        $out = (clone $query)->where('type', 'Stock Out')->sum('quantity');
        return max(0, (float) $in - (float) $out);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= (float) $this->reorder_level;
    }

    public static function generateProductCode(): string
    {
        $count = static::count() + 1;
        return 'OSS-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
}
