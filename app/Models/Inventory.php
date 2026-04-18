<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'item_name',
        'description',
        'category',
        'quantity',
        'reorder_level',
        'created_by',
    ];


    public function stocks()
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function issues()
    {
        return $this->hasMany(InventoryIssue::class);
    }
}
