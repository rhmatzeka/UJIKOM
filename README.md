# FAKTUR.ID - Sistem Kelola Faktur Penjualan

Aplikasi **Sistem Kelola Faktur Penjualan (FAKTUR.ID)** yang dibangun menggunakan PHP Native, Tailwind CSS, dan jQuery. Aplikasi ini dirancang untuk memenuhi standar Uji Kompetensi Keahlian (UKK) Skema Programmer.

## Fitur Utama

1. **Dashboard Statistik (Beranda)**
   - Menampilkan total perusahaan, customer, produk, dan total transaksi.
   - Ringkasan akumulasi total omset penjualan secara real-time.
   - Tabel ringkas 3 transaksi terbaru.

2. **Kelola Perusahaan (Penerbit Faktur)**
   - CRUD data perusahaan (Nama, Alamat, No. Telp, Fax).

3. **Kelola Customer (Penerima Faktur)**
   - CRUD data customer beserta instansi perusahaan customer.
   - Cetak Kartu Customer dengan layout formal dan menarik.

4. **Kelola Produk (Katalog Barang)**
   - CRUD data katalog produk (Nama, Harga, Kategori/Jenis, Stok).

5. **Kelola Transaksi Penjualan (Faktur Kasir Dinamis)**
   - Input baris produk secara dinamis menggunakan jQuery (tombol tambah/hapus baris tanpa reload).
   - Perhitungan subtotal, PPN (%), Uang Muka (DP), dan Grand Total otomatis di sisi klien.
   - Transaksi database aman (ACID transaction) dengan auto-update (pemotongan) stok produk secara otomatis.
   - Cetak Faktur Penjualan siap print (Format A4 print-friendly, menyembunyikan navbar dan sidebar).

6. **Laporan Rekapitulasi Penjualan (Fitur Cetak Laporan)**
   - Filter laporan penjualan berdasarkan Rentang Tanggal Mulai & Selesai, Perusahaan Penerbit, Customer Pembeli, dan Metode Pembayaran.
   - Perhitungan total rekapitulasi (Subtotal, DP, Grand Total) otomatis pada baris footer laporan.
   - Fitur Cetak Rekapitulasi Laporan Penjualan ramah cetak (print-friendly) lengkap dengan slot tanda tangan pimpinan/administrator.

## Tech Stack

- **Backend:** PHP 8+ (Native)
- **Database:** MySQL / MariaDB
- **Frontend UI:** Tailwind CSS (via CDN)
- **Frontend Logic:** jQuery (via CDN)

## Cara Instalasi & Menjalankan Program

1. Clone repositori ini ke dalam direktori server lokal Anda (misal `C:/xampp/htdocs/ujikom/`).
2. Pastikan service Apache dan MySQL di XAMPP Control Panel Anda sudah berjalan.
3. Buka web browser Anda, kemudian akses alamat:
   ```text
   http://localhost/ujikom/index.php
   ```
4. **Auto-Installer Database:** Aplikasi ini dilengkapi fitur *auto-install*. Saat halaman pertama kali diakses, aplikasi akan secara otomatis mendeteksi, membuat database baru bernama `ujikom_faktur`, membuat seluruh tabel, dan melakukan *seeding* data dummy uji coba dari berkas `database.sql`. Anda tidak perlu melakukan import manual melalui phpMyAdmin.

---

**Asesi Uji Kompetensi Keahlian (UKK):**  
Nama: **Rahmat Eka Satria**  
Skema Sertifikasi: **Programmer**
