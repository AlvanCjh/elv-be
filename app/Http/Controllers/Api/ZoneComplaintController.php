<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\ZoneComplaint;
use Illuminate\Http\Request;

class ZoneComplaintController extends Controller
{
    // GET /zones/{id}/complaints?category=BSS
    public function index($zoneId, Request $request)
    {
        $category = $request->query('category');

        $query = ZoneComplaint::with('user')
            ->where('zone_id', $zoneId)
            ->orderBy('created_at', 'desc');

        if ($category && in_array($category, \App\Models\Zone::CATEGORIES)) {
            $query->where('category', $category);
        }

        Zone::findOrFail($zoneId); // ensure zone exists
        return response()->json($query->get());
    }

    // POST /zones/{id}/complaints  body: { message, category }
    public function store(Request $request, $zoneId)
    {
        $request->validate([
            'message'  => 'required|string|min:3',
            'category' => 'nullable|in:BSS,TEL,PAM',
        ]);

        Zone::findOrFail($zoneId);

        $complaint = ZoneComplaint::create([
            'zone_id'  => $zoneId,
            'user_id'  => $request->user()->id,
            'category' => $request->category ?? null,
            'message'  => $request->message,
            'status'   => 'open',
        ]);

        return response()->json($complaint->load('user'), 201);
    }
}
