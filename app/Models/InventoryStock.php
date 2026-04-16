<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryStock extends Model
{
    use HasFactory;

    protected $fillable = ['inventory_id','quantity_added','added_by','note', 'supplier', 'purchase_price','reorder_level','total_purchase_price'];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function addedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'added_by');
    }
}