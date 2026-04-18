<?php

namespace Modules\Reports\Http\Controllers;

use App\Models\WeeklyReport;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    public function executiveDashboard()
    {
        if (!Auth::user()->can('manage reports') && !Auth::user()->can('view reports') && !Auth::user()->can('view executive dashboard')) {
            abort(403, __('Permission denied.'));
        }

        $creatorId = Auth::user()->creatorId();

        $activeFarmers = \App\Models\Vender::where('created_by', $creatorId)->where('is_active', 1)->count();
        $todayLitres   = 0;
        $weeklyTrend   = [];
        $mccSummary    = [];

        if (class_exists(\Modules\MilkCollection\Models\MilkCollection::class)) {
            $mc            = \Modules\MilkCollection\Models\MilkCollection::class;
            $todayLitres   = $mc::where('created_by', $creatorId)->whereDate('date', today())->sum('quantity_litres');
            for ($i = 29; $i >= 0; $i--) {
                $day           = now()->subDays($i);
                $weeklyTrend[] = [
                    'date'   => $day->format('d M'),
                    'litres' => $mc::where('created_by', $creatorId)->whereDate('date', $day)->sum('quantity_litres'),
                ];
            }
            foreach (['Mayo','Yola','Jabbi Lamba','Mubi','Sunkani'] as $mcc) {
                $mccSummary[$mcc] = [
                    'today' => $mc::where('created_by', $creatorId)->where('mcc', $mcc)->whereDate('date', today())->sum('quantity_litres'),
                    'month' => $mc::where('created_by', $creatorId)->where('mcc', $mcc)->whereMonth('date', now()->month)->sum('quantity_litres'),
                ];
            }
        }

        $totalFarmers        = \App\Models\Vender::where('created_by', $creatorId)->count();
        $financialInclusion  = $totalFarmers > 0 ? round($activeFarmers / $totalFarmers * 100) : 0;
        $centersOperational  = count(array_filter($mccSummary, fn($m) => $m['today'] > 0));
        $centersBelowTarget  = array_keys(array_filter($mccSummary, fn($m) => $m['today'] == 0));
        // Build rich objects for the view
        $mc = class_exists(\Modules\MilkCollection\Models\MilkCollection::class)
            ? \Modules\MilkCollection\Models\MilkCollection::class : null;

        $centersBelow = collect();
        $activeCenters = collect();

        foreach ($mccSummary as $mccName => $data) {
            $weekLitres  = $mc ? $mc::where('created_by', $creatorId)->where('mcc', $mccName)
                ->whereBetween('date', [now()->subDays(6)->toDateString(), today()->toDateString()])
                ->sum('quantity_litres') : 0;
            $daysActive  = $mc ? $mc::where('created_by', $creatorId)->where('mcc', $mccName)
                ->whereBetween('date', [now()->subDays(6)->toDateString(), today()->toDateString()])
                ->distinct('date')->count('date') : 0;
            $lastDate    = $mc ? $mc::where('created_by', $creatorId)->where('mcc', $mccName)
                ->max('date') : null;

            $obj = (object)[
                'mcc_name'       => $mccName,
                'today_litres'   => $data['today'],
                'week_litres'    => $weekLitres,
                'days_active'    => $daysActive,
                'last_collection'=> $lastDate ?? now()->toDateString(),
            ];

            if ($data['today'] == 0) {
                $centersBelow->push($obj);
            } else {
                $activeCenters->push($obj);
            }
        }
        $latestWeeklyReport  = WeeklyReport::where('created_by', $creatorId)->latest()->first();
        $lowStockCount       = \App\Models\Inventory::whereColumn('quantity', '<=', 'reorder_level')->count();
        $readOnly            = Auth::user()->type === 'board_member';

        // Chart data for the view
        $milkDates    = array_column($weeklyTrend, 'date');
        $milkDatasets = array_column($weeklyTrend, 'litres');
        $dailyTarget  = 1000; // litres/day target — adjust as needed

        // Recent & failed payments
        $recentPayments = collect();
        $failedPayments = 0;
        if (class_exists(\App\Models\PaySlipFarmerBatch::class)) {
            $recentPayments = \App\Models\PaySlipFarmerBatch::where('created_by', $creatorId)
                ->orderByDesc('created_at')->limit(5)->get();
            $failedPayments = \App\Models\PaySlipFarmerBatch::where('created_by', $creatorId)
                ->where('status', 'failed')->count();
        }

        return view('reports::dashboard', compact(
            'activeFarmers', 'todayLitres', 'weeklyTrend', 'mccSummary',
            'financialInclusion', 'centersOperational', 'centersBelowTarget',
            'centersBelow', 'activeCenters',
            'latestWeeklyReport', 'lowStockCount', 'readOnly',
            'milkDates', 'milkDatasets', 'dailyTarget',
            'recentPayments', 'failedPayments'
        ));
    }

    public function downloadWeeklyReport(int $id)
    {
        if (!Auth::user()->can('manage reports')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $report = WeeklyReport::where('created_by', Auth::user()->creatorId())->findOrFail($id);
        $path   = storage_path('app/' . $report->path);

        if (!file_exists($path)) {
            return redirect()->back()->with('error', __('Report file not found.'));
        }

        return response()->download($path, $report->filename);
    }

    public function milkCollectionReport(Request $request)
    {
        if (!Auth::user()->can('manage reports')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId   = Auth::user()->creatorId();
        $collections = collect();
        $summary     = ['total_litres' => 0, 'grade_a' => 0, 'grade_b' => 0, 'grade_c' => 0, 'total' => 0];

        if (class_exists(\Modules\MilkCollection\Models\MilkCollection::class)) {
            $mc    = \Modules\MilkCollection\Models\MilkCollection::class;
            $query = $mc::with('farmer')->where('created_by', $creatorId);
            if ($request->filled('mcc'))       $query->where('mcc', $request->mcc);
            if ($request->filled('grade'))     $query->where('quality_grade', $request->grade);
            if ($request->filled('date_from')) $query->where('date', '>=', $request->date_from);
            if ($request->filled('date_to'))   $query->where('date', '<=', $request->date_to);
            $collections          = $query->orderByDesc('date')->paginate(50)->withQueryString();
            $summary['total_litres'] = $mc::where('created_by', $creatorId)->sum('quantity_litres');
            $summary['grade_a']   = $mc::where('created_by', $creatorId)->where('quality_grade', 'A')->count();
            $summary['grade_b']   = $mc::where('created_by', $creatorId)->where('quality_grade', 'B')->count();
            $summary['grade_c']   = $mc::where('created_by', $creatorId)->where('quality_grade', 'C')->count();
            $summary['total']     = $mc::where('created_by', $creatorId)->count();
        }

        $mccs     = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];
        $grades   = ['A' => 'Premium', 'B' => 'Standard', 'C' => 'Rejected'];
        $dateFrom = $request->date_from ?? '';
        $dateTo   = $request->date_to   ?? '';

        return view('reports::reports.milk-collection', compact('collections', 'summary', 'mccs', 'grades', 'dateFrom', 'dateTo'));
    }

    public function logisticsReport(Request $request)
    {
        if (!Auth::user()->can('manage reports')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId = Auth::user()->creatorId();
        $trips     = collect();
        $summary   = ['total_trips' => 0, 'total_litres' => 0, 'avg_cost_litre' => 0];

        if (class_exists(\Modules\Logistics\Models\LogisticsTrip::class)) {
            $lt    = \Modules\Logistics\Models\LogisticsTrip::class;
            $query = $lt::with('rider')->where('created_by', $creatorId);
            if ($request->filled('mcc'))       $query->where('mcc_source', $request->mcc);
            if ($request->filled('status'))    $query->where('status', $request->status);
            if ($request->filled('date_from')) $query->where('trip_date', '>=', $request->date_from);
            if ($request->filled('date_to'))   $query->where('trip_date', '<=', $request->date_to);
            $trips                     = $query->orderByDesc('trip_date')->paginate(50)->withQueryString();
            $summary['total_trips']    = $lt::where('created_by', $creatorId)->count();
            $summary['total_litres']   = $lt::where('created_by', $creatorId)->sum('litres_transported');
            $summary['avg_cost_litre'] = $lt::where('created_by', $creatorId)->avg('cost_per_litre');
        }

        $mccs     = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];
        $statuses = ['Scheduled', 'In Transit', 'Completed', 'Delayed'];
        $riders   = \App\Models\Rider::where('created_by', $creatorId)->get();
        $dateFrom = $request->date_from ?? '';
        $dateTo   = $request->date_to   ?? '';

        return view('reports::reports.logistics', compact('trips', 'summary', 'mccs', 'statuses', 'riders', 'dateFrom', 'dateTo'));
    }

    public function centerOperationsReport(Request $request)
    {
        if (!Auth::user()->can('manage reports')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId = Auth::user()->creatorId();
        $costs     = collect();
        $summary   = ['total' => 0, 'approved' => 0, 'pending' => 0, 'paid' => 0];

        if (class_exists(\Modules\CenterOperations\Models\CenterCost::class)) {
            $cc    = \Modules\CenterOperations\Models\CenterCost::class;
            $query = $cc::where('created_by', $creatorId);
            if ($request->filled('mcc'))      $query->where('mcc', $request->mcc);
            if ($request->filled('status'))   $query->where('status', $request->status);
            $costs                = $query->orderByDesc('id')->paginate(50)->withQueryString();
            $summary['total']    = $cc::where('created_by', $creatorId)->sum('amount');
            $summary['approved'] = $cc::where('created_by', $creatorId)->where('status', 'approved')->sum('amount');
            $summary['pending']  = $cc::where('created_by', $creatorId)->where('status', 'submitted')->sum('amount');
            $summary['paid']     = $cc::where('created_by', $creatorId)->where('status', 'paid')->sum('amount');
        }

        $mccs       = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];
        $statuses   = ['draft', 'submitted', 'approved', 'rejected', 'paid'];
        $categories = ['Labour', 'Cleaning Supplies', 'Maintenance & Repairs', 'Utilities', 'Rent', 'Miscellaneous'];
        $dateFrom   = $request->date_from ?? '';
        $dateTo     = $request->date_to   ?? '';

        return view('reports::reports.center-operations', compact('costs', 'summary', 'mccs', 'statuses', 'categories', 'dateFrom', 'dateTo'));
    }

    public function requisitionsReport(Request $request)
    {
        if (!Auth::user()->can('manage reports')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId = Auth::user()->creatorId();
        $reqs      = collect();
        $summary   = ['total' => 0, 'pending' => 0, 'approved' => 0, 'total_value' => 0];

        if (class_exists(\Modules\Requisitions\Models\Requisition::class)) {
            $r     = \Modules\Requisitions\Models\Requisition::class;
            $query = $r::with('requester')->where('created_by', $creatorId);
            if ($request->filled('status')) $query->where('status', $request->status);
            if ($request->filled('center')) $query->where('center', $request->center);
            $reqs                = $query->orderByDesc('request_date')->paginate(50)->withQueryString();
            $summary['total']    = $r::where('created_by', $creatorId)->count();
            $summary['pending']  = $r::where('created_by', $creatorId)->where('status', 'pending')->count();
            $summary['approved'] = $r::where('created_by', $creatorId)->whereIn('status', ['approved', 'paid', 'completed'])->count();
            $summary['total_value'] = $r::where('created_by', $creatorId)->sum('total_estimated_cost');
        }

        $mccs     = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];
        $statuses = ['pending', 'supervisor_approved', 'manager_approved', 'approved', 'rejected', 'paid', 'completed'];
        $dateFrom = $request->date_from ?? '';
        $dateTo   = $request->date_to   ?? '';

        return view('reports::reports.requisitions', compact('reqs', 'summary', 'mccs', 'statuses', 'dateFrom', 'dateTo'));
    }

    public function extensionReport(Request $request)
    {
        if (!Auth::user()->can('manage reports')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId = Auth::user()->creatorId();
        $visits    = collect();
        $summary   = ['total_visits' => 0, 'farmers_reached' => 0, 'events' => 0, 'below_target' => 0];

        $visitsPerAgent       = collect();
        $topicCoverage        = collect();
        $farmerParticipation  = collect();
        $belowTargetAgents    = collect();
        $ossSalesPerAgent     = collect();
        $outstandingCredit    = collect();

        if (class_exists(\Modules\Extension\Models\FieldVisit::class)) {
            $fv    = \Modules\Extension\Models\FieldVisit::class;
            $query = $fv::with('agent')->where('created_by', $creatorId);
            if ($request->filled('agent_id'))  $query->where('agent_id', $request->agent_id);
            if ($request->filled('date_from')) $query->where('visit_date', '>=', $request->date_from);
            $visits                         = $query->orderByDesc('visit_date')->paginate(50)->withQueryString();
            $summary['total_visits']        = $fv::where('created_by', $creatorId)->count();
            $summary['farmers_reached']     = \Modules\Extension\Models\VisitFarmer::whereHas('visit', fn($q) => $q->where('created_by', $creatorId))->count();
            $summary['events']              = \Modules\Extension\Models\TrainingEvent::where('created_by', $creatorId)->count();
            $allAgents                      = \Modules\Extension\Models\ExtensionAgent::where('created_by', $creatorId)->get();
            $summary['below_target']        = $allAgents->filter(fn($a) => $a->isBelowTarget())->count();
            $belowTargetAgents              = $allAgents->filter(fn($a) => $a->isBelowTarget());

            $visitsPerAgent = $allAgents->map(fn($a) => [
                'agent'       => $a,
                'this_week'   => $a->visits_this_week,
                'this_month'  => $a->visits()->whereMonth('visit_date', now()->month)->count(),
                'total'       => $a->visits()->count(),
            ]);

            $topicCoverage = \Modules\Extension\Models\VisitTopic::selectRaw('topic, count(*) as count')
                ->whereHas('visit', fn($q) => $q->where('created_by', $creatorId))
                ->groupBy('topic')
                ->orderByDesc('count')
                ->get();

            $farmerParticipation = $allAgents->map(fn($a) => [
                'agent'            => $a,
                'unique_farmers'   => $a->visits()->withCount(['farmers as unique_farmers' => fn($q) => $q->whereNotNull('farmer_id')])->get()->sum('unique_farmers'),
            ]);
        }

        if (class_exists(\Modules\OSS\Models\OssAgentSale::class)) {
            $ossSalesPerAgent = \Modules\OSS\Models\OssAgentSale::selectRaw('agent_id, count(*) as sales_count, sum(total_amount) as total_value')
                ->where('created_by', $creatorId)
                ->groupBy('agent_id')
                ->with('agent')
                ->get();

            $outstandingCredit = \Modules\OSS\Models\OssAgentSale::selectRaw('agent_id, sum(total_amount) as credit_total')
                ->where('created_by', $creatorId)
                ->where('payment_method', 'credit')
                ->where('payment_status', '!=', 'paid')
                ->groupBy('agent_id')
                ->with('agent')
                ->get();
        }

        $agents = class_exists(\Modules\Extension\Models\ExtensionAgent::class)
            ? \Modules\Extension\Models\ExtensionAgent::where('created_by', $creatorId)->orderBy('name')->get()
            : collect();

        $dateFrom = $request->date_from ?? '';
        $dateTo   = $request->date_to   ?? '';

        return view('reports::reports.extension', compact(
            'visits', 'summary', 'agents',
            'visitsPerAgent', 'topicCoverage', 'farmerParticipation',
            'belowTargetAgents', 'ossSalesPerAgent', 'outstandingCredit',
            'dateFrom', 'dateTo'
        ));
    }

    public function inventoryReport(Request $request)
    {
        if (!Auth::user()->can('manage reports')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId = Auth::user()->creatorId();
        $products  = collect();
        $lowStock  = collect();

        if (class_exists(\Modules\OSS\Models\OssProduct::class)) {
            $products = \Modules\OSS\Models\OssProduct::where('created_by', $creatorId)->active()->get();
            $lowStock = $products->filter(fn($p) => $p->isLowStock());
        }

        return view('reports::reports.inventory', compact('products', 'lowStock'));
    }

    public function agentDistributionReport(Request $request)
    {
        if (!Auth::user()->can('manage reports')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId   = Auth::user()->creatorId();
        $allocations = collect();

        if (class_exists(\Modules\OSS\Models\OssAgentAllocation::class)) {
            $allocations = \Modules\OSS\Models\OssAgentAllocation::with('product', 'agent')
                ->where('created_by', $creatorId)
                ->orderByDesc('allocated_date')->paginate(50)->withQueryString();
        }

        $dateFrom = $request->date_from ?? '';
        $dateTo   = $request->date_to   ?? '';

        return view('reports::reports.agent-distribution', compact('allocations', 'dateFrom', 'dateTo'));
    }
}
