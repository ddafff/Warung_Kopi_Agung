// JS/Admin.js — JavaScript halaman admin (JS biasa, tanpa jQuery)
// Pesanan Masuk dan Antrean Dapur memuat datanya lewat fetch (GET) tiap 5 detik.
// Perubahan data (konfirmasi, tandai selesai) tetap lewat formulir POST biasa.

var intervalPantau = 5000;   // 5 detik

// Mengamankan teks sebelum dimasukkan ke HTML (mencegah kode berbahaya dari catatan/nama)
function esc(teks) {
    var div = document.createElement('div');
    div.textContent = teks;
    return div.innerHTML;
}

// Mengambil data JSON. Sesi habis (403) -> ke halaman masuk
function ambilData(alamat, saatBerhasil, saatGagal) {
    fetch(alamat)
        .then(function (respons) {
            if (respons.status == 403) {
                window.location.href = '../user/login.php';
                return null;
            }
            return respons.json();
        })
        .then(function (data) {
            if (data && data.ok) saatBerhasil(data);
        })
        .catch(saatGagal);
}

// ---------- Menu samping (di HP) ----------
function bukaMenu() {
    document.getElementById('menu-samping').classList.add('terbuka');
    document.getElementById('latar-menu').classList.add('terbuka');
}
function tutupMenu() {
    document.getElementById('menu-samping').classList.remove('terbuka');
    document.getElementById('latar-menu').classList.remove('terbuka');
}

// ---------- Jam di kepala halaman ----------
function perbaruiJam() {
    var sekarang = new Date();
    var jam = ('0' + sekarang.getHours()).slice(-2) + ':' + ('0' + sekarang.getMinutes()).slice(-2) + ' WIB';
    document.querySelectorAll('[data-jam]').forEach(function (el) {
        el.textContent = jam;
    });
}
setInterval(perbaruiJam, 15000);


// =====================================================
// PESANAN MASUK
// =====================================================
// Tab dan kata cari disimpan di sessionStorage, jadi setelah kasir menekan Konfirmasi
// (halaman dimuat ulang) tampilannya tetap di tab yang sama.
var tabAktif = sessionStorage.getItem('tabAktif') || 'semua';
var kataCari = sessionStorage.getItem('kataCari') || '';
var htmlTerakhir = '';

function pilihTab(tab, tombol) {
    tabAktif = tab;
    sessionStorage.setItem('tabAktif', tab);
    document.querySelectorAll('.tab-filter-tombol').forEach(function (t) {
        t.classList.remove('aktif');
    });
    tombol.classList.add('aktif');
    muatPesanan();
}

function cariPesanan(teks) {
    kataCari = teks.trim();
    sessionStorage.setItem('kataCari', kataCari);
    muatPesanan();
}

function muatPesanan() {
    var alamat = 'data_pesanan.php?tab=' + tabAktif + '&q=' + encodeURIComponent(kataCari);
    ambilData(alamat, tampilkanPesanan, function () {
        document.getElementById('peringatan-tabel').textContent = 'Menghubungkan ulang…';
    });
}

function tampilkanPesanan(data) {
    // Angka pada tab
    for (var nama in data.jumlah) {
        document.getElementById('angka-' + nama).textContent = data.jumlah[nama];
    }
    document.getElementById('peringatan-tabel').textContent = data.peringatan;
    document.getElementById('ringkasan-tabel').textContent = 'Menampilkan ' + data.pesanan.length + ' pesanan';

    // Susun baris tabel
    var html = '';
    data.pesanan.forEach(function (p) {
        var aksi = '';
        if (p.bisa_konfirmasi) {
            aksi = `<form method="POST" action="konfirmasi_tunai.php"
                          onsubmit="return confirm('Konfirmasi pembayaran tunai pesanan No. ${p.id} sebesar ${p.total_teks}?')">
                        <input type="hidden" name="id" value="${p.id}">
                        <button class="tombol tombol-utama tombol-kecil" type="submit"><i class="fa-solid fa-circle-check"></i> Konfirmasi</button>
                    </form>`;
        } else if (p.bisa_cetak) {
            aksi = `<a class="tombol tombol-sekunder tombol-kecil" href="cetak_struk.php?id=${p.id}"><i class="fa-solid fa-print"></i> Cetak</a>`;
        }

        var ikonMetode = (p.metode_kode == 'qris') ? 'fa-qrcode' : 'fa-money-bill-wave';
        var subStatus = (p.status_pembayaran == 'paid') ? `<span class="status-kecil">${p.status_pesanan_label}</span>` : '';

        html += `<tr>
            <td data-label="No."><strong>No. ${p.id}</strong></td>
            <td data-label="Meja"><span class="chip-meja">Meja ${p.meja}</span></td>
            <td data-label="Username"><span class="sel-pengguna"><i class="fa-solid fa-circle-user"></i> ${esc(p.username)}</span></td>
            <td data-label="Metode"><span class="pil-metode"><i class="fa-solid ${ikonMetode}"></i> ${p.metode}</span></td>
            <td data-label="Total"><strong>${p.total_teks}</strong></td>
            <td data-label="Status"><span><span class="lencana" data-status="${p.status_pembayaran}">${p.status_label}</span>${subStatus}</span></td>
            <td data-label="Waktu">${p.waktu}</td>
            <td class="sel-aksi">${aksi}</td>
        </tr>`;
    });
    if (html == '') {
        html = '<tr><td class="tabel-kosong" colspan="8">Belum ada pesanan pada tampilan ini.</td></tr>';
    }

    // Tabel hanya diganti bila isinya berubah
    if (html != htmlTerakhir) {
        document.getElementById('isi-tabel').innerHTML = html;
        htmlTerakhir = html;
    }
}

function mulaiPesanan() {
    // Kembalikan tab dan kolom cari seperti terakhir dipakai
    document.querySelectorAll('.tab-filter-tombol').forEach(function (t) {
        t.classList.toggle('aktif', t.getAttribute('data-tab') == tabAktif);
    });
    document.getElementById('kolom-cari').value = kataCari;

    muatPesanan();
    setInterval(function () {
        if (!document.hidden) muatPesanan();    // tab tidak dilihat: jeda
    }, intervalPantau);
}


// =====================================================
// ANTREAN DAPUR
// =====================================================
var antreanTerakhir = '';
var idDikenal = {};          // nomor pesanan yang sudah pernah tampil (untuk bunyi pesanan baru)
var pertamaKali = true;
var bunyiNyala = false;
var audioCtx = null;

function muatAntrean() {
    ambilData('data_antrean.php', tampilkanAntrean, function () {
        document.getElementById('pesan-koneksi').textContent = 'Menghubungkan ulang…';
    });
}

function tampilkanAntrean(data) {
    document.getElementById('pesan-koneksi').textContent = '';
    document.getElementById('jumlah-antrean').textContent = data.pesanan.length + ' pesanan sedang disiapkan';

    // Ada pesanan baru? -> bunyi
    var adaBaru = false;
    data.pesanan.forEach(function (p) {
        if (!idDikenal[p.id]) {
            idDikenal[p.id] = true;
            adaBaru = true;
        }
    });
    if (adaBaru && !pertamaKali) bunyi();
    pertamaKali = false;

    // Susun kartu
    var html = '';
    data.pesanan.forEach(function (p, urutan) {
        var itemHtml = '';
        p.item.forEach(function (i) {
            itemHtml += `<span class="item-antrean">${i.jumlah}x ${esc(i.nama)}</span>`;
        });
        var catatan = p.catatan ? `<div class="catatan-antrean"><i class="fa-solid fa-pen-to-square"></i><span><strong>Catatan:</strong> ${esc(p.catatan)}</span></div>` : '';
        var tandaTerlama = (urutan == 0) ? '<span class="tanda-terlama">Terlama</span>' : '';
        var lama = (p.menunggu_menit >= 15) ? 'lama' : '';

        html += `<article class="kartu-antrean ${urutan == 0 ? 'terlama' : ''}">
            <div class="kartu-antrean-garis"></div>
            <div class="kartu-antrean-isi">
                <div class="kartu-antrean-atas">
                    <div>
                        <div class="nomor-antrean">No. ${p.id}${tandaTerlama}</div>
                        <div class="meja-antrean">Meja ${p.meja}</div>
                    </div>
                    <div class="waktu-antrean">
                        <span><i class="fa-regular fa-clock"></i> ${p.waktu} WIB</span>
                        <span class="waktu-tunggu ${lama}">Menunggu: ${p.menunggu_menit} mnt</span>
                    </div>
                </div>
                <div class="daftar-item-antrean"><small>Daftar Menu</small>${itemHtml}</div>
                ${catatan}
            </div>
            <div class="kartu-antrean-bawah">
                <form method="POST" action="selesai_pesanan.php" onsubmit="return confirm('Tandai pesanan No. ${p.id} sudah selesai?')">
                    <input type="hidden" name="id" value="${p.id}">
                    <button class="tombol tombol-utama tombol-penuh" type="submit"><i class="fa-solid fa-circle-check"></i> Tandai Selesai</button>
                </form>
            </div>
        </article>`;
    });
    if (html == '') {
        html = `<div class="kartu keadaan-kosong kosong-antrean">
                    <div class="ikon-hero"><i class="fa-solid fa-mug-hot"></i></div>
                    <h2 class="judul-bagian">Belum ada pesanan di antrean</h2>
                    <p class="teks-pendukung">Pesanan yang sudah dibayar akan muncul di sini.</p>
                </div>`;
    }

    if (html != antreanTerakhir) {
        document.getElementById('antrean').innerHTML = html;
        antreanTerakhir = html;
    }
}

// Tombol bunyi: harus ditekan sekali supaya peramban mengizinkan suara
function ubahBunyi() {
    bunyiNyala = !bunyiNyala;
    if (bunyiNyala && !audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    document.getElementById('ikon-bunyi').className = bunyiNyala ? 'fa-solid fa-volume-high' : 'fa-solid fa-volume-xmark';
    document.getElementById('teks-bunyi').textContent = bunyiNyala ? 'Bunyi: Nyala' : 'Bunyi: Mati';
    if (bunyiNyala) bunyi();
}

// Bunyi "tit-tit" singkat
function bunyi() {
    if (!bunyiNyala || !audioCtx) return;
    [0, 0.25].forEach(function (jeda) {
        var osilator = audioCtx.createOscillator();
        osilator.frequency.value = 880;
        osilator.connect(audioCtx.destination);
        osilator.start(audioCtx.currentTime + jeda);
        osilator.stop(audioCtx.currentTime + jeda + 0.15);
    });
}

function mulaiAntrean() {
    muatAntrean();
    setInterval(function () {
        if (!document.hidden) muatAntrean();
    }, intervalPantau);
}


// =====================================================
// SUPERADMIN: grafik dashboard, QR code meja, foto menu
// =====================================================

// ---------- Grafik (Chart.js). Data dibaca dari atribut data-label dan data-nilai pada <canvas> ----------
function gambarGrafik(idCanvas, jenis, warna) {
    var kanvas = document.getElementById(idCanvas);
    if (!kanvas || typeof Chart === 'undefined') return;

    new Chart(kanvas, {
        type: jenis,
        data: {
            labels: JSON.parse(kanvas.getAttribute('data-label')),
            datasets: [{
                data: JSON.parse(kanvas.getAttribute('data-nilai')),
                backgroundColor: warna,
                borderColor: warna,
                borderWidth: 2,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
}

// ---------- QR code (qrcodejs). Elemen dengan data-qr digambar otomatis ----------
function gambarSemuaQR() {
    document.querySelectorAll('[data-qr]').forEach(function (el) {
        var ukuran = parseInt(el.getAttribute('data-ukuran') || '96');
        new QRCode(el, { text: el.getAttribute('data-qr'), width: ukuran, height: ukuran });
    });
}

// Tombol "Lihat QR": tampilkan QR besar di jendela
function lihatQR(nomor, alamat) {
    document.getElementById('judul-qr').textContent = 'QR Meja ' + nomor;
    var kotak = document.getElementById('qr-besar');
    kotak.innerHTML = '';
    new QRCode(kotak, { text: alamat, width: 220, height: 220 });
    document.getElementById('modal-qr').classList.add('terbuka');
}
function tutupQR() {
    document.getElementById('modal-qr').classList.remove('terbuka');
}

// Cetak hanya bagian QR (aturan cetak ada di CSS/Admin.css)
function cetakQR() {
    document.body.classList.add('cetak-qr');
    window.print();
    document.body.classList.remove('cetak-qr');
}

// ---------- Pratinjau foto menu sebelum disimpan ----------
function previewFoto(input) {
    var gambar = document.getElementById('pratinjau-foto');
    if (input.files && input.files[0]) {
        gambar.src = URL.createObjectURL(input.files[0]);
        gambar.classList.remove('tersembunyi');
    } else {
        gambar.classList.add('tersembunyi');
    }
}

// =====================================================
// Jalankan sesuai halaman yang sedang dibuka
// =====================================================
if (document.getElementById('isi-tabel')) mulaiPesanan();
if (document.getElementById('antrean')) mulaiAntrean();
if (document.getElementById('grafik-jam')) {
    gambarGrafik('grafik-jam', 'bar', '#3DBE5C');
    gambarGrafik('grafik-tren', 'line', '#0B7A0B');
}
if (document.querySelector('[data-qr]') && typeof QRCode !== 'undefined') gambarSemuaQR();
