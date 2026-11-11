<?php
// user/keranjang.php (POST) — menambah atau mengubah jumlah menu di keranjang.
// Keranjang disimpan di $_SESSION['keranjang'] = [id_menu => jumlah]. Jumlah 1 sampai 99, 0 = hapus.
session_start();
require_once '../koneksi.php';
include 'cek_login.php';

// Berkas ini hanya untuk kiriman formulir (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../404.php');
    exit;
}

// Halaman tujuan setelah selesai (nama berkas biasa)
$kembali = $_POST['kembali'] ?? '';
if (!preg_match('/^[a-z_]+\.php(\?[a-zA-Z0-9_=%+&]+)?$/', $kembali)) {
    $kembali = 'us_index.php';
}

// Dari halaman checkout, catatan dan metode ikut disimpan supaya tidak hilang
if (isset($_POST['catatan'])) {
    $_SESSION['catatan'] = mb_substr(trim($_POST['catatan']), 0, 200);
}
if (isset($_POST['metode'])) {
    $_SESSION['metode'] = $_POST['metode'];
}

if (!isset($_SESSION['nomor_meja'])) {
    $_SESSION['pesan'] = 'Pindai QR code di meja untuk memesan';
    $_SESSION['jenis'] = 'galat';
    header('Location: us_index.php');
    exit;
}

// Tombol di checkout bernilai "idmenu:aksi" (contoh 12:tambah). Tombol + di menu hanya mengirim menu_id.
if (isset($_POST['ubah'])) {
    list($menu_id, $aksi) = explode(':', $_POST['ubah']);
} else {
    $menu_id = $_POST['menu_id'] ?? 0;
    $aksi = 'tambah';
}
$menu_id = (int)$menu_id;

$stmt = $pdo->prepare("SELECT * FROM menu WHERE id = ?");
$stmt->execute([$menu_id]);
$menu = $stmt->fetch();
if (!$menu) {
    header('Location: ' . $kembali);
    exit;
}

$jumlah = $_SESSION['keranjang'][$menu_id] ?? 0;

if ($aksi == 'tambah') {
    if (!$menu['tersedia']) {
        $_SESSION['pesan'] = $menu['nama'] . ' sedang tidak tersedia.';
        $_SESSION['jenis'] = 'galat';
    } elseif ($jumlah >= 99) {
        $_SESSION['pesan'] = 'Jumlah maksimal per menu adalah 99.';
        $_SESSION['jenis'] = 'peringatan';
    } else {
        $jumlah++;
    }
} elseif ($aksi == 'kurang') {
    $jumlah--;
}

if ($jumlah <= 0) {
    unset($_SESSION['keranjang'][$menu_id]);
} else {
    $_SESSION['keranjang'][$menu_id] = $jumlah;
}

header('Location: ' . $kembali);
exit;
?>
