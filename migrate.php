<?php
require_once 'config/koneksi.php';

echo "<h2>Migrasi Database</h2>";

// 1. Tambah kolom nama_pengguna
try {
    $q1 = "ALTER TABLE `user` ADD COLUMN `nama_pengguna` VARCHAR(100) NULL AFTER `id_user`";
    mysqli_query($conn, $q1);
    echo "Kolom nama_pengguna berhasil ditambahkan.<br>";
} catch (Exception $e) {
    echo "Gagal/Sudah ada (nama_pengguna): " . $e->getMessage() . "<br>";
}

// 2. Tambah kolom email
try {
    $q2 = "ALTER TABLE `user` ADD COLUMN `email` VARCHAR(100) NULL AFTER `nama_pengguna`";
    mysqli_query($conn, $q2);
    echo "Kolom email berhasil ditambahkan.<br>";
} catch (Exception $e) {
    echo "Gagal/Sudah ada (email): " . $e->getMessage() . "<br>";
}

// 3. Tambah kolom hak_akses
try {
    $q3 = "ALTER TABLE `user` ADD COLUMN `hak_akses` ENUM('Admin', 'Pengunjung') NOT NULL DEFAULT 'Pengunjung' AFTER `password`";
    mysqli_query($conn, $q3);
    echo "Kolom hak_akses berhasil ditambahkan.<br>";
} catch (Exception $e) {
    echo "Gagal/Sudah ada (hak_akses): " . $e->getMessage() . "<br>";
}

// 4. Tambah kolom google_id
try {
    $q4 = "ALTER TABLE `user` ADD COLUMN `google_id` VARCHAR(100) NULL AFTER `hak_akses`";
    mysqli_query($conn, $q4);
    echo "Kolom google_id berhasil ditambahkan.<br>";
} catch (Exception $e) {
    echo "Gagal/Sudah ada (google_id): " . $e->getMessage() . "<br>";
}

// 5. Update data admin default
try {
    $q5 = "UPDATE `user` SET `nama_pengguna` = 'Admin', `email` = 'admin', `hak_akses` = 'Admin' WHERE `username` = 'admin'";
    mysqli_query($conn, $q5);
    echo "Data admin berhasil diperbarui.<br>";
} catch (Exception $e) {
    echo "Gagal memperbarui admin: " . $e->getMessage() . "<br>";
}
// 6. Tambah kolom stok pada tabel produk
try {
    $q6 = "ALTER TABLE `produk` ADD COLUMN `stok` INT(11) NOT NULL DEFAULT 0 AFTER `satuan`";
    mysqli_query($conn, $q6);
    echo "Kolom stok berhasil ditambahkan ke tabel produk.<br>";
} catch (Exception $e) {
    echo "Gagal/Sudah ada (stok di produk): " . $e->getMessage() . "<br>";
}
echo "<h3>Migrasi Selesai!</h3>";
echo "<a href='index.php'>Kembali ke Beranda</a>";
?>
