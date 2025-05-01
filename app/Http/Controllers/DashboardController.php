<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\PayMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Will pass dashboard overview data here in the future
        return Inertia::render('Dashboard/Index');
    }

    /**
     * Display the balance dashboard with filtered deposit history.
     */
    public function balance(Request $request)
    {
        $user = $request->user();
        // Smart paginator: page size from query or default to 15
        $perPage = $request->input('per_page', 15);

        return Inertia::render('Dashboard/Balance', [
            'balance' => $user->saldo,
            'deposits' => Deposit::forUser($user->id)
                ->withFilters($request)
                ->paginate($perPage)
                ->withQueryString(),
        ]);
    }

    /**
     * Show topup/payment method selection. Data scaffolding, full logic next step.
     */
    public function topup(Request $request)
    {
        $user = $request->user();

        // siapkan payment methods dari model PayMethod
        $payMethods = PayMethod::where('status', 'active')->get();
        return Inertia::render('Dashboard/Topup', [
            'balance' => $user->saldo,
            'payMethods' => $payMethods
        ]);
    }

    public function transactions()
    {
        return Inertia::render('Dashboard/Transactions');
    }

    public function mutations()
    {
        return Inertia::render('Dashboard/Mutations');
    }

    public function affiliate()
    {
        return Inertia::render('Dashboard/Affiliate');
    }
}
