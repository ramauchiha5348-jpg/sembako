<?php
require_once 'config/koneksi.php';

echo "<h2>Migrasi Database</h2>";

// 1. Tambah kolom nama_pengguna
$q1 = "ALTER TABLE `user` ADD COLUMN `nama_pengguna` VARCHAR(100) NULL AFTER `id_user`";
if (mysqli_query($conn, $q1)) {
    echo "Kolom nama_pengguna berhasil ditambahkan.<br>";
} else {
    echo "Gagal/Sudah ada (nama_pengguna): " . mysqli_error($conn) . "<br>";
}

// 2. Tambah kolom email
$q2 = "ALTER TABLE `user` ADD COLUMN `email` VARCHAR(100) NULL AFTER `nama_pengguna`";
if (mysqli_query($conn, $q2)) {
    echo "Kolom email berhasil ditambahkan.<br>";
} else {
    echo "Gagal/Sudah ada (email): " . mysqli_error($conn) . "<br>";
}

// 3. Tambah kolom hak_akses
$q3 = "ALTER TABLE `user` ADD COLUMN `hak_akses` ENUM('Admin', 'Pengunjung') NOT NULL DEFAULT 'Pengunjung' AFTER `password`";
if (mysqli_query($conn, $q3)) {
    echo "Kolom hak_akses berhasil ditambahkan.<br>";
} else {
    echo "Gagal/Sudah ada (hak_akses): " . mysqli_error($conn) . "<br>";
}

// 4. Tambah kolom google_id
$q4 = "ALTER TABLE `user` ADD COLUMN `google_id` VARCHAR(100) NULL AFTER `hak_akses`";
if (mysqli_query($conn, $q4)) {
    echo "Kolom google_id berhasil ditambahkan.<br>";
} else {
    echo "Gagal/Sudah ada (google_id): " . mysqli_error($conn) . "<br>";
}

// 5. Update data admin default
$q5 = "UPDATE `user` SET `nama_pengguna` = 'Admin', `email` = 'admin', `hak_akses` = 'Admin' WHERE `username` = 'admin'";
if (mysqli_query($conn, $q5)) {
    echo "Data admin berhasil diperbarui.<br>";
} else {
    echo "Gagal memperbarui admin: " . mysqli_error($conn) . "<br>";
}

echo "<h3>Migrasi Selesai!</h3>";
echo "<a href='index.php'>Kembali ke Beranda</a>";
?>
