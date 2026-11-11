<?php
// user/logout.php — hapus data login, tapi nomor meja tetap disimpan
session_start();

unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'], $_SESSION['keranjang'],
      $_SESSION['aktivitas_terakhir'], $_SESSION['catatan'], $_SESSION['metode']);
session_regenerate_id(true);

$_SESSION['pesan'] = 'Anda sudah keluar.';
$_SESSION['jenis'] = 'sukses';
header('Location: us_index.php');
exit;
?>
