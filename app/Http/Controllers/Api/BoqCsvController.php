<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BoqCsvUpload;
use App\Models\BoqCsvItem;
use Illuminate\Support\Facades\DB;

class BoqCsvController extends Controller
{
    public function index(Request $request)
    {
        $uploads = BoqCsvUpload::where('user_id', $request->user()->id)
            ->where('project_id', $request->header('X-Project-Id'))
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($uploads);
    }

    public function store(Request $request)
    {
        $request->validate([
            'filename' => 'required|string|max:255',
            'items' => 'required|array',
            'items.*.item_id' => 'required|string',
            'items.*.floor_number' => 'required|string',
            'items.*.alias_prefix' => 'sometimes|string|nullable',
            'items.*.legend_dbn_name' => 'sometimes|string|nullable',
        ]);

        DB::beginTransaction();
        try {
            $upload = BoqCsvUpload::create([
                'user_id' => $request->user()->id,
                'project_id' => $request->header('X-Project-Id'),
                'filename' => $request->filename
            ]);

            $itemsData = array_map(function ($item) use ($upload) {
                return [
                'boq_csv_upload_id' => $upload->id,
                'item_id' => $item['item_id'],
                'floor_number' => $item['floor_number'],
                'alias_prefix' => $item['alias_prefix'] ?? null,
                'legend_dbn_name' => $item['legend_dbn_name'] ?? null,
                'status' => 'unassigned',
                'created_at' => now(),
                'updated_at' => now()
                ];
            }, $request->items);

            BoqCsvItem::insert($itemsData);

            DB::commit();
            return response()->json($upload->load('items'), 201);
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to save CSV upload', 'details' => $e->getMessage()], 500);
        }
    }

    public function updateItemStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:unassigned,assigned'
        ]);

        $item = BoqCsvItem::findOrFail($id);

        // Ensure user owns this upload (optional security check)
        if ($item->upload->user_id !== $request->user()->id && !$request->user()->hasRole('supervisor')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $item->update(['status' => $request->status]);

        return response()->json($item);
    }
}