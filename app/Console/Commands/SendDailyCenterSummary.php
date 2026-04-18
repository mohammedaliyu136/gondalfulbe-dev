<?php

namespace App\Console\Commands;

use App\Mail\DailyCenterSummary;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Modules\CenterOperations\Models\CenterCost;
use Modules\MilkCollection\Models\MilkCollection;

class SendDailyCenterSummary extends Command
{
    protected $signature   = 'gondal:daily-center-summary';
    protected $description = 'Send daily operational summary email to Center Managers';

    public function handle(): int
    {
        $managers = User::where('type', 'center_manager')
            ->orWhereHas('roles', fn($q) => $q->where('name', 'center_manager'))
            ->whereNotNull('assigned_mcc')
            ->get();

        if ($managers->isEmpty()) {
            $this->info('No center managers with assigned MCC found.');
            return self::SUCCESS;
        }

        foreach ($managers as $manager) {
            $mcc        = $manager->assigned_mcc;
            $creatorId  = $manager->creatorId();

            $todayLitres = MilkCollection::where('created_by', $creatorId)
                ->where('mcc', $mcc)
                ->whereDate('date', today())
                ->sum('quantity_litres');

            $pendingCosts = CenterCost::where('created_by', $creatorId)
                ->where('mcc', $mcc)
                ->where('status', 'submitted')
                ->count();

            $lowStockCount = Inventory::where('created_by', $creatorId)
                ->whereColumn('quantity', '<=', 'reorder_level')
                ->count();

            if ($manager->email) {
                Mail::to($manager->email)->send(new DailyCenterSummary(
                    centerName:    $mcc,
                    todayLitres:   (float) $todayLitres,
                    pendingCosts:  $pendingCosts,
                    lowStockCount: $lowStockCount,
                    managerName:   $manager->name,
                ));
                $this->info("Sent daily summary to {$manager->name} ({$manager->email}) for {$mcc}");
            }
        }

        return self::SUCCESS;
    }
}
