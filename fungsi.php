<?php
// fungsi.php — fungsi kecil untuk mengubah angka/kode menjadi teks yang mudah dibaca

// 15000 -> "Rp 15.000"
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function label_pembayaran($status) {
    $label = [
        'unpaid'       => 'Belum dibayar',
        'pending_cash' => 'Menunggu bayar tunai',
        'paid'         => 'Sudah dibayar',
    ];
    return $label[$status] ?? $status;
}

function label_pesanan($status) {
    $label = [
        'pending'    => 'Menunggu',
        'dapur'      => 'Diproses dapur',
        'selesai'    => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];
    return $label[$status] ?? $status;
}

function label_metode($metode) {
    return $metode == 'qris' ? 'QRIS' : 'Tunai';
}

// "2026-10-28 14:15:00" -> "28 Okt 2026, 14:15 WIB"
function tanggal_indo($waktu) {
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($waktu);
    return date('d', $t) . ' ' . $bulan[(int)date('n', $t)] . ' ' . date('Y, H:i', $t) . ' WIB';
}

// Hari ini: "12:35". Hari lain: "18 Okt 12:35"
function waktu_singkat($waktu) {
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($waktu);
    if (date('Y-m-d', $t) == date('Y-m-d')) {
        return date('H:i', $t);
    }
    return date('d', $t) . ' ' . $bulan[(int)date('n', $t)] . ' ' . date('H:i', $t);
}

// "Budi Santoso" -> "BS" (untuk lingkaran avatar)
function inisial($nama) {
    $kata = explode(' ', trim($nama));
    $hasil = mb_strtoupper(mb_substr($kata[0], 0, 1));
    if (isset($kata[1])) {
        $hasil .= mb_strtoupper(mb_substr($kata[1], 0, 1));
    }
    return $hasil;
}
?>
