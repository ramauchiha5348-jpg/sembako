<?php
/**
 * Koneksi Database & Helper Global
 * Sistem Prediksi Penjualan Toko Sembako
 */

// Memulai session secara global jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definisikan konstanta keamanan agar sub-halaman dapat diakses
define('conn', true);

// Konfigurasi Database (Mendukung Environment Variables dari Railway)
$host = $_SERVER['MYSQLHOST'] ?? getenv('MYSQLHOST') ?: "localhost";
$user = $_SERVER['MYSQLUSER'] ?? getenv('MYSQLUSER') ?: "root";
$pass = $_SERVER['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD') ?: "";
$db   = $_SERVER['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE') ?: "db_prediksi_sembako";
$port = $_SERVER['MYSQLPORT'] ?? getenv('MYSQLPORT') ?: 3307; // Port menyesuaikan lingkungan Anda


// Melakukan koneksi ke database
try {
    $conn = mysqli_connect($host, $user, $pass, $db, $port);
    if (!$conn) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }
} catch (mysqli_sql_exception $e) {
    die("<div style='font-family:sans-serif; padding:20px; text-align:center;'>
        <h2>Koneksi Database Gagal (Error 500 Terhindari)</h2>
        <p>Aplikasi Anda tidak dapat terhubung ke database. Ini biasanya terjadi di Railway karena <b>Variabel Environment (Environment Variables) dari MySQL belum dihubungkan ke aplikasi web Anda</b>.</p>
        <p><b>Pesan Error Teknis:</b> " . $e->getMessage() . "</p>
        </div>");
}

/**
 * Format angka ke format Rupiah
 * Contoh: 145000 -> Rp 145.000
 */
function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

/**
 * Mengubah tanggal menjadi format Indonesia
 * Contoh: 2026-05-15 -> 15 Mei 2026
 */
function tanggal_indo($tanggal) {
    $bulan = array (
        1 =>   'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );
    $pecahkan = explode('-', $tanggal);
    
    // $pecahkan[0] = tahun, $pecahkan[1] = bulan, $pecahkan[2] = tanggal
    return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}

/**
 * Mengubah format bulan tahun ke nama bulan Indonesia
 * Contoh: "2026-05" -> "Mei 2026"
 */
function bulan_indo($periode) {
    $bulan = array (
        1 =>   'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );
    $pecahkan = explode('-', $periode);
    if(count($pecahkan) < 2) {
        return $periode;
    }
    return $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}

/**
 * Helper untuk menampilkan alert session bootstrap
 * Digunakan untuk notifikasi sukses, gagal, dll.
 */
function tampilkan_alert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        $type = $alert['type']; // success, danger, warning, info
        $message = $alert['message'];
        
        echo "
        <div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
            <i class='bi " . ($type == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill') . " me-2'></i>
            {$message}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
        
        // Hapus alert setelah ditampilkan
        unset($_SESSION['alert']);
    }
}

/**
 * Helper untuk set alert session
 */
function set_alert($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
}
?>
