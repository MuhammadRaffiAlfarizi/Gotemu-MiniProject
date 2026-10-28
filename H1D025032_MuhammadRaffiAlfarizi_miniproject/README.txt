GoTemu - Sistem Lost and Found Kampus
Mini Project Pemrograman Web I (IF21305)
Pembuat: Muhammad Raffi Alfarizi (H1D025032)

TEMA
Lost and Found. Rancangan mengacu pada SRS dan Project Charter Tim 5 (Analisis dan Desain Sistem).
Pemetaan peran: admin = Satpam (mencatat barang), anggota = Pengguna Layanan (hanya melihat).

KEBUTUHAN
XAMPP dengan PHP 8.1 atau lebih baru dan MySQL/MariaDB.

CARA MENJALANKAN
1. Salin folder ini ke C:\xampp\htdocs\gotemu (atau folder htdocs di sistemmu).
2. Buka XAMPP Control Panel, jalankan Apache dan MySQL.
3. Buka http://localhost/phpmyadmin, pilih tab Import, pilih berkas app.sql, klik Import.
   Berkas ini membuat database "gotemu", dua tabel, dua akun uji, dan lima data awal.
4. Buka http://localhost/gotemu/ di peramban.
5. Bila MySQL memakai sandi atau port lain, ubah nilai di koneksi.php (satu-satunya tempat kredensial).

AKUN UJI
  Peran   Username      Sandi
  admin   satpam01      Satpam#2026
  anggota mahasiswa01   Mahasiswa#2026
Akun baru lewat halaman Buat akun selalu berperan anggota.

BERKAS
  app.sql          skema + data awal
  koneksi.php      koneksi mysqli, set_charset, try/catch, log ke log/error.log
  sesi.php         session, e(), penjaga login dan peran, CSRF, flash
  validasi.php     helper validasi (panjang, pilihan white list, tanggal)
  tampilan.php     kerangka halaman (header, navigasi, footer)
  registrasi.php   CREATE akun
  login.php        masuk (password_verify, session_regenerate_id)
  logout.php       keluar (mengosongkan session)
  daftar.php       READ daftar kasus + JOIN pencatat
  detail.php       READ satu kasus (?id=...)
  tambah.php       CREATE kasus (khusus admin)
  assets/style.css gaya tampilan
  PENGUJIAN.docx   laporan pengujian
  CATATAN.txt      catatan rancangan dan keputusan
