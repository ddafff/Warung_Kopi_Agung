<?php
// admin/data_antrean.php (JSON) — dipanggil JS tiap 5 detik oleh antrean_dapur.php
session_start();
require_once '../koneksi.php';
include 'cek_admin_json.php';

// Pesanan yang sudah dibayar dan sedang diproses dapur, yang terlama di atas
$stmt = $pdo->query("SELECT id, nomor_meja, catatan, COALESCE(dibayar_pada, created_at) AS waktu_masuk
                     FROM orders WHERE status_pembayaran = 'paid' AND status_pesanan = 'dapur'
                     ORDER BY waktu_masuk ASC, id ASC LIMIT 100");
$pesanan = [];
foreach ($stmt->fetchAll() as $p) {
    // Daftar menu pada pesanan ini
    $stmt_item = $pdo->prepare("SELECT nama_menu, jumlah FROM order_items WHERE order_id = ? ORDER BY id");
    $stmt_item->execute([$p['id']]);
    $item = [];
    foreach ($stmt_item->fetchAll() as $i) {
        $item[] = ['nama' => $i['nama_menu'], 'jumlah' => (int)$i['jumlah']];
    }

    $masuk = strtotime($p['waktu_masuk']);
    $pesanan[] = [
        'id'             => (int)$p['id'],
        'meja'           => str_pad($p['nomor_meja'], 2, '0', STR_PAD_LEFT),
        'item'           => $item,
        'catatan'        => (string)$p['catatan'],
        'waktu'          => date('H:i', $masuk),
        'menunggu_menit' => max(0, floor((time() - $masuk) / 60)),
    ];
}

echo json_encode(['ok' => true, 'pesanan' => $pesanan]);
?>
