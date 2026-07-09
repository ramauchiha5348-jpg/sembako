<?php
/**
 * Halaman Laporan Penjualan
 */
// Mencegah akses langsung ke file
if (!defined('conn')) {
    exit('Akses langsung tidak diizinkan');
}

// Inisialisasi parameter filter
$id_produk = isset($_GET['id_produk']) ? $_GET['id_produk'] : 'all';
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01'); // Awal bulan ini
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : date('Y-m-d'); // Hari ini

// Membangun query SQL pencarian dinamis
$where = [];
if ($id_produk != 'all' && $id_produk != '') {
    $id_produk_int = intval($id_produk);
    $where[] = "p.id_produk = $id_produk_int";
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

// Ambil semua produk untuk pilihan filter dropdown
$result_produk = mysqli_query($conn, "SELECT id_produk, nama_produk, satuan FROM produk ORDER BY nama_produk ASC");
?>

<div class="row">
    <!-- Filter Pencarian -->
    <div class="col-12">
        <div class="card card-modern shadow-sm border-0 mb-4">
            <div class="card-modern-header">
                <h5 class="card-modern-title"><i class="bi bi-funnel text-primary me-2"></i>Filter Laporan Penjualan</h5>
            </div>
            <div class="card-body">
                <form action="index.php" method="GET">
                    <input type="hidden" name="page" value="laporan">
                    
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Produk Sembako</label>
                            <select name="id_produk" class="form-select form-control-modern">
                                <option value="all" <?= $id_produk == 'all' ? 'selected' : ''; ?>>-- Semua Produk --</option>
                                <?php while ($p = mysqli_fetch_assoc($result_produk)): ?>
                                    <option value="<?= $p['id_produk']; ?>" <?= $id_produk == $p['id_produk'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($p['nama_produk']); ?> (<?= htmlspecialchars($p['satuan']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control form-control-modern" value="<?= $tgl_mulai; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">Tanggal Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control form-control-modern" value="<?= $tgl_selesai; ?>">
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-modern-primary w-100 py-2 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-search me-1.5"></i> Filter
                                </button>
                                <?php if (mysqli_num_rows($result_laporan) > 0): ?>
                                    <!-- Tombol Export PDF memicu tab baru print layout -->
                                    <a href="print_laporan.php?id_produk=<?= $id_produk; ?>&tgl_mulai=<?= $tgl_mulai; ?>&tgl_selesai=<?= $tgl_selesai; ?>" 
                                       target="_blank" 
                                       class="btn btn-danger w-100 py-2 d-flex align-items-center justify-content-center shadow-sm">
                                        <i class="bi bi-printer me-1.5"></i> Cetak PDF
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hasil Laporan -->
    <div class="col-12">
        <div class="card card-modern shadow-sm border-0">
            <div class="card-modern-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-modern-title"><i class="bi bi-file-earmark-spreadsheet text-primary me-2"></i>Rincian Laporan Penjualan</h5>
                <span class="badge bg-light text-dark border px-3 py-2 fs-7">
                    Periode: <strong class="text-primary"><?= tanggal_indo($tgl_mulai); ?></strong> s/d <strong class="text-primary"><?= tanggal_indo($tgl_selesai); ?></strong>
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern table-hover m-0">
                        <thead>
                            <tr>
                                <th style="width: 70px;" class="text-center">No</th>
                                <th>Nama Produk</th>
                                <th class="text-center">Tanggal Penjualan</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-center">Jumlah Terjual</th>
                                <th class="text-end">Total Nilai</th>
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
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_produk']); ?></div>
                                    <small class="text-muted">Satuan: <?= htmlspecialchars($row['satuan']); ?></small>
                                </td>
                                <td class="text-center"><?= tanggal_indo($row['tanggal']); ?></td>
                                <td class="text-end"><?= format_rupiah($row['harga']); ?></td>
                                <td class="text-center fw-medium"><?= number_format($row['jumlah_terjual'], 0, ',', '.'); ?></td>
                                <td class="text-end text-success fw-semibold"><?= format_rupiah($subtotal); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            
                            <!-- Baris Total Summary -->
                            <tr class="table-light">
                                <td colspan="4" class="text-end fw-bold text-dark py-3">GRAND TOTAL</td>
                                <td class="text-center fw-bold text-primary py-3"><?= number_format($total_items, 0, ',', '.'); ?> Item</td>
                                <td class="text-end fw-bold text-success py-3 fs-5"><?= format_rupiah($total_omset); ?></td>
                            </tr>
                            <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-file-earmark-x fs-2 d-block mb-2"></i> Tidak ditemukan data penjualan pada filter periode ini.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
