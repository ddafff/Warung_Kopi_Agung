<?php
// user/us_footer.php — bagian bawah halaman pelanggan (navigasi bawah untuk HP + JavaScript)
$role = $_SESSION['role'] ?? '';
$menu_aktif = $menu_aktif ?? '';
?>
</div>
</main>

<nav class="nav-bawah">
    <div class="nav-bawah-isi">
        <a class="nav-bawah-tautan <?= $menu_aktif == 'menu' ? 'aktif' : '' ?>" href="us_index.php">
            <i class="fa-solid fa-store"></i><span>Menu</span></a>
        <?php if ($role == 'pelanggan'): ?>
            <a class="nav-bawah-tautan <?= $menu_aktif == 'riwayat' ? 'aktif' : '' ?>" href="riwayat_pesanan.php">
                <i class="fa-solid fa-clock-rotate-left"></i><span>Riwayat</span></a>
        <?php endif; ?>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a class="nav-bawah-tautan" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i><span>Keluar</span></a>
        <?php else: ?>
            <a class="nav-bawah-tautan" href="login.php"><i class="fa-solid fa-right-to-bracket"></i><span>Masuk</span></a>
        <?php endif; ?>
    </div>
</nav>

<div class="toast-info" id="toast-info"><i class="fa-solid fa-circle-info"></i><span id="toast-teks"></span></div>

<script src="../JS/User.js"></script>
</body>
</html>
