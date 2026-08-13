<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ujikom_faktur';

// 1. Membuat koneksi awal ke server MySQL lokal (localhost)
$conn = @new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    die("Koneksi ke MySQL database gagal: " . $conn->connect_error);
}

// 2. Memeriksa apakah database sudah ada. Jika belum, buat database baru dan import data dari database.sql
$db_check = $conn->query("SHOW DATABASES LIKE '$db_name'");
if ($db_check->num_rows == 0) {
    // Database belum ada, maka buat database baru
    if ($conn->query("CREATE DATABASE `$db_name`")) {
        $conn->select_db($db_name);
        
        // Membaca file database.sql dan mengeksekusinya
        $sql_file = __DIR__ . '/database.sql';
        if (file_exists($sql_file)) {
            $sql_content = file_get_contents($sql_file);
            // Menjalankan query beruntun (multi-query) untuk membuat tabel dan memasukkan data default
            $conn->multi_query($sql_content);
            do {
                // Membersihkan memori hasil query
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
        }
    }
} else {
    // Jika database sudah ada, langsung pilih database tersebut
    $conn->select_db($db_name);
}

// Mengatur agar encoding karakter menggunakan UTF-8 agar aman dan kompatibel
$conn->set_charset("utf8mb4");

// Fungsi pembantu untuk mengubah angka mentah menjadi format mata uang Rupiah (contoh: Rp 15.000.000)
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Fungsi pembantu untuk mengubah tanggal database (YYYY-MM-DD) menjadi format tanggal Indonesia (DD-MM-YYYY)
function formatDate($date) {
    if (!$date) return '-';
    return date('d-m-Y', strtotime($date));
}
?>
