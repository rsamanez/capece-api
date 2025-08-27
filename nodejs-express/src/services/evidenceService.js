const fs = require('fs').promises;
const path = require('path');
const { v4: uuidv4 } = require('uuid');

class EvidenceService {
    constructor() {
        this.uploadsDir = path.join(__dirname, '../../uploads/evidence');
        this.maxFileSize = 5 * 1024 * 1024; // 5MB
        this.allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        this.evidenceStore = new Map(); // In production, use a database
        this.initializeUploadsDirectory();
    }

    async initializeUploadsDirectory() {
        try {
            await fs.mkdir(this.uploadsDir, { recursive: true });
        } catch (error) {
            console.error('Error creating uploads directory:', error);
        }
    }

    validateFile(file) {
        const errors = [];

        if (!file) {
            errors.push({
                error: 'missing_file',
                message: 'No image file provided',
                field: 'image'
            });
            return errors;
        }

        if (file.size > this.maxFileSize) {
            errors.push({
                error: 'file_too_large',
                message: 'File size exceeds 5MB limit',
                maxSize: '5MB',
                actualSize: this.formatFileSize(file.size)
            });
        }

        if (!this.allowedMimeTypes.includes(file.mimetype)) {
            errors.push({
                error: 'invalid_file',
                message: 'Invalid file format. Only JPEG, PNG, GIF, WebP are allowed',
                allowedTypes: this.allowedMimeTypes,
                actualType: file.mimetype
            });
        }

        return errors;
    }

    async saveEvidence(trackingNumber, file, metadata = {}) {
        const validationErrors = this.validateFile(file);
        if (validationErrors.length > 0) {
            throw validationErrors[0];
        }

        const evidenceId = uuidv4();
        const fileExtension = path.extname(file.originalname);
        const filename = `${evidenceId}${fileExtension}`;
        const trackingDir = path.join(this.uploadsDir, trackingNumber);
        const filePath = path.join(trackingDir, filename);

        try {
            // Create tracking-specific directory
            await fs.mkdir(trackingDir, { recursive: true });

            // Save file
            await fs.writeFile(filePath, file.buffer);

            // Store evidence metadata
            const evidence = {
                id: evidenceId,
                trackingNumber,
                filename,
                originalName: file.originalname,
                size: file.size,
                mimeType: file.mimetype,
                uploadedAt: new Date().toISOString(),
                description: metadata.description || '',
                location: metadata.location || '',
                url: `/uploads/evidence/${trackingNumber}/${filename}`,
                filePath
            };

            // Store in memory (in production, save to database)
            const trackingEvidence = this.evidenceStore.get(trackingNumber) || [];
            trackingEvidence.push(evidence);
            this.evidenceStore.set(trackingNumber, trackingEvidence);

            return evidence;
        } catch (error) {
            throw {
                error: 'upload_failed',
                message: 'Failed to save evidence file',
                details: error.message
            };
        }
    }

    getEvidenceByTracking(trackingNumber) {
        return this.evidenceStore.get(trackingNumber) || [];
    }

    getEvidenceById(trackingNumber, evidenceId) {
        const trackingEvidence = this.evidenceStore.get(trackingNumber) || [];
        return trackingEvidence.find(evidence => evidence.id === evidenceId);
    }

    async deleteEvidence(trackingNumber, evidenceId) {
        const evidence = this.getEvidenceById(trackingNumber, evidenceId);
        if (!evidence) {
            throw {
                error: 'evidence_not_found',
                message: 'Evidence not found'
            };
        }

        try {
            // Delete file
            await fs.unlink(evidence.filePath);

            // Remove from store
            const trackingEvidence = this.evidenceStore.get(trackingNumber) || [];
            const updatedEvidence = trackingEvidence.filter(e => e.id !== evidenceId);
            this.evidenceStore.set(trackingNumber, updatedEvidence);

            return true;
        } catch (error) {
            throw {
                error: 'deletion_failed',
                message: 'Failed to delete evidence file',
                details: error.message
            };
        }
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Get file statistics
    getStats() {
        let totalFiles = 0;
        let totalSize = 0;
        
        for (const trackingEvidence of this.evidenceStore.values()) {
            totalFiles += trackingEvidence.length;
            totalSize += trackingEvidence.reduce((sum, evidence) => sum + evidence.size, 0);
        }

        return {
            totalFiles,
            totalSize: this.formatFileSize(totalSize),
            trackingNumbers: this.evidenceStore.size
        };
    }
}

module.exports = new EvidenceService();
