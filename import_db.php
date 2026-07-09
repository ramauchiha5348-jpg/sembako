<?php
require_once 'config/koneksi.php';

$sql_file = __DIR__ . '/database/db_prediksi_sembako.sql';

if (file_exists($sql_file)) {
    $sql = file_get_contents($sql_file);
    if (mysqli_multi_query($conn, $sql)) {
        do {
            if ($result = mysqli_store_result($conn)) {
                mysqli_free_result($result);
            }
        } while (mysqli_more_results($conn) && mysqli_next_result($conn));
        
        echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
        echo "<h2>✅ Database berhasil di-import!</h2>";
        echo "<p>Tabel-tabel telah dibuat dan data awal berhasil dimasukkan ke dalam database.</p>";
        echo "<a href='index.php' style='padding: 10px 20px; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 5px;'>Kembali ke Beranda</a>";
        echo "</div>";
    } else {
        echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px; color: red;'>";
        echo "<h2>❌ Gagal meng-import database</h2>";
        echo "<p>Error: " . mysqli_error($conn) . "</p>";
        echo "</div>";
    }
} else {
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px; color: red;'>";
    echo "<h2>❌ File SQL tidak ditemukan!</h2>";
    echo "<p>Pastikan file database/db_prediksi_sembako.sql tersedia.</p>";
    echo "</div>";
}
?>
