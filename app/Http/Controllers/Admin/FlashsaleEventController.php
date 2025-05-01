<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\FlashsaleEvent;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class FlashsaleEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'event_start_date');
        $direction = $request->input('direction', 'desc');

        // Validate the sort field to prevent SQL injection
        $allowedSortFields = ['id', 'event_name', 'event_start_date', 'event_end_date', 'status'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'event_start_date';
        }

        // Start query
        $query = FlashsaleEvent::query();

        // Apply search if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('event_name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results with item count
        $flashsaleEvents = $query->withCount('item')->paginate(5)->withQueryString();

        return Inertia::render('Admin/FlashsaleEvents', [
            'flashsaleEvents' => $flashsaleEvents,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction
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
            'event_name' => 'required|unique:flashsale_events,event_name|max:100',
            'event_start_date' => 'required|date|after_or_equal:today',
            'event_end_date' => 'required|date|after:event_start_date',
            'status' => 'required|in:active,inactive',
        ]);

        // Check for overlapping active events
        if ($validatedData['status'] === 'active') {
            $this->checkDateOverlap($validatedData['event_start_date'], $validatedData['event_end_date']);
        }

        $flashsaleEvent = FlashsaleEvent::create($validatedData);

        return to_route('flashsale-events.index')->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'Flash Sale Event has been created!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $flashsaleEvent = FlashsaleEvent::with(['item' => function ($query) {
            $query->with('layanan');
        }])->findOrFail($id);

        return response()->json([
            'flashsaleEvent' => $flashsaleEvent
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FlashsaleEvent $flashsaleEvent)
    {
        // Not used with Inertia.js (handled in frontend)
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $flashsaleEvent = FlashsaleEvent::findOrFail($id);

        $validatedData = $request->validate([
            'event_name' => "required|max:100|unique:flashsale_events,event_name,{$id}",
            'event_start_date' => 'required|date',
            'event_end_date' => 'required|date|after:event_start_date',
            'status' => 'required|in:active,inactive',
        ]);

        // For existing events, only validate future dates for upcoming events
        if ($flashsaleEvent->event_start_date->isFuture()) {
            if (Carbon::parse($validatedData['event_start_date'])->isPast()) {
                throw ValidationException::withMessages([
                    'event_start_date' => ['Start date cannot be in the past for upcoming events.'],
                ]);
            }
        }

        // Check for overlapping active events (excluding current event)
        if ($validatedData['status'] === 'active') {
            $this->checkDateOverlap(
                $validatedData['event_start_date'],
                $validatedData['event_end_date'],
                $id
            );
        }

        $flashsaleEvent->update($validatedData);

        return to_route('flashsale-events.index')->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'Flash Sale Event has been updated!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $flashsaleEvent = FlashsaleEvent::findOrFail($id);

        // Check if there are active items
        $itemCount = $flashsaleEvent->item()->count();
        if ($itemCount > 0) {
            return to_route('flashsale-events.index')->with('status', [
                'type' => 'error',
                'action' => 'Error',
                'text' => 'Cannot delete event with items. Remove items first.'
            ]);
        }

        $flashsaleEvent->delete();

        return to_route('flashsale-events.index')->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'Flash Sale Event has been deleted!'
        ]);
    }

    /**
     * Toggle the status of the resource.
     */
    public function toggleStatus($id)
    {
        $flashsaleEvent = FlashsaleEvent::findOrFail($id);
        $newStatus = $flashsaleEvent->status === 'active' ? 'inactive' : 'active';

        // If toggling to active, check for date overlaps
        if ($newStatus === 'active') {
            $this->checkDateOverlap(
                $flashsaleEvent->event_start_date,
                $flashsaleEvent->event_end_date,
                $id
            );
        }

        $flashsaleEvent->update(['status' => $newStatus]);

        return response()->json([
            'status' => $newStatus,
            'message' => 'Flash Sale Event status has been updated!'
        ]);
    }

    /**
     * Check for overlapping dates with active events
     */
    private function checkDateOverlap($startDate, $endDate, $excludeId = null)
    {
        $query = FlashsaleEvent::where('status', 'active')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('event_start_date', [$startDate, $endDate])
                    ->orWhereBetween('event_end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('event_start_date', '<=', $startDate)
                            ->where('event_end_date', '>=', $endDate);
                    });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $overlapping = $query->exists();

        if ($overlapping) {
            throw ValidationException::withMessages([
                'event_dates' => ['Date range overlaps with an existing active flash sale event.'],
            ]);
        }
    }
}
