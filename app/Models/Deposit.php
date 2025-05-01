<?php

namespace App\Models;

use App\Models\User;
use App\Models\PayMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Deposit extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pay_method(): BelongsTo
    {
        return $this->belongsTo(PayMethod::class);
    }

    /**
     * Scope to only this user's deposits.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Add server-side filter/search/sort/pagination for data table.
     * Accepts:
     * - status (pending/paid/failed/cancelled)
     * - search (free text: searches invoice id or maybe amount)
     * - date_range: [start, end] (YYYY-MM-DD or ISO8601)
     * - sort_by: any valid column (default: created_at)
     * - sort_order: asc/desc (default: desc)
     */
    public function scopeWithFilters($query, $request)
    {
        // Filter by status
        $status = $request->input('status');
        if ($status && strtolower($status) !== 'semua' && $status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by date range
        $start = $request->input('date_start');
        $end = $request->input('date_end');
        if ($start && $end) {
            // Coerce valid format, max 30-day span
            $dateStart = date('Y-m-d 00:00:00', strtotime($start));
            $dateEnd = date('Y-m-d 23:59:59', strtotime($end));
            if ((strtotime($dateEnd) - strtotime($dateStart)) <= (60 * 60 * 24 * 30)) {
                $query->whereBetween('created_at', [$dateStart, $dateEnd]);
            }
        }

        // Search filter (search invoice, amount, etc)
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Search by deposit_id, provider_reference, or amount
                $q->where('deposit_id', 'like', "%$search%")
                    ->orWhere('provider_reference', 'like', "%$search%")
                    ->orWhere('amount', 'like', "%$search%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $allowedSorts = ['created_at', 'amount', 'status'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

        return $query;
    }
}
