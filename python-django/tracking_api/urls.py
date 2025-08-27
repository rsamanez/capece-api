from django.contrib import admin
from django.urls import path, include
from django.http import JsonResponse
from django.conf import settings
from django.conf.urls.static import static
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

# Serve media files in development
if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
