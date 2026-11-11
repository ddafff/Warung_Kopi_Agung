<?php
// admin/konfirmasi_tunai.php (POST) — kasir mengonfirmasi pembayaran tunai.
// Hanya pesanan "Menunggu bayar tunai" yang berubah menjadi "Sudah dibayar" + "Diproses dapur".
session_start();
require_once '../koneksi.php';
include 'cek_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../404.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    header('Location: ../404.php');
    exit;
}

// Syarat di WHERE: bila tombol ditekan dua kali (atau dua kasir), pesanan tidak berubah dua kali
$sekarang = date('Y-m-d H:i:s');
$stmt = $pdo->prepare("UPDATE orders SET status_pembayaran = 'paid', status_pesanan = 'dapur', dibayar_pada = ?, dikonfirmasi_oleh = ?, updated_at = ?
                       WHERE id = ? AND status_pembayaran = 'pending_cash' AND status_pesanan = 'pending'");
$stmt->execute([$sekarang, $_SESSION['user_id'], $sekarang, $id]);

if ($stmt->rowCount() == 1) {
    $_SESSION['pesan'] = 'Pesanan No. ' . $id . ' sudah dikonfirmasi dan masuk antrean dapur.';
    $_SESSION['jenis'] = 'sukses';
} else {
    $_SESSION['pesan'] = 'Pesanan ini sudah dikonfirmasi';
    $_SESSION['jenis'] = 'info';
}
header('Location: pesanan_masuk.php');
exit;
?>
