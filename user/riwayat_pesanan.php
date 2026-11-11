<?php
// user/riwayat_pesanan.php — semua pesanan milik pelanggan (termasuk yang Dibatalkan). Hanya baca.
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';
include 'cek_login.php';

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC");
$stmt->execute([$_SESSION['user_id']]);
$daftar = $stmt->fetchAll();

$judul = 'Riwayat Pesanan';
$menu_aktif = 'riwayat';
include 'us_header.php';
?>
<h1 class="judul-halaman">Riwayat Pesanan</h1>
<p class="teks-pendukung" style="margin-bottom:14px"><?= count($daftar) ?> pesanan</p>

<?php if (!$daftar): ?>
    <section class="kartu keadaan-kosong">
        <div class="ikon-hero"><i class="fa-solid fa-mug-hot"></i></div>
        <h2 class="judul-bagian">Belum ada riwayat pesanan</h2>
        <p class="teks-pendukung" style="margin-bottom:16px">Pesanan yang sudah Anda buat akan muncul di sini.</p>
        <a class="tombol tombol-utama" href="us_index.php"><i class="fa-solid fa-store"></i> Mulai Pesan Kopi &amp; Makanan</a>
    </section>
<?php endif; ?>

<div class="daftar-riwayat">
    <?php foreach ($daftar as $p): ?>
        <?php
        // Ringkasan menu: "Kopi Susu x1, Mie Nyemek x2"
        $stmt = $pdo->prepare("SELECT nama_menu, jumlah FROM order_items WHERE order_id = ? ORDER BY id");
        $stmt->execute([$p['id']]);
        $ringkasan = [];
        foreach ($stmt->fetchAll() as $i) {
            $ringkasan[] = $i['nama_menu'] . ' x' . $i['jumlah'];
        }
        ?>
        <article class="kartu kartu-riwayat">
            <div>
                <div class="waktu-riwayat"><i class="fa-regular fa-clock"></i> <?= tanggal_indo($p['created_at']) ?></div>
                <span class="teks-kecil">No. <?= $p['id'] ?> &bull; Meja <?= str_pad($p['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="kotak-menu-ringkas"><small>Menu &amp; Jumlah</small><?= htmlspecialchars(implode(', ', $ringkasan)) ?></div>
            <div class="kelompok-lencana">
                <span class="lencana" data-status="<?= $p['status_pembayaran'] ?>"><?= label_pembayaran($p['status_pembayaran']) ?></span>
                <span class="lencana" data-status="<?= $p['status_pesanan'] ?>"><?= label_pesanan($p['status_pesanan']) ?></span>
            </div>
            <div class="kartu-riwayat-bawah">
                <div><span class="teks-kecil">Total (<?= label_metode($p['metode_pembayaran']) ?>)</span><br><span class="harga"><?= rupiah($p['total_harga']) ?></span></div>
                <a class="tombol tombol-sekunder" href="detail_pesanan.php?id=<?= $p['id'] ?>"><i class="fa-solid fa-receipt"></i> Lihat Detail</a>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php include 'us_footer.php'; ?>
