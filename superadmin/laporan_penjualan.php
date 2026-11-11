<?php
// superadmin/laporan_penjualan.php — laporan penjualan: total, dan daftar pesanan yang sudah dibayar.
// Periode dipilih lewat ?periode=harian|mingguan|bulanan. Tombol Cetak memakai window.print()
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';
include 'cek_superadmin.php';
include 'periode.php';           // hasil: $periode, $mulai, $selesai, $nama_periode, $kondisi_laporan

$stmt = $pdo->prepare("SELECT o.*, COALESCE(o.dibayar_pada, o.created_at) AS waktu_bayar
                       FROM orders o WHERE $kondisi_laporan
                       ORDER BY waktu_bayar DESC, o.id DESC LIMIT 500");
$stmt->execute([$mulai, $selesai]);
$daftar = $stmt->fetchAll();

$total_penjualan = 0;
foreach ($daftar as $p) {
    $total_penjualan += $p['total_harga'];
}

$judul = 'Laporan Penjualan';
$menu_aktif = 'laporan';
include 'sa_header.php';
?>
<div class="kepala-halaman">
    <div>
        <h1 class="judul-halaman">Laporan Penjualan</h1>
        <p class="teks-pendukung">Pesanan yang sudah dibayar (<?= $nama_periode ?>).</p>
    </div>
    <div class="aksi-kepala sembunyi-cetak">
        <?php include 'periode_tab.php'; ?>
        <button class="tombol tombol-perbarui" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Cetak</button>
    </div>
</div>

<?php if (!$daftar): ?>
    <section class="kartu keadaan-kosong">
        <div class="ikon-hero"><i class="fa-solid fa-file-invoice-dollar"></i></div>
        <h2 class="judul-bagian">Belum ada data</h2>
        <p class="teks-pendukung">Belum ada pesanan yang sudah dibayar pada periode ini.</p>
    </section>
<?php else: ?>
    <div class="grid-ringkasan">
        <div class="kartu-ringkas">
            <span class="teks-kecil">Total Penjualan</span>
            <strong class="angka-ringkas"><?= rupiah($total_penjualan) ?></strong>
        </div>
        <div class="kartu-ringkas">
            <span class="teks-kecil">Jumlah Pesanan</span>
            <strong class="angka-ringkas"><?= count($daftar) ?></strong>
        </div>
    </div>

    <section class="kartu-tabel">
        <div class="pembungkus-tabel">
            <table class="tabel-data">
                <thead>
                    <tr><th>No.</th><th>Waktu</th><th>Meja</th><th>Menu dan jumlah</th><th>Metode</th><th>Total</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $p): ?>
                        <?php
                        $stmt = $pdo->prepare("SELECT nama_menu, jumlah FROM order_items WHERE order_id = ? ORDER BY id");
                        $stmt->execute([$p['id']]);
                        $ringkasan = [];
                        foreach ($stmt->fetchAll() as $i) {
                            $ringkasan[] = $i['nama_menu'] . ' x' . $i['jumlah'];
                        }
                        ?>
                        <tr>
                            <td data-label="No."><strong>No. <?= $p['id'] ?></strong></td>
                            <td data-label="Waktu"><?= tanggal_indo($p['waktu_bayar']) ?></td>
                            <td data-label="Meja"><span class="chip-meja">Meja <?= str_pad($p['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></span></td>
                            <td data-label="Menu"><?= htmlspecialchars(implode(', ', $ringkasan)) ?></td>
                            <td data-label="Metode"><?= label_metode($p['metode_pembayaran']) ?></td>
                            <td data-label="Total"><strong><?= rupiah($p['total_harga']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="catatan-bawah-tabel">
            <span>Total <?= count($daftar) ?> pesanan</span>
            <strong>Total Penjualan: <?= rupiah($total_penjualan) ?></strong>
        </div>
    </section>
<?php endif; ?>
<?php include 'sa_footer.php'; ?>
