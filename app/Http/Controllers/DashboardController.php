
<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\PayMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Admin\TripayController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
            'payMethods' => $payMethods,
            'hasPendingDeposit' => Deposit::forUser($user->id)
                ->pendingAndActive()
                ->exists(),
        ]);
    }
    
    /**
     * Process top-up payment request
     */
    public function processTopup(Request $request)
    {
        $user = Auth::user();
        
        // Validate the request
        $validated = Validator::make($request->all(), [
            'nominal' => 'required|numeric|min:10000',
            'methodName' => 'required|string',
        ])->validate();
        
        // Check for existing pending deposits
        $hasPendingDeposit = Deposit::forUser($user->id)
            ->pendingAndActive()
            ->exists();
            
        if ($hasPendingDeposit) {
            return redirect()->route('dashboard.topup')
                ->with('error', 'You have a pending deposit. Please complete or wait for it to expire.');
        }
        
        // Get the payment method
        $payMethod = PayMethod::where('nama', $validated['methodName'])->first();
        if (!$payMethod) {
            return redirect()->back()->with('error', 'Invalid payment method');
        }
        
        // Create merchant reference
        $merchantRef = 'DEPO' . Carbon::now()->format('mdHis');
        
        // Create transaction with Tripay
        $tripay = new TripayController();
        $itemName = 'Deposit saldo Rp ' . number_format($validated['nominal'], 0, ',', '.');
        
        $response = $tripay->createTransaction([
            'item' => $itemName,
            'price' => $validated['nominal'],
            'quantity' => 1,
            'method' => $payMethod->kode,
            'merchant_ref' => $merchantRef,
            'customer_name' => $user->username ?? 'Guest',
            'customer_email' => $user->email ?? 'guest@example.com',
            'customer_phone' => $user->phone_number ?? '08000000000'
        ]);
        
        if (!isset($response['data']) || !isset($response['data']['reference'])) {
            return redirect()->back()->with('error', 'Payment gateway error. Please try again later.');
        }
        
        $responseData = $response['data'];
        
        // Create deposit record
        $deposit = Deposit::create([
            'user_id' => $user->id,
            'deposit_id' => $merchantRef,
            'provider_reference' => $responseData['reference'],
            'pay_method_id' => $payMethod->id,
            'amount' => $validated['nominal'],
            'fee' => $responseData['total_fee'] ?? 0,
            'qr_url' => $responseData['qr_url'] ?? null,
            'payment_link' => $responseData['checkout_url'] ?? null,
            'expired_time' => Carbon::createFromTimestamp($responseData['expired_time']),
            'status' => 'pending'
        ]);
        
        return redirect()->route('invoice.topup', $deposit->id);
    }
    
    /**
     * Show invoice for a specific deposit
     */
    public function showInvoice(Deposit $deposit)
    {
        // Security check - only allow viewing own deposits
        if ($deposit->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Load relationships
        $deposit->load('pay_method');
        
        return Inertia::render('Dashboard/InvoiceTopup', [
            'deposit' => $deposit,
            'balance' => Auth::user()->saldo,
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
