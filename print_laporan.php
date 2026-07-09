<?php
/**
 * Standalone Print Page Laporan Penjualan (Export PDF)
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Penjualan - Toko Sembako</title>
    <!-- Bootstrap 5 CSS (Hanya untuk dasar grid dan tabel) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Custom styling khusus print -->
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background-color: #fff;
            padding: 20px;
        }
        .header-laporan {
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        .header-title {
            text-transform: uppercase;
            font-weight: bold;
            font-size: 1.6rem;
            margin: 0;
            letter-spacing: 1px;
        }
        .header-subtitle {
            font-size: 1.1rem;
            margin: 5px 0 0 0;
        }
        .meta-table {
            font-size: 1rem;
            margin-bottom: 20px;
        }
        .meta-table td {
            padding: 4px 8px;
            border: none !important;
        }
        .table-laporan {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
            margin-bottom: 40px;
        }
        .table-laporan th {
            background-color: #f2f2f2 !important;
            border: 1px solid #000 !important;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .table-laporan td {
            border: 1px solid #000 !important;
            padding: 8px;
            vertical-align: middle;
        }
        .signature-container {
            float: right;
            text-align: center;
            width: 250px;
            margin-top: 20px;
            font-size: 1rem;
        }
        .signature-space {
            height: 80px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Header Dokumen (Kop Surat Formal) -->
    <div class="header-laporan text-center">
        <h2 class="header-title">Toko Sembako PBW</h2>
        <p class="header-subtitle text-muted">Jalan Raya Sembako No. 123, Kabupaten PBW, Jawa Tengah</p>
        <small class="text-muted">Telp: 0812-3456-7890 | Email: admin@tokosembakopbw.com</small>
    </div>

    <div class="container-fluid">
        <h4 class="text-center fw-bold text-uppercase mb-4" style="text-decoration: underline; letter-spacing: 0.5px;">Laporan Penjualan Produk</h4>
        
        <!-- Metadata Laporan -->
        <table class="meta-table">
            <tr>
                <td style="width: 150px; font-weight: bold;">Filter Produk</td>
                <td>: <?= htmlspecialchars($filter_info); ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Periode Laporan</td>
                <td>: <?= tanggal_indo($tgl_mulai); ?> s/d <?= tanggal_indo($tgl_selesai); ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Tanggal Cetak</td>
                <td>: <?= tanggal_indo(date('Y-m-d')); ?></td>
            </tr>
        </table>

        <!-- Tabel Rincian Data Penjualan -->
        <table class="table table-laporan">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Produk</th>
                    <th style="width: 100px;">Satuan</th>
                    <th style="width: 150px;">Tanggal Penjualan</th>
                    <th style="width: 150px;">Harga Satuan</th>
                    <th style="width: 120px;">Jumlah Terjual</th>
                    <th style="width: 150px;">Total Nilai</th>
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
                    <td class="text-center"><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['nama_produk']); ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['satuan']); ?></td>
                    <td class="text-center"><?= tanggal_indo($row['tanggal']); ?></td>
                    <td class="text-end"><?= format_rupiah($row['harga']); ?></td>
                    <td class="text-center"><?= number_format($row['jumlah_terjual'], 0, ',', '.'); ?></td>
                    <td class="text-end fw-bold"><?= format_rupiah($subtotal); ?></td>
                </tr>
                <?php endwhile; ?>
                <!-- Grand Total -->
                <tr style="background-color: #f9f9f9;">
                    <td colspan="5" class="text-end fw-bold">TOTAL PENJUALAN</td>
                    <td class="text-center fw-bold"><?= number_format($total_items, 0, ',', '.'); ?></td>
                    <td class="text-end fw-bold" style="font-size: 1.05rem;"><?= format_rupiah($total_omset); ?></td>
                </tr>
                <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-4">Tidak ditemukan data penjualan pada filter periode ini.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Bagian Tanda Tangan (Signature Block) -->
        <div class="clearfix">
            <div class="signature-container">
                <p>PBW, <?= tanggal_indo(date('Y-m-d')); ?></p>
                <p class="fw-bold">Administrator Toko,</p>
                <div class="signature-space"></div>
                <p class="fw-bold" style="text-decoration: underline; margin-bottom: 0;"><?= htmlspecialchars($_SESSION['username']); ?></p>
                <small class="text-muted">Staff Toko Sembako</small>
            </div>
        </div>
    </div>

    <!-- Pemicu window.print() otomatis setelah loading selesai -->
    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
            window.print();
        });
    </script>
</body>
</html>
