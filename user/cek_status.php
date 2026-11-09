<?php
// user/cek_status.php?id= (JSON) — dipanggil JS tiap 5 detik untuk memantau status pesanan
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';
require_once '../midtrans.php';
header('Content-Type: application/json');

// Hanya pelanggan yang sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelanggan') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'pesan' => 'Akses ditolak']);
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'pesan' => 'Data tidak ditemukan']);
    exit;
}
if ($pesanan['user_id'] != $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'pesan' => 'Akses ditolak']);
    exit;
}

// QRIS yang masih menunggu: tanyakan dulu ke Midtrans, lalu baca data terbaru
if ($pesanan['metode_pembayaran'] == 'qris' && $pesanan['status_pembayaran'] == 'unpaid' && $pesanan['status_pesanan'] == 'pending') {
    periksa_qris($pdo, $id);
    $stmt->execute([$id]);
    $pesanan = $stmt->fetch();
}

echo json_encode([
    'ok'                => true,
    'status_pembayaran' => $pesanan['status_pembayaran'],
    'label_pembayaran'  => label_pembayaran($pesanan['status_pembayaran']),
    'status_pesanan'    => $pesanan['status_pesanan'],
    'label_pesanan'     => label_pesanan($pesanan['status_pesanan']),
    'selesai'           => ($pesanan['status_pesanan'] == 'selesai' || $pesanan['status_pesanan'] == 'dibatalkan'),
]);
?>
