<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Layanan;
use App\Models\Provider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MoogoldController;

class ProviderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');

        // Validate the sort field to prevent SQL injection
        $allowedSortFields = ['id', 'provider_name', 'status', 'rate_kurs'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }

        // Start query
        $query = Provider::query();

        // Apply search if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('provider_name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results
        $providers = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Providers', [
            'providers' => $providers,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction
            ]
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'provider' => Provider::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $provider = Provider::findOrFail($id);

        $validatedData = $request->validate([
            'provider_name' => 'sometimes|required|string|max:255',
            'api_username' => 'nullable|string|max:255',
            'api_key' => 'nullable|string|max:255',
            'api_private_key' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:100',
            'rate_kurs' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:active,inactive',
        ]);

        $provider->update($validatedData);

        // If rate_kurs is updated, update all related services' harga_beli_idr
        if (isset($validatedData['rate_kurs'])) {
            $this->updateServicesRateKurs($provider);
        }

        return back()->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'Provider has been updated!'
        ]);
    }

    public function updateRateKurs(Request $request, $id)
    {
        $provider = Provider::findOrFail($id);

        $validatedData = $request->validate([
            'rate_kurs' => 'required|numeric|min:0',
        ]);

        $provider->update($validatedData);

        // Update all services' harga_beli_idr based on new rate_kurs
        $this->updateServicesRateKurs($provider);

        return back()->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'Rate kurs has been updated and all services are recalculated!'
        ]);
    }

    /**
     * Update all services' harga_beli_idr for a provider based on rate_kurs
     */
    private function updateServicesRateKurs(Provider $provider)
    {
        $services = Layanan::where('provider_id', $provider->id)->get();
        foreach ($services as $service) {
            $service->harga_beli_idr = $service->harga_beli * $provider->rate_kurs;
            $service->save();
        }
    }


    // get balance from digiflazzController
    public function getBalancesByProvider(Provider $provider)
    {
        if ($provider->provider_name == 'digiflazz') {
            $digiflazz = new DigiflazzController();
            $data = $digiflazz->getDigiflazzBalance();
            if ($data['status']) {
                $balance = $data['data'];
                $provider->update([
                    'balance' => $balance,
                    'status' => 'active'
                ]);
                return back()->with('status', [
                    'type' => 'success',
                    'action' => 'Success',
                    'text' => 'Balance has been updated!',
                    'balance' => $balance,
                    'status' => 'active',
                    'provider_id' => $provider->id,

                ]);
            } else {
                $provider->update(['status' => 'inactive']);
                return back()->with('status', [
                    'type' => 'error',
                    'action' => 'Request Error',
                    'text' => $data['message'],
                    'provider_id' => $provider->id,
                    'status' => 'inactive'
                ]);
            }
        } else if ($provider->provider_name == 'moogold') {
            $moogold = new MoogoldController();
            $data = $moogold->getMoogoldBalance();
            if ($data['status']) {
                $balance = $data['data'];
                $provider->update([
                    'balance' => $balance,
                    'status' => 'active'
                ]);
                return back()->with('status', [
                    'type' => 'success',
                    'action' => 'Success',
                    'text' => 'Balance has been updated!',
                    'balance' => $balance,
                    'status' => 'active',
                    'provider_id' => $provider->id,
                ]);
            } else {
                $provider->update(['status' => 'inactive']);
                return back()->with('status', [
                    'type' => 'error',
                    'action' => 'Request Error',
                    'text' => $data['message'],
                    'status' => 'inactive',
                    'provider_id' => $provider->id
                ]);
            }
        }
    }
}
