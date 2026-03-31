<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SafetyNotification;

class SafetyNotificationController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $projectId = $request->query('project_id');

        $query = SafetyNotification::with(['sender', 'recipient']);
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $notifications = $query->where(function($q) use ($userId) {
            $q->where('sent_to_all', true)
              ->orWhere('recipient_id', $userId);
        })
        ->latest()
        ->get();
            
        return response()->json($notifications);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string|in:info,alert',
            'sent_to_all' => 'boolean',
            'recipient_id' => 'nullable|exists:users,id',
        ]);

        $data['sender_id'] = $request->user()->id;

        $notification = SafetyNotification::create($data);
        return response()->json($notification->load(['sender', 'recipient']), 201);
    }

    public function destroy($id)
    {
        $notification = SafetyNotification::findOrFail($id);
        $notification->delete();
        return response()->json(null, 204);
    }
}
