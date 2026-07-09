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
    
    // Validasi Stok
    $q_stok = mysqli_query($conn, "SELECT stok, nama_produk FROM produk WHERE id_produk = $id_produk");
    $r_stok = mysqli_fetch_assoc($q_stok);
    if ($r_stok['stok'] < $jumlah_terjual) {
        set_alert('error_stok', json_encode([
            'nama_produk' => $r_stok['nama_produk'],
            'stok' => $r_stok['stok']
        ]));
        header("Location: ../index.php?page=penjualan");
        exit;
    }
    
    // Query Insert
    $query = "INSERT INTO penjualan (id_produk, tanggal, jumlah_terjual) VALUES ($id_produk, '$tanggal', $jumlah_terjual)";
    if (mysqli_query($conn, $query)) {
        // Kurangi stok produk
        mysqli_query($conn, "UPDATE produk SET stok = stok - $jumlah_terjual WHERE id_produk = $id_produk");
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
    
    // Ambil data penjualan lama
    $q_old = mysqli_query($conn, "SELECT id_produk, jumlah_terjual FROM penjualan WHERE id_penjualan = $id_penjualan");
    $r_old = mysqli_fetch_assoc($q_old);
    
    if (!$r_old) {
        set_alert('danger', 'Data penjualan tidak ditemukan!');
        header("Location: ../index.php?page=penjualan");
        exit;
    }
    
    $id_produk_old = $r_old['id_produk'];
    $jumlah_terjual_old = $r_old['jumlah_terjual'];
    
    // Cek ketersediaan stok untuk perubahan
    if ($id_produk == $id_produk_old) {
        $selisih = $jumlah_terjual - $jumlah_terjual_old;
        $q_stok = mysqli_query($conn, "SELECT stok, nama_produk FROM produk WHERE id_produk = $id_produk");
        $r_stok = mysqli_fetch_assoc($q_stok);
        
        if ($selisih > 0 && $r_stok['stok'] < $selisih) {
            set_alert('error_stok', json_encode([
                'nama_produk' => $r_stok['nama_produk'],
                'stok' => $r_stok['stok']
            ]));
            header("Location: ../index.php?page=penjualan");
            exit;
        }
    } else {
        // Jika produk diubah, cek full stok produk baru
        $q_stok_new = mysqli_query($conn, "SELECT stok, nama_produk FROM produk WHERE id_produk = $id_produk");
        $r_stok_new = mysqli_fetch_assoc($q_stok_new);
        
        if ($r_stok_new['stok'] < $jumlah_terjual) {
            set_alert('error_stok', json_encode([
                'nama_produk' => $r_stok_new['nama_produk'],
                'stok' => $r_stok_new['stok']
            ]));
            header("Location: ../index.php?page=penjualan");
            exit;
        }
    }
    
    // Query Update
    $query = "UPDATE penjualan SET id_produk = $id_produk, tanggal = '$tanggal', jumlah_terjual = $jumlah_terjual WHERE id_penjualan = $id_penjualan";
    if (mysqli_query($conn, $query)) {
        // Sesuaikan stok
        if ($id_produk == $id_produk_old) {
            $selisih = $jumlah_terjual - $jumlah_terjual_old;
            if ($selisih != 0) {
                mysqli_query($conn, "UPDATE produk SET stok = stok - $selisih WHERE id_produk = $id_produk");
            }
        } else {
            // Kembalikan stok lama
            mysqli_query($conn, "UPDATE produk SET stok = stok + $jumlah_terjual_old WHERE id_produk = $id_produk_old");
            // Kurangi stok baru
            mysqli_query($conn, "UPDATE produk SET stok = stok - $jumlah_terjual WHERE id_produk = $id_produk");
        }
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
    
    // Ambil data untuk kembalikan stok
    $q_del = mysqli_query($conn, "SELECT id_produk, jumlah_terjual FROM penjualan WHERE id_penjualan = $id_penjualan");
    if ($r_del = mysqli_fetch_assoc($q_del)) {
        $id_produk_del = $r_del['id_produk'];
        $jumlah_terjual_del = $r_del['jumlah_terjual'];
        
        // Query Delete
        $query = "DELETE FROM penjualan WHERE id_penjualan = $id_penjualan";
        if (mysqli_query($conn, $query)) {
            // Kembalikan stok
            mysqli_query($conn, "UPDATE produk SET stok = stok + $jumlah_terjual_del WHERE id_produk = $id_produk_del");
            set_alert('success', 'Data penjualan berhasil dihapus dan stok telah dikembalikan!');
        } else {
            set_alert('danger', 'Gagal menghapus data penjualan: ' . mysqli_error($conn));
        }
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
