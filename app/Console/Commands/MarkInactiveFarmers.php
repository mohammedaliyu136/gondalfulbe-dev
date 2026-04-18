<?php

namespace App\Console\Commands;

use App\Models\Vender;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkInactiveFarmers extends Command
{
    protected $signature   = 'gondal:mark-inactive-farmers';
    protected $description = 'Mark farmers inactive if they have not supplied milk in 60 days';

    public function handle(): int
    {
        $cutoff = now()->subDays(60)->toDateString();

        // Farmers who have a purchase record on or after the cutoff date
        $activeIds = DB::table('purchases')
            ->where('purchase_date', '>=', $cutoff)
            ->pluck('vender_id')
            ->unique();

        $updated = Vender::where('is_active', 1)
            ->whereNotIn('id', $activeIds)
            ->update(['is_active' => 0]);

        $this->info("Marked {$updated} farmer(s) as inactive (no supply in 60 days).");

        return self::SUCCESS;
    }
}
