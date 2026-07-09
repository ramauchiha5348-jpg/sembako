<?php
require_once 'config/koneksi.php';
// Autoload composer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$clientID = $_SERVER['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?: '';
$clientSecret = $_SERVER['GOOGLE_CLIENT_SECRET'] ?? getenv('GOOGLE_CLIENT_SECRET') ?: '';

// Tentukan redirect URI (Penting: URL ini harus didaftarkan di Google Cloud Console)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? "https://" : "http://";
$redirectUri = $protocol . $_SERVER['HTTP_HOST'] . "/google_callback.php";

// Konfigurasi Client Google
if (class_exists('Google\Client')) {
    $client = new Google\Client();
    $client->setClientId($clientID);
    $client->setClientSecret($clientSecret);
    $client->setRedirectUri($redirectUri);
    $client->addScope("email");
    $client->addScope("profile");

    // Jika ada kode auth dari Google
    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if (!isset($token['error'])) {
            $client->setAccessToken($token['access_token']);

            // Dapatkan data profil Google
            $google_oauth = new Google\Service\Oauth2($client);
            $google_account_info = $google_oauth->userinfo->get();
            
            $google_id = mysqli_real_escape_string($conn, $google_account_info->id);
            $email = mysqli_real_escape_string($conn, $google_account_info->email);
            $name = mysqli_real_escape_string($conn, $google_account_info->name);
            
            // Cek apakah email sudah terdaftar
            $cek_query = "SELECT * FROM `user` WHERE email = '$email' OR google_id = '$google_id'";
            $cek_result = mysqli_query($conn, $cek_query);
            
            if (mysqli_num_rows($cek_result) > 0) {
                // Login sebagai akun yang sudah ada
                $user = mysqli_fetch_assoc($cek_result);
                
                // Update google_id jika belum ada
                if (empty($user['google_id'])) {
                    mysqli_query($conn, "UPDATE `user` SET google_id = '$google_id', nama_pengguna = '$name' WHERE id_user = " . $user['id_user']);
                }
                
                $_SESSION['login'] = true;
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['hak_akses'] = $user['hak_akses'];
                $_SESSION['nama_pengguna'] = $user['nama_pengguna'];
                
                set_alert('success', 'Berhasil login via Google! Selamat datang, ' . htmlspecialchars($user['nama_pengguna']));
            } else {
                // Buat akun baru sebagai Pengunjung
                // Gunakan bagian depan email sebagai username, dengan penambahan unik jika perlu
                $username_base = explode('@', $email)[0];
                $username = $username_base;
                $counter = 1;
                while (mysqli_num_rows(mysqli_query($conn, "SELECT id_user FROM `user` WHERE username = '$username'")) > 0) {
                    $username = $username_base . $counter;
                    $counter++;
                }
                
                // Password acak karena login via Google
                $random_password = password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);
                
                $insert_query = "INSERT INTO `user` (nama_pengguna, email, username, password, hak_akses, google_id) 
                                 VALUES ('$name', '$email', '$username', '$random_password', 'Pengunjung', '$google_id')";
                
                if (mysqli_query($conn, $insert_query)) {
                    $new_id = mysqli_insert_id($conn);
                    $_SESSION['login'] = true;
                    $_SESSION['id_user'] = $new_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['hak_akses'] = 'Pengunjung';
                    $_SESSION['nama_pengguna'] = $name;
                    
                    set_alert('success', 'Akun Pengunjung berhasil dibuat. Selamat datang, ' . htmlspecialchars($name));
                } else {
                    set_alert('danger', 'Gagal membuat akun: ' . mysqli_error($conn));
                    header("Location: login.php");
                    exit;
                }
            }
            
            header("Location: index.php?page=dashboard");
            exit;
        } else {
            set_alert('danger', 'Gagal autentikasi Google!');
            header("Location: login.php");
            exit;
        }
    }
} else {
    set_alert('danger', 'Library Google Client tidak ditemukan! Jalankan composer install.');
    header("Location: login.php");
    exit;
}

// Redirect ke login jika akses file ini secara langsung tanpa kode auth
header("Location: login.php");
exit;
?>
