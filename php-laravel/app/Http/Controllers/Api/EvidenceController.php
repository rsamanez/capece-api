<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TrackingService;
use App\Services\EvidenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EvidenceController extends Controller
{
    protected $trackingService;
    protected $evidenceService;

    public function __construct(TrackingService $trackingService, EvidenceService $evidenceService)
    {
        $this->trackingService = $trackingService;
        $this->evidenceService = $evidenceService;
    }

    /**
     * Upload evidence for a tracking number
     */
    public function store(Request $request, string $trackingNumber): JsonResponse
    {
        // Validate tracking number format
        if (!$this->isValidTrackingNumber($trackingNumber)) {
            return response()->json([
                'error' => 'invalid_tracking_number',
                'message' => 'Invalid tracking number format',
                'trackingNumber' => $trackingNumber
            ], 400);
        }

        // Check if tracking number exists
        $trackingInfo = $this->trackingService->getTrackingInfo($trackingNumber);
        if (!$trackingInfo) {
            return response()->json([
                'error' => 'tracking_not_found',
                'message' => 'Tracking number not found',
                'trackingNumber' => $trackingNumber
            ], 404);
        }

        // Validate request
        $validator = validator($request->all(), [
            'image' => 'required|file|image|max:5120', // 5MB max
            'description' => 'string|max:500',
            'location' => 'string|max:200'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'validation_failed',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        // Handle file upload
        if (!$request->hasFile('image')) {
            return response()->json([
                'error' => 'missing_file',
                'message' => 'No image file provided',
                'field' => 'image'
            ], 400);
        }

        $file = $request->file('image');
        $metadata = [
            'description' => $request->input('description', ''),
            'location' => $request->input('location', '')
        ];

        // Save evidence
        $result = $this->evidenceService->saveEvidence($trackingNumber, $file, $metadata);

        if ($result['error']) {
            $statusCode = ($result['details']['error'] === 'file_too_large') ? 413 : 400;
            return response()->json($result['details'], $statusCode);
        }

        $evidence = $result['evidence'];

        return response()->json([
            'success' => true,
            'message' => 'Delivery evidence uploaded successfully',
            'trackingNumber' => $trackingNumber,
            'evidence' => [
                'id' => $evidence['id'],
                'filename' => $evidence['filename'],
                'originalName' => $evidence['original_name'],
                'size' => $evidence['size'],
                'mimeType' => $evidence['mime_type'],
                'uploadedAt' => $evidence['uploaded_at'],
                'description' => $evidence['description'],
                'location' => $evidence['location'],
                'url' => $evidence['url']
            ]
        ], 201);
    }

    /**
     * Get all evidence for a tracking number
     */
    public function index(string $trackingNumber): JsonResponse
    {
        // Validate tracking number format
        if (!$this->isValidTrackingNumber($trackingNumber)) {
            return response()->json([
                'error' => 'invalid_tracking_number',
                'message' => 'Invalid tracking number format',
                'trackingNumber' => $trackingNumber
            ], 400);
        }

        // Check if tracking number exists
        $trackingInfo = $this->trackingService->getTrackingInfo($trackingNumber);
        if (!$trackingInfo) {
            return response()->json([
                'error' => 'tracking_not_found',
                'message' => 'Tracking number not found',
                'trackingNumber' => $trackingNumber
            ], 404);
        }

        // Get evidence
        $evidenceList = $this->evidenceService->getEvidenceByTracking($trackingNumber);

        return response()->json([
            'trackingNumber' => $trackingNumber,
            'evidenceCount' => count($evidenceList),
            'evidence' => array_map(function($e) {
                return [
                    'id' => $e['id'],
                    'filename' => $e['filename'],
                    'originalName' => $e['original_name'],
                    'size' => $e['size'],
                    'mimeType' => $e['mime_type'],
                    'uploadedAt' => $e['uploaded_at'],
                    'description' => $e['description'],
                    'location' => $e['location'],
                    'url' => $e['url']
                ];
            }, $evidenceList)
        ]);
    }

    /**
     * Delete specific evidence
     */
    public function destroy(string $trackingNumber, string $evidenceId): JsonResponse
    {
        // Validate tracking number format
        if (!$this->isValidTrackingNumber($trackingNumber)) {
            return response()->json([
                'error' => 'invalid_tracking_number',
                'message' => 'Invalid tracking number format',
                'trackingNumber' => $trackingNumber
            ], 400);
        }

        // Check if tracking number exists
        $trackingInfo = $this->trackingService->getTrackingInfo($trackingNumber);
        if (!$trackingInfo) {
            return response()->json([
                'error' => 'tracking_not_found',
                'message' => 'Tracking number not found',
                'trackingNumber' => $trackingNumber
            ], 404);
        }

        // Delete evidence
        $result = $this->evidenceService->deleteEvidence($trackingNumber, $evidenceId);

        if ($result['error']) {
            return response()->json($result['details'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Evidence deleted successfully',
            'trackingNumber' => $trackingNumber,
            'evidenceId' => $evidenceId
        ]);
    }

    /**
     * Validate tracking number format
     */
    private function isValidTrackingNumber(string $trackingNumber): bool
    {
        return preg_match('/^[A-Z0-9]{10,20}$/', $trackingNumber);
    }
}
