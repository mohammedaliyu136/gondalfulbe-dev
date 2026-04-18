<?php

namespace Modules\SponsorPortal\Http\Controllers\Sponsor;

use Illuminate\Routing\Controller;
use Modules\SponsorPortal\Models\Sponsor;
use Modules\SponsorPortal\Models\SponsorProject;

class DashboardController extends Controller
{
    private function sponsor(): Sponsor
    {
        return auth('sponsor')->user();
    }

    public function index()
    {
        $sponsor  = $this->sponsor();
        $projects = $sponsor->projects()->withCount('farmers')->get();
        return view('sponsorportal::portal.dashboard', compact('sponsor', 'projects'));
    }

    public function project(int $projectId)
    {
        $sponsor = $this->sponsor();
        $project = SponsorProject::where('sponsor_id', $sponsor->id)->findOrFail($projectId);

        $farmerIds    = $project->farmers()->pluck('venders.id')->toArray();
        $totalFarmers = count($farmerIds);
        $activeFarmers = \App\Models\Vender::whereIn('id', $farmerIds)->where('is_active', 1)->count();
        $maleCount    = \App\Models\Vender::whereIn('id', $farmerIds)->where('gender', 'Male')->count();
        $femaleCount  = \App\Models\Vender::whereIn('id', $farmerIds)->where('gender', 'Female')->count();

        $milkMetrics = [];
        if ($totalFarmers > 0 && class_exists(\Modules\MilkCollection\Models\MilkCollection::class)) {
            $mc = \Modules\MilkCollection\Models\MilkCollection::class;
            $milkMetrics['total_litres']  = $mc::whereIn('farmer_id', $farmerIds)->sum('quantity_litres');
            $milkMetrics['grade_a_count'] = $mc::whereIn('farmer_id', $farmerIds)->where('quality_grade', 'A')->count();
            $milkMetrics['total_count']   = $mc::whereIn('farmer_id', $farmerIds)->count();
            $trend = [];
            for ($i = 6; $i >= 0; $i--) {
                $day = now()->subDays($i);
                $trend[] = [
                    'date'   => $day->format('d M'),
                    'litres' => $mc::whereIn('farmer_id', $farmerIds)->whereDate('date', $day)->sum('quantity_litres'),
                ];
            }
            $milkMetrics['weekly_trend'] = $trend;
        }

        return view('sponsorportal::portal.project', compact(
            'sponsor', 'project', 'totalFarmers', 'activeFarmers',
            'maleCount', 'femaleCount', 'milkMetrics'
        ));
    }

    public function downloadReport(int $projectId)
    {
        $sponsor      = $this->sponsor();
        $project      = SponsorProject::where('sponsor_id', $sponsor->id)->findOrFail($projectId);
        $farmerIds    = $project->farmers()->pluck('venders.id')->toArray();
        $totalFarmers = count($farmerIds);
        $milkLitres   = 0;
        if ($totalFarmers > 0 && class_exists(\Modules\MilkCollection\Models\MilkCollection::class)) {
            $milkLitres = \Modules\MilkCollection\Models\MilkCollection::whereIn('farmer_id', $farmerIds)->sum('quantity_litres');
        }
        $data = compact('sponsor', 'project', 'totalFarmers', 'milkLitres');
        $html = view('sponsorportal::portal.report', $data)->render();
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="impact-report-' . $project->project_code . '.html"');
    }
}
