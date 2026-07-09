<?php
/**
 * Proses CRUD Data Penjualan
 */
require_once '../config/koneksi.php';

// Proteksi Halaman: Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// 1. Tambah Data Penjualan
if (isset($_POST['tambah_penjualan'])) {
    $id_produk = intval($_POST['id_produk']);
    $tanggal = mysqli_real_escape_string($conn, trim($_POST['tanggal']));
    $jumlah_terjual = intval($_POST['jumlah_terjual']);
    
    // Validasi input
    if ($id_produk <= 0 || empty($tanggal) || $jumlah_terjual <= 0) {
        set_alert('danger', 'Semua data penjualan wajib diisi dengan benar!');
        header("Location: ../index.php?page=penjualan");
        exit;
    }
    
    // Query Insert
    $query = "INSERT INTO penjualan (id_produk, tanggal, jumlah_terjual) VALUES ($id_produk, '$tanggal', $jumlah_terjual)";
    if (mysqli_query($conn, $query)) {
        set_alert('success', 'Data penjualan berhasil ditambahkan!');
    } else {
        set_alert('danger', 'Gagal menambahkan data penjualan: ' . mysqli_error($conn));
    }
    header("Location: ../index.php?page=penjualan");
    exit;
}

// 2. Edit Data Penjualan
else if (isset($_POST['edit_penjualan'])) {
    $id_penjualan = intval($_POST['id_penjualan']);
    $id_produk = intval($_POST['id_produk']);
    $tanggal = mysqli_real_escape_string($conn, trim($_POST['tanggal']));
    $jumlah_terjual = intval($_POST['jumlah_terjual']);
    
    // Validasi input
    if ($id_penjualan <= 0 || $id_produk <= 0 || empty($tanggal) || $jumlah_terjual <= 0) {
        set_alert('danger', 'Semua data penjualan wajib diisi dengan benar!');
        header("Location: ../index.php?page=penjualan");
        exit;
    }
    
    // Query Update
    $query = "UPDATE penjualan SET id_produk = $id_produk, tanggal = '$tanggal', jumlah_terjual = $jumlah_terjual WHERE id_penjualan = $id_penjualan";
    if (mysqli_query($conn, $query)) {
        set_alert('success', 'Data penjualan berhasil diperbarui!');
    } else {
        set_alert('danger', 'Gagal memperbarui data penjualan: ' . mysqli_error($conn));
    }
    header("Location: ../index.php?page=penjualan");
    exit;
}

// 3. Hapus Data Penjualan
else if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id_penjualan = intval($_GET['id']);
    
    if ($id_penjualan <= 0) {
        set_alert('danger', 'ID Penjualan tidak valid!');
        header("Location: ../index.php?page=penjualan");
        exit;
    }
    
    // Query Delete
    $query = "DELETE FROM penjualan WHERE id_penjualan = $id_penjualan";
    if (mysqli_query($conn, $query)) {
        set_alert('success', 'Data penjualan berhasil dihapus!');
    } else {
        set_alert('danger', 'Gagal menghapus data penjualan: ' . mysqli_error($conn));
    }
    header("Location: ../index.php?page=penjualan");
    exit;
}

// Jika diakses tidak sah
else {
    header("Location: ../index.php?page=penjualan");
    exit;
}
?>
