<?php // superadmin/periode_tab.php — tombol pilihan periode (dipakai dashboard dan laporan). Butuh $periode ?>
<nav class="tab-filter sembunyi-cetak">
    <a class="tab-filter-tombol <?= $periode == 'harian' ? 'aktif' : '' ?>" href="?periode=harian">Harian</a>
    <a class="tab-filter-tombol <?= $periode == 'mingguan' ? 'aktif' : '' ?>" href="?periode=mingguan">Mingguan</a>
    <a class="tab-filter-tombol <?= $periode == 'bulanan' ? 'aktif' : '' ?>" href="?periode=bulanan">Bulanan</a>
</nav>
