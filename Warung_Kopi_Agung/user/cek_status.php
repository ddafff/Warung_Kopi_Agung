<?php
// cek_status.php?id= (JSON, GET) — dipanggil JS tiap 5 detik untuk memantau status pesanan.
// Semua balasan berupa JSON dengan status HTTP asli (bukan halaman HTML).
require_once '../koneksi.php';
require_once '../fungsi.php';
require_once '../midtrans.php';
mulai_sesi();

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelanggan') {
    kirim_json(['ok' => false, 'pesan' => 'Akses ditolak'], 403);
}

$id = $_GET['id'] ?? null;
if (!is_string($id) || !ctype_digit($id)) {
    kirim_json(['ok' => false, 'pesan' => 'Data tidak ditemukan'], 404);
}

$stmt = $pdo->prepare('SELECT id, user_id, metode_pembayaran, status_pembayaran, status_pesanan FROM orders WHERE id = ?');
$stmt->execute([(int) $id]);
$p = $stmt->fetch();
if (!$p) {
    kirim_json(['ok' => false, 'pesan' => 'Data tidak ditemukan'], 404);
}
if ((int) $p['user_id'] !== (int) $_SESSION['user_id']) {
    kirim_json(['ok' => false, 'pesan' => 'Akses ditolak'], 403);
}

// QRIS yang masih menunggu: tanyakan dulu ke Midtrans (Get Status API), lalu baca data terbaru
if ($p['metode_pembayaran'] === 'qris' && $p['status_pembayaran'] === 'unpaid' && $p['status_pesanan'] === 'pending') {
    periksa_status_qris($pdo, (int) $p['id']);
    $stmt->execute([(int) $id]);
    $p = $stmt->fetch();
}

kirim_json([
    'ok'                => true,
    'status_pembayaran' => $p['status_pembayaran'],
    'label_pembayaran'  => label_pembayaran($p['status_pembayaran']),
    'status_pesanan'    => $p['status_pesanan'],
    'label_pesanan'     => label_pesanan($p['status_pesanan']),
    'selesai'           => in_array($p['status_pesanan'], ['selesai', 'dibatalkan'], true),
]);
