<?php
// sesi.php
// Mengatur parameter keamanan cookie sesi sebelum memulai session (Materi Pertemuan 6)
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true, // Mencegah pembacaan cookie sesi oleh JavaScript (anti pencurian sesi)
    'samesite' => 'Lax',
]);
session_start();

// Fungsi sanitasi keluaran (escaping) untuk mencegah serangan XSS (Spesifikasi Bagian 4.3)
function e(?string $teks): string
{
    return htmlspecialchars($teks ?? '', ENT_QUOTES, 'UTF-8');
}

// Menyimpan pesan konfirmasi satu kali tampil (Flash Message untuk pola PRG)
function setFlash(string $pesan): void
{
    $_SESSION['flash'] = $pesan;
}

// Mengambil sekaligus menghapus pesan Flash agar hilang saat halaman dimuat ulang
function ambilFlash(): ?string
{
    $pesan = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $pesan;
}

// Mengambil data pengguna yang sedang login dari session
function penggunaAktif(): ?array
{
    if (!isset($_SESSION['id'])) {
        return null;
    }
    return [
        'id'       => $_SESSION['id'],
        'username' => $_SESSION['username'],
        'nama'     => $_SESSION['nama'],
        'peran'    => $_SESSION['peran'],
    ];
}

// Penjaga halaman: wajib login untuk mengakses halaman internal
function wajibLogin(): void
{
    if (penggunaAktif() === null) {
        setFlash('Silakan masuk terlebih dahulu untuk mengakses halaman tersebut.');
        header('Location: login.php');
        exit;
    }
}

// Penjaga otorisasi peran: membatasi halaman khusus Admin (Spesifikasi Bagian 4.1 & 7.1)
function wajibPeran(string $peranWajib): void
{
    wajibLogin();
    if (($_SESSION['peran'] ?? '') !== $peranWajib) {
        http_response_code(403);
        exit('<h1>403 Forbidden</h1><p>Akses ditolak: Anda tidak memiliki hak akses (khusus ' . e($peranWajib) . ') untuk membuka halaman ini.</p><p><a href="daftar.php">Kembali ke Daftar Kasus</a></p>');
    }
}