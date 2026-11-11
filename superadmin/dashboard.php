<?php
// superadmin/dashboard.php — Dashboard Analitik: Menu Terlaris, Jam Ramai, Tren Penjualan.
// Hanya pesanan "Sudah dibayar" yang dihitung. Periode dipilih lewat ?periode=harian|mingguan|bulanan
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';
include 'cek_superadmin.php';
include 'periode.php';           // hasil: $periode, $mulai, $selesai, $nama_periode, $kondisi_laporan

// Apakah ada data pada periode ini?
$stmt = $pdo->prepare("SELECT COUNT(*) AS jumlah, COALESCE(SUM(o.total_harga), 0) AS total FROM orders o WHERE $kondisi_laporan");
$stmt->execute([$mulai, $selesai]);
$ringkas = $stmt->fetch();
$ada_data = ($ringkas['jumlah'] > 0);

if ($ada_data) {
    // 1) Menu Terlaris: 5 menu dengan jumlah porsi terbanyak
    $stmt = $pdo->prepare("SELECT i.nama_menu, SUM(i.jumlah) AS porsi
                           FROM order_items i JOIN orders o ON o.id = i.order_id
                           WHERE $kondisi_laporan GROUP BY i.nama_menu ORDER BY porsi DESC, i.nama_menu LIMIT 5");
    $stmt->execute([$mulai, $selesai]);
    $terlaris = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT SUM(i.jumlah) FROM order_items i JOIN orders o ON o.id = i.order_id WHERE $kondisi_laporan");
    $stmt->execute([$mulai, $selesai]);
    $total_porsi = (int)$stmt->fetchColumn();

    // 2) Jam Ramai: jumlah pesanan per jam (00 - 23)
    $stmt = $pdo->prepare("SELECT HOUR(COALESCE(o.dibayar_pada, o.created_at)) AS jam, COUNT(*) AS n, SUM(o.total_harga) AS total
                           FROM orders o WHERE $kondisi_laporan GROUP BY jam");
    $stmt->execute([$mulai, $selesai]);
    $label_jam = [];
    $nilai_jam = [];
    $total_per_jam = [];
    for ($j = 0; $j < 24; $j++) {
        $label_jam[] = str_pad($j, 2, '0', STR_PAD_LEFT);
        $nilai_jam[] = 0;
        $total_per_jam[] = 0;
    }
    foreach ($stmt->fetchAll() as $baris) {
        $nilai_jam[(int)$baris['jam']]      = (int)$baris['n'];
        $total_per_jam[(int)$baris['jam']]  = (int)$baris['total'];
    }

    // 3) Tren Penjualan: total rupiah. Harian = per jam, mingguan / bulanan = per tanggal
    if ($periode == 'harian') {
        $label_tren = $label_jam;
        $nilai_tren = $total_per_jam;
    } else {
        $stmt = $pdo->prepare("SELECT DATE(COALESCE(o.dibayar_pada, o.created_at)) AS tanggal, SUM(o.total_harga) AS total
                               FROM orders o WHERE $kondisi_laporan GROUP BY tanggal");
        $stmt->execute([$mulai, $selesai]);
        $per_tanggal = [];
        foreach ($stmt->fetchAll() as $baris) {
            $per_tanggal[$baris['tanggal']] = (int)$baris['total'];
        }
        // Semua tanggal dari awal periode sampai hari ini (yang kosong dianggap 0)
        $label_tren = [];
        $nilai_tren = [];
        for ($t = strtotime($mulai); $t <= time(); $t += 86400) {
            $label_tren[] = date('d/m', $t);
            $nilai_tren[] = $per_tanggal[date('Y-m-d', $t)] ?? 0;
        }
    }
}

$judul = 'Dashboard Analitik';
$menu_aktif = 'dashboard';
include 'sa_header.php';
?>
<div class="kepala-halaman">
    <div>
        <h1 class="judul-halaman">Dashboard Analitik</h1>
        <p class="teks-pendukung">Pantau menu terlaris, jam ramai, dan tren penjualan (<?= $nama_periode ?>).</p>
    </div>
    <div class="aksi-kepala">
        <?php include 'periode_tab.php'; ?>
    </div>
</div>

<?php if (!$ada_data): ?>
    <section class="kartu keadaan-kosong">
        <div class="ikon-hero"><i class="fa-solid fa-chart-column"></i></div>
        <h2 class="judul-bagian">Belum ada data pada periode ini</h2>
        <p class="teks-pendukung">Pesanan yang sudah dibayar akan dihitung di sini.</p>
    </section>
<?php else: ?>
    <p class="teks-pendukung" style="margin-bottom:12px">
        <strong><?= $ringkas['jumlah'] ?> pesanan</strong> sudah dibayar &bull; Total <strong><?= rupiah($ringkas['total']) ?></strong>
    </p>

    <!-- Tiga kotak sejajar -->
    <div class="grid-analitik">
        <section class="kotak-analitik">
            <h2 class="kartu-judul"><i class="fa-solid fa-star"></i> Menu Terlaris</h2>
            <p class="teks-kecil">5 menu dengan porsi terbanyak</p>
            <?php foreach ($terlaris as $urutan => $m): ?>
                <?php $persen = $total_porsi > 0 ? round($m['porsi'] / $total_porsi * 100) : 0; ?>
                <div class="baris-terlaris">
                    <div class="baris-terlaris-atas">
                        <span class="nomor-terlaris"><?= $urutan + 1 ?></span>
                        <span class="nama-terlaris"><?= htmlspecialchars($m['nama_menu']) ?></span>
                        <span class="porsi-terlaris"><?= $m['porsi'] ?> porsi</span>
                    </div>
                    <div class="bar-terlaris"><div class="isi-bar-terlaris" style="width: <?= max($persen, 3) ?>%"></div></div>
                    <span class="teks-kecil"><?= $persen ?>% dari seluruh porsi</span>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="kotak-analitik">
            <h2 class="kartu-judul"><i class="fa-regular fa-clock"></i> Jam Ramai</h2>
            <p class="teks-kecil">Jumlah pesanan per jam</p>
            <div class="kanvas-grafik">
                <canvas id="grafik-jam" data-label="<?= htmlspecialchars(json_encode($label_jam)) ?>" data-nilai="<?= htmlspecialchars(json_encode($nilai_jam)) ?>"></canvas>
            </div>
        </section>

        <section class="kotak-analitik">
            <h2 class="kartu-judul"><i class="fa-solid fa-arrow-trend-up"></i> Tren Penjualan</h2>
            <p class="teks-kecil">Total penjualan <?= $periode == 'harian' ? 'per jam' : 'per hari' ?> (Rp)</p>
            <div class="kanvas-grafik">
                <canvas id="grafik-tren" data-label="<?= htmlspecialchars(json_encode($label_tren)) ?>" data-nilai="<?= htmlspecialchars(json_encode($nilai_tren)) ?>"></canvas>
            </div>
        </section>
    </div>

    <!-- Chart.js (harus dimuat sebelum JS/Admin.js) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<?php endif; ?>
<?php include 'sa_footer.php'; ?>
