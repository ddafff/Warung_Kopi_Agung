<?php
// user/detail_pesanan.php?id= — rincian satu pesanan. Hanya baca.
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';
include 'ambil_pesanan.php';      // hasilnya: $pesanan (sudah diperiksa pemiliknya)

$stmt = $pdo->prepare("SELECT i.*, m.gambar FROM order_items i LEFT JOIN menu m ON m.id = i.menu_id WHERE i.order_id = ? ORDER BY i.id");
$stmt->execute([$pesanan['id']]);
$item = $stmt->fetchAll();

$judul = 'Detail Pesanan';
$kelas_isi = 'kontainer-sempit';
include 'us_header.php';
?>
<a class="tombol tombol-sekunder tombol-kecil" href="riwayat_pesanan.php"><i class="fa-solid fa-arrow-left"></i> Riwayat</a>
<h1 class="judul-halaman" style="margin-top:12px">Detail Pesanan</h1>

<section class="kartu">
    <div class="baris-rincian"><span>No. Pesanan</span><span class="nilai">No. <?= $pesanan['id'] ?></span></div>
    <div class="baris-rincian"><span>Waktu</span><span class="nilai"><?= tanggal_indo($pesanan['created_at']) ?></span></div>
    <div class="baris-rincian"><span>Meja</span><span class="nilai"><?= str_pad($pesanan['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></span></div>
    <div class="baris-rincian"><span>Metode</span><span class="nilai"><?= label_metode($pesanan['metode_pembayaran']) ?></span></div>
    <hr class="garis-pemisah">
    <div class="kelompok-lencana">
        <span class="lencana" data-status="<?= $pesanan['status_pembayaran'] ?>"><i class="fa-solid fa-wallet"></i> <?= label_pembayaran($pesanan['status_pembayaran']) ?></span>
        <span class="lencana" data-status="<?= $pesanan['status_pesanan'] ?>"><i class="fa-solid fa-utensils"></i> <?= label_pesanan($pesanan['status_pesanan']) ?></span>
    </div>
</section>

<section class="kartu">
    <h2 class="kartu-judul">Daftar Menu</h2>
    <?php foreach ($item as $i): ?>
        <div class="baris-item">
            <?php if ($i['gambar'] && file_exists('../gambar/' . $i['gambar'])): ?>
                <img class="foto-menu" src="../gambar/<?= htmlspecialchars($i['gambar']) ?>" alt="">
            <?php else: ?>
                <span class="foto-menu"><i class="fa-solid fa-mug-saucer"></i></span>
            <?php endif; ?>
            <div class="info-menu">
                <span class="nama-menu"><?= htmlspecialchars($i['nama_menu']) ?></span>
                <span class="teks-kecil"><?= $i['jumlah'] ?> x <?= rupiah($i['harga_satuan']) ?></span>
            </div>
            <strong><?= rupiah($i['subtotal']) ?></strong>
        </div>
    <?php endforeach; ?>
    <?php if ($pesanan['catatan']): ?>
        <hr class="garis-pemisah">
        <div class="kotak-catatan"><small class="teks-kecil">Catatan</small><br><?= htmlspecialchars($pesanan['catatan']) ?></div>
    <?php endif; ?>
    <hr class="garis-pemisah">
    <div class="baris-rincian"><strong>Total</strong><span class="angka-total"><?= rupiah($pesanan['total_harga']) ?></span></div>
</section>

<?php if ($pesanan['status_pesanan'] == 'pending' || $pesanan['status_pesanan'] == 'dapur'): ?>
    <a class="tombol tombol-utama tombol-penuh" href="status_pesanan.php?id=<?= $pesanan['id'] ?>"><i class="fa-solid fa-rotate"></i> Pantau Status Pesanan</a>
<?php endif; ?>
<?php include 'us_footer.php'; ?>
