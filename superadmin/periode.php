<?php
// superadmin/periode.php — menghitung rentang waktu dari ?periode= (harian, mingguan, bulanan)
// Dipakai dashboard.php dan laporan_penjualan.php. Hasil: $periode, $mulai, $selesai, $nama_periode
$periode = $_GET['periode'] ?? 'harian';

if ($periode == 'mingguan') {                       // 7 hari terakhir (termasuk hari ini)
    $mulai   = date('Y-m-d 00:00:00', strtotime('-6 days'));
    $selesai = date('Y-m-d 00:00:00', strtotime('+1 day'));
    $nama_periode = '7 hari terakhir';
} elseif ($periode == 'bulanan') {                  // bulan berjalan
    $mulai   = date('Y-m-01 00:00:00');
    $selesai = date('Y-m-01 00:00:00', strtotime('first day of next month'));
    $nama_periode = 'bulan ini';
} else {                                            // hari ini
    $periode = 'harian';
    $mulai   = date('Y-m-d 00:00:00');
    $selesai = date('Y-m-d 00:00:00', strtotime('+1 day'));
    $nama_periode = 'hari ini';
}

// Hanya pesanan "Sudah dibayar" yang dihitung; waktunya = waktu dibayar
$kondisi_laporan = "o.status_pembayaran = 'paid' AND COALESCE(o.dibayar_pada, o.created_at) >= ? AND COALESCE(o.dibayar_pada, o.created_at) < ?";
?>
