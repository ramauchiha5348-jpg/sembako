<?php
// Include koneksi untuk menginisialisasi session
require_once 'config/koneksi.php';

// Jika user sudah login, redirect langsung ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: index.php?page=dashboard");
    exit;
}

// Autoload composer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$google_auth_url = "#";
if (class_exists('Google\Client')) {
    $clientID = $_SERVER['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?: '';
    $clientSecret = $_SERVER['GOOGLE_CLIENT_SECRET'] ?? getenv('GOOGLE_CLIENT_SECRET') ?: '';
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? "https://" : "http://";
    $redirectUri = $protocol . $_SERVER['HTTP_HOST'] . "/google_callback.php";

    $client = new Google\Client();
    $client->setClientId($clientID);
    $client->setClientSecret($clientSecret);
    $client->setRedirectUri($redirectUri);
    $client->addScope("email");
    $client->addScope("profile");
    
    $google_auth_url = $client->createAuthUrl();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Prediksi Penjualan Toko Sembako</title>
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="login-page-custom">

    <div class="login-card">
        <!-- Logo / Title -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle p-3 mb-3 shadow">
                <i class="bi bi-cart-fill fs-2"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">Toko Sembako</h4>
            <p class="text-muted small">Sistem Prediksi Penjualan</p>
        </div>

        <!-- Tampilkan Alert Jika Ada -->
        <?php tampilkan_alert(); ?>

        <!-- Form Login -->
        <form action="proses/proses_login.php" method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label text-dark fw-500">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 border-2"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" name="username" id="username" class="form-control form-control-modern border-start-0 border-2" placeholder="Masukkan username" required autocomplete="off">
                    <div class="invalid-feedback">
                        Username wajib diisi!
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label text-dark fw-500">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 border-2"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" id="password" class="form-control form-control-modern border-start-0 border-2" placeholder="Masukkan password" required>
                    <div class="invalid-feedback">
                        Password wajib diisi!
                    </div>
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-modern-primary w-100 py-2 shadow-sm d-flex align-items-center justify-content-center">
                <i class="bi bi-box-arrow-in-right me-2"></i> Masuk ke Dashboard
            </button>
            
            <div class="position-relative mt-4 mb-3 text-center">
                <hr class="text-muted">
                <span class="position-absolute top-50 start-50 translate-middle px-3 bg-white text-muted fw-bold" style="font-size: 0.85rem;">ATAU</span>
            </div>
            
            <a href="<?= htmlspecialchars($google_auth_url) ?>" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center" style="border-radius: 8px;">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" width="20" class="me-2">
                <span class="text-dark fw-semibold">Masuk sebagai Pengunjung</span>
            </a>
        </form>

        <div class="text-center mt-4">
            <span class="text-muted small">&copy; 2026 Toko Sembako PBW</span>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/custom.js"></script>
</body>
</html>
