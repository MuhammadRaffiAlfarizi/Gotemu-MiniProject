<?php
// tambah.php - Operasi CREATE: Formulir tambah data khusus Admin/Satpam (Spesifikasi Bagian 4.2)
require_once __DIR__ . '/sesi.php';
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/validasi.php';

wajibPeran('admin');
$pengguna = penggunaAktif();

$errors = [];
$input  = [
    'nama_barang'       => '',
    'kategori'          => '',
    'status'            => '',
    'lokasi'            => '',
    'imbalan'           => '0',
    'deskripsi'         => '',
    'label_penyimpanan' => '-',
    'status_penanganan' => 'Diterima',
    'tanggal_kejadian'  => date('Y-m-d'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($input) as $kunci) {
        $input[$kunci] = ambilPost($kunci);
    }

    if ($err = validasiPanjang('Nama Barang', $input['nama_barang'], 3, 150)) {
        $errors['nama_barang'] = $err;
    }
    if ($err = validasiPanjang('Deskripsi / Ciri Umum', $input['deskripsi'], 5, 2000)) {
        $errors['deskripsi'] = $err;
    }
    if ($err = validasiImbalan($input['imbalan'])) {
        $errors['imbalan'] = $err;
    }
    if ($err = validasiPilihan('Kategori Barang', $input['kategori'], DAFTAR_KATEGORI)) {
        $errors['kategori'] = $err;
    }
    if ($err = validasiPilihan('Status Laporan', $input['status'], DAFTAR_STATUS)) {
        $errors['status'] = $err;
    }
    if ($err = validasiPilihan('Status Penanganan', $input['status_penanganan'], DAFTAR_PENANGANAN)) {
        $errors['status_penanganan'] = $err;
    }
    if ($err = validasiLokasiLostFound($input['status'], $input['lokasi'])) {
        $errors['lokasi'] = $err;
    }
    if ($err = validasiTanggalKejadian($input['tanggal_kejadian'])) {
        $errors['tanggal_kejadian'] = $err;
    }
    if ($input['label_penyimpanan'] === '') {
        $input['label_penyimpanan'] = '-';
    } elseif ($err = validasiPanjang('Label Penyimpanan', $input['label_penyimpanan'], 1, 50)) {
        $errors['label_penyimpanan'] = $err;
    }

    $jalurFoto = null;
    if (empty($errors)) {
        $jalurFoto = prosesUnggahFoto('foto_barang', $errors);
    }

    if (empty($errors)) {
        $penggunaId     = (int) $_SESSION['id'];
        $nominalImbalan = (int) $input['imbalan'];

        try {
            $sql = 'INSERT INTO kasus_barang
                        (pengguna_id, nama_barang, kategori, status, lokasi,
                         deskripsi, imbalan, foto_barang, label_penyimpanan,
                         status_penanganan, tanggal_kejadian)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $stmt = $db->prepare($sql);
            $stmt->bind_param(
                'isssssissss',
                $penggunaId,
                $input['nama_barang'],
                $input['kategori'],
                $input['status'],
                $input['lokasi'],
                $input['deskripsi'],
                $nominalImbalan,
                $jalurFoto,
                $input['label_penyimpanan'],
                $input['status_penanganan'],
                $input['tanggal_kejadian']
            );
            $stmt->execute();
            $stmt->close();

            setFlash('Laporan "' . $input['nama_barang'] . '" berhasil diterbitkan ke katalog.');
            header('Location: daftar.php');
            exit;
        } catch (mysqli_sql_exception $ex) {
            error_log('[Create Kasus Gagal] ' . $ex->getMessage());
            $errors['umum'] = 'Maaf, terjadi kesalahan saat menyimpan data kasus.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan Baru — Gotemu</title>
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
                <a href="daftar.php#katalog" class="nav-pill-item">Lost &amp; Found</a>
                <a href="daftar.php#solusi" class="nav-pill-item">Alur Layanan</a>
                <a href="tambah.php" class="nav-pill-item active">+ Buat Laporan</a>
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

    <main class="container-narrow">
        <div class="panel-card">
            <span class="eyebrow-pill">Formulir Resmi Petugas (Admin)</span>
            <h1 class="section-title" style="font-size: 28px; margin-bottom: 6px;">Catat Kasus Kehilangan / Temuan</h1>
            <p style="color: var(--slate-500); margin-bottom: 24px;">
                Jika status <strong>Ditemukan</strong>, kolom Lokasi wajib diisi minimal 10 karakter secara spesifik.
            </p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    Terdapat <strong><?= count($errors) ?> kesalahan input</strong> pada formulir. Silakan periksa kembali kolom berwarna merah.
                </div>
            <?php endif; ?>

            <form method="post" action="tambah.php" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="nama_barang">Nama Barang <span class="label-hint">(3–150 karakter)</span></label>
                        <input type="text" id="nama_barang" name="nama_barang" value="<?= e($input['nama_barang']) ?>" placeholder="Contoh: Kartu Tanda Mahasiswa (KTM) Unsoed" class="<?= isset($errors['nama_barang']) ? 'input-error' : '' ?>">
                        <?php if (isset($errors['nama_barang'])): ?>
                            <span class="field-error"><?= e($errors['nama_barang']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="status">Status Laporan</label>
                        <select id="status" name="status" class="<?= isset($errors['status']) ? 'input-error' : '' ?>">
                            <option value="">-- Pilih Status --</option>
                            <?php foreach (DAFTAR_STATUS as $st): ?>
                                <option value="<?= e($st) ?>" <?= $input['status'] === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['status'])): ?>
                            <span class="field-error"><?= e($errors['status']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="kategori">Kategori Barang</label>
                        <select id="kategori" name="kategori" class="<?= isset($errors['kategori']) ? 'input-error' : '' ?>">
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach (DAFTAR_KATEGORI as $kat): ?>
                                <option value="<?= e($kat) ?>" <?= $input['kategori'] === $kat ? 'selected' : '' ?>><?= e($kat) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['kategori'])): ?>
                            <span class="field-error"><?= e($errors['kategori']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group full-width">
                        <label for="lokasi">Lokasi Kejadian / Ditemukan <span class="label-hint">(Min. 10 karakter bila Ditemukan)</span></label>
                        <input type="text" id="lokasi" name="lokasi" value="<?= e($input['lokasi']) ?>" placeholder="Contoh: Meja Baris Kedua Ruang Lab Pemrograman 1" class="<?= isset($errors['lokasi']) ? 'input-error' : '' ?>">
                        <?php if (isset($errors['lokasi'])): ?>
                            <span class="field-error"><?= e($errors['lokasi']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="imbalan">Hadiah / Imbalan (Rp) <span class="label-hint">(Isi 0 = Gratis)</span></label>
                        <input type="number" id="imbalan" name="imbalan" min="0" max="50000000" value="<?= e($input['imbalan']) ?>" class="<?= isset($errors['imbalan']) ? 'input-error' : '' ?>">
                        <?php if (isset($errors['imbalan'])): ?>
                            <span class="field-error"><?= e($errors['imbalan']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="tanggal_kejadian">Tanggal Kejadian</label>
                        <input type="date" id="tanggal_kejadian" name="tanggal_kejadian" value="<?= e($input['tanggal_kejadian']) ?>" class="<?= isset($errors['tanggal_kejadian']) ? 'input-error' : '' ?>">
                        <?php if (isset($errors['tanggal_kejadian'])): ?>
                            <span class="field-error"><?= e($errors['tanggal_kejadian']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="label_penyimpanan">Label Penyimpanan <span class="label-hint">(FR-06)</span></label>
                        <input type="text" id="label_penyimpanan" name="label_penyimpanan" value="<?= e($input['label_penyimpanan']) ?>" placeholder="LEMARI-A01 atau -" class="<?= isset($errors['label_penyimpanan']) ? 'input-error' : '' ?>">
                        <?php if (isset($errors['label_penyimpanan'])): ?>
                            <span class="field-error"><?= e($errors['label_penyimpanan']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="status_penanganan">Tahap Penanganan</label>
                        <select id="status_penanganan" name="status_penanganan" class="<?= isset($errors['status_penanganan']) ? 'input-error' : '' ?>">
                            <?php foreach (DAFTAR_PENANGANAN as $sp): ?>
                                <option value="<?= e($sp) ?>" <?= $input['status_penanganan'] === $sp ? 'selected' : '' ?>><?= e($sp) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['status_penanganan'])): ?>
                            <span class="field-error"><?= e($errors['status_penanganan']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group full-width">
                        <label for="foto_barang">Unggah Foto Barang <span class="label-hint">(Opsional · JPG/PNG/WEBP maks. 2 MB)</span></label>
                        <div class="upload-zone">
                            <input type="file" id="foto_barang" name="foto_barang" accept="image/jpeg,image/png,image/webp">
                        </div>
                        <?php if (isset($errors['foto_barang'])): ?>
                            <span class="field-error"><?= e($errors['foto_barang']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group full-width">
                        <label for="deskripsi">Deskripsi &amp; Kronologi Singkat <span class="label-hint">(Maks. 2000 karakter)</span></label>
                        <textarea id="deskripsi" name="deskripsi" rows="4" class="<?= isset($errors['deskripsi']) ? 'input-error' : '' ?>"><?= e($input['deskripsi']) ?></textarea>
                        <?php if (isset($errors['deskripsi'])): ?>
                            <span class="field-error"><?= e($errors['deskripsi']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 10px;">
                    <button type="submit" class="btn">Terbitkan Laporan &rarr;</button>
                    <a href="daftar.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>