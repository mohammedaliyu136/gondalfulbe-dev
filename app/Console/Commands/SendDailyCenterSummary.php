<?php

namespace App\Console\Commands;

use App\Mail\DailyCenterSummary;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\CenterOperations\Models\CenterCost;

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
            $mcc       = $manager->assigned_mcc;
            $creatorId = $manager->creatorId();

            // Match warehouse by name containing the assigned MCC string
            $warehouseIds = DB::table('warehouses')
                ->where('name', 'like', '%' . $mcc . '%')
                ->where('created_by', $creatorId)
                ->pluck('id');

            // Sum today's purchase quantities across matching warehouses
            $todayLitres = DB::table('purchase_products')
                ->join('purchases', 'purchases.id', '=', 'purchase_products.purchase_id')
                ->whereIn('purchases.warehouse_id', $warehouseIds)
                ->where('purchases.created_by', $creatorId)
                ->whereDate('purchases.purchase_date', today())
                ->sum('purchase_products.quantity');

            $pendingCosts = class_exists(CenterCost::class)
                ? CenterCost::where('created_by', $creatorId)
                    ->where('mcc', $mcc)
                    ->where('status', 'submitted')
                    ->count()
                : 0;

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
