# DOKUMENTASI SISTEM FAKTUR PENJUALAN (FAKTUR.ID)
## SKEMA PROGRAMMER - UJI KOMPETENSI KEAHLIAN (UKK)

---

### DAFTAR ISI
1. **PENDAHULUAN**
2. **ARSITEKTUR & TEKNOLOGI**
3. **STRUKTUR DATABASE & RELASI (LRS)**
4. **UJICUBA DATA (SEEDING)**
5. **STRUKTUR MENU & DESAIN ANTARMUKA**
6. **CARA INSTALASI & PENGGUNAAN**
7. **KODE SUMBER KUNCI (KONEKSI DATABASE)**
8. **METODE VERIFIKASI & PENJELASAN ALGORITMA**

---

### Halaman 1: Pendahuluan
Aplikasi **Sistem Faktur Penjualan (FAKTUR.ID)** dibangun untuk membantu perusahaan dalam mengelola transaksi penjualan secara efektif dan efisien. Sistem ini mencakup pengelolaan data perusahaan, customer, produk (barang), serta pencatatan transaksi penjualan secara dinamis dan real-time. Aplikasi ini dirancang menggunakan arsitektur minimalis yang memprioritaskan performa tinggi serta kemudahan penjelasan struktur kode kepada penguji (asesor).

---

### Halaman 2: Arsitektur & Teknologi
Aplikasi ini dikembangkan dengan pendekatan **PHP Native** murni tanpa framework tambahan (seperti Laravel atau CodeIgniter) guna menunjukkan penguasaan algoritma dasar, manipulasi array, dan integrasi database secara langsung.
*   **Bahasa Pemrograman:** PHP (PHP 7.4 / PHP 8+)
*   **Database Management System:** MySQL / MariaDB
*   **Desain Antarmuka (Style):** HTML5 & CSS3 bertema **Neo-Brutalisme (Brutalism)**. Desain ini menggunakan kontras tinggi, garis border tebal (`4px solid #000`), bayangan tegas tanpa blur (`box-shadow: 6px 6px 0px #000`), serta warna cerah yang mencolok (kuning, biru, coral) untuk menciptakan visual yang simple, elegan, berani, dan modern.

---

### Halaman 3: Struktur Database & Relasi (LRS)
Struktur tabel database disesuaikan dengan diagram Logical Relation Structure (LRS) yang mencakup relasi antara entitas transaksi dan master data.

1.  **Tabel `perusahaan`** (Menyimpan data internal penerbit faktur):
    *   `id_perusahaan` (INT, Primary Key, Auto Increment)
    *   `nama_perusahaan` (VARCHAR(150), Not Null)
    *   `alamat` (TEXT, Not Null)
    *   `no_telp` (VARCHAR(20), Not Null)
    *   `fax` (VARCHAR(20))
2.  **Tabel `customer`** (Menyimpan data pelanggan):
    *   `id_customer` (INT, Primary Key, Auto Increment)
    *   `nama_customer` (VARCHAR(150), Not Null)
    *   `perusahaan_cust` (VARCHAR(150), Not Null)
    *   `alamat` (TEXT, Not Null)
3.  **Tabel `produk`** (Menyimpan data barang/jasa):
    *   `id_produk` (INT, Primary Key, Auto Increment)
    *   `nama_produk` (VARCHAR(150), Not Null)
    *   `price` (DECIMAL(12,2), Not Null)
    *   `jenis` (VARCHAR(50), Not Null)
    *   `stock` (INT, Not Null)
4.  **Tabel `faktur`** (Pencatatan transaksi utama):
    *   `no_faktur` (VARCHAR(50), Primary Key)
    *   `tgl_faktur` (DATE, Not Null)
    *   `due_date` (DATE, Not Null)
    *   `metode_bayar` (VARCHAR(50), Not Null)
    *   `ppn` (DECIMAL(5,2), Default 0.00)
    *   `dp` (DECIMAL(12,2), Default 0.00)
    *   `grand_total` (DECIMAL(12,2), Not Null)
    *   `user` (VARCHAR(100), Not Null)
    *   `id_customer` (INT, Foreign Key referencing `customer`)
    *   `id_perusahaan` (INT, Foreign Key referencing `perusahaan`)
5.  **Tabel `detail_faktur`** (Item-item dalam satu faktur):
    *   `id_detail` (INT, Primary Key, Auto Increment)
    *   `id_produk` (INT, Foreign Key referencing `produk`)
    *   `no_faktur` (VARCHAR(50), Foreign Key referencing `faktur`)
    *   `qty` (INT, Not Null)
    *   `price` (DECIMAL(12,2), Not Null)

---

### Halaman 4: Ujicoba Data (Seeding)
Sistem ini telah dilengkapi dengan data uji coba default berupa 5 baris data master untuk masing-masing tabel master dan 2 baris transaksi faktur lengkap dengan detail barang belanjaan.

*   **Perusahaan Master Data:** 5 Entitas (PT. Tech Inovasi Utama, CV. Sumber Makmur, PT. Global Distribusi, UD. Berkah Abadi, PT. Solusi Mandiri).
*   **Customer Master Data:** 5 Entitas (Budi Santoso, Ani Wijaya, Candra Kusuma, Dewi Lestari, Eko Prasetyo).
*   **Produk Master Data:** 5 Entitas (Laptop Asus Zenbook, Mouse Wireless Logitech, Printer Epson L3210, Monitor Dell 24 Inch, Keyboard Mechanical Keychron).
*   **Transaksi Penjualan:**
    1.  `INV-2026-0001` (Budi Santoso - PT. Maju Bersama membeli 1 Laptop, 2 Printer, dan 4 Keyboard dengan PPN 11% dan DP Rp 5.000.000).
    2.  `INV-2026-0002` (Ani Wijaya - CV. Prima Sentosa membeli 2 Mouse Wireless dan 1 Monitor Dell).

---

### Halaman 5: Struktur Menu & Desain Antarmuka
Layout halaman admin menerapkan struktur frame terbagi seperti pada gambar panduan yang diberikan:
1.  **HEADER:** Bagian atas berisi nama sistem `FAKTUR.ID` beserta identitas Uji Kompetensi Keahlian.
2.  **NAVIGATION:** Menu navigasi horizontal yang mudah diakses untuk berpindah antar modul.
3.  **SIDEBAR:** Menu navigasi vertikal di sisi kiri yang menampilkan sub-menu operasi seperti Tambah dan Daftar Data.
4.  **CONTENT:** Area tengah dinamis untuk menampilkan data, formulir input, dan preview cetak.
5.  **FOOTER:** Hak cipta dan informasi tahun pengerjaan aplikasi.

**Struktur Menu:**
*   **Beranda / Dashboard Utama:** Statistik jumlah data master, total transaksi, total omset, serta ringkasan 3 transaksi terakhir.
*   **Kelola Data Perusahaan:** Daftar Perusahaan, Detail Perusahaan, Tambah Perusahaan, Ubah Perusahaan, Hapus Perusahaan.
*   **Kelola Data Customer:** Daftar Customer, Detail Customer, Tambah Customer, Ubah Customer, Hapus Customer, Preview / Cetak Kartu Customer.
*   **Kelola Data Produk:** Daftar Produk, Tambah Produk, Ubah Produk, Hapus Produk.
*   **Kelola Data Penjualan:** Daftar Transaksi Penjualan, Tambah Transaksi Penjualan (Input Dinamis), Ubah Transaksi, Hapus Transaksi, Preview / Cetak Faktur Penjualan (Print-Friendly).

---

### Halaman 6: Cara Instalasi & Penggunaan
1.  Pindahkan seluruh file project ke dalam direktori server lokal (misalnya `C:/xampp/htdocs/ujikom/`).
2.  Aktifkan MySQL / MariaDB dan Apache Service pada control panel server lokal Anda (XAMPP).
3.  Buka web browser dan akses alamat `http://localhost/ujikom/`.
4.  Sistem secara otomatis akan mendeteksi apabila database `ujikom_faktur` belum dibuat, lalu otomatis menginisialisasi skema tabel beserta seluruh data ujicoba dari file `database.sql`.
5.  Sistem langsung siap digunakan tanpa memerlukan konfigurasi database manual di phpMyAdmin.

---

### Halaman 7: Kode Sumber Kunci (Koneksi Database)
Berikut adalah implementasi file `config.php` yang secara otomatis menginisialisasi database dari script SQL jika terdeteksi kosong:
```php
<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ujikom_faktur';

$conn = @new mysqli($db_host, $db_user, $db_pass);
if ($conn->connect_error) {
    die("Koneksi ke MySQL database gagal: " . $conn->connect_error);
}

$db_check = $conn->query("SHOW DATABASES LIKE '$db_name'");
if ($db_check->num_rows == 0) {
    if ($conn->query("CREATE DATABASE `$db_name`")) {
        $conn->select_db($db_name);
        $sql_file = __DIR__ . '/database.sql';
        if (file_exists($sql_file)) {
            $sql_content = file_get_contents($sql_file);
            $conn->multi_query($sql_content);
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
        }
    }
} else {
    $conn->select_db($db_name);
}
$conn->set_charset("utf8mb4");

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>
```

---

### Halaman 8: Metode Verifikasi & Penjelasan Algoritma
Sistem ini menggunakan transaksi SQL (`begin_transaction`, `commit`, dan `rollback`) pada penambahan dan pengubahan faktur untuk menjamin integritas data (ACID).
*   **Pengurangan Stok Otomatis:** Saat faktur dibuat, stok produk terkait otomatis berkurang sesuai dengan kuantitas (`qty`) yang dibeli.
*   **Pengembalian Stok Otomatis:** Saat transaksi faktur dihapus atau diubah, stok produk dikembalikan ke kondisi semula sebelum perhitungan kuantitas baru diterapkan. Hal ini mencegah kebocoran data stok (stock leak).
*   **Dynamic Item Row:** Formulir pembuatan faktur menggunakan Javascript murni untuk menambahkan baris item secara dinamis, mengambil harga produk dari atribut opsi pilihan secara instan, serta menghitung subtotal, PPN, dan grand total secara real-time di sisi klien sebelum data disubmit ke server.
*   **Print-Friendly CSS:** Tampilan cetak faktur menggunakan query `@media print` untuk menyembunyikan elemen dekoratif seperti navigasi, sidebar, header, dan footer utama, sehingga menghasilkan cetakan faktur fisik yang bersih, rapi, dan formal.
