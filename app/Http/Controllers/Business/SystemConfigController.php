<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business\SystemConfig;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SystemConfigController extends Controller
{
    /**
     * GET /api/system-configs
     */
    public function index(): JsonResponse
    {
        $configs = SystemConfig::all()->pluck('value', 'key');
        return response()->json($configs);
    }

    /**
     * POST /api/system-configs
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'configs' => ['required', 'array'],
        ]);

        foreach ($data['configs'] as $key => $value) {
            SystemConfig::updateOrCreate(
                ['key' => $key],
                ['value' => $value !== null ? strval($value) : null]
            );
        }

        $configs = SystemConfig::all()->pluck('value', 'key');
        return response()->json($configs);
    }
}
