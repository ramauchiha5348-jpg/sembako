<?php
/**
 * Proses Autentikasi Login Admin
 */
require_once '../config/koneksi.php';

// Cek apakah data dikirim via POST
if (isset($_POST['login'])) {
    // Sanitasi input username untuk keamanan SQL injection
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);
    
    // Validasi input kosong
    if (empty($username) || empty($password)) {
        set_alert('danger', 'Username dan password tidak boleh kosong!');
        header("Location: ../login.php");
        exit;
    }
    
    // Cari user berdasarkan username
    $query = "SELECT * FROM user WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifikasi password hash menggunakan bcrypt
        if (password_verify($password, $user['password'])) {
            // Set session login sukses
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            
            // Set alert selamat datang
            set_alert('success', 'Selamat datang kembali, ' . htmlspecialchars($user['username']) . '!');
            
            // Redirect ke halaman dashboard
            header("Location: ../index.php?page=dashboard");
            exit;
        }
    }
    
    // Jika user tidak ditemukan atau password salah
    set_alert('danger', 'Username atau password salah!');
    header("Location: ../login.php");
    exit;
} else {
    // Jika diakses langsung tanpa POST, redirect ke login
    header("Location: ../login.php");
    exit;
}
?>
