<?php

namespace App\Console\Commands;

use App\Models\Purchase;
use App\Models\PurchaseRequisition;
use App\Models\User;
use App\Models\Vender;
use App\Models\WeeklyReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateWeeklyReport extends Command
{
    protected $signature   = 'gondal:generate-weekly-report';
    protected $description = 'Generate weekly PDF summary report for the Executive Director and Board';

    public function handle(): int
    {
        $weekStart = now()->startOfWeek();
        $weekEnd   = now()->endOfWeek();

        $companies = User::where('type', 'company')->get();

        foreach ($companies as $company) {
            $creatorId = $company->id;

            $activeFarmers      = Vender::where('created_by', $creatorId)->where('is_active', 1)->count();
            $totalFarmers       = Vender::where('created_by', $creatorId)->count();
            $financialInclusion = $totalFarmers > 0 ? round($activeFarmers / $totalFarmers * 100) : 0;

            // Sum purchase_products.quantity for purchases in the week
            $weekLitres = DB::table('purchase_products')
                ->join('purchases', 'purchases.id', '=', 'purchase_products.purchase_id')
                ->where('purchases.created_by', $creatorId)
                ->whereBetween('purchases.purchase_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->sum('purchase_products.quantity');

            // Group by warehouse name as MCC proxy
            $mccRows = DB::table('purchase_products')
                ->join('purchases', 'purchases.id', '=', 'purchase_products.purchase_id')
                ->join('warehouses', 'warehouses.id', '=', 'purchases.warehouse_id')
                ->where('purchases.created_by', $creatorId)
                ->whereBetween('purchases.purchase_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->selectRaw('warehouses.name as mcc, sum(purchase_products.quantity) as week_qty')
                ->groupBy('warehouses.id', 'warehouses.name')
                ->get();

            $mccSummary = [];
            foreach ($mccRows as $row) {
                $mccSummary[$row->mcc] = ['week' => $row->week_qty];
            }

            $centersOperational = count(array_filter($mccSummary, fn($m) => $m['week'] > 0));

            $requisitionsCount = PurchaseRequisition::where('created_by', $creatorId)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();

            $requisitionsByStatus = PurchaseRequisition::where('created_by', $creatorId)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $paymentsTotal = 0;

            $html = view('reports.weekly_pdf', compact(
                'weekStart', 'weekEnd', 'activeFarmers', 'weekLitres',
                'financialInclusion', 'centersOperational', 'requisitionsCount',
                'requisitionsByStatus', 'mccSummary', 'paymentsTotal'
            ))->render();

            $dir = storage_path('app/reports/weekly');
            if (!is_dir($dir)) mkdir($dir, 0755, true);

            $filename = 'weekly_report_' . $weekStart->format('Y-m-d') . '_company_' . $creatorId . '.html';
            $path     = $dir . '/' . $filename;
            file_put_contents($path, $html);

            WeeklyReport::create([
                'filename'   => $filename,
                'path'       => 'reports/weekly/' . $filename,
                'week_start' => $weekStart->toDateString(),
                'week_end'   => $weekEnd->toDateString(),
                'created_by' => $creatorId,
            ]);

            $this->info("Weekly report generated for company {$creatorId}: {$filename}");
        }

        return self::SUCCESS;
    }
}
