<?php

namespace App\Exports;

use Modules\Cooperatives\Models\Cooperative;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Exports cooperatives scoped to the given creator (company).
 *
 * Uses FromQuery (not FromCollection) so large datasets are streamed
 * without loading the entire table into memory at once.
 */
class CooperativeExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly int $createdBy) {}

    public function query()
    {
        return Cooperative::query()->where('created_by', $this->createdBy)->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Location (MCC)',
            'Leader Name',
            'Leader Phone',
            'Site Location',
            'Formation Date',
            'Avg Daily Supply (L)',
            'Status',
            'Members',
        ];
    }

    public function map($cooperative): array
    {
        return [
            $cooperative->code,
            $cooperative->name,
            $cooperative->location,
            $cooperative->leader_name,
            $cooperative->leader_phone,
            $cooperative->site_location,
            $cooperative->formation_date?->format('Y-m-d'),
            $cooperative->average_daily_supply,
            $cooperative->status,
            $cooperative->farmers()->count(),
        ];
    }
}
