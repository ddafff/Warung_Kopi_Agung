<?php
// riwayat_pesanan.php — semua pesanan milik pelanggan (termasuk yang Dibatalkan). Hanya baca.
require_once '../koneksi.php';
require_once '../fungsi.php';

wajib_peran(['pelanggan']);

$stmt = $pdo->prepare(
    'SELECT id, nomor_meja, total_harga, metode_pembayaran, status_pembayaran, status_pesanan, created_at
       FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC'
);
$stmt->execute([(int) $_SESSION['user_id']]);
$daftar = $stmt->fetchAll();

// Ambil semua item sekaligus (hindari satu kueri per pesanan)
$item_per_pesanan = [];
if ($daftar) {
    $id_semua = array_column($daftar, 'id');
    $tanda = implode(',', array_fill(0, count($id_semua), '?'));
    $q = $pdo->prepare("SELECT order_id, nama_menu, jumlah FROM order_items WHERE order_id IN ($tanda) ORDER BY id");
    $q->execute($id_semua);
    foreach ($q->fetchAll() as $baris) {
        $item_per_pesanan[(int) $baris['order_id']][] = $baris;
    }
}

$judul_halaman = 'Riwayat Pesanan';
$tipe_halaman  = 'utama';
$menu_aktif    = 'riwayat';
include 'us_header.php';
?>
<div class="baris-judul" style="margin-top:4px">
    <div>
        <h1 class="judul-halaman">Riwayat Pesanan</h1>
        <p class="teks-pendukung"><?= count($daftar) ?> pesanan</p>
    </div>
</div>

<?php if (!$daftar): ?>
<section class="kartu keadaan-kosong">
    <div class="ikon-hero"><i class="fa-solid fa-mug-hot"></i></div>
    <h2 class="judul-bagian">Belum ada riwayat pesanan</h2>
    <p class="teks-pendukung" style="margin-bottom:16px">Pesanan yang sudah Anda buat akan muncul di sini.</p>
    <a class="tombol tombol-utama" href="us_index.php"><i class="fa-solid fa-store"></i> Mulai Pesan Kopi &amp; Makanan</a>
</section>
<?php else: ?>
<div class="daftar-riwayat">
    <?php foreach ($daftar as $p): ?>
    <article class="kartu kartu-riwayat">
        <div class="kartu-riwayat-atas">
            <div>
                <div class="waktu-riwayat"><i class="fa-regular fa-clock"></i> <?= e(tanggal_indo($p['created_at'])) ?></div>
                <span class="teks-kecil">No. <?= (int) $p['id'] ?> &bull; Meja <?= str_pad((string) $p['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></span>
            </div>
        </div>
        <div class="kotak-menu-ringkas">
            <small>Menu &amp; Jumlah</small>
            <?= e(ringkas_item($item_per_pesanan[(int) $p['id']] ?? [])) ?>
        </div>
        <div class="kelompok-lencana">
            <span class="lencana" data-status="<?= e($p['status_pembayaran']) ?>"><?= e(label_pembayaran($p['status_pembayaran'])) ?></span>
            <span class="lencana" data-status="<?= e($p['status_pesanan']) ?>"><?= e(label_pesanan($p['status_pesanan'])) ?></span>
        </div>
        <div class="kartu-riwayat-bawah">
            <div>
                <span class="teks-kecil">Total (<?= e(label_metode($p['metode_pembayaran'])) ?>)</span><br>
                <span class="harga"><?= rupiah($p['total_harga']) ?></span>
            </div>
            <a class="tombol tombol-sekunder" href="detail_pesanan.php?id=<?= (int) $p['id'] ?>"><i class="fa-solid fa-receipt"></i> Lihat Detail</a>
        </div>
    </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php include 'us_footer.php'; ?>
