<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EvidenceService
{
    protected $evidenceDir = 'evidence';
    protected $maxFileSize = 5242880; // 5MB in bytes
    protected $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    protected $evidenceStore = []; // In production, use database

    public function __construct()
    {
        // Initialize in-memory store for demo
        $this->evidenceStore = [];
    }

    /**
     * Validate uploaded file
     */
    public function validateFile(UploadedFile $file): array
    {
        $errors = [];

        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            $errors[] = [
                'error' => 'file_too_large',
                'message' => 'File size ' . $file->getSize() . ' exceeds maximum allowed size of ' . $this->maxFileSize . ' bytes',
                'maxSize' => '5MB',
                'actualSize' => $file->getSize() . ' bytes'
            ];
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            $errors[] = [
                'error' => 'invalid_file',
                'message' => 'Invalid file format. Only JPEG, PNG, GIF, WebP are allowed',
                'allowedTypes' => $this->allowedMimeTypes,
                'actualType' => $file->getMimeType()
            ];
        }

        // Additional image validation
        $imageInfo = getimagesize($file->getPathname());
        if (!$imageInfo) {
            $errors[] = [
                'error' => 'invalid_image',
                'message' => 'File is not a valid image'
            ];
        }

        return $errors;
    }

    /**
     * Save evidence file and metadata
     */
    public function saveEvidence(string $trackingNumber, UploadedFile $file, array $metadata = []): array
    {
        // Validate file
        $validationErrors = $this->validateFile($file);
        if (!empty($validationErrors)) {
            return ['error' => true, 'details' => $validationErrors[0]];
        }

        // Generate unique ID and filename
        $evidenceId = Str::uuid()->toString();
        $extension = $file->getClientOriginalExtension();
        $filename = $evidenceId . '.' . $extension;

        // Create path
        $filePath = $this->evidenceDir . '/' . $trackingNumber . '/' . $filename;

        try {
            // Store file
            $storedPath = Storage::disk('public')->putFileAs(
                $this->evidenceDir . '/' . $trackingNumber,
                $file,
                $filename
            );

            // Create evidence metadata
            $evidence = [
                'id' => $evidenceId,
                'tracking_number' => $trackingNumber,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_at' => now()->toISOString(),
                'description' => $metadata['description'] ?? '',
                'location' => $metadata['location'] ?? '',
                'url' => '/storage/' . $storedPath,
                'file_path' => $storedPath
            ];

            // Store in memory (use database in production)
            if (!isset($this->evidenceStore[$trackingNumber])) {
                $this->evidenceStore[$trackingNumber] = [];
            }

            $this->evidenceStore[$trackingNumber][] = $evidence;

            return ['error' => false, 'evidence' => $evidence];

        } catch (\Exception $e) {
            return [
                'error' => true,
                'details' => [
                    'error' => 'upload_failed',
                    'message' => 'Failed to save evidence file',
                    'details' => $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Get all evidence for a tracking number
     */
    public function getEvidenceByTracking(string $trackingNumber): array
    {
        return $this->evidenceStore[$trackingNumber] ?? [];
    }

    /**
     * Delete specific evidence
     */
    public function deleteEvidence(string $trackingNumber, string $evidenceId): array
    {
        if (!isset($this->evidenceStore[$trackingNumber])) {
            return [
                'error' => true,
                'details' => [
                    'error' => 'evidence_not_found',
                    'message' => 'No evidence found for this tracking number'
                ]
            ];
        }

        $evidenceList = &$this->evidenceStore[$trackingNumber];
        $evidenceToDelete = null;
        $evidenceIndex = null;

        foreach ($evidenceList as $index => $evidence) {
            if ($evidence['id'] === $evidenceId) {
                $evidenceToDelete = $evidence;
                $evidenceIndex = $index;
                break;
            }
        }

        if (!$evidenceToDelete) {
            return [
                'error' => true,
                'details' => [
                    'error' => 'evidence_not_found',
                    'message' => 'Evidence with specified ID not found'
                ]
            ];
        }

        try {
            // Delete file
            if (Storage::disk('public')->exists($evidenceToDelete['file_path'])) {
                Storage::disk('public')->delete($evidenceToDelete['file_path']);
            }

            // Remove from store
            unset($evidenceList[$evidenceIndex]);
            $this->evidenceStore[$trackingNumber] = array_values($evidenceList);

            return ['error' => false];

        } catch (\Exception $e) {
            return [
                'error' => true,
                'details' => [
                    'error' => 'delete_failed',
                    'message' => 'Failed to delete evidence file',
                    'details' => $e->getMessage()
                ]
            ];
        }
    }
}
