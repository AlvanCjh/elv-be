<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BoqItem;
use App\Models\BoqUpload;
use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BoqItemController extends Controller
{
    /**
     * Get BoQ items for a specific floor and category.
     */
    public function index(Request $request)
    {
        $request->validate([
            'floor_id' => 'required|exists:floors,id',
            'category' => 'required|string',
            'project_id' => 'nullable|exists:projects,id'
        ]);

        $query = BoqItem::where('floor_id', $request->floor_id)
            ->where('category', $request->category);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $items = $query->get();

        return response()->json($items);
    }

    /**
     * Upload and parse a Master BOQ CSV for a building.
     */
    public function uploadMaster(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $project = \App\Models\UserProject::findOrFail($request->project_id);
        $file = $request->file('file');
        $csvData = file_get_contents($file->getRealPath());
        $rows = explode("\n", $csvData);

        if (count($rows) <= 1) {
            return response()->json(['message' => 'The uploaded CSV file is empty or invalid.'], 400);
        }

        // Parse the raw (un-lowercased) headers to preserve original casing for logging
        $rawHeaders = str_getcsv(trim($rows[0]));

        // Try to identify headers using lowercased, stripped versions for matching
        $headers = array_map(fn($h) => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $h)), $rawHeaders);

        $headerMap = [];

        // Build a flexible keyword map that covers both old and new CSV formats:
        //   New format: "Floor Level", "Legend/icon", "Quantity", "ID", "Description", "Categories"
        //   Old format: "floor",        "itemcode",    "quantity", "id", "name",        "category"
        $keywordMap = [
            'floor'       => ['floor', 'floorlevel', 'level'],
            'category'    => ['categor', 'categories', 'category', 'system'],
            'itemcode'    => ['legendicon', 'legend', 'icon', 'itemcode', 'code', 'item'],
            'id'          => ['id'],
            'quantity'    => ['qty', 'quantity', 'count'],
            'description' => ['description', 'desc', 'itemname', 'name'],
        ];

        foreach ($headers as $index => $col) {
            foreach ($keywordMap as $field => $keywords) {
                if (isset($headerMap[$field])) continue; // already mapped
                foreach ($keywords as $kw) {
                    if (str_contains($col, $kw)) {
                        $headerMap[$field] = $index;
                        break;
                    }
                }
            }
        }

        \Illuminate\Support\Facades\Log::info("Uploaded BOQ Raw Headers: ", $rawHeaders);
        \Illuminate\Support\Facades\Log::info("Computed Header Map: ", $headerMap);
        if (count($rows) > 1) \Illuminate\Support\Facades\Log::info("First Row: " . $rows[1]);

        // Check for minimum required headers to process (at least itemcode OR description)
        if (!isset($headerMap['itemcode']) && !isset($headerMap['description']) && !isset($headerMap['id'])) {
            return response()->json([
                'message' => 'Invalid CSV format. Could not find a Legend/icon, ID, or Description column.',
                'found_headers' => $rawHeaders,
                'expected_format' => 'Floor Level, Legend/icon, Quantity, ID, Description, Categories',
            ], 400);
        }

        $floorsInBuilding = Floor::where('building_id', $project->building_id)->get()->keyBy('floor_number');

        DB::beginTransaction();

        try {
            // Commented out to allow merging instead of replacement
            // BoqItem::query()->delete();

            Log::info("Starting Master BOQ processing. Rows to process: " . (count($rows) - 1));
            $insertedCount = 0;
            $skippedCount = 0;
            for ($i = 1; $i < count($rows); $i++) {
                $rowStr = trim($rows[$i]);
                if (empty($rowStr)) continue;

                $data = str_getcsv($rowStr);

                if (count($data) <= max($headerMap)) {
                   Log::warning("Row $i skipped: Column count (" . count($data) . ") less than max expected index (" . max($headerMap) . ")");
                   $skippedCount++;
                   continue;
                }

                $floorIndex    = $headerMap['floor']       ?? -1;
                $idIndex       = $headerMap['id']           ?? -1;
                $iconIndex     = $headerMap['itemcode']     ?? -1;
                $qtyIndex      = $headerMap['quantity']     ?? -1;
                $categoryIndex = $headerMap['category']     ?? -1;
                $descIndex     = $headerMap['description']  ?? -1;

                $floorNumber = $floorIndex >= 0 ? trim($data[$floorIndex] ?? '') : '';

                $itemCode = '';
                if ($idIndex >= 0 && !empty(trim($data[$idIndex] ?? ''))) {
                    $itemCode = trim($data[$idIndex]);
                } elseif ($iconIndex >= 0 && !empty(trim($data[$iconIndex] ?? ''))) {
                    $itemCode = trim($data[$iconIndex]);
                }

                $legendIcon = $iconIndex >= 0 ? trim($data[$iconIndex] ?? '') : '';
                $quantity = $qtyIndex >= 0 && isset($data[$qtyIndex]) ? (int) trim($data[$qtyIndex]) : 0;
                $itemName = $descIndex >= 0 && !empty(trim($data[$descIndex] ?? '')) ? trim($data[$descIndex]) : ($legendIcon ?: null);

                if ($categoryIndex >= 0 && isset($data[$categoryIndex]) && !empty(trim($data[$categoryIndex]))) {
                    $category = strtoupper(trim($data[$categoryIndex]));
                } else {
                    $searchStr = strtolower($itemCode . ' ' . $legendIcon . ' ' . $itemName);
                    if (str_contains($searchStr, 'spk') || str_contains($searchStr, 'pa') || str_contains($searchStr, 'speaker') || str_contains($searchStr, 'horn')) {
                        $category = 'PAM';
                    } elseif (str_contains($searchStr, 'tel') || str_contains($searchStr, 'phone') || str_contains($searchStr, 'intercom')) {
                        $category = 'TEL';
                    } else {
                        $category = 'BSS';
                    }
                }

                if (empty($floorNumber)) {
                    preg_match('/-(L\d+)-/i', $itemCode, $m);
                    if (!empty($m[1])) $floorNumber = strtoupper($m[1]);
                }

                if (empty($floorNumber)) {
                    $firstFloor = $floorsInBuilding->first();
                    $floorNumber = $firstFloor ? $firstFloor->floor_number : '';
                }

                if ($quantity <= 0 && !empty($itemCode)) $quantity = 1;

                if (empty($itemCode) || $quantity <= 0) {
                    $skippedCount++;
                    continue;
                }

                $floor = $floorsInBuilding->get($floorNumber);
                if (!$floor) {
                    $csvNumOnly = preg_replace('/\D/', '', $floorNumber);
                    if ($csvNumOnly !== '') {
                        $floor = $floorsInBuilding->first(function($f) use ($csvNumOnly) {
                            // Use integer comparison to handle "01" vs "1"
                            return (int)preg_replace('/\D/', '', $f->floor_number) === (int)$csvNumOnly;
                        });
                    }
                }

                if (!$floor) {
                    Log::warning("Row $i skipped: Floor '$floorNumber' not found in building.");
                    $skippedCount++;
                    continue;
                }

                $boqItem = BoqItem::firstOrNew([
                    'floor_id' => $floor->id,
                    'category' => $category,
                    'item_code' => $itemCode,
                    'project_id' => $project->id,
                ]);
                $boqItem->item_name = $itemName ?: $boqItem->item_name;
                $boqItem->total_quantity = ($boqItem->exists ? $boqItem->total_quantity : 0) + $quantity;
                $boqItem->save();
                
                $insertedCount++;
            }

            Log::info("BOQ items processed. Total $insertedCount inserted, $skippedCount skipped.");
            
            $filePath = $file->storeAs('boq_uploads', time() . '_' . $file->getClientOriginalName(), 'public');
            BoqUpload::create([
                'project_id' => $project->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'total_inserted_rows' => $insertedCount,
            ]);

            DB::commit();
            Log::info("BOQ Upload Transaction committed successfully.");
            return response()->json(['message' => "Successfully processed $insertedCount BOQ records!"]);


        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BOQ Upload Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing CSV: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Clear all BOQ records manually.
     */
    public function clearMaster(Request $request)
    {
        try {
            $projectId = $request->query('project_id') ?? $request->input('project_id');
            if ($projectId) {
                $deletedCount = BoqItem::where('project_id', $projectId)->delete();
            } else {
                $deletedCount = BoqItem::query()->delete();
            }
            Log::info("Master BOQ cleared manually. Deleted $deletedCount records.");
            return response()->json(['message' => 'Successfully wiped existing BOQ data from system.']);
        } catch (\Exception $e) {
            Log::error('BOQ Clear Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to clear BOQ data: ' . $e->getMessage()], 500);
        }
    }
}
