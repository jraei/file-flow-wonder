<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Layanan;
use Illuminate\Http\Request;
use App\Models\FlashsaleItem;
use App\Models\FlashsaleEvent;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class FlashsaleItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
        $eventId = $request->input('event_id');

        // Validate the sort field to prevent SQL injection
        $allowedSortFields = ['id', 'harga_flashsale', 'stok_tersedia', 'batas_user', 'status'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }

        // Get all flash sale events for the dropdown
        $events = FlashsaleEvent::orderBy('event_start_date', 'desc')->get();

        // Start query for items
        $query = FlashsaleItem::with(['layanan', 'flashsaleEvent']);

        // Filter by event if provided
        if ($eventId) {
            $query->where('flashsale_event_id', $eventId);
        }

        // Apply search if provided
        if ($search) {
            $query->whereHas('layanan', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('status', 'like', "%{$search}%");
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results
        $items = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/FlashsaleItems', [
            'flashsaleItems' => $items,
            'events' => $events,
            'selectedEventId' => $eventId,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'event_id' => $eventId
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used with Inertia.js (handled in frontend)
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'flashsale_event_id' => 'required|exists:flashsale_events,id',
            'layanan_id' => 'required|exists:layanans,id',
            'harga_flashsale' => 'required|numeric|min:1',
            'stok_tersedia' => 'nullable|numeric|min:1',
            'batas_user' => 'nullable|numeric|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        // Get the layanan to validate price
        $layanan = Layanan::findOrFail($validatedData['layanan_id']);

        // Check if item already exists for this event
        $exists = FlashsaleItem::where('flashsale_event_id', $validatedData['flashsale_event_id'])
            ->where('layanan_id', $validatedData['layanan_id'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'layanan_id' => ['This service is already included in the event.'],
            ]);
        }

        // Validate flash sale price must be lower than original price
        // $hargaBeli = $layanan->harga_beli_idr ?: $layanan->harga_beli;
        // if ($validatedData['harga_flashsale'] >= $hargaBeli) {
        //     throw ValidationException::withMessages([
        //         'harga_flashsale' => ['Flash sale price must be lower than the original price.'],
        //     ]);
        // }

        FlashsaleItem::create($validatedData);

        return to_route('flashsale-items.index', [
            'event_id' => $validatedData['flashsale_event_id']
        ])->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'Flash Sale Item has been added!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = FlashsaleItem::with(['layanan', 'layanan.provider', 'layanan.produk', 'flashsaleEvent'])
            ->findOrFail($id);

        return response()->json([
            'item' => $item
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FlashsaleItem $flashsaleItem)
    {
        // Not used with Inertia.js (handled in frontend)
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $flashsaleItem = FlashsaleItem::findOrFail($id);

        $validatedData = $request->validate([
            'harga_flashsale' => 'required|numeric|min:1',
            'stok_tersedia' => 'nullable|numeric|min:1',
            'batas_user' => 'nullable|numeric|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        // Get the layanan to validate price
        $layanan = Layanan::findOrFail($flashsaleItem->layanan_id);

        // Validate flash sale price must be lower than original price
        // $hargaBeli = $layanan->harga_beli_idr ?: $layanan->harga_beli;
        // if ($validatedData['harga_flashsale'] >= $hargaBeli) {
        //     throw ValidationException::withMessages([
        //         'harga_flashsale' => ['Flash sale price must be lower than the original price.'],
        //     ]);
        // }

        $flashsaleItem->update($validatedData);

        return to_route('flashsale-items.index', [
            'event_id' => $flashsaleItem->flashsale_event_id
        ])->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'Flash Sale Item has been updated!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $flashsaleItem = FlashsaleItem::findOrFail($id);
        $eventId = $flashsaleItem->flashsale_event_id;

        $flashsaleItem->delete();

        return to_route('flashsale-items.index', [
            'event_id' => $eventId
        ])->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'Flash Sale Item has been deleted!'
        ]);
    }

    /**
     * Bulk assignment of services to a flash sale event
     */
    public function bulkAssign(Request $request)
    {
        $validatedData = $request->validate([
            'flashsale_event_id' => 'required|exists:flashsale_events,id',
            'layanan_ids' => 'required|array',
            'layanan_ids.*' => 'exists:layanans,id',
            'markup_percentage' => 'required|numeric|between:1,100',
            'stok_tersedia' => 'nullable|numeric|min:1',
            'batas_user' => 'nullable|numeric|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        $eventId = $validatedData['flashsale_event_id'];
        $markupPercentage = $validatedData['markup_percentage'] / 100;

        // Find existing items to avoid duplicates
        $existingItems = FlashsaleItem::where('flashsale_event_id', $eventId)
            ->whereIn('layanan_id', $validatedData['layanan_ids'])
            ->pluck('layanan_id')
            ->toArray();

        $newItems = [];
        $skippedItems = [];

        foreach ($validatedData['layanan_ids'] as $layananId) {
            // Skip if already exists
            if (in_array($layananId, $existingItems)) {
                $skippedItems[] = $layananId;
                continue;
            }

            $layanan = Layanan::find($layananId);
            if (!$layanan) continue;

            $hargaBeli = $layanan->harga_beli_idr ?: $layanan->harga_beli;
            $hargaFlashsale = ceil($hargaBeli * (1 + $markupPercentage));

            // Ensure price is at least 1
            if ($hargaFlashsale < 1) $hargaFlashsale = 1;

            FlashsaleItem::create([
                'flashsale_event_id' => $eventId,
                'layanan_id' => $layananId,
                'harga_flashsale' => $hargaFlashsale,
                'stok_tersedia' => $validatedData['stok_tersedia'],
                'batas_user' => $validatedData['batas_user'],
                'status' => $validatedData['status'],
            ]);

            $newItems[] = $layananId;
        }

        $message = count($newItems) . ' services added to flash sale.';
        if (count($skippedItems) > 0) {
            $message .= ' ' . count($skippedItems) . ' services skipped (already exists).';
        }

        return to_route('flashsale-items.index', [
            'event_id' => $eventId
        ])->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => $message
        ]);
    }

    /**
     * Bulk delete of flash sale items by event
     */
    public function bulkDelete(Request $request)
    {
        $validatedData = $request->validate([
            'flashsale_event_id' => 'required|exists:flashsale_events,id',
        ]);

        $count = FlashsaleItem::where('flashsale_event_id', $validatedData['flashsale_event_id'])->count();
        FlashsaleItem::where('flashsale_event_id', $validatedData['flashsale_event_id'])->delete();

        return response()->json([
            'message' => $count . ' flash sale items have been deleted.',
            'deletedCount' => $count
        ]);
    }

    /**
     * Get available services for an event
     */
    public function availableServices(Request $request)
    {
        // dd($request->all());

        $eventId = $request->input('event_id');

        if (!$eventId) {
            return response()->json([
                'services' => []
            ]);
        }

        // Get IDs of services that are already in the event
        $existingServiceIds = FlashsaleItem::where('flashsale_event_id', $eventId)
            ->pluck('layanan_id')
            ->toArray();

        // Get all active services not already in the event
        $services = Layanan::whereNotIn('id', $existingServiceIds)
            ->where('status', 'active')
            ->with(['produk', 'provider'])
            ->get();


        return response()->json([
            'services' => $services
        ]);
    }
}
