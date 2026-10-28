<?php
/**
 * tampilan.php
 * Kerangka halaman bersama (header, navigasi, pesan flash, footer).
 * Semua nilai dinamis tetap dibungkus e().
 */
declare(strict_types=1);

/** Badge status: bentuk dan warna berbeda agar tidak hanya bergantung pada warna. */
function badge_status(string $status): string
{
    $kelas = $status === 'Hilang' ? 'status-hilang' : 'status-ditemukan';
    return '<span class="status ' . $kelas . '">' . e($status) . '</span>';
}

function awal_halaman(string $judul, string $aktif = ''): void
{
    $pengguna = pengguna_aktif();
    $flash = flash_ambil();
    ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($judul) ?> | GoTemu</title>
  <link rel="icon" href="data:,">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<a class="lewati" href="#isi">Lewati ke isi halaman</a>
<header class="atas">
  <div class="atas-dalam">
    <a class="merek" href="daftar.php" aria-label="GoTemu, ke daftar barang"><span class="merek-go">Go</span><span class="merek-temu">Temu</span></a>
    <nav class="menu" aria-label="Navigasi utama">
      <?php if ($pengguna !== null): ?>
        <a href="daftar.php"<?= $aktif === 'daftar' ? ' aria-current="page"' : '' ?>>Daftar barang</a>
        <?php if ($pengguna['peran'] === 'admin'): ?>
          <a href="tambah.php"<?= $aktif === 'tambah' ? ' aria-current="page"' : '' ?>>Catat barang</a>
        <?php endif; ?>
        <span class="pengguna">
          <span class="pengguna-nama"><?= e($pengguna['nama']) ?></span>
          <span class="peran"><?= $pengguna['peran'] === 'admin' ? 'Satpam' : 'Pengguna layanan' ?></span>
        </span>
        <form method="post" action="logout.php" class="form-keluar">
          <input type="hidden" name="csrf" value="<?= e(token_csrf()) ?>">
          <button type="submit" class="tombol tombol-garis tombol-kecil">Keluar</button>
        </form>
      <?php else: ?>
        <a href="login.php"<?= $aktif === 'login' ? ' aria-current="page"' : '' ?>>Masuk</a>
        <a href="registrasi.php"<?= $aktif === 'registrasi' ? ' aria-current="page"' : '' ?>>Buat akun</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main id="isi" class="isi">
<?php if ($flash !== null): ?>
  <p class="pesan pesan-sukses" role="status"><?= e($flash) ?></p>
<?php endif;
}

function akhir_halaman(): void
{
    ?>
</main>
<footer class="bawah">
  <p>GoTemu, sistem lost and found Fakultas Teknik Unsoed. Mini project Pemrograman Web I.</p>
</footer>
</body>
</html>
<?php
}
