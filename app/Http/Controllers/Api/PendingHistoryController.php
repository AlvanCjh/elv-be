<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PendingHistory;
use Illuminate\Http\Request;

class PendingHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = PendingHistory::with(['user', 'annotation.floor', 'annotation.zone']);
        
        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }

        if ($request->filled('category') && $request->category !== 'ALL') {
            $query->whereHas('annotation', function($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->filled('project_id')) {
            $query->whereHas('annotation', function($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('remarks', 'like', $s)
                  ->orWhere('base_location', 'like', $s);
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $sortOrder = $request->input('sort', 'desc');
        $histories = $query->orderBy('created_at', $sortOrder === 'asc' ? 'asc' : 'desc')->get();
        return response()->json($histories);
    }
}
