<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanAnnotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlanAnnotationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'floor_id' => 'required|exists:floors,id',
            'system_type' => 'required|string',
        ]);

        $annotations = PlanAnnotation::where('floor_id', $request->floor_id)
            ->where('system_type', $request->system_type)
            ->get();

        return response()->json($annotations);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'floor_id' => 'required|exists:floors,id',
            'zone_id' => 'nullable|exists:zones,id',
            'object_id' => 'nullable|exists:object_components,id',
            'system_type' => 'required|string|max:50',
            'annotation_type' => 'required|string|max:50',
            'geometry' => 'required|array',
            'style' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $annotation = PlanAnnotation::create([
            'floor_id' => $request->floor_id,
            'zone_id' => $request->zone_id,
            'object_id' => $request->object_id,
            'system_type' => $request->system_type,
            'annotation_type' => $request->annotation_type,
            'geometry' => $request->geometry,
            'style' => $request->style,
            'created_by' => $request->user()->id ?? 1, // fallback to 1 temporarily if not using sanctum token during test
        ]);

        return response()->json(['message' => 'Annotation created successfully', 'data' => $annotation], 201);
    }
}
