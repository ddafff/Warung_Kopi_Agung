<?php
// admin/cek_admin_json.php — pemeriksaan akses untuk halaman JSON (data_pesanan.php, data_antrean.php).
// Gagal -> balasan JSON dengan status 403 (bukan halaman HTML).
header('Content-Type: application/json');

$boleh = isset($_SESSION['user_id'])
      && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin')
      && time() - ($_SESSION['aktivitas_terakhir'] ?? 0) <= 1800;

if (!$boleh) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'pesan' => 'Akses ditolak']);
    exit;
}
?>
