<?php
// checkout.php — konfirmasi pesanan, pilih metode bayar, lalu "Bayar Sekarang".
// Tombol + dan - memakai formaction ke keranjang.php (POST), jadi catatan tidak hilang.
require_once '../koneksi.php';
require_once '../fungsi.php';
require_once '../midtrans.php';

wajib_peran(['pelanggan']);   // Admin/Superadmin membuka halaman pesan -> 403

$alamat_checkout = parse_url(BASE_URL . '/user/checkout.php', PHP_URL_PATH);

if (empty($_SESSION['nomor_meja'])) {
    set_pesan('galat', 'Pindai QR code di meja untuk memesan');
    alihkan(BASE_URL . '/user/us_index.php');
}
$nomor_meja = (int) $_SESSION['nomor_meja'];

// Menyusun isi keranjang dari data menu terbaru. Menu yang hilang atau tidak tersedia dikeluarkan.
// Hasil: [daftar baris, apakah ada yang dikeluarkan]
function susun_keranjang(PDO $pdo): array
{
    $keranjang = $_SESSION['keranjang'] ?? [];
    $menu = muat_menu_keranjang($pdo, $keranjang);
    $baris = [];
    $dikeluarkan = [];
    foreach ($keranjang as $id => $jumlah) {
        $id = (int) $id;
        if (!isset($menu[$id])) {
            unset($_SESSION['keranjang'][$id]);
            continue;
        }
        if ((int) $menu[$id]['tersedia'] !== 1) {
            $dikeluarkan[] = $menu[$id]['nama'];
            unset($_SESSION['keranjang'][$id]);
            continue;
        }
        $baris[] = $menu[$id] + ['jumlah' => (int) $jumlah];
    }
    if ($dikeluarkan) {
        set_pesan('peringatan', implode(', ', $dikeluarkan) . ' sudah tidak tersedia dan dikeluarkan dari pesanan.');
    }
    return [$baris, (bool) $dikeluarkan];
}

[$baris, $ada_dikeluarkan] = susun_keranjang($pdo);
if (!$baris) {
    alihkan(BASE_URL . '/user/us_index.php');
}

$catatan = $_SESSION['catatan_checkout'] ?? '';
$metode  = $_SESSION['metode_checkout'] ?? '';

/* ---------- Proses "Bayar Sekarang" ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_csrf();

    $metode  = (string) ($_POST['metode'] ?? '');
    $hasil_c = validasi_catatan($_POST['catatan'] ?? '');
    $catatan = $hasil_c['nilai'];
    $_SESSION['catatan_checkout'] = mb_substr($catatan, 0, 200, 'UTF-8');
    $_SESSION['metode_checkout']  = in_array($metode, ['qris', 'cash'], true) ? $metode : '';

    if ($ada_dikeluarkan) {
        // Ada menu yang dikeluarkan: tampilkan total terbaru dulu, jangan langsung diproses
        alihkan(BASE_URL . '/user/checkout.php');
    }
    if (!in_array($metode, ['qris', 'cash'], true)) {
        set_pesan('galat', 'Pilih metode pembayaran');
        alihkan(BASE_URL . '/user/checkout.php');
    }
    if (!$hasil_c['valid']) {
        set_pesan('galat', $hasil_c['pesan']);
        alihkan(BASE_URL . '/user/checkout.php');
    }

    // Total SELALU dihitung ulang di server dari harga di tabel menu
    $total = hitung_total(array_map(fn($b) => ['harga' => $b['harga'], 'jumlah' => $b['jumlah']], $baris));
    $sekarang = date('Y-m-d H:i:s');
    $user_id = (int) $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        if ($metode === 'cash') {
            // Satu pelanggan hanya boleh punya satu pesanan tunai yang masih menunggu
            $aktif = $pdo->prepare(
                "SELECT id FROM orders WHERE user_id = ? AND status_pembayaran = 'pending_cash'
                    AND status_pesanan = 'pending' LIMIT 1 FOR UPDATE"
            );
            $aktif->execute([$user_id]);
            $ada = $aktif->fetch();
            if ($ada) {
                $pdo->rollBack();
                set_pesan('galat', 'Anda masih punya pesanan tunai No. ' . (int) $ada['id']
                    . ' yang menunggu pembayaran. Selesaikan di kasir dulu.');
                alihkan(BASE_URL . '/user/menunggu_tunai.php?id=' . (int) $ada['id']);
            }
        }

        $status_bayar = ($metode === 'cash') ? 'pending_cash' : 'unpaid';
        $simpan = $pdo->prepare(
            "INSERT INTO orders (user_id, nomor_meja, total_harga, metode_pembayaran, status_pembayaran,
                                 status_pesanan, catatan, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?)"
        );
        $simpan->execute([
            $user_id, $nomor_meja, $total, $metode, $status_bayar,
            ($catatan === '' ? null : $catatan), $sekarang, $sekarang,
        ]);
        $order_id = (int) $pdo->lastInsertId();

        $item = $pdo->prepare(
            'INSERT INTO order_items (order_id, menu_id, nama_menu, harga_satuan, jumlah, subtotal)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        foreach ($baris as $b) {
            $item->execute([
                $order_id, (int) $b['id'], $b['nama'], (int) $b['harga'], (int) $b['jumlah'],
                (int) $b['harga'] * (int) $b['jumlah'],
            ]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        tulis_log('Checkout gagal: ' . $e->getMessage());
        set_pesan('galat', 'Terjadi kesalahan, silakan coba lagi');
        alihkan(BASE_URL . '/user/checkout.php');
    }

    $bersihkan = function () {
        unset($_SESSION['keranjang'], $_SESSION['catatan_checkout'], $_SESSION['metode_checkout']);
    };

    if ($metode === 'cash') {
        $bersihkan();
        alihkan(BASE_URL . '/user/menunggu_tunai.php?id=' . $order_id);
    }

    // QRIS: minta QR ke Midtrans Sandbox
    $qris = buat_qris($order_id, $total);
    if ($qris) {
        $kedaluwarsa = $qris['expiry_time'] ?: date('Y-m-d H:i:s', time() + 15 * 60);
        try {
            $up = $pdo->prepare(
                'UPDATE orders SET midtrans_order_id = ?, qris_qr_string = ?, qris_expired_at = ?, updated_at = ? WHERE id = ?'
            );
            $up->execute(['KOPI-' . $order_id, $qris['qr_string'], $kedaluwarsa, date('Y-m-d H:i:s'), $order_id]);
            $bersihkan();
            alihkan(BASE_URL . '/user/bayar_qris.php?id=' . $order_id);
        } catch (Throwable $e) {
            tulis_log('Simpan data QRIS gagal: ' . $e->getMessage());
        }
    }

    // Gagal membuat QRIS: pesanan dibatalkan, keranjang tetap utuh
    $pdo->prepare("UPDATE orders SET status_pesanan = 'dibatalkan', updated_at = ? WHERE id = ?")
        ->execute([date('Y-m-d H:i:s'), $order_id]);
    set_pesan('galat', 'QRIS belum bisa dibuat, coba lagi atau pilih tunai');
    alihkan(BASE_URL . '/user/checkout.php');
}

/* ---------- Tampilan ---------- */
$jumlah_menu = count($baris);
$total = hitung_total(array_map(fn($b) => ['harga' => $b['harga'], 'jumlah' => $b['jumlah']], $baris));

$judul_halaman   = 'Konfirmasi Pesanan';
$tipe_halaman    = 'tumpuk';
$judul_kepala    = 'Keranjang & Pembayaran';
$url_kembali     = BASE_URL . '/user/us_index.php';
$ada_bilah_bawah = true;
include 'us_header.php';
?>
<h1 class="judul-halaman">Konfirmasi Pesanan</h1>

<form id="form-checkout" method="post" action="checkout.php">
    <?= csrf_input() ?>
    <input type="hidden" name="kembali" value="<?= e($alamat_checkout) ?>">
    <!-- Tombol bawaan (Enter di formulir = Bayar Sekarang) -->
    <button class="sembunyi-visual" type="submit" tabindex="-1" aria-hidden="true">Bayar Sekarang</button>

    <!-- Meja -->
    <section class="kartu kartu-meja">
        <div>
            <span class="teks-kecil">Posisi Pemesanan</span><br>
            <strong>Meja Anda</strong>
        </div>
        <span class="nomor-meja-besar"><?= str_pad((string) $nomor_meja, 2, '0', STR_PAD_LEFT) ?></span>
    </section>

    <!-- Daftar pesanan -->
    <div class="baris-judul">
        <h2 class="judul-bagian">Daftar Pesanan</h2>
        <span class="teks-kecil"><?= $jumlah_menu ?> Menu</span>
    </div>
    <div class="daftar-pesanan">
        <?php foreach ($baris as $b): $foto = url_foto_menu($b['gambar']); ?>
        <div class="baris-pesanan">
            <?php if ($foto): ?>
                <img class="foto-menu" src="<?= e($foto) ?>" alt="<?= e($b['nama']) ?>" loading="lazy">
            <?php else: ?>
                <span class="foto-menu" aria-hidden="true"><i class="fa-solid fa-mug-saucer"></i></span>
            <?php endif; ?>
            <div class="info-menu">
                <span class="nama-menu"><?= e($b['nama']) ?></span>
                <span class="harga"><?= rupiah($b['harga']) ?></span>
            </div>
            <div class="pengatur-jumlah">
                <button class="tombol-jumlah" type="submit" name="ubah" value="<?= (int) $b['id'] ?>:kurang"
                        formaction="keranjang.php" formnovalidate aria-label="Kurangi <?= e($b['nama']) ?>">
                    <i class="fa-solid fa-minus"></i>
                </button>
                <span class="angka-jumlah"><?= (int) $b['jumlah'] ?></span>
                <button class="tombol-jumlah tambah" type="submit" name="ubah" value="<?= (int) $b['id'] ?>:tambah"
                        formaction="keranjang.php" formnovalidate aria-label="Tambah <?= e($b['nama']) ?>"
                        <?= ((int) $b['jumlah'] >= 99) ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Catatan -->
    <section class="kartu" style="margin-top:12px">
        <div class="kolom-isian">
            <label class="label-isian" for="catatan"><i class="fa-solid fa-pen-to-square"></i> Catatan (opsional)</label>
            <textarea class="isian-teks" id="catatan" name="catatan" rows="2" maxlength="200"
                      placeholder="Contoh: Es dipisah, pedas sedang..."><?= e($catatan) ?></textarea>
            <span class="hitungan-catatan"><span id="hitung-catatan"><?= mb_strlen($catatan, 'UTF-8') ?></span>/200</span>
        </div>
    </section>

    <!-- Total -->
    <section class="kartu rincian-biaya">
        <div class="baris-total">
            <span>Total</span>
            <span class="harga-total"><?= rupiah($total) ?></span>
        </div>
    </section>

    <!-- Metode pembayaran -->
    <div class="baris-judul"><h2 class="judul-bagian">Pilih Metode Pembayaran</h2></div>
    <div class="pilihan-metode">
        <label class="kartu-metode">
            <input type="radio" name="metode" value="qris" <?= $metode === 'qris' ? 'checked' : '' ?>>
            <span class="kartu-metode-isi">
                <span class="ikon-metode"><i class="fa-solid fa-qrcode"></i></span>
                <span class="kartu-metode-teks"><strong>QRIS (E-Wallet)</strong><span class="teks-kecil">Bayar lewat aplikasi e-wallet atau mobile banking</span></span>
                <span class="tanda-pilih"><i class="fa-solid fa-check"></i></span>
            </span>
        </label>
        <label class="kartu-metode">
            <input type="radio" name="metode" value="cash" <?= $metode === 'cash' ? 'checked' : '' ?>>
            <span class="kartu-metode-isi">
                <span class="ikon-metode"><i class="fa-solid fa-money-bill-wave"></i></span>
                <span class="kartu-metode-teks"><strong>Cash (Tunai)</strong><span class="teks-kecil">Bayar di kasir</span></span>
                <span class="tanda-pilih"><i class="fa-solid fa-check"></i></span>
            </span>
        </label>
    </div>

    <!-- Bilah bayar -->
    <div class="bilah-bayar">
        <div class="kontainer-sempit bilah-bayar-isi">
            <div class="ringkasan">
                <span class="teks-kecil">Total pembayaran</span>
                <strong><?= rupiah($total) ?></strong>
            </div>
            <button class="tombol tombol-utama" type="submit">Bayar Sekarang <i class="fa-solid fa-arrow-right"></i></button>
        </div>
    </div>
</form>
<?php include 'us_footer.php'; ?>
