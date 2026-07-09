<?php
/**
 * Proses CRUD Data Produk
 */
require_once '../config/koneksi.php';

// Proteksi Halaman: Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// 1. Tambah Produk
if (isset($_POST['tambah_produk'])) {
    $nama_produk = mysqli_real_escape_string($conn, trim($_POST['nama_produk']));
    $satuan = mysqli_real_escape_string($conn, trim($_POST['satuan']));
    $harga = intval($_POST['harga']);
    
    // Validasi input
    if (empty($nama_produk) || empty($satuan) || $harga <= 0) {
        set_alert('danger', 'Semua data produk wajib diisi dengan benar!');
        header("Location: ../index.php?page=produk");
        exit;
    }
    
    // Query Insert
    $query = "INSERT INTO produk (nama_produk, satuan, harga) VALUES ('$nama_produk', '$satuan', $harga)";
    if (mysqli_query($conn, $query)) {
        set_alert('success', 'Produk baru berhasil ditambahkan!');
    } else {
        set_alert('danger', 'Gagal menambahkan produk: ' . mysqli_error($conn));
    }
    header("Location: ../index.php?page=produk");
    exit;
}

// 2. Edit Produk
else if (isset($_POST['edit_produk'])) {
    $id_produk = intval($_POST['id_produk']);
    $nama_produk = mysqli_real_escape_string($conn, trim($_POST['nama_produk']));
    $satuan = mysqli_real_escape_string($conn, trim($_POST['satuan']));
    $harga = intval($_POST['harga']);
    
    // Validasi input
    if ($id_produk <= 0 || empty($nama_produk) || empty($satuan) || $harga <= 0) {
        set_alert('danger', 'Semua data produk wajib diisi dengan benar!');
        header("Location: ../index.php?page=produk");
        exit;
    }
    
    // Query Update
    $query = "UPDATE produk SET nama_produk = '$nama_produk', satuan = '$satuan', harga = $harga WHERE id_produk = $id_produk";
    if (mysqli_query($conn, $query)) {
        set_alert('success', 'Data produk berhasil diperbarui!');
    } else {
        set_alert('danger', 'Gagal memperbarui data produk: ' . mysqli_error($conn));
    }
    header("Location: ../index.php?page=produk");
    exit;
}

// 3. Hapus Produk
else if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id_produk = intval($_GET['id']);
    
    if ($id_produk <= 0) {
        set_alert('danger', 'ID Produk tidak valid!');
        header("Location: ../index.php?page=produk");
        exit;
    }
    
    // Query Delete
    $query = "DELETE FROM produk WHERE id_produk = $id_produk";
    if (mysqli_query($conn, $query)) {
        set_alert('success', 'Produk berhasil dihapus dari sistem!');
    } else {
        set_alert('danger', 'Gagal menghapus produk: ' . mysqli_error($conn));
    }
    header("Location: ../index.php?page=produk");
    exit;
}

// Jika diakses tidak sah
else {
    header("Location: ../index.php?page=produk");
    exit;
}
?>
