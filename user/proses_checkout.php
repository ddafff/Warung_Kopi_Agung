<?php
// user/proses_checkout.php (POST) — memproses tombol "Bayar Sekarang".
// Urutan: periksa data -> hitung total dari database -> simpan pesanan -> tunai / QRIS
session_start();
require_once '../koneksi.php';
require_once '../midtrans.php';
include 'cek_login.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../404.php');
    exit;
}

// Jika ada yang salah: simpan pesan lalu kembali ke checkout
function kembali_ke_checkout($pesan) {
    $_SESSION['pesan'] = $pesan;
    $_SESSION['jenis'] = 'galat';
    header('Location: checkout.php');
    exit;
}

$metode  = $_POST['metode'] ?? '';
$catatan = trim($_POST['catatan'] ?? '');
$_SESSION['catatan'] = $catatan;      // supaya tidak hilang bila kembali ke checkout
$_SESSION['metode']  = $metode;

if ($metode != 'qris' && $metode != 'cash') {
    kembali_ke_checkout('Pilih metode pembayaran');
}
if (mb_strlen($catatan) > 200) {
    kembali_ke_checkout('Catatan terlalu panjang (maksimal 200 karakter).');
}
if (!isset($_SESSION['nomor_meja']) || empty($_SESSION['keranjang'])) {
    header('Location: us_index.php');
    exit;
}

// Ambil harga dari database (bukan dari formulir) dan hitung total
$daftar = [];
$total = 0;
foreach ($_SESSION['keranjang'] as $id_menu => $jumlah) {
    $stmt = $pdo->prepare("SELECT * FROM menu WHERE id = ?");
    $stmt->execute([$id_menu]);
    $m = $stmt->fetch();
    if (!$m || !$m['tersedia']) {
        kembali_ke_checkout('Ada menu yang sudah tidak tersedia. Periksa kembali pesanan Anda.');
    }
    $m['jumlah'] = $jumlah;
    $daftar[] = $m;
    $total += $m['harga'] * $jumlah;
}

// Satu pelanggan hanya boleh punya satu pesanan tunai yang masih menunggu
if ($metode == 'cash') {
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND status_pembayaran = 'pending_cash' AND status_pesanan = 'pending'");
    $stmt->execute([$_SESSION['user_id']]);
    $lama = $stmt->fetch();
    if ($lama) {
        $_SESSION['pesan'] = 'Anda masih punya pesanan tunai No. ' . $lama['id'] . ' yang menunggu pembayaran. Selesaikan di kasir dulu.';
        $_SESSION['jenis'] = 'galat';
        header('Location: menunggu_tunai.php?id=' . $lama['id']);
        exit;
    }
}

// Simpan pesanan dan itemnya
$status_bayar = ($metode == 'cash') ? 'pending_cash' : 'unpaid';
$sekarang = date('Y-m-d H:i:s');
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, nomor_meja, total_harga, metode_pembayaran, status_pembayaran, status_pesanan, catatan, created_at, updated_at)
                           VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['nomor_meja'], $total, $metode, $status_bayar,
                    ($catatan == '' ? null : $catatan), $sekarang, $sekarang]);
    $order_id = $pdo->lastInsertId();

    foreach ($daftar as $m) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_id, nama_menu, harga_satuan, jumlah, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$order_id, $m['id'], $m['nama'], $m['harga'], $m['jumlah'], $m['harga'] * $m['jumlah']]);
    }
    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    kembali_ke_checkout('Terjadi kesalahan, silakan coba lagi');
}

// TUNAI: kosongkan keranjang, tunggu kasir
if ($metode == 'cash') {
    unset($_SESSION['keranjang'], $_SESSION['catatan'], $_SESSION['metode']);
    header('Location: menunggu_tunai.php?id=' . $order_id);
    exit;
}

// QRIS: minta kode QR ke Midtrans
$pesan_error = '';
$qris = buat_qris($order_id, $total, $pesan_error);
if ($qris) {
    $kedaluwarsa = $qris['expiry_time'] ?: date('Y-m-d H:i:s', time() + 15 * 60);
    $stmt = $pdo->prepare("UPDATE orders SET midtrans_order_id = ?, qris_url = ?, qris_kedaluwarsa = ? WHERE id = ?");
    $stmt->execute(['KOPI-' . $order_id, ($qris['qr_url'] ?: $qris['qr_string']), $kedaluwarsa, $order_id]);
    unset($_SESSION['keranjang'], $_SESSION['catatan'], $_SESSION['metode']);
    header('Location: bayar_qris.php?id=' . $order_id);
    exit;
}

// Gagal membuat QRIS: pesanan dibatalkan, keranjang tetap utuh
$stmt = $pdo->prepare("UPDATE orders SET status_pesanan = 'dibatalkan' WHERE id = ?");
$stmt->execute([$order_id]);
kembali_ke_checkout($pesan_error ?: 'QRIS belum bisa dibuat, coba lagi atau pilih tunai');
?>
