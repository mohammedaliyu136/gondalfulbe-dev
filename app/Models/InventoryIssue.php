<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'quantity',
        'issued_by',
        'issued_to',
        'issue_date',
    ];

    /**
     * Get the inventory item associated with this issue.
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
    protected $casts = [
    'issue_date' => 'datetime',
];
}
