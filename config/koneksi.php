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

// Konfigurasi Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_prediksi_sembako";
$port = 3307; // Port menyesuaikan XAMPP Anda

// Melakukan koneksi ke database
$conn = mysqli_connect($host, $user, $pass, $db, $port);

// Periksa koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
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
