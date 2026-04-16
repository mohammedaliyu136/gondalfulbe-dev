<?php

namespace App\Exports;

use App\Models\Vender;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Exports all farmers (Venders) that belong to a specific cooperative.
 * Scoped strictly by cooperative_id — NOT by created_by — so no farmer
 * belonging to the cooperative is silently excluded.
 */
class CooperativeFarmerExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly int $cooperativeId) {}

    public function query()
    {
        return Vender::query()
            ->where('cooperative_id', $this->cooperativeId)
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Farmer ID',
            'Name',
            'Email',
            'Contact',
            'Status',
        ];
    }

    public function map($farmer): array
    {
        return [
            $farmer->vender_id,
            $farmer->name,
            $farmer->email,
            $farmer->contact,
            $farmer->is_active ? 'Active' : 'Inactive',
        ];
    }
}
