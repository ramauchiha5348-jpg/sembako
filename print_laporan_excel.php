<?php
/**
 * Export Laporan Penjualan ke Excel
 */
require_once 'config/koneksi.php';

// Proteksi Halaman: Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    die("Akses ditolak! Silakan login terlebih dahulu.");
}

// Inisialisasi parameter filter
$id_produk = isset($_GET['id_produk']) ? $_GET['id_produk'] : 'all';
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : '';
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : '';

// Membangun query SQL pencarian dinamis
$where = [];
$filter_info = "Semua Produk";

if ($id_produk != 'all' && $id_produk != '') {
    $id_produk_int = intval($id_produk);
    $where[] = "p.id_produk = $id_produk_int";
    
    // Ambil nama produk untuk filter_info
    $q_prod = mysqli_query($conn, "SELECT nama_produk FROM produk WHERE id_produk = $id_produk_int LIMIT 1");
    $r_prod = mysqli_fetch_assoc($q_prod);
    if ($r_prod) {
        $filter_info = $r_prod['nama_produk'];
    }
}

if (!empty($tgl_mulai)) {
    $tgl_mulai_esc = mysqli_real_escape_string($conn, $tgl_mulai);
    $where[] = "p.tanggal >= '$tgl_mulai_esc'";
}
if (!empty($tgl_selesai)) {
    $tgl_selesai_esc = mysqli_real_escape_string($conn, $tgl_selesai);
    $where[] = "p.tanggal <= '$tgl_selesai_esc'";
}

$where_clause = "";
if (count($where) > 0) {
    $where_clause = "WHERE " . implode(" AND ", $where);
}

// Query mengambil data penjualan terfilter
$query_laporan = "SELECT p.*, pr.nama_produk, pr.satuan, pr.harga 
                  FROM penjualan p 
                  JOIN produk pr ON p.id_produk = pr.id_produk 
                  $where_clause 
                  ORDER BY p.tanggal ASC";
$result_laporan = mysqli_query($conn, $query_laporan);

// Headers untuk membuat file Excel (XLS)
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Laporan_Penjualan_Sembako_" . date('Y-m-d') . ".xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table border="0">
        <tr>
            <td colspan="7" align="center" style="font-size: 18px; font-weight: bold;">Toko Sembako PBW</td>
        </tr>
        <tr>
            <td colspan="7" align="center" style="font-size: 14px;">Laporan Penjualan Produk</td>
        </tr>
    </table>
    <br>
    
    <table border="0">
        <tr>
            <td colspan="2"><b>Filter Produk:</b></td>
            <td colspan="5"><?= htmlspecialchars($filter_info); ?></td>
        </tr>
        <tr>
            <td colspan="2"><b>Periode Laporan:</b></td>
            <td colspan="5"><?= tanggal_indo($tgl_mulai); ?> s/d <?= tanggal_indo($tgl_selesai); ?></td>
        </tr>
        <tr>
            <td colspan="2"><b>Tanggal Cetak:</b></td>
            <td colspan="5"><?= tanggal_indo(date('Y-m-d')); ?></td>
        </tr>
    </table>
    <br>

    <table border="1">
        <thead>
            <tr style="background-color: #cccccc;">
                <th>No</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>Tanggal Penjualan</th>
                <th>Harga Satuan</th>
                <th>Jumlah Terjual</th>
                <th>Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $total_items = 0;
            $total_omset = 0;
            
            if (mysqli_num_rows($result_laporan) > 0):
                while ($row = mysqli_fetch_assoc($result_laporan)):
                    $subtotal = $row['harga'] * $row['jumlah_terjual'];
                    $total_items += $row['jumlah_terjual'];
                    $total_omset += $subtotal;
            ?>
            <tr>
                <td align="center"><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['nama_produk']); ?></td>
                <td align="center"><?= htmlspecialchars($row['satuan']); ?></td>
                <td align="center"><?= tanggal_indo($row['tanggal']); ?></td>
                <td align="right"><?= format_rupiah($row['harga']); ?></td>
                <td align="center"><?= $row['jumlah_terjual']; ?></td>
                <td align="right"><?= format_rupiah($subtotal); ?></td>
            </tr>
            <?php endwhile; ?>
            <tr style="background-color: #eeeeee;">
                <td colspan="5" align="right"><b>TOTAL PENJUALAN</b></td>
                <td align="center"><b><?= $total_items; ?></b></td>
                <td align="right"><b><?= format_rupiah($total_omset); ?></b></td>
            </tr>
            <?php else: ?>
            <tr>
                <td colspan="7" align="center">Tidak ditemukan data penjualan pada filter periode ini.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
