<?php
// midtrans.php — QRIS Midtrans SANDBOX memakai cURL (tanpa Snap, tanpa webhook).
// Status pembayaran "ditarik" dari Midtrans setiap kali halaman memantau.

// Kirim permintaan ke Midtrans. Hasil: array balasan, atau null bila gagal.
function kirim_midtrans($metode, $alamat, $data = null) {
    $ch = curl_init(MIDTRANS_URL . $alamat);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_USERPWD, MIDTRANS_SERVER_KEY . ':');   // Server Key sebagai nama pengguna
    if ($metode == 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $balasan = curl_exec($ch);
    if ($balasan === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['error' => 'cURL Error: ' . $err];
    }
    curl_close($ch);
    return json_decode($balasan, true);
}

// Buat QRIS untuk satu pesanan. Hasil: ['qr_string' => ..., 'expiry_time' => ...] atau false
function buat_qris($order_id, $total, &$pesan_error = null) {
    if (MIDTRANS_SERVER_KEY === 'ISI_SERVER_KEY_SANDBOX_ANDA' || trim(MIDTRANS_SERVER_KEY) === '') {
        $pesan_error = 'MIDTRANS_SERVER_KEY di koneksi.php masih teks bawaan. Pastikan Anda sudah mengedit dan menyimpan file koneksi.php (Ctrl + S).';
        return false;
    }

    $hasil = kirim_midtrans('POST', '/v2/charge', [
        'payment_type'        => 'qris',
        'transaction_details' => ['order_id' => 'KOPI-' . $order_id, 'gross_amount' => $total], 
        'qris'                => ['acquirer' => 'gopay'],
    ]);

    if ($hasil === null) {
        $pesan_error = 'Gagal menghubungi server Midtrans (Respons kosong).';
        return false;
    }

    if (!empty($hasil['error'])) {
        $pesan_error = 'Midtrans: ' . $hasil['error'] . ' (Periksa Server Key di koneksi.php).';
        return false;
    }

    if (!in_array($hasil['status_code'] ?? '', ['200', '201']) || empty($hasil['qr_string'])) {
        $pesan_error = 'Midtrans [' . ($hasil['status_code'] ?? '?') . ']: ' . ($hasil['status_message'] ?? 'Gagal membuat QRIS');
        return false;
    }

    $qr_url = '';
    if (!empty($hasil['actions'])) {
        foreach ($hasil['actions'] as $act) {
            if ($act['name'] === 'generate-qr-code') {
                $qr_url = $act['url'];
                break;
            }
        }
    }

    return [
        'qr_string'   => $hasil['qr_string'],
        'qr_url'      => $qr_url,
        'expiry_time' => $hasil['expiry_time'] ?? null
    ];
}

// Periksa pesanan QRIS yang masih menunggu (satu pesanan bila $id diisi).
// Hasil: true bila semua pemeriksaan berhasil, false bila Midtrans tidak terjangkau.
function periksa_qris($pdo, $id = null) {
    $sql = "SELECT id, midtrans_order_id FROM orders
            WHERE metode_pembayaran = 'qris' AND status_pembayaran = 'unpaid'
            AND status_pesanan = 'pending' AND midtrans_order_id IS NOT NULL";
    $param = [];
    if ($id !== null) {
        $sql .= ' AND id = ?';
        $param[] = $id;
    }
    $stmt = $pdo->prepare($sql . ' ORDER BY id LIMIT 20');
    $stmt->execute($param);

    foreach ($stmt->fetchAll() as $p) {
        $hasil = kirim_midtrans('GET', '/v2/' . $p['midtrans_order_id'] . '/status');
        if (!$hasil || !isset($hasil['transaction_status'])) {
            return false;   // Midtrans tidak terjangkau, berhenti
        }
        $status = $hasil['transaction_status'];

        if ($status == 'settlement' || $status == 'capture') {            // sudah dibayar
            $bayar = 'paid';  $pesanan = 'dapur';  $waktu = date('Y-m-d H:i:s');
        } elseif (in_array($status, ['expire', 'cancel', 'deny'])) {      // gagal / kedaluwarsa
            $bayar = 'unpaid'; $pesanan = 'dibatalkan'; $waktu = null;
        } else {
            continue;                                                     // masih pending
        }
        // Syarat WHERE membuat pembaruan aman bila dijalankan dua kali
        $ubah = $pdo->prepare("UPDATE orders SET status_pembayaran = ?, status_pesanan = ?, dibayar_pada = ?, updated_at = ?
                               WHERE id = ? AND status_pembayaran = 'unpaid' AND status_pesanan = 'pending'");
        $ubah->execute([$bayar, $pesanan, $waktu, date('Y-m-d H:i:s'), $p['id']]);
    }
    return true;
}
?>
