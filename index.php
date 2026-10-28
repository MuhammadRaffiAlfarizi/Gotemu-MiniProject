<?php
// Pintu masuk: pengguna login ke daftar, tamu ke halaman masuk.
require __DIR__ . '/sesi.php';
arahkan(pengguna_aktif() !== null ? 'daftar.php' : 'login.php');
