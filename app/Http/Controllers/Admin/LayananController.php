<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Produk;
use App\Models\Layanan;
use App\Models\Provider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\MoogoldController;

class LayananController extends Controller
{


    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
        $provider_filter = $request->input('provider_id');

        // Validate the sort field to prevent SQL injection
        $allowedSortFields = ['id', 'nama_layanan', 'kode_layanan', 'produk_id', 'provider_id', 'harga_beli', 'harga_beli_idr', 'status'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }

        // Start query
        $query = Layanan::with(['produk', 'provider']);

        // Apply provider filter if provided
        if ($provider_filter) {
            $query->where('provider_id', $provider_filter);
        }

        // Apply search if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_layanan', 'like', "%{$search}%")
                    ->orWhere('kode_layanan', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results
        $services = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Layanan', [
            'services' => $services,
            'produkList' => Produk::all(),
            'providerList' => Provider::all(),
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'provider_id' => $provider_filter
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'nama_layanan' => 'required|string|max:255',
            'kode_layanan' => 'required|string|max:100|unique:layanans,kode_layanan',
            'produk_id' => 'required|exists:produks,id',
            'provider_id' => 'required|exists:providers,id',
            'harga_beli' => 'required|numeric|min:0',

            'catatan' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ];

        // Validate gambar only if provided
        if ($request->hasFile('gambar')) {
            $rules['gambar'] = 'image|mimes:jpeg,png,jpg,webp|max:10240';
        }

        $validatedData = $request->validate($rules);

        // Handle file upload if present
        if ($request->hasFile('gambar')) {
            $validatedData['gambar'] = $request->file('gambar')->store('layanan', 'public');
        }

        // Get provider rate_kurs for calculation
        $provider = Provider::findOrFail($validatedData['provider_id']);
        $validatedData['harga_beli_idr'] = $validatedData['harga_beli'] * $provider->rate_kurs;

        Layanan::create($validatedData);

        return to_route('services.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'New Service has been added!']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $layanan = Layanan::with(['produk', 'provider'])->where('id', $id)->first();
        return response()->json([
            'layanan' => $layanan
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $layanan = Layanan::findOrFail($id);

        $rules = [
            'nama_layanan' => 'required|string|max:255',
            'kode_layanan' => 'required|string|max:100|unique:layanans,kode_layanan,' . $id,
            'produk_id' => 'required|exists:produks,id',
            'provider_id' => 'required|exists:providers,id',
            'harga_beli' => 'required|numeric|min:0',
            'catatan' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ];

        // Validate gambar only if provided
        if ($request->hasFile('gambar')) {
            $rules['gambar'] = 'image|mimes:jpeg,png,jpg,webp|max:10240';
        }

        $validatedData = $request->validate($rules);

        // Handle file upload if present
        if ($request->hasFile('gambar')) {
            if ($layanan->gambar) {
                Storage::disk('public')->delete($layanan->gambar);
            }
            $validatedData['gambar'] = $request->file('gambar')->store('layanan', 'public');
        }

        // Get provider rate_kurs for calculation
        $provider = Provider::findOrFail($validatedData['provider_id']);
        $validatedData['harga_beli_idr'] = $validatedData['harga_beli'] * $provider->rate_kurs;

        $layanan->update($validatedData);

        return to_route('services.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Service has been updated!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $layanan = Layanan::findOrFail($id);

        // Delete image if exists
        if ($layanan->gambar) {
            Storage::disk('public')->delete($layanan->gambar);
        }

        $layanan->delete();

        return to_route('services.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Service has been deleted!']);
    }

    /**
     * Get services from API and store to database
     */
    public function getServicesByProvider(Provider $provider)
    {
        // Get services from API
        if ($provider->provider_name == 'digiflazz') {
            $digiflazz = new DigiflazzController();
            $affectedRow = $digiflazz->getDigiflazzService();

            return back()->with('status', ['type' => 'success', 'action' => 'Success', 'text' => $affectedRow . ' services have been added or updated!']);
        } else if ($provider->provider_name == 'moogold') {
            $moogold = new MoogoldController();
            $affectedArray = $moogold->getMoogoldServices();

            return back()->with('status', ['type' => 'success', 'action' => 'Success', 'text' => $affectedArray['success'] . ' services successfully and ' . $affectedArray['failed'] . ' failed to be added!']);
        } else {
            return back()->with('status', ['type' => 'error', 'action' => 'Request Error', 'text' => 'Provider not found!']);
        }
    }

    /**
     * Delete services by provider
     */
    public function deleteLayanan(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:providers,id'
        ]);

        $count = Layanan::where('provider_id', $request->provider_id)->count();

        // Delete images for all services being deleted
        $services = Layanan::where('provider_id', $request->provider_id)->get();
        foreach ($services as $service) {
            if ($service->gambar) {
                Storage::disk('public')->delete($service->gambar);
            }
        }

        // Delete the services
        Layanan::where('provider_id', $request->provider_id)->delete();

        return back()->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => $count . ' services have been deleted!'
        ]);
    }
}