<?php
require_once __DIR__ . '/auth.php';
$pageTitle = isset($pageTitle) ? $pageTitle . ' - AFRIDI PHARMACY' : 'AFRIDI PHARMACY - Your Health, Our Priority';
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle); ?></title>
  <meta name="description" content="AFRIDI PHARMACY - Complete online pharmacy and healthcare management system. Order genuine prescription medicines, vitamins, baby care, medical equipment with doorstep delivery.">
  <meta name="theme-color" content="#0f766e">
  
  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  
  <!-- Custom CSS Design System -->
  <link rel="stylesheet" href="<?= SITE_URL; ?>assets/css/style.css">
</head>
<body>
