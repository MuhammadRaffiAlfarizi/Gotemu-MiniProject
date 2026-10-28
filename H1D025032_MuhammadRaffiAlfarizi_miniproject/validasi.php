<?php
// validasi.php
// Daftar putih (whitelist) untuk kolom bertipe ENUM (Spesifikasi Bagian 3 & 5)
const DAFTAR_KATEGORI   = ['Elektronik', 'Dokumen', 'Pakaian', 'Aksesoris', 'Lainnya'];
const DAFTAR_STATUS     = ['Hilang', 'Ditemukan'];
const DAFTAR_PENANGANAN = ['Diterima', 'Dalam Penanganan', 'Dalam Verifikasi', 'Diserahterimakan', 'Ditutup'];

// Sanitasi input: merapikan spasi di ujung dan membuang tag HTML dari input (Pertemuan 4)
function bersihkanTeks(string $teks): string
{
    return trim(strip_tags($teks));
}

// Mengambil satu nilai dari $_POST yang langsung dibersihkan
function ambilPost(string $kunci): string
{
    return bersihkanTeks($_POST[$kunci] ?? '');
}

// Format angka menjadi mata uang Rupiah yang rapi
function formatRupiah(int $nominal): string
{
    if ($nominal <= 0) {
        return 'Tanpa Imbalan';
    }
    return 'Rp ' . number_format($nominal, 0, ',', '.');
}

// Validasi umum: wajib isi dan rentang panjang karakter (Spesifikasi Bagian 5)
function validasiPanjang(string $label, string $nilai, int $min, int $max): ?string
{
    if ($nilai === '') {
        return "$label wajib diisi.";
    }
    $panjang = mb_strlen($nilai);
    if ($panjang < $min || $panjang > $max) {
        return "$label harus terdiri dari $min sampai $max karakter (saat ini $panjang karakter).";
    }
    return null;
}

// Validasi daftar putih (whitelist) untuk kolom ENUM (Pertemuan 4 & 7)
function validasiPilihan(string $label, string $nilai, array $daftarSah): ?string
{
    if ($nilai === '') {
        return "$label wajib dipilih.";
    }
    if (!in_array($nilai, $daftarSah, true)) {
        return "Pilihan $label tidak sah.";
    }
    return null;
}

// Validasi aturan khusus tema Lost and Found (Spesifikasi Bagian 3):
// "Bila status = Ditemukan, kolom lokasi wajib diisi minimal 10 karakter."
function validasiLokasiLostFound(string $status, string $lokasi): ?string
{
    if ($lokasi === '') {
        return "Lokasi wajib diisi.";
    }
    $panjang = mb_strlen($lokasi);
    if ($status === 'Ditemukan' && $panjang < 10) {
        return "Untuk status 'Ditemukan', kolom Lokasi wajib diisi minimal 10 karakter secara spesifik (saat ini $panjang karakter).";
    }
    if ($panjang < 3 || $panjang > 150) {
        return "Lokasi harus terdiri dari 3 sampai 150 karakter.";
    }
    return null;
}

// Validasi angka untuk kolom Imbalan (Spesifikasi Bagian 5: Angka & Rentang)
function validasiImbalan(string $nilai): ?string
{
    if ($nilai === '') {
        return "Nominal imbalan wajib diisi (isi 0 bila tanpa imbalan).";
    }
    if (!ctype_digit($nilai) && !is_numeric($nilai)) {
        return "Imbalan harus berupa angka bulat tanpa titik atau huruf.";
    }
    $angka = filter_var($nilai, FILTER_VALIDATE_INT);
    if ($angka === false || $angka < 0 || $angka > 50000000) {
        return "Imbalan harus berupa bilangan bulat antara Rp 0 hingga Rp 50.000.000.";
    }
    return null;
}

// Validasi format tanggal kejadian
function validasiTanggalKejadian(string $tanggal): ?string
{
    if ($tanggal === '') {
        return "Tanggal kejadian wajib diisi.";
    }
    $obj = DateTime::createFromFormat('Y-m-d', $tanggal);
    if (!$obj || $obj->format('Y-m-d') !== $tanggal) {
        return "Format Tanggal Kejadian tidak valid.";
    }
    if ($tanggal > date('Y-m-d')) {
        return "Tanggal Kejadian tidak boleh melebihi tanggal hari ini.";
    }
    return null;
}

// Validasi dan proses unggah foto barang secara aman
function prosesUnggahFoto(string $kunciFile, array &$errors): ?string
{
    if (!isset($_FILES[$kunciFile]) || $_FILES[$kunciFile]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // Foto bersifat opsional
    }

    $file = $_FILES[$kunciFile];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[$kunciFile] = 'Gagal mengunggah gambar (pastikan ukuran file di bawah 2 MB).';
        return null;
    }

    // Batas ukuran maksimal 2 MB
    if ($file['size'] > 2 * 1024 * 1024) {
        $errors[$kunciFile] = 'Ukuran gambar terlalu besar. Maksimal 2 MB.';
        return null;
    }

    // Validasi whitelist tipe MIME gambar asli dari isi berkas
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $daftarMimeSah = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!array_key_exists($mime, $daftarMimeSah)) {
        $errors[$kunciFile] = 'Format gambar tidak didukung. Gunakan file JPG, PNG, atau WEBP.';
        return null;
    }

    // Buat folder uploads/ secara otomatis bila belum ada
    $folderTujuan = __DIR__ . '/uploads';
    if (!is_dir($folderTujuan)) {
        mkdir($folderTujuan, 0755, true);
    }

    $namaBaru = 'gtm_' . bin2hex(random_bytes(8)) . '.' . $daftarMimeSah[$mime];
    $jalurSimpan = $folderTujuan . '/' . $namaBaru;

    if (!move_uploaded_file($file['tmp_name'], $jalurSimpan)) {
        $errors[$kunciFile] = 'Gagal menyimpan file gambar ke server.';
        return null;
    }

    return 'uploads/' . $namaBaru;
}