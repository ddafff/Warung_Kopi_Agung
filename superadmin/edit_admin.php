<?php

session_start();
require_once '../koneksi.php';
include 'cek_superadmin.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'admin'");
$stmt->execute([$id]);
$admin = $stmt->fetch();
if (!$admin) {
    header('Location: ../404.php');
    exit;
}

$username = $admin['username'];
$email    = $admin['email'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $mode     = 'edit';
    $id_admin = $id;
    include 'cek_data_admin.php';     // hasil: $error

    if ($error == '') {
        if ($password != '') {                         // sandi baru diisi -> diganti
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {                                       // sandi dikosongkan -> tetap
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$username, $email, $id]);
        }

        $_SESSION['pesan'] = 'Perubahan data admin "' . $username . '" disimpan.';
        $_SESSION['jenis'] = 'sukses';
        header('Location: kelola_admin.php');
        exit;
    }
}

$judul = 'Edit Admin';
$menu_aktif = 'admin';
$mode = 'edit';
include 'sa_header.php';
?>
<div class="kepala-halaman">
    <div>
        <h1 class="judul-halaman">Edit Admin</h1>
        <p class="teks-pendukung">Ubah data akun. Kata sandi hanya berubah bila kolomnya diisi.</p>
    </div>
</div>
<?php include 'form_admin.php'; ?>
<?php include 'sa_footer.php'; ?>