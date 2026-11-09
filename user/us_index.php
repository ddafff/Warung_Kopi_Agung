<?php
// user/us_index.php — Halaman Menu. Alamat QR meja: us_index.php?meja=2
// Bagian atas berkas ini mengambil data, lalu header + isi (us_beranda.php) + footer ditampilkan.
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';

$role = $_SESSION['role'] ?? '';

// 1) Nomor meja dari QR code. Harus ada di tabel meja, kalau tidak -> 404
if (isset($_GET['meja'])) {
    $stmt = $pdo->prepare("SELECT nomor_meja FROM meja WHERE nomor_meja = ?");
    $stmt->execute([(int)$_GET['meja']]);
    if (!$stmt->fetch()) {
        header('Location: ../404.php?pesan=meja');
        exit;
    }
    $_SESSION['nomor_meja'] = (int)$_GET['meja'];
}
$nomor_meja = $_SESSION['nomor_meja'] ?? 0;

// 2) Kategori yang dipilih (?kategori=Kopi)
$daftar_kategori = [
    'Semua'    => 'fa-border-all',
    'Kopi'     => 'fa-mug-hot',
    'Non Kopi' => 'fa-glass-water',
    'Makanan'  => 'fa-utensils',
    'Snack'    => 'fa-cookie-bite',
];
$kategori = $_GET['kategori'] ?? 'Semua';
if (!isset($daftar_kategori[$kategori])) {
    $kategori = 'Semua';
}

// 3) Daftar menu
if ($kategori == 'Semua') {
    $stmt = $pdo->query("SELECT * FROM menu ORDER BY tersedia DESC, nama");
} else {
    $stmt = $pdo->prepare("SELECT * FROM menu WHERE kategori = ? ORDER BY tersedia DESC, nama");
    $stmt->execute([$kategori]);
}
$daftar_menu = $stmt->fetchAll();

// 4) Isi keranjang (jumlah item dan total harga)
$jumlah_item = 0;
$total_keranjang = 0;
if ($role == 'pelanggan' && isset($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $id_menu => $jumlah) {
        $stmt = $pdo->prepare("SELECT harga FROM menu WHERE id = ?");
        $stmt->execute([$id_menu]);
        $m = $stmt->fetch();
        if ($m) {
            $jumlah_item += $jumlah;
            $total_keranjang += $m['harga'] * $jumlah;
        }
    }
}

// Alamat halaman ini (dipakai untuk kembali setelah login / tambah menu)
$kembali = 'us_index.php';
if ($kategori != 'Semua') {
    $kembali .= '?kategori=' . urlencode($kategori);
}

$judul = 'Daftar Menu';
$menu_aktif = 'menu';
include 'us_header.php';
include 'us_beranda.php';
include 'us_footer.php';
?>
