<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Illuminate\View\View;

class TrackingController extends Controller
{
    public function show(string $token): View
    {
        $workOrder = WorkOrder::where('tracking_token', $token)
            ->with(['client', 'tenant'])
            ->firstOrFail();

        return view('tracking.show', compact('workOrder'));
    }
}
