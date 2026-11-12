<?php
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';
include 'cek_superadmin.php';


$cari = trim($_GET['cari'] ?? '');

$sql = "SELECT * FROM users WHERE role = 'admin'";
$param = [];
if ($cari !== '') {
    $sql .= " AND (username LIKE ? OR email LIKE ?)";
    $param[] = '%' . $cari . '%';
    $param[] = '%' . $cari . '%';
}

$stmt = $pdo->prepare($sql . "  ORDER BY username ASC");
$stmt->execute($param);
$daftar = $stmt->fetchAll();

$judul = 'Kelola Admin';
$menu_aktif = 'admin';
include 'sa_header.php';
?>

<div class="kepala-halaman">
    <div>
        <h1 class="judul-halaman">Kelola Admin</h1>
        <p class="teks-pendukung"><?= count($daftar) ?> akun admin. Kasir yang berhenti bekerja cukup dihapus akunnya agar tidak punya akses lagi.</p>
    </div>
    <div class="aksi-kepala">
        <a class="tombol tombol-utama" href="tambah_admin.php"><i class="fa-solid fa-plus"></i> Tambah Admin</a>
    </div>
</div>
 
<section class="kartu-tabel">
    <div class="alat-tabel">
        <form class="formulir-cari" method="GET" style="width:100%">
            <div class="kolom-cari">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input class="isian" type="search" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari username atau email">
            </div>
            <button class="tombol tombol-sekunder tombol-kecil" type="submit">Cari</button>
            <?php if ($cari != ''): ?><a class="tombol tombol-kecil" href="kelola_admin.php">Hapus pencarian</a><?php endif; ?>
        </form>
    </div>
 
    <div class="pembungkus-tabel">
        <table class="tabel-data">
            <thead>
                <tr><th>Username</th><th>Email</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                 <?php if (!$daftar): ?>
                    <tr><td class="tabel-kosong" colspan="3"><?= $cari != '' ? 'Admin tidak ditemukan.' : 'Belum ada akun admin. Tekan Tambah Admin.' ?></td></tr>
                <?php endif; ?>
                <?php foreach ($daftar as $a): ?>
                    <tr>
                        <td data-label="Username"><span class="chip-meja">@<?= htmlspecialchars($a['username']) ?></span></td>
                        <td data-label="Email"><?= htmlspecialchars($a['email']) ?></td>
                        <td class="sel-aksi">
                            <div class="aksi-baris">
                                <a class="tombol tombol-sekunder tombol-kecil" href="edit_admin.php?id=<?= $a['id'] ?>"><i class="fa-solid fa-pen"></i> Edit</a>
                                <form method="POST" action="hapus_admin.php">
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                    <button class="tombol tombol-bahaya tombol-kecil" type="submit"><i class="fa-solid fa-trash"></i> Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="catatan-bawah-tabel"><span>Menampilkan <?= count($daftar) ?> admin</span></div>
</section>
<?php include 'sa_footer.php'; ?>


