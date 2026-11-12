<?php
// us_index.php — Halaman Menu. Alamat QR meja: user/us_index.php?meja=N
require_once '../koneksi.php';
require_once '../fungsi.php';
mulai_sesi();

// 1) Nomor meja dari QR: harus terdaftar di tabel meja, kalau tidak -> 404
if (isset($_GET['meja'])) {
    $meja_get = $_GET['meja'];
    if (!is_string($meja_get) || !ctype_digit($meja_get) || (int) $meja_get < 1 || (int) $meja_get > 999) {
        tampilkan_error(404, 'Meja tidak ditemukan');
    }
    $cek = $pdo->prepare('SELECT nomor_meja FROM meja WHERE nomor_meja = ?');
    $cek->execute([(int) $meja_get]);
    if (!$cek->fetch()) {
        tampilkan_error(404, 'Meja tidak ditemukan');
    }
    $_SESSION['nomor_meja'] = (int) $meja_get;
}

$nomor_meja  = $_SESSION['nomor_meja'] ?? null;
$peran       = $_SESSION['role'] ?? null;
$sudah_masuk = !empty($_SESSION['user_id']);
$adalah_pelanggan = ($peran === 'pelanggan');

// 2) Filter kategori
$kategori_ada = [
    'Semua'    => 'fa-border-all',
    'Kopi'     => 'fa-mug-hot',
    'Non Kopi' => 'fa-glass-water',
    'Makanan'  => 'fa-utensils',
    'Snack'    => 'fa-cookie-bite',
];
$kategori = $_GET['kategori'] ?? 'Semua';
if (!is_string($kategori) || !isset($kategori_ada[$kategori])) {
    $kategori = 'Semua';
}

if ($kategori === 'Semua') {
    $stmt = $pdo->query('SELECT id, nama, harga, kategori, gambar, tersedia FROM menu ORDER BY tersedia DESC, kategori, nama');
} else {
    $stmt = $pdo->prepare('SELECT id, nama, harga, kategori, gambar, tersedia FROM menu WHERE kategori = ? ORDER BY tersedia DESC, nama');
    $stmt->execute([$kategori]);
}
$daftar_menu = $stmt->fetchAll();

// 3) Isi keranjang (hanya pelanggan)
$jumlah_item = 0;
$total_keranjang = 0;
if ($adalah_pelanggan && !empty($_SESSION['keranjang'])) {
    $menu_keranjang = muat_menu_keranjang($pdo, $_SESSION['keranjang']);
    foreach ($_SESSION['keranjang'] as $id => $jumlah) {
        if (isset($menu_keranjang[(int) $id])) {
            $jumlah_item     += (int) $jumlah;
            $total_keranjang += (int) $menu_keranjang[(int) $id]['harga'] * (int) $jumlah;
        }
    }
}

// Alamat halaman ini (dipakai untuk "kembali" setelah login atau menambah menu)
$alamat_ini = BASE_URL . '/user/us_index.php' . ($kategori !== 'Semua' ? '?kategori=' . urlencode($kategori) : '');
$kembali_ini = parse_url($alamat_ini, PHP_URL_PATH) . ($kategori !== 'Semua' ? '?kategori=' . urlencode($kategori) : '');

$judul_halaman   = 'Daftar Menu';
$tipe_halaman    = 'utama';
$menu_aktif      = 'menu';
$ada_bilah_bawah = ($adalah_pelanggan && $nomor_meja && $jumlah_item > 0);
include 'us_header.php';
?>

<!-- Spanduk meja -->
<section class="spanduk-meja">
    <?php if ($nomor_meja): ?>
        <span class="spanduk-meja-label">Meja Anda</span>
        <span class="spanduk-meja-angka"><?= str_pad((string) $nomor_meja, 2, '0', STR_PAD_LEFT) ?></span>
        <p class="spanduk-meja-teks">Selamat menikmati kopi terbaik dan suasana terbaik.</p>
    <?php else: ?>
        <span class="spanduk-meja-label">Selamat datang</span>
        <span class="spanduk-meja-angka"><i class="fa-solid fa-qrcode"></i></span>
        <p class="spanduk-meja-teks">Pindai QR code di meja untuk memesan</p>
    <?php endif; ?>
</section>

<?php if (!$sudah_masuk): ?>
<!-- Ajakan masuk untuk tamu -->
<a class="kotak-ajakan" href="login.php?kembali=<?= urlencode($kembali_ini) ?>">
    <span>
        <strong>Masuk untuk mulai memesan</strong>
        <small>Tamu hanya bisa melihat menu dan harga</small>
    </span>
    <i class="fa-solid fa-chevron-right"></i>
</a>
<?php endif; ?>

<!-- Kategori -->
<div class="baris-judul"><h2 class="judul-bagian">Kategori Menu</h2></div>
<nav class="deretan-kategori" aria-label="Kategori menu">
    <?php foreach ($kategori_ada as $nama_kat => $ikon): ?>
        <a class="chip-kategori<?= $nama_kat === $kategori ? ' aktif' : '' ?>"
           href="us_index.php<?= $nama_kat === 'Semua' ? '' : '?kategori=' . urlencode($nama_kat) ?>"
           <?= $nama_kat === $kategori ? 'aria-current="page"' : '' ?>>
            <i class="fa-solid <?= $ikon ?>"></i><span><?= e($nama_kat) ?></span>
        </a>
    <?php endforeach; ?>
</nav>

<!-- Daftar menu -->
<div class="baris-judul">
    <h2 class="judul-bagian">Daftar Menu</h2>
    <span class="teks-kecil"><?= count($daftar_menu) ?> Pilihan</span>
</div>

<?php if (!$daftar_menu): ?>
    <div class="kartu keadaan-kosong">
        <p class="teks-pendukung">Belum ada menu pada kategori ini.</p>
    </div>
<?php else: ?>
<div class="daftar-menu">
    <?php foreach ($daftar_menu as $m):
        $tersedia = ((int) $m['tersedia'] === 1);
        $foto = url_foto_menu($m['gambar']);
    ?>
    <article class="kartu-menu<?= $tersedia ? '' : ' kartu-menu-nonaktif' ?>">
        <div class="kartu-menu-isi">
            <?php if ($foto): ?>
                <img class="foto-menu" src="<?= e($foto) ?>" alt="<?= e($m['nama']) ?>" loading="lazy">
            <?php else: ?>
                <span class="foto-menu" aria-hidden="true"><i class="fa-solid fa-mug-saucer"></i></span>
            <?php endif; ?>
            <div class="info-menu">
                <span class="nama-menu"><?= e($m['nama']) ?></span>
                <span class="harga"><?= rupiah($m['harga']) ?></span>
                <span class="status-ketersediaan<?= $tersedia ? '' : ' tidak-tersedia' ?>"><?= label_ketersediaan($m['tersedia']) ?></span>
            </div>
        </div>

        <?php if ($peran === 'admin' || $peran === 'superadmin'): ?>
            <?php /* Admin dan Superadmin tidak melihat tombol + */ ?>
        <?php elseif (!$tersedia): ?>
            <button class="tombol-tambah tombol-nonaktif" type="button" disabled aria-label="<?= e($m['nama']) ?> tidak tersedia">
                <i class="fa-solid fa-plus"></i>
            </button>
        <?php elseif (!$sudah_masuk): ?>
            <!-- Tamu: tombol + mengarah ke halaman masuk -->
            <a class="tombol-tambah" href="login.php?kembali=<?= urlencode($kembali_ini) ?>" aria-label="Masuk untuk memesan <?= e($m['nama']) ?>">
                <i class="fa-solid fa-plus"></i>
            </a>
        <?php elseif (!$nomor_meja): ?>
            <!-- Belum ada nomor meja: tombol nonaktif, JS menampilkan pesan -->
            <button class="tombol-tambah tombol-nonaktif" type="button" data-pesan-toast="Pindai QR code di meja untuk memesan"
                    aria-disabled="true" aria-label="Pindai QR code di meja untuk memesan">
                <i class="fa-solid fa-plus"></i>
            </button>
        <?php else: ?>
            <form class="formulir-tambah" method="post" action="keranjang.php">
                <?= csrf_input() ?>
                <input type="hidden" name="aksi" value="tambah">
                <input type="hidden" name="menu_id" value="<?= (int) $m['id'] ?>">
                <input type="hidden" name="kembali" value="<?= e($kembali_ini) ?>">
                <button class="tombol-tambah" type="submit" aria-label="Tambah <?= e($m['nama']) ?>">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </form>
        <?php endif; ?>
    </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($ada_bilah_bawah): ?>
<a class="bilah-keranjang" href="checkout.php">
    <span class="bilah-keranjang-kiri"><i class="fa-solid fa-cart-shopping"></i> Lihat Keranjang (<?= $jumlah_item ?>)</span>
    <span><?= rupiah($total_keranjang) ?> <i class="fa-solid fa-chevron-right"></i></span>
</a>
<?php endif; ?>

<?php include 'us_footer.php'; ?>
