<?php
// admin/ad_menu.php — menu samping (dipakai ad_header.php untuk halaman admin dan superadmin)
// Superadmin mewarisi semua menu admin, ditambah menu Pemilik.
// Variabel: $role, $menu_aktif, $ke_admin (alamat folder admin), $ke_sa (alamat folder superadmin)
?>
    <aside class="menu-samping" id="menu-samping">
        <div class="menu-samping-judul">Kasir</div>
        <a class="menu-samping-tautan <?= $menu_aktif == 'pesanan' ? 'aktif' : '' ?>" href="<?= $ke_admin ?>pesanan_masuk.php"><i class="fa-solid fa-receipt"></i> Pesanan Masuk</a>
        <a class="menu-samping-tautan <?= $menu_aktif == 'antrean' ? 'aktif' : '' ?>" href="<?= $ke_admin ?>antrean_dapur.php"><i class="fa-solid fa-kitchen-set"></i> Antrean Dapur</a>
        <a class="menu-samping-tautan <?= $menu_aktif == 'pengguna' ? 'aktif' : '' ?>" href="<?= $ke_admin ?>daftar_pengguna.php"><i class="fa-solid fa-users"></i> Daftar Pengguna</a>

        <?php if ($role == 'superadmin'): ?>
            <div class="menu-samping-pemisah"></div>
            <div class="menu-samping-judul">Pemilik</div>
            <a class="menu-samping-tautan <?= $menu_aktif == 'dashboard' ? 'aktif' : '' ?>" href="<?= $ke_sa ?>dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard Analitik</a>
            <a class="menu-samping-tautan <?= $menu_aktif == 'laporan' ? 'aktif' : '' ?>" href="<?= $ke_sa ?>laporan_penjualan.php"><i class="fa-solid fa-file-invoice-dollar"></i> Laporan Penjualan</a>
            <a class="menu-samping-tautan <?= $menu_aktif == 'menu' ? 'aktif' : '' ?>" href="<?= $ke_sa ?>kelola_menu.php"><i class="fa-solid fa-mug-saucer"></i> Kelola Menu</a>
            <a class="menu-samping-tautan <?= $menu_aktif == 'meja' ? 'aktif' : '' ?>" href="<?= $ke_sa ?>kelola_meja.php"><i class="fa-solid fa-table-cells"></i> Kelola Meja</a>
<<<<<<< HEAD
=======
            <a class="menu-samping-tautan <?= $menu_aktif == 'admin' ? 'aktif' : '' ?>" href="<?= $ke_sa ?>kelola_admin.php"><i class="fa-solid fa-user-gear"></i> Kelola Admin</a>
>>>>>>> d33efa4
        <?php endif; ?>

        <div class="menu-samping-bawah">
            <div class="menu-samping-pemisah"></div>
            <a class="menu-samping-tautan" href="../user/us_index.php"><i class="fa-solid fa-store"></i> Lihat Menu</a>
            <a class="menu-samping-tautan" href="../user/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
<<<<<<< HEAD
        </div>
=======
        </div> 
>>>>>>> d33efa4
    </aside>
