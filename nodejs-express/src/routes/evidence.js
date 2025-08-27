const express = require('express');
const multer = require('multer');
const trackingService = require('../services/trackingService');
const evidenceService = require('../services/evidenceService');
const { validateTrackingNumber } = require('../utils/validators');

const router = express.Router();

// Configure multer for file uploads
const upload = multer({
    storage: multer.memoryStorage(),
    limits: {
        fileSize: 5 * 1024 * 1024, // 5MB
        files: 1
    },
    fileFilter: (req, file, cb) => {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (allowedTypes.includes(file.mimetype)) {
            cb(null, true);
        } else {
            cb(new Error('Invalid file type'), false);
        }
    }
});

/**
 * POST /evidence for a tracking number
 * Upload delivery evidence for a package
 */
router.post('/:trackingNumber/evidence', upload.single('image'), async (req, res) => {
    try {
        const { trackingNumber } = req.params;
        const { description, location } = req.body;

        // Validate tracking number format
        const { error, value } = validateTrackingNumber(trackingNumber);
        if (error) {
            return res.status(400).json({
                error: 'invalid_tracking_number',
                message: 'Invalid tracking number format',
                trackingNumber
            });
        }

        // Check if tracking number exists
        const trackingInfo = trackingService.getTrackingInfo(value);
        if (!trackingInfo) {
            return res.status(404).json({
                error: 'tracking_not_found',
                message: 'Tracking number not found',
                trackingNumber
            });
        }

        // Handle file upload
        if (!req.file) {
            return res.status(400).json({
                error: 'missing_file',
                message: 'No image file provided',
                field: 'image'
            });
        }

        // Save evidence
        const evidence = await evidenceService.saveEvidence(
            value, 
            req.file, 
            { description, location }
        );

        res.status(201).json({
            success: true,
            message: 'Delivery evidence uploaded successfully',
            trackingNumber: value,
            evidence: {
                id: evidence.id,
                filename: evidence.filename,
                originalName: evidence.originalName,
                size: evidence.size,
                mimeType: evidence.mimeType,
                uploadedAt: evidence.uploadedAt,
                description: evidence.description,
                location: evidence.location,
                url: evidence.url
            }
        });

    } catch (error) {
        console.error('Evidence upload error:', error);

        // Handle specific errors
        if (error.error) {
            const statusCode = error.error === 'file_too_large' ? 413 : 400;
            return res.status(statusCode).json(error);
        }

        // Handle multer errors
        if (error.code === 'LIMIT_FILE_SIZE') {
            return res.status(413).json({
                error: 'file_too_large',
                message: 'File size exceeds 5MB limit',
                maxSize: '5MB'
            });
        }

        if (error.message === 'Invalid file type') {
            return res.status(400).json({
                error: 'invalid_file',
                message: 'Invalid file format. Only JPEG, PNG, GIF, WebP are allowed',
                allowedTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
            });
        }

        res.status(500).json({
            error: 'internal_server_error',
            message: 'An error occurred while uploading evidence'
        });
    }
});

/**
 * GET /evidence for a tracking number
 * Get all evidence for a tracking number
 */
router.get('/:trackingNumber/evidence', (req, res) => {
    try {
        const { trackingNumber } = req.params;

        // Validate tracking number format
        const { error, value } = validateTrackingNumber(trackingNumber);
        if (error) {
            return res.status(400).json({
                error: 'invalid_tracking_number',
                message: 'Invalid tracking number format',
                trackingNumber
            });
        }

        // Check if tracking number exists
        const trackingInfo = trackingService.getTrackingInfo(value);
        if (!trackingInfo) {
            return res.status(404).json({
                error: 'tracking_not_found',
                message: 'Tracking number not found',
                trackingNumber
            });
        }

        // Get evidence
        const evidence = evidenceService.getEvidenceByTracking(value);

        res.json({
            trackingNumber: value,
            evidenceCount: evidence.length,
            evidence: evidence.map(e => ({
                id: e.id,
                filename: e.filename,
                originalName: e.originalName,
                size: e.size,
                mimeType: e.mimeType,
                uploadedAt: e.uploadedAt,
                description: e.description,
                location: e.location,
                url: e.url
            }))
        });

    } catch (error) {
        console.error('Get evidence error:', error);
        res.status(500).json({
            error: 'internal_server_error',
            message: 'An error occurred while retrieving evidence'
        });
    }
});

/**
 * DELETE /evidence for a tracking number
 * Delete specific evidence
 */
router.delete('/:trackingNumber/evidence/:evidenceId', async (req, res) => {
    try {
        const { trackingNumber, evidenceId } = req.params;

        // Validate tracking number format
        const { error, value } = validateTrackingNumber(trackingNumber);
        if (error) {
            return res.status(400).json({
                error: 'invalid_tracking_number',
                message: 'Invalid tracking number format',
                trackingNumber
            });
        }

        // Check if tracking number exists
        const trackingInfo = trackingService.getTrackingInfo(value);
        if (!trackingInfo) {
            return res.status(404).json({
                error: 'tracking_not_found',
                message: 'Tracking number not found',
                trackingNumber
            });
        }

        // Delete evidence
        await evidenceService.deleteEvidence(value, evidenceId);

        res.json({
            success: true,
            message: 'Evidence deleted successfully',
            trackingNumber: value,
            evidenceId
        });

    } catch (error) {
        console.error('Delete evidence error:', error);

        if (error.error === 'evidence_not_found') {
            return res.status(404).json(error);
        }

        res.status(500).json({
            error: 'internal_server_error',
            message: 'An error occurred while deleting evidence'
        });
    }
});

module.exports = router;
