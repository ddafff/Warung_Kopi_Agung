<?php
// pesan.php — menampilkan pesan sekali tampil (diisi lewat $_SESSION['pesan'] dan $_SESSION['jenis'])
// jenis: sukses, galat, info, peringatan
if (isset($_SESSION['pesan'])): ?>
    <div class="pesan pesan-<?= $_SESSION['jenis'] ?? 'info' ?>" role="alert">
        <i class="fa-solid fa-circle-info"></i>
        <span><?= htmlspecialchars($_SESSION['pesan']) ?></span>
    </div>
    <?php unset($_SESSION['pesan'], $_SESSION['jenis']); ?>
<?php endif; ?>
