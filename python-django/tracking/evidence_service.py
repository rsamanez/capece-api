import os
import uuid
from django.conf import settings
from django.core.files.storage import default_storage
from django.core.files.base import ContentFile
from PIL import Image
import io

class EvidenceService:
    def __init__(self):
        self.evidence_dir = 'evidence'
        self.max_file_size = 5 * 1024 * 1024  # 5MB
        self.allowed_formats = ['JPEG', 'PNG', 'GIF', 'WEBP']
        self.allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
        
        # In-memory storage for demo (use database in production)
        self.evidence_store = {}
    
    def validate_file(self, file):
        """Validate uploaded file"""
        errors = []
        
        # Check file size
        if file.size > self.max_file_size:
            errors.append({
                'error': 'file_too_large',
                'message': f'File size {file.size} exceeds maximum allowed size of {self.max_file_size} bytes',
                'maxSize': '5MB',
                'actualSize': f'{file.size} bytes'
            })
        
        # Check MIME type
        if file.content_type not in self.allowed_mime_types:
            errors.append({
                'error': 'invalid_file',
                'message': 'Invalid file format. Only JPEG, PNG, GIF, WebP are allowed',
                'allowedTypes': self.allowed_mime_types,
                'actualType': file.content_type
            })
        
        # Validate image format
        try:
            with Image.open(file) as img:
                if img.format not in self.allowed_formats:
                    errors.append({
                        'error': 'invalid_image_format',
                        'message': 'Invalid image format',
                        'allowedFormats': self.allowed_formats,
                        'actualFormat': img.format
                    })
        except Exception:
            errors.append({
                'error': 'invalid_image',
                'message': 'File is not a valid image'
            })
        
        return errors
    
    def save_evidence(self, tracking_number, file, metadata=None):
        """Save evidence file and metadata"""
        if metadata is None:
            metadata = {}
        
        # Validate file
        validation_errors = self.validate_file(file)
        if validation_errors:
            return {'error': True, 'details': validation_errors[0]}
        
        # Generate unique ID and filename
        evidence_id = str(uuid.uuid4())
        file_extension = os.path.splitext(file.name)[1]
        filename = f"{evidence_id}{file_extension}"
        
        # Create path
        file_path = os.path.join(self.evidence_dir, tracking_number, filename)
        
        try:
            # Save file
            saved_path = default_storage.save(file_path, ContentFile(file.read()))
            
            # Create evidence metadata
            evidence = {
                'id': evidence_id,
                'tracking_number': tracking_number,
                'filename': filename,
                'original_name': file.name,
                'size': file.size,
                'mime_type': file.content_type,
                'uploaded_at': None,  # Will be set by Django
                'description': metadata.get('description', ''),
                'location': metadata.get('location', ''),
                'url': f'/media/{saved_path}',
                'file_path': saved_path
            }
            
            # Store in memory (use database in production)
            if tracking_number not in self.evidence_store:
                self.evidence_store[tracking_number] = []
            
            self.evidence_store[tracking_number].append(evidence)
            
            return {'error': False, 'evidence': evidence}
            
        except Exception as e:
            return {
                'error': True,
                'details': {
                    'error': 'upload_failed',
                    'message': 'Failed to save evidence file',
                    'details': str(e)
                }
            }
    
    def get_evidence_by_tracking(self, tracking_number):
        """Get all evidence for a tracking number"""
        return self.evidence_store.get(tracking_number, [])
    
    def delete_evidence(self, tracking_number, evidence_id):
        """Delete specific evidence"""
        if tracking_number not in self.evidence_store:
            return {
                'error': True,
                'details': {
                    'error': 'evidence_not_found',
                    'message': 'No evidence found for this tracking number'
                }
            }
        
        evidence_list = self.evidence_store[tracking_number]
        evidence_to_delete = None
        
        for evidence in evidence_list:
            if evidence['id'] == evidence_id:
                evidence_to_delete = evidence
                break
        
        if not evidence_to_delete:
            return {
                'error': True,
                'details': {
                    'error': 'evidence_not_found',
                    'message': 'Evidence with specified ID not found'
                }
            }
        
        try:
            # Delete file
            if default_storage.exists(evidence_to_delete['file_path']):
                default_storage.delete(evidence_to_delete['file_path'])
            
            # Remove from store
            evidence_list.remove(evidence_to_delete)
            
            return {'error': False}
            
        except Exception as e:
            return {
                'error': True,
                'details': {
                    'error': 'delete_failed',
                    'message': 'Failed to delete evidence file',
                    'details': str(e)
                }
            }

# Global instance
evidence_service = EvidenceService()
