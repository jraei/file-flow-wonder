<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Voucher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Date;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');

        // Validate the sort field to prevent SQL injection
        $allowedSortFields = ['id', 'code', 'description', 'discount_type', 'min_purchase', 'max_discount', 'usage_limit', 'status'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }

        // Start query
        $query = Voucher::query();

        // Apply search if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('discount_type', 'like', "%{$search}%")
                    ->orWhere('discount_value', 'like', "%{$search}%")
                    ->orWhere('start_date', 'like', "%{$search}%")
                    ->orWhere('end_date', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results
        $vouchers = $query->paginate(5)
            ->withQueryString()
            ->through(function ($voucher) {
                return [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'description' => $voucher->description,
                    'discount_type' => $voucher->discount_type,
                    'discount_value' => $voucher->discount_value,
                    'min_purchase' => $voucher->min_purchase,
                    'max_discount' => $voucher->max_discount,
                    'usage_limit' => $voucher->usage_limit,
                    'status' => $voucher->status,
                    'start_date' => $voucher->start_date
                        ? Carbon::parse($voucher->start_date)->timezone(config('app.timezone'))->format('Y-m-d\TH:i')
                        : null,
                    'end_date' => $voucher->end_date
                        ? Carbon::parse($voucher->end_date)->timezone(config('app.timezone'))->format('Y-m-d\TH:i')
                        : null,
                ];
            });

        return Inertia::render('Admin/Vouchers', [
            'vouchers' => $vouchers,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction
            ]
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->end_date);
        $rules = [
            'code' => 'required|unique:vouchers,code|max:50|min:3|alpha_dash',
            'discount_type' => 'required',
            'discount_value' => 'required|numeric',
            'min_purchase' => 'required|numeric',
            'status' => 'required'
        ];

        if ($request->max_discount) {
            $rules['max_discount'] = 'required|numeric';
        }
        if ($request->usage_limit) {
            $rules['usage_limit'] = 'required|numeric';
        }
        if ($request->end_date) {
            $rules['end_date'] = 'required|date_format:Y-m-d\TH:i';
        }
        if ($request->description) {
            $rules['description'] = 'required';
        }


        $voucher = $request->validate($rules);
        $voucher['start_date'] = Date::now();

        Voucher::create($voucher);

        // return to_route('categories.index')->with('success', 'New Category has been added!');
        return to_route('vouchers.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'New Voucher has been added!']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $voucher = Voucher::find($id);
        return response()->json([
            'voucher' => $voucher
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $voucher = Voucher::where('id', $id)->first();
        $rules = [
            'discount_type' => 'required',
            'discount_value' => 'required|numeric',
            'min_purchase' => 'required|numeric',
            'status' => 'required'
        ];

        if ($request->code != $voucher->code) {
            $rules['code'] = 'required|unique:vouchers,code|max:50|min:3|alpha_dash';
        }

        if ($request->max_discount) {
            $rules['max_discount'] = 'required|numeric';
        }
        if ($request->usage_limit) {
            $rules['usage_limit'] = 'required|numeric';
        }
        if ($request->end_date) {
            $rules['end_date'] = 'required|date_format:Y-m-d\TH:i';
        }
        if ($request->description) {
            $rules['description'] = 'required';
        }

        $validatedData = $request->validate($rules);

        $voucher->update($validatedData);

        return to_route('vouchers.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Voucher has been updated!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // hapus data berdasarkan id
        // dd($kategori->id);
        $kategori = Voucher::find($id);
        $kategori->delete();

        return to_route('vouchers.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Voucher has been deleted!']);
    }
}
