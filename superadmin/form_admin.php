<?php
// superadmin/form_admin.php — isian formulir admin (dipakai tambah_admin.php dan edit_admin.php).
// Variabel dari halaman: $username, $email, $error, $mode ('tambah' atau 'edit')
$username = $username ?? '';
$email    = $email    ?? '';
$error    = $error    ?? '';
$mode     = $mode     ?? 'tambah';
?>
<section class="kartu">
    <?php if ($error): ?>
        <div class="pesan pesan-galat"><i class="fa-solid fa-circle-exclamation"></i><span><?= $error ?></span></div>
    <?php endif; ?>
 
    <form class="formulir" method="POST">
        <div class="kolom-isian">
            <label class="label-isian" for="username"><i class="fa-solid fa-at"></i> Username</label>
            <input class="isian" type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" placeholder="Contoh: kasir2" required>
            <span class="petunjuk-isian">3-30 karakter: huruf, angka, titik, dan garis bawah.</span>
        </div>
        <div class="kolom-isian">
            <label class="label-isian" for="email"><i class="fa-solid fa-envelope"></i> Email (untuk masuk)</label>
            <input class="isian" type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="kasir@email.com" required>
        </div>
        <div class="kolom-isian">
            <label class="label-isian" for="password"><i class="fa-solid fa-lock"></i> <?= $mode == 'tambah' ? 'Kata sandi' : 'Kata sandi baru (kosongkan bila tidak diganti)' ?></label>
            <div class="isian-dengan-tombol">
                <input class="isian" type="password" id="password" name="password" placeholder="Minimal 8 karakter" <?= $mode == 'tambah' ? 'required' : '' ?>>
                <button class="tombol-mata" type="button" onclick="togglePassword()"><i class="fa-solid fa-eye" id="ikonMata"></i></button>
            </div>
            <span class="petunjuk-isian">Sandi disimpan dan tidak bisa dilihat kembali.</span>
        </div>
        <div class="aksi-form">
            <a class="tombol tombol-sekunder" href="kelola_admin.php">Batal</a>
            <button class="tombol tombol-utama" type="submit"><i class="fa-solid fa-check"></i> <?= $mode == 'tambah' ? 'Simpan Admin' : 'Simpan Perubahan' ?></button>
        </div>
    </form>
</section>
<script>
function togglePassword() {
    var input = document.getElementById('password');
    var ikon  = document.getElementById('ikonMata');
    if (input.type === 'password') {
        input.type = 'text';
        ikon.className = 'fa-solid fa-eye-slash';
    } else {
        input.type = 'password';
        ikon.className = 'fa-solid fa-eye';
    }
}
</script>