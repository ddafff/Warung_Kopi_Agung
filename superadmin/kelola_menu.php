<?php
// superadmin/kelola_menu.php — daftar menu: cari, filter kategori, saklar Tersedia, Edit, Hapus, Tambah.
session_start();
require_once '../koneksi.php';
require_once '../fungsi.php';
include 'cek_superadmin.php';

$cari = trim($_GET['cari'] ?? '');
$daftar_kategori = ['Semua', 'Kopi', 'Non Kopi', 'Makanan', 'Snack'];
$kategori = $_GET['kategori'] ?? 'Semua';
if (!in_array($kategori, $daftar_kategori)) {
    $kategori = 'Semua';
}

// Jumlah menu per kategori (untuk angka pada tombol kategori)
$jumlah = ['Semua' => 0, 'Kopi' => 0, 'Non Kopi' => 0, 'Makanan' => 0, 'Snack' => 0];
$aktif = 0;
foreach ($pdo->query("SELECT kategori, tersedia FROM menu")->fetchAll() as $m) {
    $jumlah['Semua']++;
    $jumlah[$m['kategori']]++;
    $aktif += $m['tersedia'];
}

// Daftar menu sesuai pencarian dan kategori
$sql = "SELECT * FROM menu WHERE 1 = 1";
$param = [];
if ($kategori != 'Semua') {
    $sql .= " AND kategori = ?";
    $param[] = $kategori;
}
if ($cari != '') {
    $sql .= " AND (nama LIKE ? OR kategori LIKE ?)";
    $param[] = '%' . $cari . '%';
    $param[] = '%' . $cari . '%';
}
$stmt = $pdo->prepare($sql . " ORDER BY kategori, nama");
$stmt->execute($param);
$daftar = $stmt->fetchAll();

$judul = 'Kelola Menu';
$menu_aktif = 'menu';
include 'sa_header.php';
?>
<div class="kepala-halaman">
    <div>
        <h1 class="judul-halaman">Kelola Menu</h1>
        <p class="teks-pendukung"><?= $jumlah['Semua'] ?> menu &bull; <?= $aktif ?> tersedia &bull; <?= $jumlah['Semua'] - $aktif ?> tidak tersedia</p>
    </div>
    <div class="aksi-kepala">
        <a class="tombol tombol-utama" href="tambah_menu.php"><i class="fa-solid fa-plus"></i> Tambah Menu</a>
    </div>
</div>

<section class="kartu-tabel">
    <div class="alat-tabel">
        <nav class="tab-filter">
            <?php foreach ($daftar_kategori as $k): ?>
                <a class="tab-filter-tombol <?= $k == $kategori ? 'aktif' : '' ?>" href="?kategori=<?= urlencode($k) ?>&cari=<?= urlencode($cari) ?>">
                    <span><?= $k ?></span> <span class="tab-filter-angka"><?= $jumlah[$k] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <form class="kolom-cari" method="GET">
            <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input class="isian" type="search" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari nama atau kategori menu">
        </form>
    </div>

    <div class="pembungkus-tabel">
        <table class="tabel-data">
            <thead>
                <tr><th>Menu</th><th>Kategori</th><th>Harga</th><th>Status penjualan</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (!$daftar): ?>
                    <tr><td class="tabel-kosong" colspan="5">Menu tidak ditemukan.</td></tr>
                <?php endif; ?>
                <?php foreach ($daftar as $m): ?>
                    <tr>
                        <td data-label="Menu">
                            <span class="sel-pengguna">
                                <?php if ($m['gambar'] && file_exists('../gambar/' . $m['gambar'])): ?>
                                    <img class="foto-tabel" src="../gambar/<?= htmlspecialchars($m['gambar']) ?>" alt="">
                                <?php else: ?>
                                    <span class="foto-tabel"><i class="fa-solid fa-mug-saucer"></i></span>
                                <?php endif; ?>
                                <?= htmlspecialchars($m['nama']) ?>
                            </span>
                        </td>
                        <td data-label="Kategori"><?= $m['kategori'] ?></td>
                        <td data-label="Harga"><strong><?= rupiah($m['harga']) ?></strong></td>
                        <td data-label="Status">
                            <!-- Saklar: begitu digeser, formulir dikirim ke ketersediaan_menu.php -->
                            <form method="POST" action="ketersediaan_menu.php">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">
                                <input type="hidden" name="cari" value="<?= htmlspecialchars($cari) ?>">
                                <label class="saklar">
                                    <input type="checkbox" <?= $m['tersedia'] ? 'checked' : '' ?> onchange="this.form.submit()">
                                    <span class="saklar-geser"></span>
                                    <span><?= $m['tersedia'] ? 'Tersedia' : 'Tidak tersedia' ?></span>
                                </label>
                            </form>
                        </td>
                        <td class="sel-aksi">
                            <div class="aksi-baris">
                                <a class="tombol tombol-sekunder tombol-kecil" href="edit_menu.php?id=<?= $m['id'] ?>"><i class="fa-solid fa-pen"></i> Edit</a>
                                <form method="POST" action="hapus_menu.php">
                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                    <button class="tombol tombol-bahaya tombol-kecil" type="submit"><i class="fa-solid fa-trash"></i> Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="catatan-bawah-tabel"><span>Menampilkan <?= count($daftar) ?> menu</span></div>
</section>
<?php include 'sa_footer.php'; ?>
