<?php
// admin/data_pesanan.php?tab=&q= (JSON) — dipanggil JS tiap 5 detik oleh pesanan_masuk.php
// Sebelum membalas, status pesanan QRIS yang masih menunggu diperiksa ke Midtrans.
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';
require_once '../midtrans.php';
include 'cek_admin_json.php';

$tab = $_GET['tab'] ?? 'semua';
$q   = trim($_GET['q'] ?? '');

$peringatan = '';
if (!periksa_qris($pdo)) {
    $peringatan = 'Pemeriksaan QRIS gagal, coba lagi';
}

// Jumlah pesanan pada tiap tab (pesanan dibatalkan tidak dihitung)
$status_tab = ['belum_dibayar' => 'unpaid', 'menunggu_tunai' => 'pending_cash', 'sudah_dibayar' => 'paid'];
$jumlah = ['semua' => 0, 'belum_dibayar' => 0, 'menunggu_tunai' => 0, 'sudah_dibayar' => 0];
$stmt = $pdo->query("SELECT status_pembayaran, COUNT(*) AS n FROM orders WHERE status_pesanan <> 'dibatalkan' GROUP BY status_pembayaran");
foreach ($stmt->fetchAll() as $baris) {
    $jumlah['semua'] += $baris['n'];
    foreach ($status_tab as $nama_tab => $status) {
        if ($status == $baris['status_pembayaran']) {
            $jumlah[$nama_tab] = (int)$baris['n'];
        }
    }
}

// Daftar pesanan sesuai tab dan kata cari
$sql = "SELECT o.*, u.username FROM orders o JOIN users u ON u.id = o.user_id WHERE o.status_pesanan <> 'dibatalkan'";
$param = [];
if (isset($status_tab[$tab])) {
    $sql .= " AND o.status_pembayaran = ?";
    $param[] = $status_tab[$tab];
}
if ($q != '') {
    $sql .= " AND (o.nomor_meja LIKE ? OR u.username LIKE ?)";
    $param[] = '%' . $q . '%';
    $param[] = '%' . $q . '%';
}
$stmt = $pdo->prepare($sql . " ORDER BY o.created_at DESC, o.id DESC LIMIT 200");
$stmt->execute($param);

$pesanan = [];
foreach ($stmt->fetchAll() as $p) {
    $pesanan[] = [
        'id'                   => (int)$p['id'],
        'meja'                 => str_pad($p['nomor_meja'], 2, '0', STR_PAD_LEFT),
        'username'             => $p['username'],
        'metode'               => label_metode($p['metode_pembayaran']),
        'metode_kode'          => $p['metode_pembayaran'],
        'total_teks'           => rupiah($p['total_harga']),
        'status_pembayaran'    => $p['status_pembayaran'],
        'status_label'         => label_pembayaran($p['status_pembayaran']),
        'status_pesanan_label' => label_pesanan($p['status_pesanan']),
        'waktu'                => waktu_singkat($p['created_at']),
        'bisa_konfirmasi'      => ($p['status_pembayaran'] == 'pending_cash' && $p['status_pesanan'] == 'pending'),
        'bisa_cetak'           => ($p['status_pembayaran'] == 'paid'),
    ];
}

echo json_encode(['ok' => true, 'jumlah' => $jumlah, 'pesanan' => $pesanan, 'peringatan' => $peringatan]);
?>
