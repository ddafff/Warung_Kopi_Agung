<?php
// logout.php — menghapus data login, tetapi mempertahankan nomor meja.
require_once '../koneksi.php';
require_once '../fungsi.php';
mulai_sesi();

keluarkan_pengguna();
set_pesan('sukses', 'Anda sudah keluar.');
alihkan(BASE_URL . '/user/us_index.php');
