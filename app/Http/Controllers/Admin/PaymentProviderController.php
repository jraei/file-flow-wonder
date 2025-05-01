<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\PaymentProvider;
use App\Http\Controllers\Controller;

class PaymentProviderController extends Controller
{
    public function index()
    {
        $search = request('search');
        $sort = request('sort', 'id');
        $direction = request('direction', 'asc');

        // Validate the sort field to prevent SQL injection
        $allowedSortFields = ['provider_name', 'kode_merchant', 'status'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }

        // Start query
        $query = PaymentProvider::query();

        // Apply search if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('provider_name', 'like', "%{$search}%")
                    ->orWhere('kode_merchant', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results
        $providers = $query->paginate(5)->withQueryString();

        return Inertia::render('Admin/PaymentProviders', [
            'providers' => $providers,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $provider = PaymentProvider::findOrFail($id);

        // Validate only editable fields
        $validatedData = $request->validate([
            'kode_merchant' => 'nullable|string|max:255',
            'api_id' => 'nullable|string|max:255',
            'api_key' => 'nullable|string|max:255',
            'private_key' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $provider->update($validatedData);

        return back()->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'Payment Provider has been updated!']);
    }

    protected function getProvider($name)
    {
        return PaymentProvider::where('provider_name', $name)->get();
    }
}
