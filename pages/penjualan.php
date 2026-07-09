<?php
/**
 * Halaman Kelola Data Penjualan
 */
// Mencegah akses langsung ke file
if (!defined('conn')) {
    exit('Akses langsung tidak diizinkan');
}

// Ambil semua data penjualan beserta relasi nama produk
$query_sales = "SELECT p.*, pr.nama_produk, pr.satuan, pr.harga 
                FROM penjualan p 
                JOIN produk pr ON p.id_produk = pr.id_produk 
                ORDER BY p.tanggal DESC";
$result_sales = mysqli_query($conn, $query_sales);

// Ambil data produk untuk dropdown input tambah/edit
$query_produk = "SELECT id_produk, nama_produk, satuan FROM produk ORDER BY nama_produk ASC";
$result_produk = mysqli_query($conn, $query_produk);

// Simpan data produk ke array agar bisa di-loop berkali-kali untuk modal edit
$produk_options = [];
while ($p = mysqli_fetch_assoc($result_produk)) {
    $produk_options[] = $p;
}
?>

<div class="row">
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-modern-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-modern-title"><i class="bi bi-cart-check me-2 text-primary"></i>Catatan Penjualan Produk</h5>
                <button type="button" class="btn btn-modern-primary btn-sm d-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPenjualan">
                    <i class="bi bi-plus-lg me-1"></i> Input Penjualan
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern table-hover m-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;" class="text-center">No</th>
                                <th>Produk</th>
                                <th class="text-center">Tanggal Penjualan</th>
                                <th class="text-center">Jumlah Terjual</th>
                                <th class="text-end">Total Nilai</th>
                                <th style="width: 180px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($result_sales) > 0):
                                while ($row = mysqli_fetch_assoc($result_sales)):
                                    $total_nilai = $row['harga'] * $row['jumlah_terjual'];
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_produk']); ?></div>
                                    <small class="text-muted">Satuan: <?= htmlspecialchars($row['satuan']); ?></small>
                                </td>
                                <td class="text-center"><?= tanggal_indo($row['tanggal']); ?></td>
                                <td class="text-center fw-medium"><?= number_format($row['jumlah_terjual'], 0, ',', '.'); ?></td>
                                <td class="text-end text-success fw-semibold"><?= format_rupiah($total_nilai); ?></td>
                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded" data-bs-toggle="modal" data-bs-target="#modalEditPenjualan-<?= $row['id_penjualan']; ?>">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                        <a href="proses/proses_penjualan.php?action=delete&id=<?= $row['id_penjualan']; ?>" class="btn btn-outline-danger btn-sm rounded" onclick="return konfirmasiHapus('Apakah Anda yakin ingin menghapus data penjualan produk <?= htmlspecialchars($row['nama_produk']); ?> pada <?= tanggal_indo($row['tanggal']); ?>?')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Edit Penjualan -->
                            <div class="modal fade" id="modalEditPenjualan-<?= $row['id_penjualan']; ?>" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content shadow border-0" style="border-radius: 12px;">
                                        <div class="modal-header border-bottom-0 pt-4 px-4">
                                            <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Catatan Penjualan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="proses/proses_penjualan.php" method="POST" class="needs-validation" novalidate>
                                            <div class="modal-body px-4 pb-4">
                                                <input type="hidden" name="id_penjualan" value="<?= $row['id_penjualan']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold text-dark">Pilih Produk</label>
                                                    <select name="id_produk" class="form-select form-control-modern" required>
                                                        <option value="" disabled>-- Pilih Produk Sembako --</option>
                                                        <?php foreach ($produk_options as $p): ?>
                                                            <option value="<?= $p['id_produk']; ?>" <?= $p['id_produk'] == $row['id_produk'] ? 'selected' : ''; ?>>
                                                                <?= htmlspecialchars($p['nama_produk']); ?> (<?= htmlspecialchars($p['satuan']); ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <div class="invalid-feedback">Silakan pilih produk!</div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold text-dark">Tanggal Transaksi</label>
                                                    <input type="date" name="tanggal" class="form-control form-control-modern" value="<?= $row['tanggal']; ?>" required>
                                                    <div class="invalid-feedback">Tanggal penjualan wajib diisi!</div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold text-dark">Jumlah Terjual</label>
                                                    <input type="number" name="jumlah_terjual" class="form-control form-control-modern" value="<?= $row['jumlah_terjual']; ?>" placeholder="Contoh: 10" min="1" required>
                                                    <div class="invalid-feedback">Jumlah terjual minimal 1!</div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 px-4 pb-4">
                                                <button type="button" class="btn btn-light rounded px-3" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="edit_penjualan" class="btn btn-modern-primary rounded px-4">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- Akhir Modal Edit -->

                            <?php 
                                endwhile; 
                            else: 
                            ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-folder-x fs-2 d-block mb-2"></i> Belum ada data penjualan tersedia.
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

<!-- Modal Tambah Penjualan -->
<div class="modal fade" id="modalTambahPenjualan" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0" style="border-radius: 12px;">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark" id="modalTambahLabel"><i class="bi bi-cart-plus text-primary me-2"></i>Input Penjualan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="proses/proses_penjualan.php" method="POST" class="needs-validation" novalidate>
                <div class="modal-body px-4 pb-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Pilih Produk</label>
                        <select name="id_produk" class="form-select form-control-modern" required>
                            <option value="" disabled selected>-- Pilih Produk Sembako --</option>
                            <?php foreach ($produk_options as $p): ?>
                                <option value="<?= $p['id_produk']; ?>"><?= htmlspecialchars($p['nama_produk']); ?> (<?= htmlspecialchars($p['satuan']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Silakan pilih produk!</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Tanggal Transaksi</label>
                        <input type="date" name="tanggal" class="form-control form-control-modern" value="<?= date('Y-m-d'); ?>" required>
                        <div class="invalid-feedback">Tanggal penjualan wajib diisi!</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Jumlah Terjual</label>
                        <input type="number" name="jumlah_terjual" class="form-control form-control-modern" placeholder="Contoh: 50" min="1" required>
                        <div class="invalid-feedback">Jumlah terjual minimal 1!</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_penjualan" class="btn btn-modern-primary rounded px-4">Tambah Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Akhir Modal Tambah -->
