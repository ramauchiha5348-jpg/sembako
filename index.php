<?php
/**
 * Layout Template dan Router Utama
 * Menggunakan AdminLTE 4 & Bootstrap 5
 */

// Include koneksi & helper
require_once 'config/koneksi.php';

// Proteksi Halaman Admin: Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    set_alert('warning', 'Silakan login terlebih dahulu untuk mengakses sistem.');
    header("Location: login.php");
    exit;
}

// Router sederhana
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = ['dashboard', 'produk', 'penjualan', 'prediksi', 'laporan'];

// Mengatur title dinamis
$page_titles = [
    'dashboard' => 'Dashboard Utama',
    'produk' => 'Kelola Data Produk',
    'penjualan' => 'Kelola Data Penjualan',
    'prediksi' => 'Prediksi Penjualan Moving Average',
    'laporan' => 'Laporan Penjualan Produk'
];
$title = isset($page_titles[$page]) ? $page_titles[$page] : 'Sistem Prediksi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title; ?> - Sistem Prediksi Penjualan</title>
    
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- AdminLTE 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta3/dist/css/adminlte.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/custom.css">
    
    <!-- Chart.js (Dipakai di Dashboard dan Prediksi) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

    <!-- Wrapper Utama -->
    <div class="app-wrapper">
        
        <!-- Header / Top Navbar -->
        <nav class="app-header navbar navbar-expand bg-body shadow-sm">
            <div class="container-fluid">
                <!-- Hamburger menu untuk mobile -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="bi bi-list fs-4"></i>
                        </a>
                    </li>
                </ul>
                
                <!-- Navbar Kanan (User Profile info) -->
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item dropdown">
                        <a class="nav-link d-flex align-items-center dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 32px; height: 32px;">
                                <i class="bi bi-person"></i>
                            </div>
                            <span class="d-none d-md-inline fw-semibold text-dark"><?= htmlspecialchars($_SESSION['username']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <a href="logout.php" class="dropdown-item text-danger d-flex align-items-center" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Sidebar Utama -->
        <aside class="app-sidebar shadow" data-bs-theme="dark">
            <!-- Brand Logo -->
            <div class="sidebar-brand text-center py-3">
                <a href="index.php?page=dashboard" class="brand-link text-decoration-none text-white d-flex align-items-center justify-content-center">
                    <i class="bi bi-cart-fill text-info me-2 fs-4"></i>
                    <span class="brand-text fw-bold fs-5">PREDIKSI SEMBAKO</span>
                </a>
            </div>
            
            <!-- Menu Sidebar -->
            <div class="sidebar-wrapper">
                <nav class="mt-3">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                        
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="index.php?page=dashboard" class="nav-link <?= $page == 'dashboard' ? 'active' : ''; ?>">
                                <i class="nav-icon bi bi-speedometer2 me-2"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        
                        <!-- Header Data Master -->
                        <li class="nav-header text-uppercase text-muted small fw-bold px-3 mt-3 mb-1">Data Master</li>

                        <!-- Data Produk -->
                        <li class="nav-item">
                            <a href="index.php?page=produk" class="nav-link <?= $page == 'produk' ? 'active' : ''; ?>">
                                <i class="nav-icon bi bi-box-seam me-2"></i>
                                <p>Data Produk</p>
                            </a>
                        </li>

                        <!-- Data Penjualan -->
                        <li class="nav-item">
                            <a href="index.php?page=penjualan" class="nav-link <?= $page == 'penjualan' ? 'active' : ''; ?>">
                                <i class="nav-icon bi bi-cart-check me-2"></i>
                                <p>Data Penjualan</p>
                            </a>
                        </li>

                        <!-- Header Analisis & Prediksi -->
                        <li class="nav-header text-uppercase text-muted small fw-bold px-3 mt-3 mb-1">Analisis</li>

                        <!-- Prediksi Penjualan -->
                        <li class="nav-item">
                            <a href="index.php?page=prediksi" class="nav-link <?= $page == 'prediksi' ? 'active' : ''; ?>">
                                <i class="nav-icon bi bi-graph-up-arrow me-2"></i>
                                <p>Prediksi Moving Average</p>
                            </a>
                        </li>

                        <!-- Laporan -->
                        <li class="nav-item">
                            <a href="index.php?page=laporan" class="nav-link <?= $page == 'laporan' ? 'active' : ''; ?>">
                                <i class="nav-icon bi bi-file-earmark-pdf me-2"></i>
                                <p>Laporan Penjualan</p>
                            </a>
                        </li>
                        
                        <!-- Divider -->
                        <li class="border-top my-3 border-secondary mx-3"></li>

                        <!-- Logout -->
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link text-danger" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                                <i class="nav-icon bi bi-box-arrow-left me-2"></i>
                                <p>Logout</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <main class="app-main py-4">
            <div class="container-fluid px-4">
                
                <!-- Breadcrumbs & Dynamic Title -->
                <div class="row mb-4 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="h3 m-0 fw-bold text-dark"><?= $title; ?></h1>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <nav aria-label="breadcrumb" class="d-inline-block">
                            <ol class="breadcrumb m-0 bg-transparent p-0">
                                <li class="breadcrumb-item"><a href="index.php?page=dashboard" class="text-decoration-none">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?= ucfirst($page); ?></li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <!-- Notifikasi Global dari CRUD/Proses -->
                <div class="row">
                    <div class="col-12">
                        <?php tampilkan_alert(); ?>
                    </div>
                </div>

                <!-- Include Halaman Dinamis -->
                <?php
                if (in_array($page, $allowed_pages)) {
                    $page_path = "pages/" . $page . ".php";
                    if (file_exists($page_path)) {
                        include $page_path;
                    } else {
                        echo "
                        <div class='card card-modern p-4 text-center shadow-sm'>
                            <i class='bi bi-exclamation-octagon text-danger fs-1 mb-2'></i>
                            <h4 class='fw-bold'>Halaman Tidak Ditemukan</h4>
                            <p class='text-muted'>File halaman <strong>{$page_path}</strong> belum dibuat.</p>
                            <a href='index.php?page=dashboard' class='btn btn-primary d-inline-flex align-items-center justify-content-center mx-auto' style='max-width: 200px;'><i class='bi bi-arrow-left me-2'></i> Kembali ke Dashboard</a>
                        </div>";
                    }
                } else {
                    echo "
                    <div class='card card-modern p-4 text-center shadow-sm'>
                        <i class='bi bi-shield-slash text-danger fs-1 mb-2'></i>
                        <h4 class='fw-bold'>Akses Ditolak</h4>
                        <p class='text-muted'>Anda tidak diizinkan mengakses halaman ini.</p>
                    </div>";
                }
                ?>

            </div>
        </main>

        <!-- Footer -->
        <footer class="app-footer py-3 text-center border-top bg-white">
            <div class="container-fluid">
                <span class="text-muted small">&copy; 2026 <strong>Sistem Prediksi Toko Sembako</strong> - PBW. All Rights Reserved.</span>
            </div>
        </footer>

    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE 4 JS -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta3/dist/js/adminlte.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/custom.js"></script>
</body>
</html>
