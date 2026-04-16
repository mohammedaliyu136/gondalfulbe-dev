<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Modules\Cooperatives\Models\Cooperative;

/**
 * Properly implemented Maatwebsite import for Cooperatives.
 *
 * CSV / XLSX must have a header row with (at minimum) a "name" column.
 * Optional columns: location, leader_name, leader_phone, site_location,
 *                   formation_date, average_daily_supply, status
 *
 * Fixes applied vs. the erp-copy stub:
 *  - Uses Laravel session, not raw $_SESSION
 *  - No HTML generation in PHP — results reported via flash messages
 *  - All user-supplied values sanitised before storage
 *  - Duplicate names are skipped (not silently overwritten)
 *  - Negative supply values clamped to 0
 */
class CooperativeImport implements ToCollection, WithHeadingRow
{
    use Importable;

    private int $inserted = 0;
    private int $skipped  = 0;

    public function __construct(private readonly int $createdBy) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));

            // Skip rows without a name
            if ($name === '') {
                $this->skipped++;
                continue;
            }

            // Skip duplicates (case-insensitive via DB unique constraint)
            if (Cooperative::where('name', $name)
                           ->where('created_by', $this->createdBy)
                           ->exists()) {
                $this->skipped++;
                continue;
            }

            $phone = trim((string) ($row['leader_phone'] ?? ''));

            $cooperative = Cooperative::create([
                'name'                 => $name,
                'location'             => trim((string) ($row['location'] ?? '')),
                'leader_name'          => trim((string) ($row['leader_name'] ?? '')),
                'leader_phone'         => $phone !== '' ? $phone : null,
                'site_location'        => trim((string) ($row['site_location'] ?? '')),
                'formation_date'       => $this->parseDate($row['formation_date'] ?? null),
                'average_daily_supply' => max(0, (float) ($row['average_daily_supply'] ?? 0)),
                'status'               => in_array(strtolower((string) ($row['status'] ?? '')), ['active', 'inactive'])
                                              ? strtolower($row['status'])
                                              : 'active',
                'created_by'           => $this->createdBy,
            ]);

            // Auto-generate code after we have the primary key
            $cooperative->update([
                'code' => Cooperative::generateCode($cooperative->id, $cooperative->location),
            ]);

            $this->inserted++;
        }
    }

    public function getInserted(): int
    {
        return $this->inserted;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        $timestamp = strtotime((string) $value);
        return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }
}
