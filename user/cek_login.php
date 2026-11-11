<?php
// user/cek_login.php — dipasang di awal halaman pelanggan.
// Belum login -> ke halaman masuk. Bukan pelanggan (admin/superadmin) -> 403.
// (session_start() sudah dipanggil di halaman yang memuat berkas ini)
if (!isset($_SESSION['user_id'])) {
    $kembali = basename($_SERVER['PHP_SELF']);
    if (!empty($_SERVER['QUERY_STRING'])) {
        $kembali .= '?' . $_SERVER['QUERY_STRING'];
    }
    header('Location: login.php?kembali=' . urlencode($kembali));
    exit;
}
if ($_SESSION['role'] != 'pelanggan') {
    header('Location: ../403.php');
    exit;
}
?>
