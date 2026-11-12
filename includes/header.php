<?php
// includes/header.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SHOP_NAME ?> - ម្ហូប និងភេសជ្ជៈស្រុកយើង</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Top Announcement Bar -->
<div class="top-bar py-2">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-3 text-white small">
            <span class="d-inline-flex align-items-center gap-2">
                <span class="pulse-dot"></span>
                <strong>បើកលក់រាល់ថ្ងៃ៖</strong> ៦:៣០ ព្រឹក ដល់ ៩:០០ យប់
            </span>
            <span class="d-none d-lg-inline opacity-75">|</span>
            <span class="d-none d-lg-inline opacity-90"><i class="bi bi-bicycle me-1"></i> សេវាដឹកជូនដល់កន្លែងក្នុងឃុំស៊ាងឃ្វាង/ត្របែក</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="https://t.me/<?= ltrim(TELEGRAM_USERNAME, '@') ?>" target="_blank" class="phone-pill" style="background: rgba(34, 158, 217, 0.25); border-color: rgba(34, 158, 217, 0.5);">
                <i class="bi bi-telegram text-info"></i> Telegram
            </a>
            <a href="tel:<?= PHONE_1 ?>" class="phone-pill">
                <i class="bi bi-telephone-fill"></i> <?= PHONE_1_DISPLAY ?>
            </a>
            <a href="tel:<?= PHONE_2 ?>" class="phone-pill d-none d-sm-inline-flex">
                <i class="bi bi-telephone-fill"></i> <?= PHONE_2_DISPLAY ?>
            </a>
        </div>
    </div>
</div>

<!-- Main Sticky Navbar -->
<nav class="navbar navbar-expand-lg sticky-top main-navbar py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <span class="brand-icon"><i class="bi bi-cup-hot-fill"></i></span>
            <div>
                <span class="brand-title d-block"><?= SHOP_NAME ?></span>
                <span class="brand-subtitle"><?= SHOP_NAME_KH ?></span>
            </div>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1 mt-3 mt-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="bi bi-house-door me-1"></i> ទំព័រដើម</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#menu"><i class="bi bi-grid me-1"></i> បញ្ជីមុខម្ហូប</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contact"><i class="bi bi-headset me-1"></i> ទំនាក់ទំនង</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-primary fw-semibold" href="https://t.me/<?= ltrim(TELEGRAM_USERNAME, '@') ?>" target="_blank">
                        <i class="bi bi-telegram me-1"></i> Telegram
                    </a>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-outline-orange btn-sm" href="admin/login.php">
                        <i class="bi bi-shield-lock me-1"></i> ចូល Admin
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
