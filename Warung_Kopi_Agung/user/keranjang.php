<?php
// keranjang.php (POST) — menambah atau mengubah jumlah item di keranjang.
// Keranjang disimpan di $_SESSION['keranjang'] = [menu_id => jumlah]. Jumlah 1 sampai 99, 0 = hapus.
require_once '../koneksi.php';
require_once '../fungsi.php';

wajib_post();
wajib_peran(['pelanggan']);   // belum masuk -> ke login; Admin/Superadmin -> 403
wajib_csrf();

$kembali = url_kembali_aman($_POST['kembali'] ?? null) ?? (BASE_URL . '/user/us_index.php');

// Halaman checkout ikut mengirim catatan dan metode agar tidak hilang saat tekan + atau -
if (isset($_POST['catatan'])) {
    $_SESSION['catatan_checkout'] = mb_substr(trim((string) $_POST['catatan']), 0, 200, 'UTF-8');
}
if (isset($_POST['metode']) && in_array($_POST['metode'], ['qris', 'cash'], true)) {
    $_SESSION['metode_checkout'] = $_POST['metode'];
}

if (empty($_SESSION['nomor_meja'])) {
    set_pesan('galat', 'Pindai QR code di meja untuk memesan');
    alihkan(BASE_URL . '/user/us_index.php');
}

// Aksi bisa datang dari tombol "ubah" (format "id:arah") atau dari kolom aksi + menu_id
$aksi = (string) ($_POST['aksi'] ?? '');
$menu_id = $_POST['menu_id'] ?? null;
$arah = null;
if (isset($_POST['ubah']) && is_string($_POST['ubah']) && preg_match('/^(\d+):(tambah|kurang)$/', $_POST['ubah'], $m)) {
    $aksi = 'ubah';
    $menu_id = $m[1];
    $arah = $m[2];
} elseif ($aksi === 'ubah') {
    $arah = (string) ($_POST['arah'] ?? '');
}

if (!in_array($aksi, ['tambah', 'ubah'], true) || !is_string($menu_id) || !ctype_digit($menu_id)) {
    set_pesan('galat', 'Permintaan tidak dikenali.');
    alihkan($kembali);
}
$menu_id = (int) $menu_id;

$stmt = $pdo->prepare('SELECT id, nama, tersedia FROM menu WHERE id = ?');
$stmt->execute([$menu_id]);
$menu = $stmt->fetch();
if (!$menu) {
    unset($_SESSION['keranjang'][$menu_id]);
    set_pesan('galat', 'Menu tidak ditemukan.');
    alihkan($kembali);
}

$sekarang = (int) ($_SESSION['keranjang'][$menu_id] ?? 0);

if ($aksi === 'tambah' || $arah === 'tambah') {
    if ((int) $menu['tersedia'] !== 1) {
        set_pesan('galat', $menu['nama'] . ' sedang tidak tersedia.');
        alihkan($kembali);
    }
    $baru = validasi_jumlah($sekarang + 1);
    if ($baru === null) {
        set_pesan('peringatan', 'Jumlah maksimal per menu adalah 99.');
        alihkan($kembali);
    }
} elseif ($arah === 'kurang') {
    $baru = $sekarang - 1;
} else {
    set_pesan('galat', 'Permintaan tidak dikenali.');
    alihkan($kembali);
}

if ($baru <= 0) {
    unset($_SESSION['keranjang'][$menu_id]);
} else {
    $_SESSION['keranjang'][$menu_id] = $baru;
}
alihkan($kembali);
