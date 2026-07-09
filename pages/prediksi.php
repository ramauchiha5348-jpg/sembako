<?php
/**
 * Halaman Prediksi Penjualan Moving Average (MA-3)
 */
// Mencegah akses langsung ke file
if (!defined('conn')) {
    exit('Akses langsung tidak diizinkan');
}

// Ambil data produk untuk pilihan form
$query_produk = "SELECT * FROM produk ORDER BY nama_produk ASC";
$result_produk = mysqli_query($conn, $query_produk);
$produk_list = [];
while ($p = mysqli_fetch_assoc($result_produk)) {
    $produk_list[] = $p;
}

// Inisialisasi variabel input
$selected_produk_id = isset($_GET['id_produk']) ? intval($_GET['id_produk']) : (count($produk_list) > 0 ? $produk_list[0]['id_produk'] : 0);
$selected_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$selected_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$target_periode = isset($_GET['target_periode']) ? $_GET['target_periode'] : "{$selected_tahun}-{$selected_bulan}";

// Jika form disubmit (melalui parameter hitung)
$hitung = isset($_GET['hitung']) ? true : false;
$hasil_kalkulasi = null;
$error_kalkulasi = null;

if ($hitung && $selected_produk_id > 0) {
    // Tentukan periode target
    if (isset($_GET['target_periode'])) {
        $target_periode = $_GET['target_periode'];
        $parts = explode('-', $target_periode);
        $selected_tahun = $parts[0];
        $selected_bulan = $parts[1];
    } else {
        $target_periode = "{$selected_tahun}-{$selected_bulan}";
    }
    
    // Hitung 3 periode sebelumnya secara kronologis
    // Periode 1 sebelumnya (t-1)
    $p1 = date('Y-m', strtotime($target_periode . ' -1 month'));
    // Periode 2 sebelumnya (t-2)
    $p2 = date('Y-m', strtotime($target_periode . ' -2 month'));
    // Periode 3 sebelumnya (t-3)
    $p3 = date('Y-m', strtotime($target_periode . ' -3 month'));
    
    // Fungsi pembantu untuk mengambil total penjualan bulanan
    function ambil_total_penjualan($conn, $id_produk, $periode) {
        $q = "SELECT SUM(jumlah_terjual) as total FROM penjualan 
              WHERE id_produk = $id_produk AND DATE_FORMAT(tanggal, '%Y-%m') = '$periode'";
        $r = mysqli_query($conn, $q);
        $row = mysqli_fetch_assoc($r);
        return $row['total'] !== null ? intval($row['total']) : 0;
    }
    
    // Ambil data penjualan aktual untuk 3 periode sebelumnya
    $s1 = ambil_total_penjualan($conn, $selected_produk_id, $p1); // t-1
    $s2 = ambil_total_penjualan($conn, $selected_produk_id, $p2); // t-2
    $s3 = ambil_total_penjualan($conn, $selected_produk_id, $p3); // t-3
    
    // Hitung rata-rata bergerak (Moving Average 3 Periode)
    $ma = ($s1 + $s2 + $s3) / 3;
    $hasil_prediksi = number_format($ma, 2, '.', '');
    
    $hasil_kalkulasi = [
        'p1' => ['periode' => $p1, 'nilai' => $s1],
        'p2' => ['periode' => $p2, 'nilai' => $s2],
        'p3' => ['periode' => $p3, 'nilai' => $s3],
        'hasil' => $hasil_prediksi
    ];
    
    // Cek apakah data penjualan semuanya nol (belum ada data input)
    if ($s1 == 0 && $s2 == 0 && $s3 == 0) {
        $error_kalkulasi = "Perhatian: Tidak ditemukan data penjualan pada 3 periode sebelumnya ($p3, $p2, $p1). Hasil prediksi akan bernilai 0. Silakan isi data penjualan terlebih dahulu.";
    }
}
?>

<div class="row">
    <!-- Form Input Prediksi -->
    <div class="col-md-5">
        <div class="card card-modern shadow-sm">
            <div class="card-modern-header">
                <h5 class="card-modern-title"><i class="bi bi-calculator me-2 text-primary"></i>Kalkulator Moving Average (MA-3)</h5>
            </div>
            <div class="card-body">
                <form action="index.php" method="GET" class="needs-validation" novalidate>
                    <input type="hidden" name="page" value="prediksi">
                    <input type="hidden" name="hitung" value="1">
                    
                    <div class="mb-3">
                        <label for="id_produk" class="form-label fw-semibold text-dark">Pilih Produk</label>
                        <select name="id_produk" id="id_produk" class="form-select form-control-modern" required>
                            <option value="" disabled selected>-- Pilih Produk --</option>
                            <?php foreach ($produk_list as $prod): ?>
                                <option value="<?= $prod['id_produk']; ?>" <?= $prod['id_produk'] == $selected_produk_id ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($prod['nama_produk']); ?> (<?= htmlspecialchars($prod['satuan']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Silakan pilih produk!</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Periode Target Prediksi</label>
                        <div class="row g-2">
                            <div class="col-7">
                                <select name="bulan" class="form-select form-control-modern" required>
                                    <?php
                                    $bulan_list = [
                                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                    ];
                                    foreach ($bulan_list as $key => $name):
                                    ?>
                                        <option value="<?= $key; ?>" <?= $key == $selected_bulan ? 'selected' : ''; ?>><?= $name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-5">
                                <select name="tahun" class="form-select form-control-modern" required>
                                    <?php
                                    $current_year = date('Y');
                                    for ($y = $current_year - 2; $y <= $current_year + 2; $y++):
                                    ?>
                                        <option value="<?= $y; ?>" <?= $y == $selected_tahun ? 'selected' : ''; ?>><?= $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-modern-primary w-100 py-2 d-flex align-items-center justify-content-center shadow-sm">
                        <i class="bi bi-cpu me-2"></i> Hitung Prediksi
                    </button>
                </form>
            </div>
        </div>

        <!-- Card Hasil Kalkulasi -->
        <?php if ($hitung && $hasil_kalkulasi !== null): ?>
        <div class="card card-modern shadow-sm mt-4">
            <div class="card-modern-header bg-light border-bottom">
                <h5 class="card-modern-title"><i class="bi bi-clipboard-data me-2 text-success"></i>Hasil Perhitungan</h5>
            </div>
            <div class="card-body">
                <?php if ($error_kalkulasi !== null): ?>
                    <div class="alert alert-warning mb-3 small py-2"><i class="bi bi-exclamation-circle me-1"></i><?= $error_kalkulasi; ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center mb-3">
                        <thead class="table-light">
                            <tr>
                                <th>Periode</th>
                                <th>Bulan</th>
                                <th>Penjualan Aktual (X)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>t-3</td>
                                <td><?= bulan_indo($hasil_kalkulasi['p3']['periode']); ?></td>
                                <td class="fw-bold"><?= $hasil_kalkulasi['p3']['nilai']; ?></td>
                            </tr>
                            <tr>
                                <td>t-2</td>
                                <td><?= bulan_indo($hasil_kalkulasi['p2']['periode']); ?></td>
                                <td class="fw-bold"><?= $hasil_kalkulasi['p2']['nilai']; ?></td>
                            </tr>
                            <tr>
                                <td>t-1</td>
                                <td><?= bulan_indo($hasil_kalkulasi['p1']['periode']); ?></td>
                                <td class="fw-bold"><?= $hasil_kalkulasi['p1']['nilai']; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Rumus Detail -->
                <div class="bg-light p-3 rounded mb-4 border text-center">
                    <div class="small text-muted mb-1">Rumus Moving Average (MA-3)</div>
                    <div class="fw-bold text-dark fs-5 mb-2">MA = (X<sub>t-3</sub> + X<sub>t-2</sub> + X<sub>t-1</sub>) / 3</div>
                    <div class="small text-primary font-monospace">
                        MA = (<?= $hasil_kalkulasi['p3']['nilai']; ?> + <?= $hasil_kalkulasi['p2']['nilai']; ?> + <?= $hasil_kalkulasi['p1']['nilai']; ?>) / 3
                    </div>
                    <div class="fw-bold text-success fs-4 mt-2">
                        <?= $hasil_kalkulasi['hasil']; ?>
                    </div>
                    <div class="small text-muted mt-1">Estimasi Produk Terjual</div>
                </div>

                <!-- Form Simpan ke Database -->
                <form action="proses/proses_prediksi.php" method="POST">
                    <input type="hidden" name="id_produk" value="<?= $selected_produk_id; ?>">
                    <input type="hidden" name="periode" value="<?= $target_periode; ?>">
                    <input type="hidden" name="hasil_prediksi" value="<?= $hasil_kalkulasi['hasil']; ?>">
                    
                    <button type="submit" name="simpan_prediksi" class="btn btn-success w-100 py-2 d-flex align-items-center justify-content-center shadow-sm">
                        <i class="bi bi-sd-card me-2"></i> Simpan Hasil Prediksi
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Grafik dan Histori Prediksi -->
    <div class="col-md-7">
        <?php if ($selected_produk_id > 0): ?>
        <!-- Grafik Penjualan vs Prediksi -->
        <div class="card card-modern shadow-sm">
            <div class="card-modern-header">
                <h5 class="card-modern-title"><i class="bi bi-graph-up text-primary me-2"></i>Grafik Tren Penjualan vs Prediksi</h5>
            </div>
            <div class="card-body">
                <div style="height: 300px; position: relative;">
                    <canvas id="chartPrediksi"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabel Histori Prediksi yang Disimpan -->
        <div class="card card-modern shadow-sm mt-4">
            <div class="card-modern-header">
                <h5 class="card-modern-title"><i class="bi bi-clock-history text-primary me-2"></i>Daftar Prediksi yang Disimpan</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern table-hover m-0">
                        <thead>
                            <tr>
                                <th style="width: 70px;" class="text-center">No</th>
                                <th>Nama Produk</th>
                                <th class="text-center">Periode Target</th>
                                <th class="text-center">Hasil Prediksi</th>
                                <th style="width: 100px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_saved = "SELECT p.*, pr.nama_produk, pr.satuan 
                                        FROM prediksi p 
                                        JOIN produk pr ON p.id_produk = pr.id_produk 
                                        ORDER BY p.periode DESC, pr.nama_produk ASC";
                            $r_saved = mysqli_query($conn, $q_saved);
                            $no_saved = 1;
                            
                            if (mysqli_num_rows($r_saved) > 0):
                                while ($row_s = mysqli_fetch_assoc($r_saved)):
                            ?>
                            <tr>
                                <td class="text-center"><?= $no_saved++; ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($row_s['nama_produk']); ?></div>
                                    <small class="text-muted">Satuan: <?= htmlspecialchars($row_s['satuan']); ?></small>
                                </td>
                                <td class="text-center fw-medium"><?= bulan_indo($row_s['periode']); ?></td>
                                <td class="text-center text-success fw-bold"><?= number_format($row_s['hasil_prediksi'], 2, ',', '.'); ?></td>
                                <td class="text-center">
                                    <a href="proses/proses_prediksi.php?action=delete&id=<?= $row_s['id_prediksi']; ?>" class="btn btn-outline-danger btn-sm px-2 py-1 rounded" onclick="return konfirmasiHapus('Apakah Anda yakin ingin menghapus data prediksi ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i> Belum ada prediksi tersimpan.
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

<?php
// PHP Script to retrieve and merge data for Chart.js
if ($selected_produk_id > 0) {
    $all_months = [];
    $actual_data = [];
    $pred_data = [];

    // Ambil penjualan riil bulanan
    $q_act = "SELECT DATE_FORMAT(tanggal, '%Y-%m') as bulan, SUM(jumlah_terjual) as total 
              FROM penjualan 
              WHERE id_produk = $selected_produk_id 
              GROUP BY DATE_FORMAT(tanggal, '%Y-%m') 
              ORDER BY bulan ASC";
    $r_act = mysqli_query($conn, $q_act);
    $act_map = [];
    while ($row = mysqli_fetch_assoc($r_act)) {
        $act_map[$row['bulan']] = intval($row['total']);
        $all_months[$row['bulan']] = true;
    }

    // Ambil data prediksi tersimpan
    $q_pred = "SELECT periode, hasil_prediksi 
               FROM prediksi 
               WHERE id_produk = $selected_produk_id 
               ORDER BY periode ASC";
    $r_pred = mysqli_query($conn, $q_pred);
    $pred_map = [];
    while ($row = mysqli_fetch_assoc($r_pred)) {
        $pred_map[$row['periode']] = floatval($row['hasil_prediksi']);
        $all_months[$row['periode']] = true;
    }

    // Urutkan periode secara kronologis
    ksort($all_months);

    $chart_labels = [];
    $chart_actual = [];
    $chart_predicted = [];

    foreach ($all_months as $m => $val) {
        $chart_labels[] = bulan_indo($m);
        // Jika tidak ada penjualan di bulan tersebut, set null agar grafik terputus (bukan bernilai 0 di masa depan)
        $chart_actual[] = isset($act_map[$m]) ? $act_map[$m] : null;
        $chart_predicted[] = isset($pred_map[$m]) ? $pred_map[$m] : null;
    }
    
    // Dapatkan nama produk terpilih untuk label grafik
    $prod_name_query = "SELECT nama_produk FROM produk WHERE id_produk = $selected_produk_id LIMIT 1";
    $prod_name_res = mysqli_query($conn, $prod_name_query);
    $prod_name_row = mysqli_fetch_assoc($prod_name_res);
    $selected_produk_name = $prod_name_row ? $prod_name_row['nama_produk'] : '';
}
?>

<?php if ($selected_produk_id > 0): ?>
<!-- Inisialisasi Chart.js -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartPrediksi').getContext('2d');
    
    const chartLabels = <?= json_encode($chart_labels); ?>;
    const dataActual = <?= json_encode($chart_actual); ?>;
    const dataPredicted = <?= json_encode($chart_predicted); ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Penjualan Riil',
                    data: dataActual,
                    borderColor: '#0f52ba',
                    backgroundColor: 'rgba(15, 82, 186, 0.1)',
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#0f52ba',
                    pointRadius: 4
                },
                {
                    label: 'Hasil Prediksi (MA-3)',
                    data: dataPredicted,
                    borderColor: '#198754',
                    backgroundColor: 'transparent',
                    borderWidth: 3,
                    borderDash: [5, 5],
                    tension: 0.3,
                    fill: false,
                    pointBackgroundColor: '#198754',
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Tren Penjualan vs Prediksi - <?= htmlspecialchars($selected_produk_name); ?>',
                    font: {
                        size: 14,
                        weight: 'bold',
                        family: 'Outfit'
                    },
                    padding: { bottom: 15 }
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { family: 'Outfit' }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah Terjual (Satuan)',
                        font: { family: 'Outfit', weight: 'bold' }
                    },
                    ticks: {
                        font: { family: 'Outfit' }
                    }
                },
                x: {
                    ticks: {
                        font: { family: 'Outfit' }
                    }
                }
            }
        }
    });
});
</script>
<?php endif; ?>
