from django.shortcuts import render
from django.http import HttpResponse
from django.template import loader
from django.core.paginator import Paginator
from django.shortcuts import render, get_object_or_404, redirect
from .models import Province
# Create your views here.

def ok (request):
    template = loader.get_template('ok.html')
    provinces = Province.objects.all()
    context = {'provinces': provinces}
    return HttpResponse(template.render(context, request))