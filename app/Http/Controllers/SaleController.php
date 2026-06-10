<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->can('pos.access')) {
                abort(403, 'No tienes permiso para acceder a este módulo.');
            }
            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $query = Sale::with('user', 'cashRegister')
            ->where('tenant_id', Auth::user()->tenant_id);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('payment_reference', 'like', "%{$search}%");
            });
        }

        if ($paymentMethod = $request->get('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $sales = $query->latest()->paginate(20);

        return view('sales.index', compact('sales'));
    }

    public function show(Sale $sale): View
    {
        $this->authorizeTenant($sale);
        $sale->load(['saleItems', 'user', 'client', 'cashRegister']);

        return view('sales.show', compact('sale'));
    }

    public function print(Sale $sale)
    {
        $this->authorizeTenant($sale);

        return redirect()->route('pos.print', $sale);
    }

    private function authorizeTenant(Sale $sale): void
    {
        abort_if($sale->tenant_id !== Auth::user()->tenant_id, 403);
    }
}
