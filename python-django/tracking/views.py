import re
from rest_framework.decorators import api_view
from rest_framework.response import Response
from rest_framework import status
from .services import get_tracking_data

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
    
    return Response(tracking_info, status=status.HTTP_200_OK)
