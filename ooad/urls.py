import django.urls as urls
from django.urls import path, include

from project_ooad.urls import urlpatterns
from . import views
from django.contrib import admin

urlpatterns += [
    path('admin/', admin.site.urls),
    path('', views.index, name='index'),
]