<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business\License;
use App\Models\Business\Tender;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Business\WorkflowControllerTrait;

class LicenseController extends Controller
{
    use WorkflowControllerTrait;

    protected function getModel($id) {
        return License::findOrFail($id);
    }

    /** GET /licenses */
    public function index(): JsonResponse
    {
        $licenses = License::with(['tender:id,project_code,company', 'creator:id,name'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($licenses);
    }

    /** GET /licenses/{id} */
    public function show(int $id): JsonResponse
    {
        $license = License::with(['tender:id,project_code,company', 'creator:id,name'])->findOrFail($id);
        return response()->json($license);
    }

    /** POST /licenses */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tender_id'       => ['nullable', 'integer', 'exists:tenders,id'],
            'company'         => ['nullable', 'string', 'max:100'],
            'project_code'    => ['nullable', 'string', 'max:100'],
            'do_no'           => ['nullable', 'string', 'max:100'],
            'quotation_no'    => ['nullable', 'string', 'max:255'],
            'client_name'     => ['nullable', 'string', 'max:255'],
            'product_name'    => ['nullable', 'string', 'max:500'],
            'serial_no'       => ['nullable', 'string', 'max:255'],
            'start_date'      => ['nullable', 'date'],
            'expiry_date'     => ['nullable', 'date'],
            'validity_period' => ['nullable', 'string', 'max:100'],
        ]);

        $data['created_by'] = $request->user()->id;
        $license = License::create($data);

        return response()->json($license->load(['tender:id,project_code,company', 'creator:id,name']), 201);
    }

    /** PUT /licenses/{id} */
    public function update(Request $request, int $id): JsonResponse
    {
        $license = License::findOrFail($id);

        $data = $request->validate([
            'tender_id'       => ['nullable', 'integer', 'exists:tenders,id'],
            'company'         => ['nullable', 'string', 'max:100'],
            'project_code'    => ['nullable', 'string', 'max:100'],
            'do_no'           => ['nullable', 'string', 'max:100'],
            'quotation_no'    => ['nullable', 'string', 'max:255'],
            'client_name'     => ['nullable', 'string', 'max:255'],
            'product_name'    => ['nullable', 'string', 'max:500'],
            'serial_no'       => ['nullable', 'string', 'max:255'],
            'start_date'      => ['nullable', 'date'],
            'expiry_date'     => ['nullable', 'date'],
            'validity_period' => ['nullable', 'string', 'max:100'],
            'acknowledged'    => ['nullable', 'boolean'],
        ]);

        $license->update($data);

        return response()->json($license->load(['tender:id,project_code,company', 'creator:id,name']));
    }

    /** POST /licenses/{id}/acknowledge */
    public function acknowledge(int $id): JsonResponse
    {
        $license = License::findOrFail($id);
        $license->update([
            'acknowledged'     => true,
            'last_notified_at' => now(),
        ]);

        return response()->json($license->load(['tender:id,project_code,company', 'creator:id,name']));
    }

    /** POST /licenses/{id}/complete */
    public function complete(int $id): JsonResponse
    {
        $license = License::findOrFail($id);
        $license->update([
            'completed'    => true,
            'completed_at' => now(),
            'acknowledged' => true,
        ]);

        return response()->json($license->load(['tender:id,project_code,company', 'creator:id,name']));
    }

    /** POST /licenses/{id}/upload-pdf — upload license PDF document */
    public function uploadPdf(Request $request, int $id): JsonResponse
    {
        $license = License::findOrFail($id);

        $request->validate([
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:20480'], // max 20MB
        ]);

        // Delete old file if exists
        if ($license->pdf_path && Storage::disk('public')->exists($license->pdf_path)) {
            Storage::disk('public')->delete($license->pdf_path);
        }

        $path = $request->file('pdf')->store('licenses/pdfs', 'public');
        $license->update(['pdf_path' => $path]);

        return response()->json([
            'pdf_path' => $path,
            'pdf_url'  => Storage::disk('public')->url($path),
        ]);
    }

    /** DELETE /licenses/{id}/pdf — remove license PDF */
    public function removePdf(int $id): JsonResponse
    {
        $license = License::findOrFail($id);

        if ($license->pdf_path && Storage::disk('public')->exists($license->pdf_path)) {
            Storage::disk('public')->delete($license->pdf_path);
        }

        $license->update(['pdf_path' => null]);

        return response()->json(['message' => 'PDF removed']);
    }

    /** DELETE /licenses/{id} */
    public function destroy(int $id): JsonResponse
    {
        $license = License::findOrFail($id);

        // Clean up PDF file
        if ($license->pdf_path && Storage::disk('public')->exists($license->pdf_path)) {
            Storage::disk('public')->delete($license->pdf_path);
        }

        $license->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
