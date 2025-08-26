from django.contrib import admin
from django.urls import path, include
from django.http import JsonResponse
from datetime import datetime

def health_check(request):
    return JsonResponse({
        'status': 'OK',
        'timestamp': datetime.now().isoformat() + 'Z'
    })

urlpatterns = [
    path('admin/', admin.site.urls),
    path('health/', health_check, name='health'),
    path('api/v1/', include('tracking.urls')),
]
