<?php
// detail.php - Operasi READ: Menampilkan satu data kasus berdasarkan id dari URL (Spesifikasi Bagian 4.3)
require_once __DIR__ . '/sesi.php';
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/validasi.php';

wajibLogin();
$pengguna = penggunaAktif();

// Validasi parameter id dari URL sebagai bilangan bulat (integer positif)
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    http_response_code(400);
    exit('<h1>400 Bad Request</h1><p>Parameter ID kasus tidak valid.</p><p><a href="daftar.php">Kembali ke Daftar Kasus</a></p>');
}

try {
    // Prepared statement dengan parameter bertipe integer ('i')
    $sql = 'SELECT k.*, u.nama AS pencatat, u.username AS username_pencatat
            FROM kasus_barang AS k
            JOIN pengguna AS u ON u.id = k.pengguna_id
            WHERE k.id = ?';
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $kasus = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (mysqli_sql_exception $ex) {
    error_log('[Read Detail Gagal] ' . $ex->getMessage());
    http_response_code(500);
    exit('Maaf, detail kasus belum dapat ditampilkan.');
}

if ($kasus === null) {
    http_response_code(404);
    exit('<h1>404 Not Found</h1><p>Data kasus yang Anda cari tidak ditemukan.</p><p><a href="daftar.php">Kembali ke Daftar Kasus</a></p>');
}

$nomorKasus = '#GTM-' . str_pad((string) (int) $kasus['id'], 3, '0', STR_PAD_LEFT);
$adaImbalan = ((int) $kasus['imbalan'] > 0);
$kelasKat   = 'kat-' . strtolower($kasus['kategori']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kasus <?= e($nomorKasus) ?> — Gotemu</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a href="daftar.php" class="brand">
                <span class="brand-logo">G</span>
                <span>Gotemu</span>
            </a>

            <nav class="nav-pills">
                <a href="daftar.php" class="nav-pill-item">Beranda</a>
                <a href="daftar.php#katalog" class="nav-pill-item active">Detail Kasus</a>
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

    <main class="container" style="padding-top: 36px; padding-bottom: 80px;">
        <p style="margin-bottom: 22px;">
            <a href="daftar.php#katalog" class="btn btn-secondary">&larr; Kembali ke Katalog Barang</a>
        </p>

        <div class="detail-layout">
            <!-- Kolom Kiri: Gambar & Kartu Hadiah -->
            <aside class="media-showcase">
                <div class="media-frame">
                    <?php if (!empty($kasus['foto_barang']) && file_exists(__DIR__ . '/' . $kasus['foto_barang'])): ?>
                        <img src="<?= e($kasus['foto_barang']) ?>" alt="<?= e($kasus['nama_barang']) ?>">
                    <?php else: ?>
                        <div class="card-visual-fallback <?= e($kelasKat) ?>">
                            <span class="fallback-code"><?= e($nomorKasus) ?></span>
                            <span class="fallback-cat"><?= e($kasus['kategori']) ?></span>
                        </div>
                    <?php endif; ?>
                    <span class="pill-top-left"><?= e($kasus['kategori']) ?></span>
                    <span class="pill-top-right <?= $kasus['status'] === 'Ditemukan' ? 'status-ditemukan' : 'status-hilang' ?>">
                        <?= e($kasus['status']) ?>
                    </span>
                </div>

                <div class="reward-highlight-box">
                    <?php if ($adaImbalan): ?>
                        <div class="reward-type"><span class="gift">🎁</span> Apresiasi Hadiah Penemu</div>
                        <div class="reward-amount is-paid" style="font-size: 22px;"><?= e(formatRupiah((int) $kasus['imbalan'])) ?></div>
                    <?php else: ?>
                        <div class="reward-type"><span class="heart">❤️</span> Kategori Pengembalian</div>
                        <div class="reward-amount" style="font-size: 20px;">Bantuan Sosial (Gratis)</div>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- Kolom Kanan: Rincian Lengkap Kasus -->
            <article class="panel-card">
                <span class="eyebrow-pill">Nomor Kasus Resmi: <?= e($nomorKasus) ?></span>
                <h1 class="section-title" style="font-size: 28px; margin-bottom: 6px;"><?= e($kasus['nama_barang']) ?></h1>
                <p style="color: var(--slate-500); font-size: 13.5px;">
                    Dicatat oleh <strong><?= e($kasus['pencatat']) ?></strong> (@<?= e($kasus['username_pencatat']) ?>) pada <?= e($kasus['dibuat_pada']) ?>
                </p>

                <div class="dossier-grid">
                    <div class="dossier-item">
                        <div class="dossier-label">Status Laporan</div>
                        <div class="dossier-val"><?= e($kasus['status']) ?></div>
                    </div>

                    <div class="dossier-item">
                        <div class="dossier-label">Tahap Penanganan (SRS 2.4)</div>
                        <div class="dossier-val" style="color: var(--orange-600);"><?= e($kasus['status_penanganan']) ?></div>
                    </div>

                    <div class="dossier-item">
                        <div class="dossier-label">Lokasi Kejadian / Temuan</div>
                        <div class="dossier-val">📍 <?= e($kasus['lokasi']) ?></div>
                    </div>

                    <div class="dossier-item">
                        <div class="dossier-label">Tanggal Kejadian</div>
                        <div class="dossier-val">🕒 <?= e($kasus['tanggal_kejadian']) ?></div>
                    </div>

                    <div class="dossier-item">
                        <div class="dossier-label">Kategori Barang</div>
                        <div class="dossier-val"><?= e($kasus['kategori']) ?></div>
                    </div>

                    <div class="dossier-item">
                        <div class="dossier-label">Label Penyimpanan (FR-06)</div>
                        <div class="dossier-val" style="font-family: var(--font-mono);"><?= e($kasus['label_penyimpanan']) ?></div>
                    </div>
                </div>

                <div>
                    <div class="dossier-label">Deskripsi &amp; Ciri Umum Publik (SRS BR-02)</div>
                    <div style="margin-top: 8px; padding: 18px; border-radius: 14px; background: var(--bg-body); border: 1px solid var(--slate-200); white-space: pre-line; color: var(--slate-600);">
                        <?= e($kasus['deskripsi']) ?>
                    </div>
                </div>
            </article>
        </div>
    </main>
</body>
</html>