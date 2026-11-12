<?php

session_start();
require_once '../koneksi.php';
include 'cek_superadmin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../404.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'admin'");
$stmt->execute([$id]);
$admin = $stmt->fetch();
if (!$admin) {
    header('Location: ../404.php');
    exit;
}

// Berapa pesanan yang pernah dikonfirmasi admin ini? (hanya untuk ditampilkan pada pertanyaan)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE dikonfirmasi_oleh = ?");
$stmt->execute([$id]);
$jumlah_konfirmasi = $stmt->fetchColumn();

// Sudah dikonfirmasi (tombol Ya) -> hapus
if (isset($_POST['ya'])) {
    try {
        $pdo->beginTransaction();
        // Kosongkan catatan "dikonfirmasi oleh" pada pesanan lama supaya akun bisa dihapus
        $stmt = $pdo->prepare("UPDATE orders SET dikonfirmasi_oleh = NULL WHERE dikonfirmasi_oleh = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['pesan'] = 'Akun admin gagal dihapus, silakan coba lagi.';
        $_SESSION['jenis'] = 'galat';
        header('Location: kelola_admin.php');
        exit;
    }

    $_SESSION['pesan'] = 'Akun admin "' . $admin['username'] . '" sudah dihapus dan tidak bisa masuk lagi.';
    $_SESSION['jenis'] = 'sukses';
    header('Location: kelola_admin.php');
    exit;
}

// Belum dikonfirmasi -> tampilkan pertanyaan
$judul = 'Hapus Admin';
$menu_aktif = 'admin';
include 'sa_header.php';
?>
<section class="kartu konfirmasi-hapus pusat">
    <div class="ikon-hero"><i class="fa-solid fa-user-xmark"></i></div>
    <h1 class="judul-halaman">Hapus akun admin ini?</h1>
    <p class="teks-pendukung"><strong><?= htmlspecialchars($admin['username']) ?></strong> (@<?= htmlspecialchars($admin['username']) ?>) akan dihapus permanen dan <strong>langsung kehilangan akses</strong> ke sistem.</p>
    <?php if ($jumlah_konfirmasi > 0): ?>
        <p class="teks-kecil" style="margin-top:8px">Pernah mengonfirmasi <?= $jumlah_konfirmasi ?> pembayaran. Pesanan tersebut tetap tersimpan, hanya nama pengonfirmasinya tidak tercatat lagi.</p>
    <?php endif; ?>
    <form method="POST" class="aksi-form" style="justify-content:center; margin-top:16px">
        <input type="hidden" name="id" value="<?= $admin['id'] ?>">
        <a class="tombol tombol-sekunder" href="kelola_admin.php">Batal</a>
        <button class="tombol tombol-bahaya" type="submit" name="ya" value="1"><i class="fa-solid fa-trash"></i> Ya, hapus</button>
    </form>
</section>
<?php include 'sa_footer.php'; ?>