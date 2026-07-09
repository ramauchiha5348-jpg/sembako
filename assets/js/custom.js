/**
 * Custom JS untuk Sistem Prediksi Penjualan Toko Sembako
 */

document.addEventListener('DOMContentLoaded', function() {
    // 1. Auto-close alert setelah 4 detik
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            // Menggunakan Bootstrap alert close method
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 4000);
    });

    // 2. Validasi Form Bootstrap (needs-validation)
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});

/**
 * Konfirmasi Hapus Data
 * @param {string} message - Pesan konfirmasi
 * @returns {boolean}
 */
function konfirmasiHapus(message = "Apakah Anda yakin ingin menghapus data ini?") {
    return confirm(message);
}
