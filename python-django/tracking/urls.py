from django.urls import path
from . import views

urlpatterns = [
    path('tracking/<str:tracking_number>/', views.get_tracking_info, name='tracking'),
    path('tracking/<str:tracking_number>/evidence/', views.evidence_handler, name='evidence_handler'),
    path('tracking/<str:tracking_number>/evidence/<str:evidence_id>/', views.delete_evidence, name='delete_evidence'),
]
