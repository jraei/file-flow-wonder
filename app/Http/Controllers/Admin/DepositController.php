<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Deposit;
use App\Models\PayMethod;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DepositController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $statusFilter = $request->input('status');

        // Validate the sort field to prevent SQL injection
        $allowedSortFields = ['deposit_id', 'amount', 'status', 'created_at'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'created_at';
        }

        // Start query with relationships
        $query = Deposit::with(['user', 'pay_method']);

        // Apply search if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('deposit_id', 'like', "%{$search}%")
                    ->orWhere('provider_reference', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('username', 'like', "%{$search}%");
                    });
            });
        }

        // Apply status filter if provided
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results (5 per page to match Categories.vue)
        $deposits = $query->paginate(5)->withQueryString();

        // Get payment methods for the create form
        $payMethods = PayMethod::where('status', 'active')->get();
        $users = User::all();

        return Inertia::render('Admin/Deposits', [
            'deposits' => $deposits,
            'payMethods' => $payMethods,
            'users' => $users,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'status' => $statusFilter,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'pay_method_id' => 'required|exists:pay_methods,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,paid,failed,cancelled',
            'provider_reference' => 'required|string|unique:deposits,provider_reference',
        ]);

        // Generate a unique deposit_id
        $validated['deposit_id'] = 'DEP-' . Str::random(10);

        Deposit::create($validated);

        return redirect()->route('deposits.index')
            ->with('status', [
                'type' => 'success',
                'action' => 'Success',
                'text' => 'Deposit created successfully!'
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $deposit = Deposit::with(['user', 'pay_method'])->findOrFail($id);

        return response()->json([
            'deposit' => $deposit
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $deposit = Deposit::findOrFail($id);
        $deposit->delete();

        return redirect()->route('deposits.index')
            ->with('status', [
                'type' => 'success',
                'action' => 'Success',
                'text' => 'Deposit deleted successfully!'
            ]);
    }
}
