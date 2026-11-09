<?php
// user/checkout.php — konfirmasi pesanan, pilih metode bayar. Tombol "Bayar Sekarang" ke proses_checkout.php
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';
include 'cek_login.php';

if (!isset($_SESSION['nomor_meja'])) {
    $_SESSION['pesan'] = 'Pindai QR code di meja untuk memesan';
    $_SESSION['jenis'] = 'galat';
    header('Location: us_index.php');
    exit;
}

// Ambil data menu untuk isi keranjang. Menu yang tidak tersedia lagi dikeluarkan.
$daftar = [];
$dibuang = [];
$total = 0;
$keranjang = $_SESSION['keranjang'] ?? [];
foreach ($keranjang as $id_menu => $jumlah) {
    $stmt = $pdo->prepare("SELECT * FROM menu WHERE id = ?");
    $stmt->execute([$id_menu]);
    $m = $stmt->fetch();

    if (!$m || !$m['tersedia']) {
        if ($m) $dibuang[] = $m['nama'];
        unset($_SESSION['keranjang'][$id_menu]);
        continue;
    }
    $m['jumlah'] = $jumlah;
    $daftar[] = $m;
    $total += $m['harga'] * $jumlah;
}
if ($dibuang) {
    $_SESSION['pesan'] = implode(', ', $dibuang) . ' sudah tidak tersedia dan dikeluarkan dari pesanan.';
    $_SESSION['jenis'] = 'peringatan';
}

// Keranjang kosong -> kembali ke menu
if (!$daftar) {
    header('Location: us_index.php');
    exit;
}

$catatan = $_SESSION['catatan'] ?? '';
$metode  = $_SESSION['metode'] ?? '';

$judul = 'Konfirmasi Pesanan';
$kelas_isi = 'kontainer-sempit';
include 'us_header.php';
?>
<a class="tombol tombol-sekunder tombol-kecil" href="us_index.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke menu</a>
<h1 class="judul-halaman" style="margin-top:12px">Konfirmasi Pesanan</h1>

<!-- Satu formulir: tombol + / - mengirim ke keranjang.php, tombol Bayar mengirim ke proses_checkout.php -->
<form method="POST" action="proses_checkout.php">
    <input type="hidden" name="kembali" value="checkout.php">
    <button class="sembunyi-visual" type="submit" tabindex="-1">Bayar Sekarang</button>

    <section class="kartu kartu-meja">
        <div><span class="teks-kecil">Posisi Pemesanan</span><br><strong>Meja Anda</strong></div>
        <span class="nomor-meja-besar"><?= str_pad($_SESSION['nomor_meja'], 2, '0', STR_PAD_LEFT) ?></span>
    </section>

    <div class="baris-judul">
        <h2 class="judul-bagian">Daftar Pesanan</h2>
        <span class="teks-kecil"><?= count($daftar) ?> Menu</span>
    </div>
    <div class="daftar-pesanan">
        <?php foreach ($daftar as $m): ?>
            <div class="baris-pesanan">
                <?php if ($m['gambar'] && file_exists('../gambar/' . $m['gambar'])): ?>
                    <img class="foto-menu" src="../gambar/<?= htmlspecialchars($m['gambar']) ?>" alt="">
                <?php else: ?>
                    <span class="foto-menu"><i class="fa-solid fa-mug-saucer"></i></span>
                <?php endif; ?>
                <div class="info-menu">
                    <span class="nama-menu"><?= htmlspecialchars($m['nama']) ?></span>
                    <span class="harga"><?= rupiah($m['harga']) ?></span>
                </div>
                <div class="pengatur-jumlah">
                    <button class="tombol-jumlah" type="submit" name="ubah" value="<?= $m['id'] ?>:kurang" formaction="keranjang.php"><i class="fa-solid fa-minus"></i></button>
                    <span class="angka-jumlah"><?= $m['jumlah'] ?></span>
                    <button class="tombol-jumlah tambah" type="submit" name="ubah" value="<?= $m['id'] ?>:tambah" formaction="keranjang.php"><i class="fa-solid fa-plus"></i></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <section class="kartu" style="margin-top:12px">
        <div class="kolom-isian">
            <label class="label-isian" for="catatan"><i class="fa-solid fa-pen-to-square"></i> Catatan (opsional)</label>
            <textarea class="isian-teks" id="catatan" name="catatan" rows="2" maxlength="200" oninput="hitungCatatan()"
                      placeholder="Contoh: Es dipisah, pedas sedang..."><?= htmlspecialchars($catatan) ?></textarea>
            <span class="hitungan-catatan"><span id="hitung-catatan"><?= mb_strlen($catatan) ?></span>/200</span>
        </div>
    </section>

    <section class="kartu rincian-biaya">
        <div class="baris-total"><span>Total</span><span class="harga-total"><?= rupiah($total) ?></span></div>
    </section>

    <div class="baris-judul"><h2 class="judul-bagian">Pilih Metode Pembayaran</h2></div>
    <div class="pilihan-metode">
        <label class="kartu-metode">
            <input type="radio" name="metode" value="qris" <?= $metode == 'qris' ? 'checked' : '' ?>>
            <span class="kartu-metode-isi">
                <span class="ikon-metode"><i class="fa-solid fa-qrcode"></i></span>
                <span class="kartu-metode-teks"><strong>QRIS (E-Wallet)</strong><span class="teks-kecil">Bayar lewat aplikasi e-wallet atau mobile banking</span></span>
                <span class="tanda-pilih"><i class="fa-solid fa-check"></i></span>
            </span>
        </label>
        <label class="kartu-metode">
            <input type="radio" name="metode" value="cash" <?= $metode == 'cash' ? 'checked' : '' ?>>
            <span class="kartu-metode-isi">
                <span class="ikon-metode"><i class="fa-solid fa-money-bill-wave"></i></span>
                <span class="kartu-metode-teks"><strong>Cash (Tunai)</strong><span class="teks-kecil">Bayar di kasir</span></span>
                <span class="tanda-pilih"><i class="fa-solid fa-check"></i></span>
            </span>
        </label>
    </div>

    <div style="height:90px"></div>
    <div class="bilah-bayar">
        <div class="kontainer-sempit bilah-bayar-isi">
            <div class="ringkasan"><span class="teks-kecil">Total pembayaran</span><strong><?= rupiah($total) ?></strong></div>
            <button class="tombol tombol-utama" type="submit">Bayar Sekarang <i class="fa-solid fa-arrow-right"></i></button>
        </div>
    </div>
</form>
<?php include 'us_footer.php'; ?>
