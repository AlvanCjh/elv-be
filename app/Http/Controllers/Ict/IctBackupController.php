<?php

namespace App\Http\Controllers\Ict;

use App\Http\Controllers\Controller;

use App\Models\Ict\IctBackup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IctBackupController extends Controller
{
    /**
     * Display a listing of backups with optional status filtering.
     */
    public function index(Request $request)
    {
        $query = IctBackup::query();

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        } else {
            $query->where('category', 'backup');
        }

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        return response()->json($query->orderBy('backup_date', 'desc')->get());
    }

    /**
     * Store a newly created backup in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'nullable|string',
            'backup_date' => 'required|date',
            'responsible_first_name' => 'required|string',
            'responsible_last_name' => 'required|string',
            'department' => 'required|in:ICT,ELV,SSDC,Others',
            'systems' => 'required', // JSON string from FormData
            'status' => 'required|in:success,unsuccess,recovery,in progress,failed',
            'location' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $data = $request->all();

        // Handle File Upload
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('ict_backups', 'public');
            $data['attachment_url'] = 'storage/' . $path;
        }

        // Decode JSON systems
        if (isset($data['systems']) && is_string($data['systems'])) {
            $data['systems'] = json_decode($data['systems'], true);
        }

        // Decode JSON custom_fields from dynamic masterform fields
        if (isset($data['custom_fields']) && is_string($data['custom_fields'])) {
            $data['custom_fields'] = json_decode($data['custom_fields'], true);
        }

        $backup = IctBackup::create($data);

        return response()->json([
            'message' => 'Backup record created successfully!',
            'backup' => $backup
        ], 201);
    }


    /**
     * Remove the specified backup from storage.
     */
    public function destroy($id)
    {
        $backup = IctBackup::findOrFail($id);
        
        if ($backup->attachment_url) {
            Storage::disk('public')->delete(str_replace('storage/', '', $backup->attachment_url));
        }

        $backup->delete();

        return response()->json(['message' => 'Backup record deleted.']);
    }
}
