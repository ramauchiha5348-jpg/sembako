<?php
/**
 * Proses CRUD dan Perhitungan Prediksi Moving Average
 */
require_once '../config/koneksi.php';

// Proteksi Halaman: Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// 1. Simpan Prediksi ke Database
if (isset($_POST['simpan_prediksi'])) {
    $id_produk = intval($_POST['id_produk']);
    $periode = mysqli_real_escape_string($conn, trim($_POST['periode']));
    $hasil_prediksi = floatval($_POST['hasil_prediksi']);
    
    // Validasi input
    if ($id_produk <= 0 || empty($periode) || $hasil_prediksi < 0) {
        set_alert('danger', 'Data prediksi tidak valid!');
        header("Location: ../index.php?page=prediksi");
        exit;
    }
    
    // Cek apakah prediksi untuk produk dan periode ini sudah ada
    $check_query = "SELECT id_prediksi FROM prediksi WHERE id_produk = $id_produk AND periode = '$periode' LIMIT 1";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Jika sudah ada, lakukan UPDATE
        $row = mysqli_fetch_assoc($check_result);
        $id_prediksi = $row['id_prediksi'];
        $query = "UPDATE prediksi SET hasil_prediksi = $hasil_prediksi WHERE id_prediksi = $id_prediksi";
        $msg = "Data prediksi berhasil diperbarui!";
    } else {
        // Jika belum ada, lakukan INSERT
        $query = "INSERT INTO prediksi (id_produk, hasil_prediksi, periode) VALUES ($id_produk, $hasil_prediksi, '$periode')";
        $msg = "Hasil prediksi berhasil disimpan ke database!";
    }
    
    if (mysqli_query($conn, $query)) {
        set_alert('success', $msg);
    } else {
        set_alert('danger', 'Gagal menyimpan prediksi: ' . mysqli_error($conn));
    }
    
    // Kembalikan ke halaman prediksi dengan mengirim kembali parameter kalkulasi agar tetap terbuka
    header("Location: ../index.php?page=prediksi&id_produk=$id_produk&target_periode=$periode&hitung=1");
    exit;
}

// 2. Hapus Prediksi
else if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id_prediksi = intval($_GET['id']);
    
    if ($id_prediksi <= 0) {
        set_alert('danger', 'ID Prediksi tidak valid!');
        header("Location: ../index.php?page=prediksi");
        exit;
    }
    
    // Query Delete
    $query = "DELETE FROM prediksi WHERE id_prediksi = $id_prediksi";
    if (mysqli_query($conn, $query)) {
        set_alert('success', 'Data prediksi berhasil dihapus!');
    } else {
        set_alert('danger', 'Gagal menghapus data prediksi: ' . mysqli_error($conn));
    }
    header("Location: ../index.php?page=prediksi");
    exit;
}

// Jika diakses tidak sah
else {
    header("Location: ../index.php?page=prediksi");
    exit;
}
?>
