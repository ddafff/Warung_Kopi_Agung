<?php
// midtrans.php — integrasi Midtrans SANDBOX lewat cURL (tanpa Snap, tanpa webhook).
// Status QRIS diperiksa dengan cara "tarik" (Get Status API) setiap kali halaman memantau.

require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/fungsi.php';

// Mengirim permintaan ke Midtrans. Hasil: array balasan, atau null kalau gagal jaringan.
function midtrans_kirim(string $metode, string $alamat, ?array $isi = null): ?array
{
    if (!function_exists('curl_init')) {
        tulis_log('Midtrans: ekstensi cURL belum aktif');
        return null;
    }
    $ch = curl_init(MIDTRANS_BASE_URL . $alamat);
    $header = ['Accept: application/json', 'Content-Type: application/json'];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER     => $header,
        CURLOPT_USERPWD        => MIDTRANS_SERVER_KEY . ':',   // Basic Auth: Server Key sebagai nama pengguna, sandi kosong
    ]);
    if ($metode === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($isi ?? []));
    }
    $balasan = curl_exec($ch);
    if ($balasan === false) {
        tulis_log('Midtrans: gagal jaringan: ' . curl_error($ch));
        curl_close($ch);
        return null;
    }
    curl_close($ch);
    $data = json_decode($balasan, true);
    return is_array($data) ? $data : null;
}

// Membuat QRIS untuk satu pesanan.
// Hasil sukses: ['qr_string' => ..., 'expiry_time' => 'Y-m-d H:i:s' atau null]. Gagal: null.
function buat_qris(int $order_id, int $total): ?array
{
    $data = midtrans_kirim('POST', '/v2/charge', [
        'payment_type'        => 'qris',
        'transaction_details' => [
            'order_id'     => 'KOPI-' . $order_id,
            'gross_amount' => $total,
        ],
        'qris'                => ['acquirer' => 'gopay'],
    ]);
    if (!$data || !in_array((string) ($data['status_code'] ?? ''), ['200', '201'], true)) {
        tulis_log('Midtrans: buat_qris gagal untuk pesanan ' . $order_id . ': ' . json_encode($data));
        return null;
    }
    $qr = $data['qr_string'] ?? null;
    if (!$qr) {
        tulis_log('Midtrans: balasan tanpa qr_string untuk pesanan ' . $order_id);
        return null;
    }
    return [
        'qr_string'   => $qr,
        'expiry_time' => $data['expiry_time'] ?? null,
    ];
}

// Memeriksa status satu transaksi. Hasil: nilai transaction_status, atau null kalau gagal.
function cek_status(string $midtrans_order_id): ?string
{
    $data = midtrans_kirim('GET', '/v2/' . rawurlencode($midtrans_order_id) . '/status');
    if (!$data || !isset($data['transaction_status'])) {
        return null;
    }
    return (string) $data['transaction_status'];
}

// Fungsi murni: mengubah status Midtrans menjadi status sistem. null = tidak ada perubahan.
function petakan_status_midtrans(string $ts): ?array
{
    switch ($ts) {
        case 'settlement':
        case 'capture':
            return ['status_pembayaran' => 'paid', 'status_pesanan' => 'dapur'];
        case 'pending':
            return null;
        case 'expire':
        case 'cancel':
        case 'deny':
            return ['status_pembayaran' => 'unpaid', 'status_pesanan' => 'dibatalkan'];
        default:
            tulis_log('Midtrans: status tidak dikenal: ' . $ts);
            return null;
    }
}

// Memeriksa pesanan QRIS yang masih menunggu (satu pesanan bila $id diisi). Aman dipanggil berulang.
function periksa_status_qris(PDO $pdo, ?int $id = null): void
{
    try {
        $sql = "SELECT id, midtrans_order_id FROM orders
                 WHERE metode_pembayaran = 'qris' AND status_pembayaran = 'unpaid'
                   AND status_pesanan = 'pending' AND midtrans_order_id IS NOT NULL";
        $param = [];
        if ($id !== null) {
            $sql .= ' AND id = ?';
            $param[] = $id;
        }
        $sql .= ' ORDER BY id LIMIT 20';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($param);

        foreach ($stmt->fetchAll() as $p) {
            $ts = cek_status($p['midtrans_order_id']);
            if ($ts === null) {
                continue;
            }
            $hasil = petakan_status_midtrans($ts);
            if ($hasil === null) {
                continue;
            }
            // "WHERE status_pembayaran = 'unpaid'" membuat pembaruan ini aman bila terjadi dua kali
            $dibayar = ($hasil['status_pembayaran'] === 'paid') ? date('Y-m-d H:i:s') : null;
            $up = $pdo->prepare(
                "UPDATE orders SET status_pembayaran = ?, status_pesanan = ?, dibayar_pada = ?
                  WHERE id = ? AND status_pembayaran = 'unpaid' AND status_pesanan = 'pending'"
            );
            $up->execute([$hasil['status_pembayaran'], $hasil['status_pesanan'], $dibayar, $p['id']]);
        }
    } catch (Throwable $e) {
        // Jangan pernah melempar galat ke pengguna
        tulis_log('periksa_status_qris gagal: ' . $e->getMessage());
    }
}
