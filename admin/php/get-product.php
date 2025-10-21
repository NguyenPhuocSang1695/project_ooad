<?php
require_once('ProductController.php');

$controller = new ProductController();
$controller->handleGetProductRequest();
$controller->close();
