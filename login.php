<?php
// login.php - Autentikasi pengguna berbasis tabel basis data (Spesifikasi Bagian 4.1)
require_once __DIR__ . '/sesi.php';
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/validasi.php';

if (penggunaAktif() !== null) {
    header('Location: daftar.php');
    exit;
}

$galat    = null;
$info     = isset($_GET['keluar']) ? 'Sesi Anda telah diakhiri dengan aman.' : ambilFlash();
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = ambilPost('username');
    $sandi    = $_POST['sandi'] ?? '';

    if ($username === '' || $sandi === '') {
        $galat = 'Username dan kata sandi wajib diisi.';
    } else {
        try {
            $stmt = $db->prepare('SELECT id, nama, peran, hash_sandi FROM pengguna WHERE username = ?');
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $pengguna = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($pengguna === null || !password_verify($sandi, $pengguna['hash_sandi'])) {
                $galat = 'Username atau kata sandi salah.';
            } else {
                session_regenerate_id(true);
                $_SESSION['id']       = (int) $pengguna['id'];
                $_SESSION['username'] = $username;
                $_SESSION['nama']     = $pengguna['nama'];
                $_SESSION['peran']    = $pengguna['peran'];

                setFlash('Selamat datang kembali, ' . $pengguna['nama'] . '.');
                header('Location: daftar.php');
                exit;
            }
        } catch (mysqli_sql_exception $ex) {
            error_log('[Login Gagal] ' . $ex->getMessage());
            $galat = 'Terjadi gangguan sistem saat memproses login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem — Gotemu</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <span class="brand-mark">GOTEMU</span>
                <span class="brand-sub">Lost &amp; Found Registry — FT UNSOED</span>
            </div>
        </div>
    </header>

    <main class="auth-wrapper">
        <div class="panel">
            <div class="page-eyebrow">AUTENTIKASI SISTEM</div>
            <h1 class="page-title" style="margin-bottom: 6px;">Masuk ke Portal</h1>
            <p class="page-desc" style="margin-bottom: 22px;">Gunakan akun Satpam (Admin) atau Civitas (Anggota) untuk mengakses register kasus.</p>

            <?php if ($info !== null): ?>
                <div class="alert alert-success"><?= e($info) ?></div>
            <?php endif; ?>

            <?php if ($galat !== null): ?>
                <div class="alert alert-danger"><?= e($galat) ?></div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= e($username) ?>" autocomplete="username" autofocus>
                </div>

                <div class="form-group">
                    <label for="sandi">Kata Sandi</label>
                    <input type="password" id="sandi" name="sandi" autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-block">Masuk ke Register</button>
            </form>

            <p style="margin-top: 18px; font-size: 13.5px; text-align: center; color: var(--text-secondary);">
                Belum memiliki akun anggota? <a href="registrasi.php" style="font-weight: 700; color: var(--text-primary); text-decoration: underline;">Daftar akun baru</a>
            </p>

            <div class="credential-box">
                <strong>Akun Pengujian (Sandi: <code>password</code>)</strong><br>
                • Petugas Satpam (Admin): <code>admin</code><br>
                • Mahasiswa (Anggota): <code>raffi</code>
            </div>
        </div>
    </main>
</body>
</html>