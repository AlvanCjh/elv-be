<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Message;
use App\Models\User;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $receiverId = $request->query('receiver_id');

        $query = Message::with(['sender']);

        if ($receiverId) {
            $query->where(function($q) use ($userId, $receiverId) {
                $q->where('sender_id', $userId)->where('receiver_id', $receiverId);
            })->orWhere(function($q) use ($userId, $receiverId) {
                $q->where('sender_id', $receiverId)->where('receiver_id', $userId);
            });
        } else {
            $query->whereNull('receiver_id');
        }

        return $query->orderBy('created_at', 'asc')->take(100)->get()->map(function($msg) {
            $msg->sender->photoURL = $msg->sender->photo_url ? asset($msg->sender->photo_url) : '';
            return $msg;
        });
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'receiver_id' => 'nullable|exists:users,id'
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'content' => $request->content,
        ]);

        $msg = $message->load('sender');
        $msg->sender->photoURL = $msg->sender->photo_url ? asset($msg->sender->photo_url) : '';
        return $msg;
    }

    public function getUsers(Request $request)
    {
        $onlineThreshold = now()->subMinutes(5);
        return User::where('id', '!=', $request->user()->id)->get()->map(function($user) use ($onlineThreshold) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'photoURL' => $user->photo_url ? asset($user->photo_url) : '',
                'last_seen_at' => $user->last_seen_at,
                'is_online' => $user->last_seen_at && $user->last_seen_at >= $onlineThreshold
            ];
        });
    }
}
