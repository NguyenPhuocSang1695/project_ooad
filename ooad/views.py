from django.shortcuts import render
from django.http import HttpResponse
from django.template import loader
from django.core.paginator import Paginator
from django.shortcuts import render, get_object_or_404, redirect
# Create your views here.
def index(request):
    template = loader.get_template('admin/index.html')
    return HttpResponse(template.render({}, request))
