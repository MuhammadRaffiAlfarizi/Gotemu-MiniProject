<?php
// registrasi.php - Operasi CREATE untuk membuat akun pengguna baru (Spesifikasi Bagian 4.1)
require_once __DIR__ . '/sesi.php';
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/validasi.php';

if (penggunaAktif() !== null) {
    header('Location: daftar.php');
    exit;
}

$errors = [];
$input  = ['username' => '', 'nama' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['username'] = ambilPost('username');
    $input['nama']     = ambilPost('nama');
    $sandi             = $_POST['sandi'] ?? '';

    // 1) Validasi input registrasi di sisi server
    if ($err = validasiPanjang('Username', $input['username'], 3, 30)) {
        $errors['username'] = $err;
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $input['username'])) {
        $errors['username'] = 'Username hanya boleh berisi huruf, angka, dan garis bawah (_).';
    }

    if ($err = validasiPanjang('Nama Lengkap', $input['nama'], 3, 100)) {
        $errors['nama'] = $err;
    }

    if (mb_strlen($sandi) < 6) {
        $errors['sandi'] = 'Kata sandi wajib diisi minimal 6 karakter.';
    }

    // 2) Bila validasi lolos, hash kata sandi dan simpan dengan Prepared Statement
    if (empty($errors)) {
        $hash = password_hash($sandi, PASSWORD_DEFAULT);
        try {
            $stmt = $db->prepare(
                'INSERT INTO pengguna (username, nama, peran, hash_sandi) VALUES (?, ?, "anggota", ?)'
            );
            $stmt->bind_param('sss', $input['username'], $input['nama'], $hash);
            $stmt->execute();
            $stmt->close();

            setFlash('Registrasi akun berhasil. Silakan masuk dengan akun baru Anda.');
            header('Location: login.php');
            exit;
        } catch (mysqli_sql_exception $ex) {
            // Menangkap pelanggaran UNIQUE kode 1062 bila username sudah dipakai (Materi Pertemuan 7)
            if ($ex->getCode() === 1062) {
                $errors['username'] = 'Username tersebut sudah terdaftar. Silakan gunakan username lain.';
            } else {
                error_log('[Registrasi Gagal] ' . $ex->getMessage());
                $errors['umum'] = 'Terjadi kesalahan sistem saat menyimpan akun.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun — Gotemu</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <span class="brand-mark">GOTEMU</span>
                <span class="brand-sub">Buku Register Lost &amp; Found Fakultas Teknik</span>
            </div>
        </div>
    </header>

    <main class="auth-wrapper">
        <div class="panel">
            <div class="page-eyebrow">PENDAFTARAN PENGGUNA LAYANAN</div>
            <h1 class="page-title" style="margin-bottom: 6px;">Buat Akun Anggota</h1>
            <p class="page-desc" style="margin-bottom: 20px;">Akun anggota digunakan untuk memantau ringkasan publik dan status penanganan kasus.</p>

            <?php if (isset($errors['umum'])): ?>
                <div class="alert alert-danger"><?= e($errors['umum']) ?></div>
            <?php endif; ?>

            <form method="post" action="registrasi.php">
                <div class="form-group">
                    <label for="username">Username <span class="label-hint">(3–30 karakter, huruf/angka)</span></label>
                    <input type="text" id="username" name="username" value="<?= e($input['username']) ?>" class="<?= isset($errors['username']) ? 'input-error' : '' ?>">
                    <?php if (isset($errors['username'])): ?>
                        <span class="field-error"><?= e($errors['username']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?= e($input['nama']) ?>" class="<?= isset($errors['nama']) ? 'input-error' : '' ?>">
                    <?php if (isset($errors['nama'])): ?>
                        <span class="field-error"><?= e($errors['nama']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="sandi">Kata Sandi <span class="label-hint">(Min. 6 karakter)</span></label>
                    <input type="password" id="sandi" name="sandi" class="<?= isset($errors['sandi']) ? 'input-error' : '' ?>">
                    <?php if (isset($errors['sandi'])): ?>
                        <span class="field-error"><?= e($errors['sandi']) ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-block">Daftarkan Akun</button>
            </form>

            <p style="margin-top: 18px; font-size: 13.5px; text-align: center;">
                Sudah memiliki akun? <a href="login.php">Kembali ke halaman masuk</a>
            </p>
        </div>
    </main>
</body>
</html>