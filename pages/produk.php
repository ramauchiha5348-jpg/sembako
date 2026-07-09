<?php
/**
 * Halaman Kelola Data Produk
 */
// Mencegah akses langsung ke file
if (!defined('conn')) {
    exit('Akses langsung tidak diizinkan');
}

// Ambil semua data produk
$query = "SELECT * FROM produk ORDER BY nama_produk ASC";
$result = mysqli_query($conn, $query);
?>

<div class="row">
    <div class="col-12">
        <div class="card card-modern">
            <div class="card-modern-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-modern-title"><i class="bi bi-box-seam me-2 text-primary"></i>Daftar Produk Sembako</h5>
                <!-- Tombol tambah produk memicu modal (Hanya Admin) -->
                <?php if ($_SESSION['hak_akses'] == 'Admin'): ?>
                <button type="button" class="btn btn-modern-primary btn-sm d-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Produk
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern table-hover m-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;" class="text-center">No</th>
                                <th>Nama Produk</th>
                                <th>Satuan</th>
                                <th>Harga</th>
                                <?php if ($_SESSION['hak_akses'] == 'Admin'): ?>
                                <th style="width: 180px;" class="text-center">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)):
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_produk']); ?></td>
                                <td><span class="badge bg-light text-dark border px-2 py-1.5"><?= htmlspecialchars($row['satuan']); ?></span></td>
                                <td class="text-primary fw-medium"><?= format_rupiah($row['harga']); ?></td>
                                <?php if ($_SESSION['hak_akses'] == 'Admin'): ?>
                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        <!-- Tombol Edit memicu modal edit spesifik ID -->
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded" data-bs-toggle="modal" data-bs-target="#modalEdit-<?= $row['id_produk']; ?>">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                        <a href="proses/proses_produk.php?action=delete&id=<?= $row['id_produk']; ?>" class="btn btn-outline-danger btn-sm rounded" onclick="return konfirmasiHapus('Apakah Anda yakin ingin menghapus produk <?= htmlspecialchars($row['nama_produk']); ?>? Menghapus produk ini akan menghapus semua data penjualan dan prediksi terkait.')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Edit Produk (Dicetak per produk) -->
                            <div class="modal fade" id="modalEdit-<?= $row['id_produk']; ?>" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content shadow border-0" style="border-radius: 12px;">
                                        <div class="modal-header border-bottom-0 pt-4 px-4">
                                            <h5 class="modal-title fw-bold text-dark" id="modalEditLabel"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Data Produk</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="proses/proses_produk.php" method="POST" class="needs-validation" novalidate>
                                            <div class="modal-body px-4 pb-4">
                                                <input type="hidden" name="id_produk" value="<?= $row['id_produk']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold text-dark">Nama Produk</label>
                                                    <input type="text" name="nama_produk" class="form-control form-control-modern" value="<?= htmlspecialchars($row['nama_produk']); ?>" placeholder="Contoh: Minyak Goreng Bimoli 2L" required>
                                                    <div class="invalid-feedback">Nama produk wajib diisi!</div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold text-dark">Satuan</label>
                                                    <input type="text" name="satuan" class="form-control form-control-modern" value="<?= htmlspecialchars($row['satuan']); ?>" placeholder="Contoh: Pouch, Kg, Karung" required>
                                                    <div class="invalid-feedback">Satuan wajib diisi!</div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold text-dark">Harga (Rp)</label>
                                                    <input type="number" name="harga" class="form-control form-control-modern" value="<?= $row['harga']; ?>" placeholder="Contoh: 38000" min="1" required>
                                                    <div class="invalid-feedback">Harga harus berupa angka lebih besar dari 0!</div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 px-4 pb-4">
                                                <button type="button" class="btn btn-light rounded px-3" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="edit_produk" class="btn btn-modern-primary rounded px-4">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- Akhir Modal Edit -->
                            <?php else: ?>
                            </tr>
                            <?php endif; ?>

                            <?php 
                                endwhile; 
                            else: 
                            ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-folder-x fs-2 d-block mb-2"></i> Belum ada data produk tersedia.
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

<?php if ($_SESSION['hak_akses'] == 'Admin'): ?>
<!-- Modal Tambah Produk -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0" style="border-radius: 12px;">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark" id="modalTambahLabel"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Produk Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="proses/proses_produk.php" method="POST" class="needs-validation" novalidate>
                <div class="modal-body px-4 pb-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Nama Produk</label>
                        <input type="text" name="nama_produk" class="form-control form-control-modern" placeholder="Contoh: Gula Pasir Gulaku 1kg" required>
                        <div class="invalid-feedback">Nama produk wajib diisi!</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Satuan</label>
                        <input type="text" name="satuan" class="form-control form-control-modern" placeholder="Contoh: Pouch, Kg, Karung, Dus" required>
                        <div class="invalid-feedback">Satuan wajib diisi!</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Harga (Rp)</label>
                        <input type="number" name="harga" class="form-control form-control-modern" placeholder="Contoh: 17500" min="1" required>
                        <div class="invalid-feedback">Harga harus berupa angka lebih besar dari 0!</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_produk" class="btn btn-modern-primary rounded px-4">Tambah Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Akhir Modal Tambah -->
<?php endif; ?>
