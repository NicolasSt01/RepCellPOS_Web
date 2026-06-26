<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $allReports = config('reports');

        if ($isSuperAdmin) {
            $reports = $allReports;
        } else {
            $tenant = $user->tenant;
            $reports = collect($allReports)->filter(function ($report) use ($tenant) {
                return $tenant->hasFeature($report['plan_feature']);
            })->all();
        }

        $grouped = collect($reports)->groupBy('area');

        return view('reportes.index', compact('grouped', 'isSuperAdmin'));
    }

    public function redirectToIndex()
    {
        return redirect()->route('reportes.index');
    }
}
