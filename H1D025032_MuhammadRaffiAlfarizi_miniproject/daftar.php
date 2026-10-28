<?php
// daftar.php - Operasi READ: Menampilkan katalog kasus + JOIN nama pemilik (Spesifikasi Bagian 4.3)
require_once __DIR__ . '/sesi.php';
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/validasi.php';

wajibLogin();

$pengguna = penggunaAktif();
$pesan    = ambilFlash();

try {
    // Kueri JOIN ke tabel pengguna untuk mengambil nama penginput/pencatat, diurutkan dari yang terbaru
    $sql = 'SELECT k.id, k.nama_barang, k.kategori, k.status, k.lokasi,
                   k.deskripsi, k.imbalan, k.foto_barang, k.status_penanganan,
                   k.tanggal_kejadian, k.dibuat_pada,
                   u.nama AS pencatat
            FROM kasus_barang AS k
            JOIN pengguna AS u ON u.id = k.pengguna_id
            ORDER BY k.dibuat_pada DESC, k.id DESC';
    $daftar = $db->query($sql)->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $ex) {
    error_log('[Read Daftar Gagal] ' . $ex->getMessage());
    http_response_code(500);
    exit('Maaf, data kasus barang belum dapat ditampilkan saat ini.');
}

// Filter kategori opsional di tampilan katalog
$filterKategori = $_GET['kat'] ?? 'Semua';
$sedangFilter   = isset($_GET['kat']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda &amp; Katalog Lost &amp; Found — Gotemu</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- 1. Floating Pill Navbar (Hanya berpindah saat diklik / aktif) -->
    <header class="topbar">
        <div class="topbar-inner">
            <a href="daftar.php" class="brand">
                <span class="brand-logo">G</span>
                <span>Gotemu</span>
            </a>

            <nav class="nav-pills" id="mainNav">
                <a href="daftar.php" data-nav="beranda" class="nav-pill-item <?= !$sedangFilter ? 'active' : '' ?>">Beranda</a>
                <a href="#katalog" data-nav="katalog" class="nav-pill-item <?= $sedangFilter ? 'active' : '' ?>">Lost &amp; Found</a>
                <a href="#solusi" data-nav="solusi" class="nav-pill-item">Alur Layanan</a>
                <?php if ($pengguna['peran'] === 'admin'): ?>
                    <a href="tambah.php" class="nav-pill-item">+ Buat Laporan</a>
                <?php endif; ?>
            </nav>

            <div class="user-actions">
                <span class="user-badge">
                    <?= e($pengguna['nama']) ?>
                    <span class="role-tag"><?= e($pengguna['peran']) ?></span>
                </span>
                <a href="logout.php" class="btn btn-secondary">Keluar</a>
            </div>
        </div>
    </header>

    <!-- 2. Hero Banner ("Bersama, Kita Temukan") -->
<!-- 2. Hero Banner ("Bersama, Kita Temukan") dengan Video Latar -->
    <section class="hero-banner">
        <video class="hero-video" autoplay loop muted playsinline preload="auto">
            <source src="hero.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>

        <div class="hero-content">
            <h1 class="hero-title">Bersama, Kita Temukan</h1>
            <p class="hero-subtitle">
                Gotemu menghubungkan pemilik dan penemu barang di lingkungan Fakultas Teknik secara aman, cepat, dan terverifikasi.
            </p>
            <?php if ($pengguna['peran'] === 'admin'): ?>
                <a href="tambah.php" class="btn-orange-pill">Catat Laporan Baru &rarr;</a>
            <?php else: ?>
                <a href="#katalog" class="btn-orange-pill" id="btnJelajah">Jelajahi Katalog Barang &rarr;</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- 3. Lembar Utama: "Satu Aplikasi, Beragam Solusi" + Katalog Kartu -->
    <div class="main-sheet">
        <div class="container">
            <?php if ($pesan !== null): ?>
                <div class="alert alert-success"><?= e($pesan) ?></div>
            <?php endif; ?>

            <!-- Bagian 3 Kartu Solusi (Nomor 1, 2, 3 Konsisten Seragam) -->
            <section id="solusi" style="margin-bottom: 56px;">
                <div class="section-head">
                    <span class="eyebrow-pill">Platform Lost &amp; Found Terpercaya</span>
                    <h2 class="section-title">Satu Aplikasi, <span class="text-orange">Beragam Solusi</span></h2>
                    <p class="section-subtitle">
                        Temukan barang hilang dengan mudah, laporkan penemuan dengan cepat, dan pantau status penanganan secara transparan.
                    </p>
                    <div class="section-divider"></div>
                </div>

                <div class="steps-grid">
                    <div class="step-card">
                        <span class="step-num">1</span>
                        <div class="step-icon">📄</div>
                        <h3 class="step-title">Lapor Kehilangan</h3>
                        <p class="step-desc">
                            Laporkan barang hilang atau temuan melalui pos operasional Satpam lengkap dengan foto, lokasi spesifik, dan nomor kasus resmi.
                        </p>
                    </div>

                    <div class="step-card">
                        <span class="step-num">2</span>
                        <div class="step-icon">🔍</div>
                        <h3 class="step-title">Temukan Barang</h3>
                        <p class="step-desc">
                            Telusuri katalog ringkasan publik berdasarkan kategori barang, lokasi kejadian, waktu pelaporan, serta informasi hadiah penemu.
                        </p>
                    </div>

                    <div class="step-card">
                        <span class="step-num">3</span>
                        <div class="step-icon">🛡️</div>
                        <h3 class="step-title">Terhubung Aman</h3>
                        <p class="step-desc">
                            Verifikasi kepemilikan dan serah terima barang dikelola secara aman tanpa mengekspos data pribadi sensitif ke publik.
                        </p>
                    </div>
                </div>
            </section>

            <!-- 4. Bagian Katalog Barang ("Temukan & Bantu Sesama Pengguna") -->
            <section id="katalog" class="catalog-section">
                <div class="section-head">
                    <span class="eyebrow-pill">Laporan Kehilangan &amp; Temuan Terbaru</span>
                    <h2 class="section-title">Temukan &amp; Bantu <span class="text-orange">Sesama Pengguna</span></h2>
                    <p class="section-subtitle">
                        Bantu temukan barang dan dokumen penting yang hilang di lingkungan kampus. Laporkan temuan Anda dan dapatkan apresiasi jika berhasil mengembalikan.
                    </p>
                </div>

                <!-- Pill Filter Kategori -->
                <div class="category-Variable-pills">
                    <a href="daftar.php?kat=Semua#katalog" class="cat-pill <?= $filterKategori === 'Semua' ? 'active' : '' ?>">Semua (<?= count($daftar) ?>)</a>
                    <?php foreach (DAFTAR_KATEGORI as $kat): ?>
                        <a href="daftar.php?kat=<?= urlencode($kat) ?>#katalog" class="cat-pill <?= $filterKategori === $kat ? 'active' : '' ?>">
                            <?= e($kat) ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Grid Kartu Katalog 4 Kolom -->
                <div class="catalog-grid">
                    <?php
                    $jumlahTampil = 0;
                    foreach ($daftar as $baris):
                        if ($filterKategori !== 'Semua' && $baris['kategori'] !== $filterKategori) {
                            continue;
                        }
                        $jumlahTampil++;
                        $kodeKasus  = '#GTM-' . str_pad((string) (int) $baris['id'], 3, '0', STR_PAD_LEFT);
                        $inisial    = mb_strtoupper(mb_substr($baris['pencatat'], 0, 1));
                        $adaImbalan = ((int) $baris['imbalan'] > 0);
                        $kelasKat   = 'kat-' . strtolower($baris['kategori']);
                    ?>
                        <a href="detail.php?id=<?= (int) $baris['id'] ?>" class="item-card">
                            <div class="card-media">
                                <?php if (!empty($baris['foto_barang']) && file_exists(__DIR__ . '/' . $baris['foto_barang'])): ?>
                                    <img src="<?= e($baris['foto_barang']) ?>" alt="<?= e($baris['nama_barang']) ?>">
                                <?php else: ?>
                                    <div class="card-visual-fallback <?= e($kelasKat) ?>">
                                        <span class="fallback-code"><?= e($kodeKasus) ?></span>
                                        <span class="fallback-cat"><?= e($baris['kategori']) ?></span>
                                    </div>
                                <?php endif; ?>

                                <!-- Badge Kiri Atas: Kategori -->
                                <span class="pill-top-left"><?= e($baris['kategori']) ?></span>

                                <!-- Badge Kanan Atas: Status Hilang / Ditemukan -->
                                <span class="pill-top-right <?= $baris['status'] === 'Ditemukan' ? 'status-ditemukan' : 'status-hilang' ?>">
                                    <?= e($baris['status']) ?>
                                </span>

                                <!-- Badge Kanan Bawah: Hadiah (bila imbalan > 0) -->
                                <?php if ($adaImbalan): ?>
                                    <span class="pill-bottom-right">🎁 Hadiah</span>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <h3 class="card-title"><?= e($baris['nama_barang']) ?></h3>
                                <p class="card-desc"><?= e($baris['deskripsi']) ?></p>

                                <div class="card-meta-list">
                                    <div class="card-meta-item">
                                        <span class="meta-icon">📍</span>
                                        <span><?= e($baris['lokasi']) ?></span>
                                    </div>
                                    <div class="card-meta-item">
                                        <span class="meta-icon">🕒</span>
                                        <span><?= e($baris['status']) ?>: <?= e($baris['tanggal_kejadian']) ?></span>
                                    </div>
                                </div>

                                <!-- Nama Pemilik/Pencatat dari hasil JOIN -->
                                <div class="card-author">
                                    <span class="author-avatar"><?= e($inisial) ?></span>
                                    <span class="author-name"><?= e($baris['pencatat']) ?></span>
                                </div>

                                <!-- Footer Kartu: Bantuan Sosial (Gratis) vs Hadiah (Rp) -->
                                <div class="card-footer">
                                    <div>
                                        <?php if ($adaImbalan): ?>
                                            <div class="reward-type"><span class="gift">🎁</span> Hadiah</div>
                                            <div class="reward-amount is-paid"><?= e(formatRupiah((int) $baris['imbalan'])) ?></div>
                                        <?php else: ?>
                                            <div class="reward-type"><span class="heart">❤️</span> Bantuan Sosial</div>
                                            <div class="reward-amount">Gratis</div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="card-detail-btn">Detail &rarr;</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if ($jumlahTampil === 0): ?>
                    <div class="panel-card" style="text-align: center; margin-top: 16px;">
                        <p style="color: var(--slate-500);">Belum ada laporan pada kategori <strong><?= e($filterKategori) ?></strong>.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <script>
        // Memindahkan kapsul oranye HANYA saat menu diklik atau sesuai hash URL aktif
        (function () {
            const navItems = document.querySelectorAll('#mainNav .nav-pill-item');

            function aktifkanMenuBerdasarkanHash() {
                const hash = window.location.hash;
                if (hash === '#katalog') {
                    navItems.forEach(el => el.classList.remove('active'));
                    const elKatalog = document.querySelector('#mainNav [data-nav="katalog"]');
                    if (elKatalog) elKatalog.classList.add('active');
                } else if (hash === '#solusi') {
                    navItems.forEach(el => el.classList.remove('active'));
                    const elSolusi = document.querySelector('#mainNav [data-nav="solusi"]');
                    if (elSolusi) elSolusi.classList.add('active');
                }
            }

            navItems.forEach(item => {
                item.addEventListener('click', function () {
                    navItems.forEach(el => el.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            window.addEventListener('hashchange', aktifkanMenuBerdasarkanHash);
            aktifkanMenuBerdasarkanHash();
        })();
    </script>
    <!-- 5. Footer Profesional Gotemu -->
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-col brand-col">
                <div class="brand">
                    <span class="brand-logo">G</span>
                    <span>Gotemu</span>
                </div>
                <p class="footer-desc">
                    Sistem Informasi Pengelolaan Barang Hilang &amp; Temuan (Lost and Found) Terpadu Fakultas Teknik Universitas Jenderal Soedirman.
                </p>
                <p class="footer-copyright">
                    &copy; 2026 Tim Pengembang Gotemu
                </p>
            </div>

            <div class="footer-col">
                <h4 class="footer-heading">Navigasi Utama</h4>
                <ul class="footer-links">
                    <li><a href="daftar.php">Beranda Utama</a></li>
                    <li><a href="#katalog">Katalog Lost &amp; Found</a></li>
                    <li><a href="#solusi">Alur &amp; Prosedur</a></li>
                    <?php if ($pengguna['peran'] === 'admin'): ?>
                        <li><a href="tambah.php">Pos Satpam (+ Laporan)</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-col">
                <h4 class="footer-heading">Pusat Bantuan &amp; Pos</h4>
                <ul class="footer-links">
                    <li><a href="#katalog">Cek Status Kasus</a></li>
                    <li><a href="#solusi">Kebijakan Privasi Publik</a></li>
                    <li><span style="color: #94a3b8; font-size: 13.5px;">Gedung Dekanat FT UNSOED</span></li>
                    <li><span style="color: #94a3b8; font-size: 13.5px;">Purbalingga, Jawa Tengah</span></li>
                </ul>
            </div>
        </div>
    </footer>
</body>
</html>