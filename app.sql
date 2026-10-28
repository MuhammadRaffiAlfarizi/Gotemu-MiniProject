-- =========================================================================
-- app.sql
-- Skema Basis Data Aplikasi "Gotemu" (Lost and Found Kampus)
-- Memuat fitur Imbalan (Reward) & Upload Foto Barang
-- =========================================================================

CREATE DATABASE IF NOT EXISTS gotemu_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gotemu_db;

DROP TABLE IF EXISTS kasus_barang;
DROP TABLE IF EXISTS pengguna;

-- 1. Tabel pengguna (Akun Admin/Satpam dan Anggota/Mahasiswa)
CREATE TABLE pengguna (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(30) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  peran ENUM('admin','anggota') NOT NULL DEFAULT 'anggota',
  hash_sandi VARCHAR(255) NOT NULL,
  dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel entitas: kasus_barang (Dilengkapi kolom imbalan dan foto_barang)
CREATE TABLE kasus_barang (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pengguna_id INT UNSIGNED NOT NULL,
  nama_barang VARCHAR(150) NOT NULL,
  kategori ENUM('Elektronik','Dokumen','Pakaian','Aksesoris','Lainnya') NOT NULL,
  status ENUM('Hilang','Ditemukan') NOT NULL,
  lokasi VARCHAR(150) NOT NULL,
  deskripsi VARCHAR(2000) NOT NULL,
  imbalan INT UNSIGNED NOT NULL DEFAULT 0,
  foto_barang VARCHAR(255) DEFAULT NULL,
  label_penyimpanan VARCHAR(50) NOT NULL DEFAULT '-',
  status_penanganan ENUM('Diterima','Dalam Penanganan','Dalam Verifikasi','Diserahterimakan','Ditutup') NOT NULL DEFAULT 'Diterima',
  tanggal_kejadian DATE NOT NULL,
  dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pengguna_id) REFERENCES pengguna (id)
);

-- 3. Data Awal Pengguna (Sandi untuk semua akun: password)
INSERT INTO pengguna (username, nama, peran, hash_sandi) VALUES
('admin', 'Petugas Satpam FT', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('raffi', 'Muhammad Raffi Alfarizi', 'anggota', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('hanifah', 'Hanifah Silmi Fajri', 'anggota', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 4. Data Awal Entitas (Minimal 5 baris data kasus barang beserta contoh imbalan)
INSERT INTO kasus_barang (pengguna_id, nama_barang, kategori, status, lokasi, deskripsi, imbalan, foto_barang, label_penyimpanan, status_penanganan, tanggal_kejadian) VALUES
(1, 'Helm KYT Galaxy Hitam Doff', 'Aksesoris', 'Hilang', 'Parkiran Motor Gedung E', 'Helm warna hitam doff ukuran L, kaca visor bening sedikit tergores di sisi kiri. Bagi yang menemukan mohon hubungi pos satpam.', 150000, NULL, '-', 'Dalam Penanganan', '2026-10-10'),
(1, 'Dompet Kulit Cokelat Tua', 'Dokumen', 'Ditemukan', 'Meja Kantin Fakultas Teknik', 'Dompet lipat kulit warna cokelat ditemukan di dekat kasir kantin. Ciri khusus dan isi disimpan petugas untuk verifikasi klaim.', 0, NULL, 'LEMARI-A01', 'Diterima', '2026-10-11'),
(1, 'TWS Baseus Bowie E19 Putih', 'Elektronik', 'Ditemukan', 'Ruang Laboratorium Pemrograman 1', 'Ditemukan tertinggal di meja baris kedua setelah praktikum selesai.', 0, NULL, 'LEMARI-A02', 'Dalam Verifikasi', '2026-10-12'),
(1, 'Botol Minum Corkcicle Hitam 16oz', 'Lainnya', 'Hilang', 'Gedung Kuliah Bersama Lantai 2', 'Tumbler warna hitam ukuran 16oz, ada karet pelindung bawah warna abu-abu.', 75000, NULL, '-', 'Diterima', '2026-10-13'),
(1, 'Jaket Cardigan Rajut Abu-abu', 'Pakaian', 'Ditemukan', 'Bangku Taman Depan Dekanat FT', 'Cardigan rajut warna abu-abu ditemukan sore hari oleh petugas patroli.', 0, NULL, 'LEMARI-B01', 'Diserahterimakan', '2026-10-14');