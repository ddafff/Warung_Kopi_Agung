<?php
// us_footer.php — kerangka bawah halaman pelanggan/umum.
// Variabel opsional: $skrip_tambahan (daftar alamat skrip pustaka yang dimuat sebelum JS/User.js)
$tipe_halaman    = $tipe_halaman    ?? 'utama';
$menu_aktif      = $menu_aktif      ?? '';
$skrip_tambahan  = $skrip_tambahan  ?? [];
$peran           = $_SESSION['role'] ?? null;
$sudah_masuk     = !empty($_SESSION['user_id']);
?>
<?php if ($tipe_halaman === 'polos'): ?>
</main>
<?php else: ?>
</div>
</main>
<?php endif; ?>

<?php if ($tipe_halaman === 'utama'): ?>
<nav class="nav-bawah" aria-label="Navigasi bawah">
    <div class="nav-bawah-isi">
        <a class="nav-bawah-tautan<?= $menu_aktif === 'menu' ? ' aktif' : '' ?>" href="<?= BASE_URL ?>/user/us_index.php">
            <i class="fa-solid fa-store"></i><span>Menu</span>
        </a>
        <?php if ($peran === 'pelanggan'): ?>
        <a class="nav-bawah-tautan<?= $menu_aktif === 'riwayat' ? ' aktif' : '' ?>" href="<?= BASE_URL ?>/user/riwayat_pesanan.php">
            <i class="fa-solid fa-clock-rotate-left"></i><span>Riwayat</span>
        </a>
        <?php endif; ?>
        <?php if ($peran === 'admin' || $peran === 'superadmin'): ?>
        <a class="nav-bawah-tautan" href="<?= e(halaman_peran($peran)) ?>">
            <i class="fa-solid fa-gauge"></i><span>Panel Admin</span>
        </a>
        <?php endif; ?>
        <?php if ($sudah_masuk): ?>
        <a class="nav-bawah-tautan" href="<?= BASE_URL ?>/user/logout.php">
            <i class="fa-solid fa-right-from-bracket"></i><span>Keluar</span>
        </a>
        <?php else: ?>
        <a class="nav-bawah-tautan" href="<?= BASE_URL ?>/user/login.php">
            <i class="fa-solid fa-right-to-bracket"></i><span>Masuk</span>
        </a>
        <?php endif; ?>
    </div>
</nav>
<?php endif; ?>

<div class="toast-info" id="toast-info" role="status" aria-live="polite">
    <i class="fa-solid fa-circle-info"></i><span id="toast-teks"></span>
</div>

<script>window.INTERVAL_PANTAU = <?= (int) INTERVAL_PANTAU ?>; window.ALAMAT_DASAR = <?= json_encode(BASE_URL) ?>;</script>
<?php foreach ($skrip_tambahan as $alamat): ?>
<script src="<?= e($alamat) ?>"></script>
<?php endforeach; ?>
<script src="<?= BASE_URL ?>/JS/User.js"></script>
</body>
</html>
