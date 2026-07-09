<?php
require_once '../config/koneksi.php';

// Cek hak akses
if (!isset($_SESSION['login']) || $_SESSION['hak_akses'] !== 'Admin') {
    set_alert('danger', 'Akses ditolak!');
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // UBAH PASSWORD
    if ($_POST['action'] === 'ubah_password') {
        $id_user = (int)$_POST['id_user'];
        $password_baru = $_POST['password_baru'];
        $konfirmasi_password = $_POST['konfirmasi_password'];
        
        if (empty($id_user) || empty($password_baru) || empty($konfirmasi_password)) {
            set_alert('danger', 'Semua kolom wajib diisi!');
            header("Location: ../index.php?page=kelola_akun");
            exit;
        }
        
        if ($password_baru !== $konfirmasi_password) {
            set_alert('danger', 'Password baru dan konfirmasi tidak cocok!');
            header("Location: ../index.php?page=kelola_akun");
            exit;
        }
        
        // Hash password baru
        $hashed_password = password_hash($password_baru, PASSWORD_BCRYPT);
        
        $query = "UPDATE `user` SET password = '$hashed_password' WHERE id_user = $id_user";
        if (mysqli_query($conn, $query)) {
            set_alert('success', 'Password pengguna berhasil diubah!');
        } else {
            set_alert('danger', 'Gagal mengubah password: ' . mysqli_error($conn));
        }
    }
    
    // HAPUS PENGGUNA
    else if ($_POST['action'] === 'hapus_pengguna') {
        $id_user = (int)$_POST['id_user'];
        
        // Jangan izinkan hapus diri sendiri
        if ($id_user == $_SESSION['id_user']) {
            set_alert('danger', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login!');
            header("Location: ../index.php?page=kelola_akun");
            exit;
        }
        
        $query = "DELETE FROM `user` WHERE id_user = $id_user";
        if (mysqli_query($conn, $query)) {
            set_alert('success', 'Pengguna berhasil dihapus!');
        } else {
            set_alert('danger', 'Gagal menghapus pengguna: ' . mysqli_error($conn));
        }
    }
}

header("Location: ../index.php?page=kelola_akun");
exit;
?>
