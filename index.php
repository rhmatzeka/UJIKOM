<?php
// index.php: File Router Utama & Dashboard Aplikasi (Tailwind Edition).
// Mengarahkan halaman admin secara bersih dengan Tailwind CSS grid & layouting.

require_once 'config.php';
require_once 'layout_header.php';

// Router: Mengambil parameter halaman (default ke 'home' jika kosong)
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    case 'perusahaan':
        require_once 'perusahaan.php';
        break;
    case 'customer':
        require_once 'customer.php';
        break;
    case 'produk':
        require_once 'produk.php';
        break;
    case 'penjualan':
        require_once 'penjualan.php';
        break;
    case 'laporan':
        require_once 'laporan.php';
        break;
    case 'home':
    default:
        // KONTEN DASHBOARD UTAMA (BERANDA)
        $count_perusahaan = $conn->query("SELECT COUNT(*) as count FROM perusahaan")->fetch_assoc()['count'];
        $count_customer = $conn->query("SELECT COUNT(*) as count FROM customer")->fetch_assoc()['count'];
        $count_produk = $conn->query("SELECT COUNT(*) as count FROM produk")->fetch_assoc()['count'];
        $count_faktur = $conn->query("SELECT COUNT(*) as count FROM faktur")->fetch_assoc()['count'];
        $sum_grand_total = $conn->query("SELECT SUM(grand_total) as sum FROM faktur")->fetch_assoc()['sum'] ?: 0;
        ?>
        <h2 class="text-2xl font-black uppercase mb-2">Beranda (Dashboard Utama)</h2>
        <p class="text-sm text-stone-600 mb-6 font-medium">Selamat datang di Dashboard Utama Aplikasi Sistem Faktur Penjualan. Aplikasi ini dirancang untuk memenuhi kriteria Uji Kompetensi Keahlian (UKK) skema programmer secara simple, efisien, dengan desain bertema Neo-Brutalisme yang elegan.</p>

        <!-- Stats Cards Grid (4 Kolom dengan Tailwind Grid) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="p-6 border-4 border-black bg-yellow-300 shadow-brutal">
                <span class="text-xs uppercase font-black">Perusahaan</span>
                <div class="text-4xl font-black mt-2"><?php echo $count_perusahaan; ?></div>
                <a href="index.php?page=perusahaan" class="text-xs uppercase font-black underline block mt-4 hover:text-stone-700">Kelola Data &raquo;</a>
            </div>
            <div class="p-6 border-4 border-black bg-sky-300 shadow-brutal">
                <span class="text-xs uppercase font-black">Customer</span>
                <div class="text-4xl font-black mt-2"><?php echo $count_customer; ?></div>
                <a href="index.php?page=customer" class="text-xs uppercase font-black underline block mt-4 hover:text-stone-700">Kelola Data &raquo;</a>
            </div>
            <div class="p-6 border-4 border-black bg-green-300 shadow-brutal">
                <span class="text-xs uppercase font-black">Produk</span>
                <div class="text-4xl font-black mt-2"><?php echo $count_produk; ?></div>
                <a href="index.php?page=produk" class="text-xs uppercase font-black underline block mt-4 hover:text-stone-700">Kelola Data &raquo;</a>
            </div>
            <div class="p-6 border-4 border-black bg-red-300 shadow-brutal">
                <span class="text-xs uppercase font-black">Total Transaksi</span>
                <div class="text-4xl font-black mt-2"><?php echo $count_faktur; ?></div>
                <a href="index.php?page=penjualan" class="text-xs uppercase font-black underline block mt-4 hover:text-stone-700">Kelola Data &raquo;</a>
            </div>
        </div>

        <!-- Omset Card -->
        <div class="p-6 border-4 border-black shadow-brutal bg-white mb-8">
            <h3 class="text-xs uppercase font-black text-stone-500 mb-2">Ringkasan Total Omset Penjualan</h3>
            <div class="text-3xl font-black text-red-500"><?php echo formatRupiah($sum_grand_total); ?></div>
            <p class="text-xs text-stone-500 mt-2">Hasil akumulasi dari seluruh faktur penjualan yang tercatat dalam sistem.</p>
        </div>

        <!-- Latest Transactions -->
        <div class="p-6 border-4 border-black shadow-brutal bg-white">
            <h3 class="text-lg font-black uppercase mb-4 pb-2 border-b-4 border-black">3 Transaksi Terakhir</h3>
            <table class="w-full text-sm border-2 border-black">
                <thead>
                    <tr class="bg-stone-100 border-b-2 border-black text-left">
                        <th class="p-3 border-r-2 border-black">No Faktur</th>
                        <th class="p-3 border-r-2 border-black">Tanggal</th>
                        <th class="p-3 border-r-2 border-black">Customer</th>
                        <th class="p-3 border-r-2 border-black">Grand Total</th>
                        <th class="p-3 border-r-2 border-black">Metode</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $latest = $conn->query("SELECT f.*, c.nama_customer, c.perusahaan_cust 
                                            FROM faktur f 
                                            JOIN customer c ON f.id_customer = c.id_customer 
                                            ORDER BY f.tgl_faktur DESC, f.no_faktur DESC LIMIT 3");
                    if ($latest->num_rows > 0):
                        while ($l_row = $latest->fetch_assoc()):
                    ?>
                            <tr class="border-b-2 border-black hover:bg-stone-50">
                                <td class="p-3 border-r-2 border-black"><?php echo htmlspecialchars($l_row['no_faktur']); ?></td>
                                <td class="p-3 border-r-2 border-black"><?php echo formatDate($l_row['tgl_faktur']); ?></td>
                                <td class="p-3 border-r-2 border-black"><?php echo htmlspecialchars($l_row['nama_customer'] . " (" . $l_row['perusahaan_cust'] . ")"); ?></td>
                                <td class="p-3 border-r-2 border-black"><?php echo formatRupiah($l_row['grand_total']); ?></td>
                                <td class="p-3 border-r-2 border-black"><?php echo htmlspecialchars($l_row['metode_bayar']); ?></td>
                                <td class="p-3"><a href="index.php?page=penjualan&action=cetak&id=<?php echo urlencode($l_row['no_faktur']); ?>" class="px-2 py-1 border-2 border-black bg-sky-300 text-xs shadow-brutal-sm">Cetak</a></td>
                            </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="6" class="p-3 text-center">Belum ada data transaksi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
        break;
}

require_once 'layout_footer.php';
?>