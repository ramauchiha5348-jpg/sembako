<?php
/**
 * Halaman Dashboard Utama
 */
// Mencegah akses langsung ke file
if (!defined('conn')) {
    exit('Akses langsung tidak diizinkan');
}

// 1. Hitung Total Produk
$q_prod = mysqli_query($conn, "SELECT COUNT(*) as total FROM produk");
$r_prod = mysqli_fetch_assoc($q_prod);
$total_produk = $r_prod['total'];

// 2. Hitung Total Item Terjual
$q_sales = mysqli_query($conn, "SELECT SUM(jumlah_terjual) as total FROM penjualan");
$r_sales = mysqli_fetch_assoc($q_sales);
$total_terjual = $r_sales['total'] ? $r_sales['total'] : 0;

// 3. Hitung Total Pendapatan
$q_rev = mysqli_query($conn, "SELECT SUM(p.jumlah_terjual * pr.harga) as total 
                              FROM penjualan p 
                              JOIN produk pr ON p.id_produk = pr.id_produk");
$r_rev = mysqli_fetch_assoc($q_rev);
$total_pendapatan = $r_rev['total'] ? $r_rev['total'] : 0;

// 4. Hitung Total Prediksi Tersimpan
$q_pred = mysqli_query($conn, "SELECT COUNT(*) as total FROM prediksi");
$r_pred = mysqli_fetch_assoc($q_pred);
$total_prediksi = $r_pred['total'];
?>

<!-- Baris Card Summary Statistik -->
<div class="row">
    <!-- Card Total Produk -->
    <div class="col-lg-3 col-6">
        <div class="card card-modern bg-white shadow-sm border-0 hover-scale mb-4">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase text-muted small fw-bold d-block mb-1">Total Produk</span>
                    <h3 class="fw-bold m-0 text-dark"><?= number_format($total_produk); ?></h3>
                    <a href="index.php?page=produk" class="small text-decoration-none text-primary mt-2 d-inline-block">Detail Produk <i class="bi bi-arrow-right-short"></i></a>
                </div>
                <div class="bg-light-blue text-primary rounded p-3">
                    <i class="bi bi-box-seam fs-2"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Total Terjual -->
    <div class="col-lg-3 col-6">
        <div class="card card-modern bg-white shadow-sm border-0 hover-scale mb-4">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase text-muted small fw-bold d-block mb-1">Total Terjual</span>
                    <h3 class="fw-bold m-0 text-dark"><?= number_format($total_terjual); ?> <span class="fs-6 fw-normal text-muted">Item</span></h3>
                    <a href="index.php?page=penjualan" class="small text-decoration-none text-primary mt-2 d-inline-block">Detail Penjualan <i class="bi bi-arrow-right-short"></i></a>
                </div>
                <div class="bg-light-blue text-success rounded p-3" style="background-color: #e8f5e9 !important;">
                    <i class="bi bi-cart-check fs-2 text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Total Pendapatan -->
    <div class="col-lg-3 col-6">
        <div class="card card-modern bg-white shadow-sm border-0 hover-scale mb-4">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase text-muted small fw-bold d-block mb-1">Total Pendapatan</span>
                    <h3 class="fw-bold m-0 text-dark fs-4"><?= format_rupiah($total_pendapatan); ?></h3>
                    <a href="index.php?page=laporan" class="small text-decoration-none text-primary mt-2 d-inline-block">Lihat Laporan <i class="bi bi-arrow-right-short"></i></a>
                </div>
                <div class="bg-light-blue text-warning rounded p-3" style="background-color: #fff8e1 !important;">
                    <i class="bi bi-cash-coin fs-2 text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Total Prediksi -->
    <div class="col-lg-3 col-6">
        <div class="card card-modern bg-white shadow-sm border-0 hover-scale mb-4">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase text-muted small fw-bold d-block mb-1">Hasil Prediksi</span>
                    <h3 class="fw-bold m-0 text-dark"><?= number_format($total_prediksi); ?> <span class="fs-6 fw-normal text-muted">Periode</span></h3>
                    <a href="index.php?page=prediksi" class="small text-decoration-none text-primary mt-2 d-inline-block">Kalkulasi Prediksi <i class="bi bi-arrow-right-short"></i></a>
                </div>
                <div class="bg-light-blue text-info rounded p-3" style="background-color: #e0f7fa !important;">
                    <i class="bi bi-graph-up-arrow fs-2 text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Baris Grafik dan Transaksi Terakhir -->
<div class="row">
    <!-- Grafik Penjualan Bulanan (Revenue) -->
    <div class="col-md-7">
        <div class="card card-modern shadow-sm border-0">
            <div class="card-modern-header">
                <h5 class="card-modern-title"><i class="bi bi-bar-chart-line text-primary me-2"></i>Tren Penjualan Bulanan (Omset)</h5>
            </div>
            <div class="card-body">
                <div style="height: 320px; position: relative;">
                    <canvas id="chartOmsetBulanan"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Informasi / Transaksi Terakhir -->
    <div class="col-md-5">
        <div class="card card-modern shadow-sm border-0">
            <div class="card-modern-header d-flex justify-content-between align-items-center">
                <h5 class="card-modern-title"><i class="bi bi-clock-history text-primary me-2"></i>Penjualan Terakhir</h5>
                <a href="index.php?page=penjualan" class="btn btn-outline-primary btn-xs rounded text-decoration-none small px-2">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-modern m-0" style="font-size: 0.9rem;">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-end">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_recent = mysqli_query($conn, "SELECT p.*, pr.nama_produk, pr.satuan 
                                                             FROM penjualan p 
                                                             JOIN produk pr ON p.id_produk = pr.id_produk 
                                                             ORDER BY p.tanggal DESC LIMIT 5");
                            if (mysqli_num_rows($q_recent) > 0):
                                while ($row = mysqli_fetch_assoc($q_recent)):
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_produk']); ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($row['satuan']); ?></small>
                                </td>
                                <td class="text-center"><?= tanggal_indo($row['tanggal']); ?></td>
                                <td class="text-end fw-bold text-primary"><?= number_format($row['jumlah_terjual']); ?></td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">Belum ada transaksi.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Card Panduan Cepat -->
        <div class="card card-modern shadow-sm border-0 mt-4 bg-primary text-white">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-2"><i class="bi bi-info-circle-fill me-2"></i>Prediksi Moving Average</h5>
                <p class="small mb-3" style="opacity: 0.9;">
                    Website ini menggunakan metode <strong>Moving Average 3 Periode</strong>. 
                    Pastikan Anda telah memasukkan data penjualan minimal 3 bulan berturut-turut untuk menghitung prediksi bulan berikutnya secara akurat.
                </p>
                <a href="index.php?page=prediksi" class="btn btn-light btn-sm text-primary fw-bold rounded shadow-sm">Mulai Prediksi <i class="bi bi-arrow-right-short"></i></a>
            </div>
        </div>
    </div>
</div>

<?php
// Query untuk mendapatkan omset bulanan
$q_chart = "SELECT DATE_FORMAT(p.tanggal, '%Y-%m') as bulan, SUM(p.jumlah_terjual * pr.harga) as omset 
            FROM penjualan p 
            JOIN produk pr ON p.id_produk = pr.id_produk 
            GROUP BY DATE_FORMAT(p.tanggal, '%Y-%m') 
            ORDER BY bulan ASC";
$r_chart = mysqli_query($conn, $q_chart);

$months = [];
$revenues = [];

while ($c_row = mysqli_fetch_assoc($r_chart)) {
    $months[] = bulan_indo($c_row['bulan']);
    $revenues[] = intval($c_row['omset']);
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartOmsetBulanan').getContext('2d');
    
    const chartLabels = <?= json_encode($months); ?>;
    const chartData = <?= json_encode($revenues); ?>;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Total Omset Penjualan (Rupiah)',
                data: chartData,
                backgroundColor: 'rgba(15, 82, 186, 0.7)',
                borderColor: '#0f52ba',
                borderWidth: 1.5,
                borderRadius: 6,
                barPercentage: 0.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let val = context.raw;
                            return ' ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: "compact", compactDisplay: "short" }).format(value);
                        },
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
