from django.urls import path
from . import views

urlpatterns = [
    path('tracking/<str:tracking_number>/', views.get_tracking_info, name='tracking'),
]
