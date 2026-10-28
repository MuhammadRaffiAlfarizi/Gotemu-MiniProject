<?php
// koneksi.php
// Kredensial basis data terpusat di satu berkas (Spesifikasi Bagian 6).
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = ''; // Default XAMPP: root tanpa kata sandi
const DB_NAME = 'gotemu_db';

// Melempar setiap error MySQL sebagai mysqli_sql_exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Membuat objek koneksi mysqli dan mengatur pengkodean karakter utf8mb4
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    // Detail error teknis dicatat ke log server, tidak dibocorkan ke layar pengguna
    error_log('[Koneksi Database Gagal] ' . $ex->getCode() . ': ' . $ex->getMessage());
    http_response_code(500);
    exit('Maaf, sistem sedang mengalami gangguan koneksi basis data.');
}
