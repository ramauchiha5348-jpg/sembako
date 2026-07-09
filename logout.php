<?php
/**
 * Proses Logout Admin
 */
require_once 'config/koneksi.php';

// Hapus semua data session
$_SESSION = array();

// Jika ingin menghapus cookie session, hapus juga cookie tersebut
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Memulai session baru untuk menampung alert logout sukses
session_start();
set_alert('success', 'Anda telah berhasil keluar dari sistem.');

// Redirect ke halaman login
header("Location: login.php");
exit;
?>
