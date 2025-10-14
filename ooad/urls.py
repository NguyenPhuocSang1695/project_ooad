import django.urls as urls
from django.urls import path, include
from . import views
from django.contrib import admin

urlpatterns = [
    path('', views.ok, name='ok'),
]
