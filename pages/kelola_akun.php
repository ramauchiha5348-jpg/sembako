<div class="row g-4">
    <!-- Kolom Ubah Password -->
    <div class="col-lg-4">
        <div class="card card-modern border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="fw-bold text-warning mb-0"><i class="bi bi-key-fill me-2"></i> Ubah Password Akun</h5>
            </div>
            <div class="card-body">
                <form action="proses/proses_kelola_akun.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="ubah_password">
                    
                    <div class="mb-3">
                        <label for="id_user" class="form-label text-muted small fw-semibold">Pilih Akun</label>
                        <select class="form-select border-2" name="id_user" id="id_user" required>
                            <option value="">Pilih pengguna</option>
                            <?php
                            // Ambil daftar pengguna
                            $q_users = mysqli_query($conn, "SELECT id_user, nama_pengguna, username FROM `user` ORDER BY nama_pengguna ASC");
                            while($u = mysqli_fetch_assoc($q_users)){
                                $name = htmlspecialchars($u['nama_pengguna'] ?? $u['username']);
                                echo "<option value='{$u['id_user']}'>{$name}</option>";
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback">Pilih pengguna yang ingin diubah password-nya!</div>
                    </div>

                    <div class="mb-3">
                        <label for="password_baru" class="form-label text-muted small fw-semibold">Password Baru</label>
                        <input type="password" class="form-control border-2" name="password_baru" id="password_baru" placeholder="Minimal 6 karakter" required minlength="6">
                        <div class="invalid-feedback">Password minimal 6 karakter!</div>
                    </div>

                    <div class="mb-4">
                        <label for="konfirmasi_password" class="form-label text-muted small fw-semibold">Konfirmasi Password</label>
                        <input type="password" class="form-control border-2" name="konfirmasi_password" id="konfirmasi_password" placeholder="Ulangi password baru" required minlength="6">
                        <div class="invalid-feedback">Konfirmasi password wajib diisi!</div>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 fw-bold text-dark shadow-sm">
                        Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Daftar Pengguna -->
    <div class="col-lg-8">
        <div class="card card-modern border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-primary mb-0"><i class="bi bi-people-fill me-2"></i> Daftar Pengguna Sistem</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light text-center">
                            <tr>
                                <th>No</th>
                                <th class="text-start">Nama Pengguna</th>
                                <th>Email</th>
                                <th>Hak Akses</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php
                            $q_list = mysqli_query($conn, "SELECT * FROM `user` ORDER BY id_user ASC");
                            $no = 1;
                            while($row = mysqli_fetch_assoc($q_list)){
                                $badge_color = $row['hak_akses'] == 'Admin' ? 'primary' : 'secondary';
                                $is_current_user = ($row['id_user'] == $_SESSION['id_user']);
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td class="text-start fw-semibold"><?= htmlspecialchars($row['nama_pengguna'] ?? $row['username']); ?></td>
                                <td class="text-muted"><?= htmlspecialchars($row['email'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge bg-<?= $badge_color ?> rounded-pill px-3 py-2"><?= htmlspecialchars($row['hak_akses']); ?></span>
                                </td>
                                <td>
                                    <?php if(!$is_current_user): ?>
                                    <form action="proses/proses_kelola_akun.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                                        <input type="hidden" name="action" value="hapus_pengguna">
                                        <input type="hidden" name="id_user" value="<?= $row['id_user'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm px-3 rounded-pill">
                                            <i class="bi bi-trash-fill me-1"></i> Hapus
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="badge bg-light text-muted border px-3 py-2">Sedang Aktif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validasi Form Client-Side
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            
            // Validasi custom password cocok
            var pass = form.querySelector('#password_baru');
            var confirmPass = form.querySelector('#konfirmasi_password');
            if (pass && confirmPass && pass.value !== confirmPass.value) {
                confirmPass.setCustomValidity('Password tidak cocok');
                event.preventDefault()
                event.stopPropagation()
            } else if (confirmPass) {
                confirmPass.setCustomValidity('');
            }
            
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>
