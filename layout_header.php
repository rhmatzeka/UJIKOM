<?php
// layout_header.php: Header halaman menggunakan Tailwind CSS & jQuery.
// Desain bertema Neo-Brutalisme (border tebal 4px, shadow tajam, warna kontras).
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAKTUR.ID - Sistem Faktur Penjualan</title>
    <!-- 1. Menghubungkan Tailwind CSS dari CDN (Pre-existing Library untuk UI modern & ringkas) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- 2. Menghubungkan jQuery dari CDN (Library JavaScript untuk interaksi & manipulasi DOM yang ringkas) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Konfigurasi custom Tailwind jika diperlukan (opsional, untuk font monospace & shadow brutalism)
        tailwind.config = {
            theme: {
                extend: {
                    boxShadow: {
                        'brutal': '6px 6px 0px 0px #000000',
                        'brutal-sm': '3px 3px 0px 0px #000000',
                    }
                }
            }
        }
    </script>
    <style>
        /* Menggunakan font Courier agar bertema retro-brutalisme */
        body { font-family: 'Courier New', Courier, monospace; font-weight: 900; }
        /* CSS Khusus Print: Menyembunyikan elemen no-print saat mencetak faktur */
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: white !important; }
            main { border: none !important; box-shadow: none !important; width: 100% !important; }
        }
    </style>
</head>
<body class="bg-stone-100 text-black p-5 min-h-screen flex flex-col gap-6">

    <!-- Wrapper Bungkus Layout Utama -->
    <div class="flex flex-col gap-6 flex-grow max-w-7xl mx-auto w-full">
        
        <!-- HEADER UTAMA -->
        <header class="bg-yellow-300 border-4 border-black p-6 shadow-brutal flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-wider">FAKTUR.ID</h1>
                <p class="text-xs mt-1 uppercase">Sistem Kelola Faktur Penjualan</p>
            </div>
            <div class="bg-white border-2 border-black px-3 py-1 text-sm uppercase">
                UJI KOMPETENSI KEAHLIAN - SKEMA PROGRAMMER
            </div>
        </header>

        <!-- NAVIGATION BAR -->
        <nav class="bg-sky-400 border-4 border-black p-4 shadow-brutal flex flex-wrap gap-4 no-print">
            <a href="index.php" class="px-4 py-2 border-2 border-black bg-white hover:bg-yellow-300 shadow-brutal-sm active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all uppercase text-sm <?php echo (!isset($_GET['page']) || $_GET['page'] == 'home') ? 'bg-yellow-300' : ''; ?>">Beranda</a>
            <a href="index.php?page=perusahaan" class="px-4 py-2 border-2 border-black bg-white hover:bg-yellow-300 shadow-brutal-sm active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all uppercase text-sm <?php echo (isset($_GET['page']) && $_GET['page'] == 'perusahaan') ? 'bg-yellow-300' : ''; ?>">Kelola Perusahaan</a>
            <a href="index.php?page=customer" class="px-4 py-2 border-2 border-black bg-white hover:bg-yellow-300 shadow-brutal-sm active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all uppercase text-sm <?php echo (isset($_GET['page']) && $_GET['page'] == 'customer') ? 'bg-yellow-300' : ''; ?>">Kelola Customer</a>
            <a href="index.php?page=produk" class="px-4 py-2 border-2 border-black bg-white hover:bg-yellow-300 shadow-brutal-sm active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all uppercase text-sm <?php echo (isset($_GET['page']) && $_GET['page'] == 'produk') ? 'bg-yellow-300' : ''; ?>">Kelola Produk</a>
            <a href="index.php?page=penjualan" class="px-4 py-2 border-2 border-black bg-white hover:bg-yellow-300 shadow-brutal-sm active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all uppercase text-sm <?php echo (isset($_GET['page']) && $_GET['page'] == 'penjualan') ? 'bg-yellow-300' : ''; ?>">Kelola Penjualan</a>
            <a href="index.php?page=laporan" class="px-4 py-2 border-2 border-black bg-white hover:bg-yellow-300 shadow-brutal-sm active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all uppercase text-sm <?php echo (isset($_GET['page']) && $_GET['page'] == 'laporan') ? 'bg-yellow-300' : ''; ?>">Laporan Penjualan</a>
        </nav>

        <!-- CONTAINER UTAMA: SIDEBAR + KONTEN -->
        <div class="flex flex-col md:flex-row gap-6 flex-grow">
            
            <!-- SIDEBAR KIRI -->
            <aside class="w-full md:w-64 bg-white border-4 border-black p-5 shadow-brutal flex flex-col gap-6 no-print shrink-0">
                <div>
                    <h3 class="border-b-4 border-black pb-2 text-md font-black uppercase">Menu Utama</h3>
                    <ul class="flex flex-col gap-2 mt-3">
                        <li><a href="index.php" class="block p-2 border-2 border-black bg-stone-50 hover:bg-red-300 shadow-brutal-sm text-sm <?php echo (!isset($_GET['page']) || $_GET['page'] == 'home') ? 'bg-red-300' : ''; ?>">Dashboard</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="border-b-4 border-black pb-2 text-md font-black uppercase">Perusahaan</h3>
                    <ul class="flex flex-col gap-2 mt-3">
                        <li><a href="index.php?page=perusahaan" class="block p-2 border-2 border-black bg-stone-50 hover:bg-red-300 shadow-brutal-sm text-sm <?php echo (isset($_GET['page']) && $_GET['page'] == 'perusahaan' && !isset($_GET['action'])) ? 'bg-red-300' : ''; ?>">Daftar Perusahaan</a></li>
                        <li><a href="index.php?page=perusahaan&action=add" class="block p-2 border-2 border-black bg-stone-50 hover:bg-red-300 shadow-brutal-sm text-sm <?php echo (isset($_GET['page']) && $_GET['page'] == 'perusahaan' && isset($_GET['action']) && $_GET['action'] == 'add') ? 'bg-red-300' : ''; ?>">Tambah Perusahaan</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="border-b-4 border-black pb-2 text-md font-black uppercase">Customer</h3>
                    <ul class="flex flex-col gap-2 mt-3">
                        <li><a href="index.php?page=customer" class="block p-2 border-2 border-black bg-stone-50 hover:bg-red-300 shadow-brutal-sm text-sm <?php echo (isset($_GET['page']) && $_GET['page'] == 'customer' && !isset($_GET['action'])) ? 'bg-red-300' : ''; ?>">Daftar Customer</a></li>
                        <li><a href="index.php?page=customer&action=add" class="block p-2 border-2 border-black bg-stone-50 hover:bg-red-300 shadow-brutal-sm text-sm <?php echo (isset($_GET['page']) && $_GET['page'] == 'customer' && isset($_GET['action']) && $_GET['action'] == 'add') ? 'bg-red-300' : ''; ?>">Tambah Customer</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="border-b-4 border-black pb-2 text-md font-black uppercase">Produk</h3>
                    <ul class="flex flex-col gap-2 mt-3">
                        <li><a href="index.php?page=produk" class="block p-2 border-2 border-black bg-stone-50 hover:bg-red-300 shadow-brutal-sm text-sm <?php echo (isset($_GET['page']) && $_GET['page'] == 'produk' && !isset($_GET['action'])) ? 'bg-red-300' : ''; ?>">Daftar Produk</a></li>
                        <li><a href="index.php?page=produk&action=add" class="block p-2 border-2 border-black bg-stone-50 hover:bg-red-300 shadow-brutal-sm text-sm <?php echo (isset($_GET['page']) && $_GET['page'] == 'produk' && isset($_GET['action']) && $_GET['action'] == 'add') ? 'bg-red-300' : ''; ?>">Tambah Produk</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="border-b-4 border-black pb-2 text-md font-black uppercase">Penjualan</h3>
                    <ul class="flex flex-col gap-2 mt-3">
                        <li><a href="index.php?page=penjualan" class="block p-2 border-2 border-black bg-stone-50 hover:bg-red-300 shadow-brutal-sm text-sm <?php echo (isset($_GET['page']) && $_GET['page'] == 'penjualan' && !isset($_GET['action'])) ? 'bg-red-300' : ''; ?>">Daftar Transaksi</a></li>
                        <li><a href="index.php?page=penjualan&action=add" class="block p-2 border-2 border-black bg-stone-50 hover:bg-red-300 shadow-brutal-sm text-sm <?php echo (isset($_GET['page']) && $_GET['page'] == 'penjualan' && isset($_GET['action']) && $_GET['action'] == 'add') ? 'bg-red-300' : ''; ?>">Tambah Penjualan</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="border-b-4 border-black pb-2 text-md font-black uppercase">Laporan</h3>
                    <ul class="flex flex-col gap-2 mt-3">
                        <li><a href="index.php?page=laporan" class="block p-2 border-2 border-black bg-stone-50 hover:bg-red-300 shadow-brutal-sm text-sm <?php echo (isset($_GET['page']) && $_GET['page'] == 'laporan') ? 'bg-red-300' : ''; ?>">Cetak Laporan</a></li>
                    </ul>
                </div>
            </aside>

            <!-- KONTEN UTAMA (KANAN) -->
            <main class="flex-grow bg-white border-4 border-black p-6 shadow-brutal overflow-x-auto">
