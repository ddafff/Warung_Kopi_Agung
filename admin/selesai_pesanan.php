<?php
// admin/selesai_pesanan.php (POST) — dapur menandai pesanan selesai: "Diproses dapur" -> "Selesai"
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

$stmt = $pdo->prepare("UPDATE orders SET status_pesanan = 'selesai', updated_at = ?
                       WHERE id = ? AND status_pembayaran = 'paid' AND status_pesanan = 'dapur'");
$stmt->execute([date('Y-m-d H:i:s'), $id]);

if ($stmt->rowCount() == 1) {
    $_SESSION['pesan'] = 'Pesanan No. ' . $id . ' ditandai selesai.';
    $_SESSION['jenis'] = 'sukses';
} else {
    $_SESSION['pesan'] = 'Pesanan ini sudah selesai atau tidak ada di antrean.';
    $_SESSION['jenis'] = 'info';
}
header('Location: antrean_dapur.php');
exit;
?>
