<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\PayMethod;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Admin\TripayController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $yesterday = now()->subDays(1);

        // Ambil data pembelian 1 hari terakhir
        $pembelian = Pembelian::with('layanan')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $yesterday)
            ->limit(10)
            ->get();

        // Hitung total pembelian 1hari terakhir
        $totalPembelian = Pembelian::where('user_id', $user->id)
            ->where('created_at', '>=', $yesterday)
            ->count();

        // Hitung berdasarkan status, dalam 1hari terakhir
        $pendingPembelian = Pembelian::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('created_at', '>=', $yesterday)
            ->count();

        $processingPembelian = Pembelian::where('user_id', $user->id)
            ->where('status', 'processing')
            ->where('created_at', '>=', $yesterday)
            ->count();

        $completedPembelian = Pembelian::where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', $yesterday)
            ->count();

        $failedPembelian = Pembelian::where('user_id', $user->id)
            ->whereIn('status', ['failed', 'cancelled'])
            ->where('created_at', '>=', $yesterday)
            ->count();

        return Inertia::render('Dashboard/Index', [
            'pembelian' => $pembelian,
            'totalPembelian' => $totalPembelian,
            'pendingPembelian' => $pendingPembelian,
            'processingPembelian' => $processingPembelian,
            'completedPembelian' => $completedPembelian,
            'failedPembelian' => $failedPembelian
        ]);
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
            abort(422, 'You have a pending deposit. Please complete or wait for it to expire.');
            // return redirect()->route('dashboard.topup')
            //     ->with('error', 'You have a pending deposit. Please complete or wait for it to expire.');
        }

        // Get the payment method
        $payMethod = PayMethod::where('nama', $validated['methodName'])->first();
        if (!$payMethod) {
            abort(422, 'Invalid payment method');
            // return redirect()->back()->with('error', 'Invalid payment method');
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

        if (!isset($response['data']['status']) || !isset($response['data']['data']['reference'])) {
            abort(422, 'Payment gateway error. Please try again later.');

            // return redirect()->back()->with('error', 'Payment gateway error. Please try again later.');
        }


        $responseData = $response['data']['data'];

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

        return redirect()->route('invoice.topup', $deposit->deposit_id);
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

    /**
     * Display the transaction history for authenticated user with filters.
     */
    public function transactions(Request $request)
    {
        $user = $request->user();
        $cacheKey = 'transactions_' . $user->id . '_' . md5(json_encode($request->all()));
        $perPage = $request->input('per_page', 10);

        // Try to get from cache with 5 min TTL
        $transactions = Cache::remember($cacheKey, 300, function () use ($request, $user, $perPage) {
            return Pembelian::with(['layanan', 'layanan.produk'])
                ->where('user_id', $user->id)
                ->when($request->input('status'), function ($query, $status) {
                    if ($status !== 'all') {
                        $query->where('status', $status);
                    }
                })
                ->when($request->input('search'), function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('order_id', 'like', "%{$search}%")
                            ->orWhereHas('layanan', function ($q) use ($search) {
                                $q->where('nama_layanan', 'like', "%{$search}%");
                            });
                    });
                })
                ->when($request->input('date_start') && $request->input('date_end'), function ($query) use ($request) {
                    $start = date('Y-m-d 00:00:00', strtotime($request->input('date_start')));
                    $end = date('Y-m-d 23:59:59', strtotime($request->input('date_end')));
                    $query->whereBetween('created_at', [$start, $end]);
                })
                ->when($request->input('sort_by') && $request->input('sort_order'), function ($query) use ($request) {
                    $query->orderBy($request->input('sort_by'), $request->input('sort_order'));
                }, function ($query) {
                    $query->latest();
                })
                ->paginate($perPage)
                ->withQueryString();
        });

        return Inertia::render('Dashboard/Transactions', [
            'transactions' => $transactions,
            'filters' => [
                'status' => $request->input('status', ''),
                'date_start' => $request->input('date_start', ''),
                'date_end' => $request->input('date_end', ''),
                'search' => $request->input('search', ''),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_order' => $request->input('sort_order', 'desc'),
            ],
        ]);
    }

    /**
     * Export transactions to CSV
     */
    public function exportTransactions(Request $request)
    {
        $user = $request->user();

        $transactions = Pembelian::with(['layanan', 'layanan.produk'])
            ->where('user_id', $user->id)
            ->when($request->input('status'), function ($query, $status) {
                if ($status !== 'all') {
                    $query->where('status', $status);
                }
            })
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_id', 'like', "%{$search}%")
                        ->orWhereHas('layanan', function ($q) use ($search) {
                            $q->where('nama_layanan', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('date_start') && $request->input('date_end'), function ($query) use ($request) {
                $start = date('Y-m-d 00:00:00', strtotime($request->input('date_start')));
                $end = date('Y-m-d 23:59:59', strtotime($request->input('date_end')));
                $query->whereBetween('created_at', [$start, $end]);
            })
            ->when($request->input('sort_by') && $request->input('sort_order'), function ($query) use ($request) {
                $query->orderBy($request->input('sort_by'), $request->input('sort_order'));
            }, function ($query) {
                $query->latest();
            })
            ->get();

        // Create CSV file
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['Invoice', 'Item', 'User Input', 'Price', 'Date', 'Status'];

        $callback = function () use ($transactions, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($transactions as $transaction) {
                $row = [
                    $transaction->order_id,
                    $transaction->layanan->nama_layanan ?? 'Unknown Service',
                    $transaction->input_zone ?
                        "{$transaction->input_id} (Zone {$transaction->input_zone})" :
                        $transaction->input_id,
                    'Rp ' . number_format($transaction->price, 0, ',', '.'),
                    $transaction->created_at->format('d-m-Y H:i'),
                    $transaction->status,
                ];
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
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