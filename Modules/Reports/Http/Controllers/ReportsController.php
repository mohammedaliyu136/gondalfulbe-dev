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
            if ($request->filled('farmer'))    $query->whereHas('farmer', fn($q) => $q->where('name', 'like', '%' . $request->farmer . '%'));
            $collections          = $query->orderByDesc('date')->paginate(50)->withQueryString();
            $summary['total_litres'] = $mc::where('created_by', $creatorId)->sum('quantity_litres');
            $summary['grade_a']   = $mc::where('created_by', $creatorId)->where('quality_grade', 'A')->count();
            $summary['grade_b']   = $mc::where('created_by', $creatorId)->where('quality_grade', 'B')->count();
            $summary['grade_c']   = $mc::where('created_by', $creatorId)->where('quality_grade', 'C')->count();
            $summary['total']     = $mc::where('created_by', $creatorId)->count();
        }

        $mccList = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];
        $dateFrom = $request->date_from ?? '';
        $dateTo   = $request->date_to   ?? '';
        $mcc      = $request->mcc    ?? '';
        $grade    = $request->grade  ?? '';
        $farmer   = $request->farmer ?? '';
        $records  = $collections;
        $summary  = (object) $summary;

        return view('reports::reports.milk-collection', compact('records', 'summary', 'mccList', 'dateFrom', 'dateTo', 'mcc', 'grade', 'farmer'));
    }

    public function logisticsReport(Request $request)
    {
        if (!Auth::user()->can('manage reports')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId   = Auth::user()->creatorId();
        $mccList     = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];
        $dateFrom    = $request->date_from ?? '';
        $dateTo      = $request->date_to   ?? '';
        $mcc         = $request->mcc    ?? '';
        $status      = $request->status ?? '';
        $rider       = $request->rider  ?? '';

        $trips        = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);
        $summary      = (object)['total_trips' => 0, 'total_litres' => 0, 'total_cost' => 0, 'avg_cost_per_litre' => 0];
        $riderRanking = collect();

        $riders = class_exists(\App\Models\Rider::class)
            ? \App\Models\Rider::where('created_by', $creatorId)->orderBy('name')->get()
            : collect();

        if (class_exists(\Modules\Logistics\Models\LogisticsTrip::class)) {
            $lt    = \Modules\Logistics\Models\LogisticsTrip::class;
            $query = $lt::where('created_by', $creatorId);
            if ($mcc)      $query->where('mcc_source', $mcc);
            if ($status)   $query->where('status', $status);
            if ($rider)    $query->where('rider_id', $rider);
            if ($dateFrom) $query->where('trip_date', '>=', $dateFrom);
            if ($dateTo)   $query->where('trip_date', '<=', $dateTo);

            $trips = $query->orderByDesc('trip_date')->paginate(50)->withQueryString();

            $base = $lt::where('created_by', $creatorId);
            $summary = (object)[
                'total_trips'       => (clone $base)->count(),
                'total_litres'      => (clone $base)->sum('litres_transported'),
                'total_cost'        => (clone $base)->selectRaw('sum(fuel_cost + other_expenses) as tc')->value('tc') ?? 0,
                'avg_cost_per_litre'=> (clone $base)->avg('cost_per_litre') ?? 0,
            ];

            $riderRanking = $lt::where('logistics_trips.created_by', $creatorId)
                ->join('riders', 'riders.id', '=', 'logistics_trips.rider_id')
                ->selectRaw('riders.name as rider_name, count(*) as trips, sum(logistics_trips.litres_transported) as litres')
                ->groupBy('riders.id', 'riders.name')
                ->orderByDesc('litres')
                ->get();
        }

        return view('reports::reports.logistics', compact(
            'trips', 'summary', 'riders', 'mccList', 'riderRanking',
            'dateFrom', 'dateTo', 'mcc', 'status', 'rider'
        ));
    }

    public function centerOperationsReport(Request $request)
    {
        if (!Auth::user()->can('manage reports')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId  = Auth::user()->creatorId();
        $centers    = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];
        $categories = ['Labour', 'Cleaning Supplies', 'Maintenance & Repairs', 'Utilities', 'Rent', 'Miscellaneous'];
        $center     = $request->center   ?? '';
        $month      = $request->month    ?? now()->format('Y-m');
        $category   = $request->category ?? '';

        $summary    = (object)['total_spend' => 0, 'approved' => 0, 'pending' => 0, 'paid' => 0];
        $byCategory = collect();
        $byCenter   = collect();

        if (class_exists(\Modules\CenterOperations\Models\CenterCost::class)) {
            $cc   = \Modules\CenterOperations\Models\CenterCost::class;
            $base = $cc::where('created_by', $creatorId);

            if ($center)   $base->where('mcc', $center);
            if ($category) $base->where('category', $category);
            if ($month) {
                [$y, $m] = explode('-', $month);
                $base->whereYear('created_at', $y)->whereMonth('created_at', $m);
            }

            $summary = (object)[
                'total_spend' => (clone $base)->sum('amount'),
                'approved'    => (clone $base)->where('status', 'approved')->sum('amount'),
                'pending'     => (clone $base)->whereIn('status', ['draft', 'submitted'])->sum('amount'),
                'paid'        => (clone $base)->where('status', 'paid')->sum('amount'),
            ];

            $byCategory = (clone $base)->selectRaw('category, count(*) as req_count, sum(amount) as total')
                ->groupBy('category')->orderByDesc('total')->get();

            $byCenter = (clone $base)->selectRaw('mcc as mcc_name, count(*) as req_count, sum(amount) as total,
                sum(case when status in (\'draft\',\'submitted\') then 1 else 0 end) as pending_count')
                ->groupBy('mcc')->orderByDesc('total')->get();
        }

        return view('reports::reports.center-operations', compact(
            'centers', 'center', 'month', 'category', 'categories',
            'summary', 'byCategory', 'byCenter'
        ));
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
                ->where('is_credit', true)
                ->groupBy('agent_id')
                ->with('agent')
                ->get();
        }

        $agents = class_exists(\Modules\Extension\Models\ExtensionAgent::class)
            ? \Modules\Extension\Models\ExtensionAgent::where('created_by', $creatorId)->orderBy('name')->get()
            : collect();

        $centers        = ['Mayo', 'Yola', 'Jabbi Lamba', 'Mubi', 'Sunkani'];
        $center         = $request->center   ?? '';
        $dateFrom       = $request->date_from ?? '';
        $dateTo         = $request->date_to   ?? '';
        $farmersReached = $summary['farmers_reached'];
        $eventsHeld     = $summary['events'];
        $agentsBelow    = $belowTargetAgents;

        $agentStats = collect();
        if (class_exists(\Modules\Extension\Models\FieldVisit::class) && class_exists(\Modules\Extension\Models\ExtensionAgent::class)) {
            $fv = \Modules\Extension\Models\FieldVisit::class;
            $agentStats = $fv::where('field_visits.created_by', $creatorId)
                ->join('extension_agents', 'extension_agents.id', '=', 'field_visits.agent_id')
                ->selectRaw('extension_agents.name as agent_name, field_visits.center as mcc_name,
                    count(*) as visit_count, count(distinct DATE(visit_date)) as days_active')
                ->groupBy('extension_agents.id', 'extension_agents.name', 'field_visits.center')
                ->orderByDesc('visit_count')
                ->get();
        }

        return view('reports::reports.extension', compact(
            'visits', 'summary', 'agents',
            'visitsPerAgent', 'topicCoverage', 'farmerParticipation',
            'belowTargetAgents', 'ossSalesPerAgent', 'outstandingCredit',
            'centers', 'center', 'dateFrom', 'dateTo',
            'farmersReached', 'eventsHeld', 'agentsBelow', 'agentStats'
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
