<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Vender;
use App\Models\WeeklyReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\MilkCollection\Models\MilkCollection;
use Modules\Requisitions\Models\Requisition;

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

            $activeFarmers    = Vender::where('created_by', $creatorId)->where('is_active', 1)->count();
            $totalFarmers     = Vender::where('created_by', $creatorId)->count();
            $financialInclusion = $totalFarmers > 0 ? round($activeFarmers / $totalFarmers * 100) : 0;

            $weekLitres = MilkCollection::where('created_by', $creatorId)
                ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->sum('quantity_litres');

            $mccSummary = [];
            foreach (['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'] as $mcc) {
                $mccSummary[$mcc] = [
                    'week' => MilkCollection::where('created_by', $creatorId)
                        ->where('mcc', $mcc)
                        ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                        ->sum('quantity_litres'),
                ];
            }

            $centersOperational = count(array_filter($mccSummary, fn($m) => $m['week'] > 0));

            $requisitionsCount = Requisition::where('created_by', $creatorId)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();

            $requisitionsByStatus = Requisition::where('created_by', $creatorId)
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

            $dir      = storage_path('app/reports/weekly');
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
