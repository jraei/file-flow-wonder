<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\PayMethod;
use Illuminate\Http\Request;
use App\Models\PaymentProvider;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class PayMethodController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');

        // Validate the sort field to prevent SQL injection
        $allowedSortFields = ['nama', 'kode', 'tipe', 'payment_provider',  'status'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }

        // Start query
        $query = PayMethod::query();

        // Apply search if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%")
                    ->orWhere('tipe', 'like', "%{$search}%")
                    ->orWhere('payment_provider', 'like', "%{$search}%")
                    ->orWhere('norek', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results
        $payMethods = $query->paginate(5)->withQueryString();

        $tipePembayaran = ["Saldo Akun", "E-wallet", "Virtual Account", "Convenience Store", "Transfer Bank", "QRIS"];

        return Inertia::render('Admin/PayMethods', [
            'payMethods' => $payMethods,
            'payment_provider_list' => PaymentProvider::all(),
            'tipe_pembayaran_list' => $tipePembayaran,
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
        $rules = [
            'nama' => 'required|max:255',
            'kode' => 'required|alpha_num|max:50',
            'tipe' => 'required|string|max:50',
            'payment_provider' => 'required|string|max:100',
            'gambar' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
            'keterangan' => 'required|string|max:500',
            'status' => 'required',
        ];

        if ($request->norek) {
            $rules['norek'] = 'required|numeric|digits_between:1,30';
        }

        $payMethod = $request->validate($rules);

        if ($request->hasFile('gambar')) {
            $payMethod['gambar'] = $request->file('gambar')->store('payMethods', 'public');
        }

        PayMethod::create($payMethod);

        // return to_route('categories.index')->with('success', 'New Category has been added!');
        return to_route('pay-methods.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'New Payment Method has been added!']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $payMethod = PayMethod::where('id', $id)->first();
        return response()->json([
            'payMethod' => $payMethod
        ]);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $payMethod = PayMethod::findOrFail($id);
        // update data berdasarkan id
        $rules = [
            'nama' => 'required|max:255',
            'kode' => 'required|alpha_num|max:50',
            'tipe' => 'required|string|max:50',
            'payment_provider' => 'required|string|max:100',
            'keterangan' => 'required|string|max:500',
            'status' => 'required',
        ];

        if ($request->norek) {
            $rules['norek'] = 'required|numeric|digits_between:1,30';
        }

        // Jika ada file baru, tambahkan validasi gambar
        if ($request->hasFile('gambar')) {
            $rules['gambar'] = 'image|mimes:jpeg,png,jpg,webp|max:10240';
        }

        // validasi data
        $validatedData = $request->validate($rules);

        // Simpan file jika ada yang diunggah
        if ($request->hasFile('gambar')) {
            if ($payMethod->gambar) {
                Storage::disk('public')->delete($payMethod->gambar);
            }
            $validatedData['gambar'] = $request->file('gambar')->store('payMethods', 'public');
        }


        $payMethod->update($validatedData);

        return to_route('pay-methods.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Payment Method has been updated!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // hapus data berdasarkan id
        $payMethod = PayMethod::find($id);
        if ($payMethod->gambar) {
            Storage::disk('public')->delete($payMethod->gambar);
        }
        $payMethod->delete();

        return to_route('pay-methods.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Payment Method has been deleted!']);
    }

    public function getMethodsByProvider($id)
    {
        $payProvider = PaymentProvider::find($id);
        if ($payProvider->provider_name == 'Tripay') {
            $tripay = new TripayController();
            $affectedRows = $tripay->getTripayMethod();
            return back()->with('status', ['type' => 'success', 'action' => 'Success', 'text' => $affectedRows . ' tripay methods has been added or updated.']);
        } else {
            return back()->with('status', ['type' => 'error', 'action' => 'Request Error', 'text' => 'Provider not found!']);
        }
    }
}
