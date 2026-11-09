<?php
// user/ambil_pesanan.php — mengambil satu pesanan dari ?id= lalu memeriksa pemiliknya.
// Hasil: variabel $pesanan. ?id= tidak ada / bukan angka -> 404. Milik orang lain -> 403.
include 'cek_login.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT o.*, u.username FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?");
$stmt->execute([$id]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    header('Location: ../404.php');
    exit;
}
if ($pesanan['user_id'] != $_SESSION['user_id']) {
    header('Location: ../403.php');
    exit;
}
?>
