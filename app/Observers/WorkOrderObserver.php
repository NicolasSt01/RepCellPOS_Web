<?php

namespace App\Observers;

use App\Models\WorkOrder;
use App\Models\WorkOrderStatusHistory;
use Illuminate\Support\Facades\Auth;

class WorkOrderObserver
{
    public function created(WorkOrder $workOrder): void
    {
        WorkOrderStatusHistory::create([
            'work_order_id' => $workOrder->id,
            'user_id' => Auth::id(),
            'from_status' => null,
            'to_status' => $workOrder->status,
            'notes' => 'OT creada',
        ]);
    }

    public function updated(WorkOrder $workOrder): void
    {
        if ($workOrder->isDirty('status')) {
            WorkOrderStatusHistory::create([
                'work_order_id' => $workOrder->id,
                'user_id' => Auth::id(),
                'from_status' => $workOrder->getOriginal('status'),
                'to_status' => $workOrder->status,
                'notes' => null,
            ]);
        }
    }
}
