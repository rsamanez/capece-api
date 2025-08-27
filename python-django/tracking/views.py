import re
from datetime import datetime
from rest_framework.decorators import api_view, parser_classes
from rest_framework.parsers import MultiPartParser, FormParser
from rest_framework.response import Response
from rest_framework import status
from .services import get_tracking_data
from .evidence_service import evidence_service

def validate_tracking_number(tracking_number):
    """Validate tracking number format"""
    pattern = r'^[A-Z0-9]{10,20}$'
    return re.match(pattern, tracking_number) is not None

@api_view(['GET'])
def get_tracking_info(request, tracking_number):
    """Get tracking information for a package"""
    
    # Validate tracking number format
    if not validate_tracking_number(tracking_number):
        return Response({
            'error': 'invalid_tracking_number',
            'message': 'Invalid tracking number format',
            'trackingNumber': tracking_number
        }, status=status.HTTP_400_BAD_REQUEST)
    
    # Get tracking information
    tracking_info = get_tracking_data(tracking_number)
    
    if not tracking_info:
        return Response({
            'error': 'tracking_not_found',
            'message': 'Tracking number not found',
            'trackingNumber': tracking_number
        }, status=status.HTTP_404_NOT_FOUND)
    
    return Response(tracking_info)


@api_view(['GET', 'POST'])
@parser_classes([MultiPartParser, FormParser])
def evidence_handler(request, tracking_number):
    """Handle evidence operations - GET to retrieve, POST to upload"""
    if request.method == 'GET':
        # Validate tracking number format
        if not validate_tracking_number(tracking_number):
            return Response({
                'error': 'invalid_tracking_number',
                'message': 'Invalid tracking number format',
                'trackingNumber': tracking_number
            }, status=status.HTTP_400_BAD_REQUEST)
        
        # Check if tracking number exists
        tracking_info = get_tracking_data(tracking_number)
        if not tracking_info:
            return Response({
                'error': 'tracking_not_found',
                'message': 'Tracking number not found',
                'trackingNumber': tracking_number
            }, status=status.HTTP_404_NOT_FOUND)
        
        # Get evidence
        evidence_list = evidence_service.get_evidence_by_tracking(tracking_number)
        
        return Response({
            'trackingNumber': tracking_number,
            'evidenceCount': len(evidence_list),
            'evidence': [{
                'id': e['id'],
                'filename': e['filename'],
                'originalName': e['original_name'],
                'size': e['size'],
                'mimeType': e['mime_type'],
                'uploadedAt': e.get('uploaded_at'),
                'description': e['description'],
                'location': e['location'],
                'url': e['url']
            } for e in evidence_list]
        }, status=status.HTTP_200_OK)
    elif request.method == 'POST':
        return upload_evidence(request, tracking_number)


def upload_evidence(request, tracking_number):
    """Upload delivery evidence for a package"""
    
    # Validate tracking number format
    if not validate_tracking_number(tracking_number):
        return Response({
            'error': 'invalid_tracking_number',
            'message': 'Invalid tracking number format',
            'trackingNumber': tracking_number
        }, status=status.HTTP_400_BAD_REQUEST)
    
    # Check if tracking number exists
    tracking_info = get_tracking_data(tracking_number)
    if not tracking_info:
        return Response({
            'error': 'tracking_not_found',
            'message': 'Tracking number not found',
            'trackingNumber': tracking_number
        }, status=status.HTTP_404_NOT_FOUND)
    
    # Handle file upload
    if 'image' not in request.FILES:
        return Response({
            'error': 'missing_file',
            'message': 'No image file provided',
            'field': 'image'
        }, status=status.HTTP_400_BAD_REQUEST)
    
    file = request.FILES['image']
    metadata = {
        'description': request.data.get('description', ''),
        'location': request.data.get('location', '')
    }
    
    # Save evidence
    result = evidence_service.save_evidence(tracking_number, file, metadata)
    
    if result['error']:
        error_details = result['details']
        status_code = status.HTTP_413_REQUEST_ENTITY_TOO_LARGE if error_details.get('error') == 'file_too_large' else status.HTTP_400_BAD_REQUEST
        return Response(error_details, status=status_code)
    
    evidence = result['evidence']
    evidence['uploaded_at'] = datetime.now().isoformat()
    
    return Response({
        'success': True,
        'message': 'Delivery evidence uploaded successfully',
        'trackingNumber': tracking_number,
        'evidence': {
            'id': evidence['id'],
            'filename': evidence['filename'],
            'originalName': evidence['original_name'],
            'size': evidence['size'],
            'mimeType': evidence['mime_type'],
            'uploadedAt': evidence['uploaded_at'],
            'description': evidence['description'],
            'location': evidence['location'],
            'url': evidence['url']
        }
    }, status=status.HTTP_201_CREATED)


def get_evidence(request, tracking_number):
    """Get all evidence for a tracking number"""
    
    # Validate tracking number format
    if not validate_tracking_number(tracking_number):
        return Response({
            'error': 'invalid_tracking_number',
            'message': 'Invalid tracking number format',
            'trackingNumber': tracking_number
        }, status=status.HTTP_400_BAD_REQUEST)
    
    # Check if tracking number exists
    tracking_info = get_tracking_data(tracking_number)
    if not tracking_info:
        return Response({
            'error': 'tracking_not_found',
            'message': 'Tracking number not found',
            'trackingNumber': tracking_number
        }, status=status.HTTP_404_NOT_FOUND)
    
    # Get evidence
    evidence_list = evidence_service.get_evidence_by_tracking(tracking_number)
    
    return Response({
        'trackingNumber': tracking_number,
        'evidenceCount': len(evidence_list),
        'evidence': [{
            'id': e['id'],
            'filename': e['filename'],
            'originalName': e['original_name'],
            'size': e['size'],
            'mimeType': e['mime_type'],
            'uploadedAt': e.get('uploaded_at'),
            'description': e['description'],
            'location': e['location'],
            'url': e['url']
        } for e in evidence_list]
    })


@api_view(['DELETE'])
def delete_evidence(request, tracking_number, evidence_id):
    """Delete specific evidence"""
    
    # Validate tracking number format
    if not validate_tracking_number(tracking_number):
        return Response({
            'error': 'invalid_tracking_number',
            'message': 'Invalid tracking number format',
            'trackingNumber': tracking_number
        }, status=status.HTTP_400_BAD_REQUEST)
    
    # Check if tracking number exists
    tracking_info = get_tracking_data(tracking_number)
    if not tracking_info:
        return Response({
            'error': 'tracking_not_found',
            'message': 'Tracking number not found',
            'trackingNumber': tracking_number
        }, status=status.HTTP_404_NOT_FOUND)
    
    # Delete evidence
    result = evidence_service.delete_evidence(tracking_number, evidence_id)
    
    if result['error']:
        return Response(result['details'], status=status.HTTP_404_NOT_FOUND)
    
    return Response({
        'success': True,
        'message': 'Evidence deleted successfully',
        'trackingNumber': tracking_number,
        'evidenceId': evidence_id
    })
