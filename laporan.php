<?php
// laporan.php: Modul Laporan Penjualan (Tailwind CSS Edition).
// Menyediakan rekapitulasi data penjualan dengan filter tanggal, perusahaan, customer, dan cetak laporan.

// Ambil data untuk filter
$perusahaan_list = $conn->query("SELECT id_perusahaan, nama_perusahaan FROM perusahaan ORDER BY nama_perusahaan ASC");
$customer_list = $conn->query("SELECT id_customer, nama_customer, perusahaan_cust FROM customer ORDER BY nama_customer ASC");

// Inisialisasi filter
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : '';
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : '';
$id_perusahaan = isset($_GET['id_perusahaan']) ? $_GET['id_perusahaan'] : '';
$id_customer = isset($_GET['id_customer']) ? $_GET['id_customer'] : '';
$metode_bayar = isset($_GET['metode_bayar']) ? $_GET['metode_bayar'] : '';

// Bangun query SQL berdasarkan filter
$where_clauses = [];

if (!empty($tgl_mulai)) {
    $tgl_mulai_esc = $conn->real_escape_string($tgl_mulai);
    $where_clauses[] = "f.tgl_faktur >= '$tgl_mulai_esc'";
}
if (!empty($tgl_selesai)) {
    $tgl_selesai_esc = $conn->real_escape_string($tgl_selesai);
    $where_clauses[] = "f.tgl_faktur <= '$tgl_selesai_esc'";
}
if (!empty($id_perusahaan)) {
    $id_perusahaan_esc = intval($id_perusahaan);
    $where_clauses[] = "f.id_perusahaan = $id_perusahaan_esc";
}
if (!empty($id_customer)) {
    $id_customer_esc = intval($id_customer);
    $where_clauses[] = "f.id_customer = $id_customer_esc";
}
if (!empty($metode_bayar)) {
    $metode_bayar_esc = $conn->real_escape_string($metode_bayar);
    $where_clauses[] = "f.metode_bayar = '$metode_bayar_esc'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Jalankan Query
$query = "SELECT f.*, c.nama_customer, c.perusahaan_cust, p.nama_perusahaan,
                 (SELECT SUM(qty * price) FROM detail_faktur WHERE no_faktur = f.no_faktur) as subtotal
          FROM faktur f
          JOIN customer c ON f.id_customer = c.id_customer
          JOIN perusahaan p ON f.id_perusahaan = p.id_perusahaan
          $where_sql
          ORDER BY f.tgl_faktur DESC, f.no_faktur DESC";

$result = $conn->query($query);

// Dapatkan informasi filter aktif untuk judul cetak
$filter_info = [];
if (!empty($tgl_mulai)) $filter_info[] = "Mulai: " . formatDate($tgl_mulai);
if (!empty($tgl_selesai)) $filter_info[] = "Selesai: " . formatDate($tgl_selesai);
if (!empty($id_perusahaan)) {
    $p_info = $conn->query("SELECT nama_perusahaan FROM perusahaan WHERE id_perusahaan = " . intval($id_perusahaan))->fetch_assoc();
    if ($p_info) $filter_info[] = "Perusahaan: " . $p_info['nama_perusahaan'];
}
if (!empty($id_customer)) {
    $c_info = $conn->query("SELECT nama_customer FROM customer WHERE id_customer = " . intval($id_customer))->fetch_assoc();
    if ($c_info) $filter_info[] = "Customer: " . $c_info['nama_customer'];
}
if (!empty($metode_bayar)) $filter_info[] = "Metode: " . strtoupper($metode_bayar);

$filter_text = count($filter_info) > 0 ? implode(" | ", $filter_info) : "Semua Data Penjualan";
?>

<!-- Header Khusus Cetak (Hanya tampil saat print) -->
<div class="hidden print:block mb-8 border-b-4 border-black pb-4 text-center">
    <h1 class="text-3xl font-black uppercase">LAPORAN REKAPITULASI PENJUALAN</h1>
    <h2 class="text-xl font-bold uppercase mt-1">SISTEM FAKTUR - FAKTUR.ID</h2>
    <p class="text-sm text-stone-600 mt-2 font-bold"><?php echo $filter_text; ?></p>
    <p class="text-xs text-stone-500 mt-1">Dicetak pada tanggal: <?php echo date('d-m-Y H:i'); ?></p>
</div>

<!-- TAMPILAN FILTER & NAVIGASI (Hanya tampil di screen) -->
<div class="no-print mb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-black uppercase">Laporan Penjualan</h2>
            <p class="text-sm text-stone-600 font-medium">Lakukan pemfilteran data transaksi faktur dan cetak laporan rekap secara langsung.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 border-4 border-black bg-yellow-300 hover:bg-yellow-400 font-black shadow-brutal active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all uppercase text-sm">
                Cetak Laporan
            </button>
            <a href="index.php?page=laporan" class="px-4 py-2 border-4 border-black bg-stone-200 hover:bg-stone-300 font-black shadow-brutal active:translate-x-[2px] active:translate-y-[2px] active:shadow-none transition-all uppercase text-sm">
                Reset Filter
            </a>
        </div>
    </div>

    <!-- Form Filter (Neo-Brutalism Card) -->
    <form method="GET" action="index.php" class="bg-white border-4 border-black p-5 shadow-brutal mb-8">
        <input type="hidden" name="page" value="laporan">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            
            <!-- Filter Tanggal Mulai -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-black uppercase text-stone-700">Tanggal Mulai</label>
                <input type="date" name="tgl_mulai" value="<?php echo htmlspecialchars($tgl_mulai); ?>" class="border-2 border-black p-2 text-sm font-bold bg-stone-50 focus:bg-white focus:outline-none">
            </div>

            <!-- Filter Tanggal Selesai -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-black uppercase text-stone-700">Tanggal Selesai</label>
                <input type="date" name="tgl_selesai" value="<?php echo htmlspecialchars($tgl_selesai); ?>" class="border-2 border-black p-2 text-sm font-bold bg-stone-50 focus:bg-white focus:outline-none">
            </div>

            <!-- Filter Perusahaan -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-black uppercase text-stone-700">Perusahaan Penerbit</label>
                <select name="id_perusahaan" class="border-2 border-black p-2 text-sm font-bold bg-stone-50 focus:bg-white focus:outline-none">
                    <option value="">-- Semua Perusahaan --</option>
                    <?php while ($p = $perusahaan_list->fetch_assoc()): ?>
                        <option value="<?php echo $p['id_perusahaan']; ?>" <?php echo ($id_perusahaan == $p['id_perusahaan']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['nama_perusahaan']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Filter Customer -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-black uppercase text-stone-700">Customer Pembeli</label>
                <select name="id_customer" class="border-2 border-black p-2 text-sm font-bold bg-stone-50 focus:bg-white focus:outline-none">
                    <option value="">-- Semua Customer --</option>
                    <?php while ($c = $customer_list->fetch_assoc()): ?>
                        <option value="<?php echo $c['id_customer']; ?>" <?php echo ($id_customer == $c['id_customer']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['nama_customer'] . " (" . $c['perusahaan_cust'] . ")"); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Filter Metode Pembayaran -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-black uppercase text-stone-700">Metode Bayar</label>
                <select name="metode_bayar" class="border-2 border-black p-2 text-sm font-bold bg-stone-50 focus:bg-white focus:outline-none">
                    <option value="">-- Semua Metode --</option>
                    <option value="tunai" <?php echo ($metode_bayar == 'tunai') ? 'selected' : ''; ?>>TUNAI</option>
                    <option value="kredit" <?php echo ($metode_bayar == 'kredit') ? 'selected' : ''; ?>>KREDIT</option>
                    <option value="transfer" <?php echo ($metode_bayar == 'transfer') ? 'selected' : ''; ?>>TRANSFER</option>
                </select>
            </div>
            
        </div>

        <div class="flex justify-end mt-4">
            <button type="submit" class="w-full sm:w-auto px-6 py-2 border-2 border-black bg-sky-300 hover:bg-sky-400 font-black shadow-brutal-sm active:translate-x-[1px] active:translate-y-[1px] active:shadow-none transition-all uppercase text-sm">
                Terapkan Filter
            </button>
        </div>
    </form>
</div>

<!-- AREA TABEL LAPORAN (Tampil di screen & print) -->
<div class="bg-white border-4 border-black p-6 shadow-brutal print:shadow-none print:border-none print:p-0">
    
    <!-- Info Filter Terpilih -->
    <div class="mb-4">
        <span class="bg-black text-white text-[10px] px-2 py-0.5 inline-block uppercase font-black">Filter Aktif:</span>
        <span class="text-xs font-black ml-1 uppercase"><?php echo htmlspecialchars($filter_text); ?></span>
    </div>

    <!-- Tabel Rekap Penjualan -->
    <table class="w-full text-xs border-4 border-black mb-6">
        <thead>
            <tr class="bg-yellow-300 border-b-4 border-black text-left font-black">
                <th class="p-2.5 border-r-2 border-black text-center w-10">NO</th>
                <th class="p-2.5 border-r-2 border-black w-24">NO FAKTUR</th>
                <th class="p-2.5 border-r-2 border-black w-24">TGL FAKTUR</th>
                <th class="p-2.5 border-r-2 border-black">PERUSAHAAN</th>
                <th class="p-2.5 border-r-2 border-black">CUSTOMER</th>
                <th class="p-2.5 border-r-2 border-black text-right w-28">SUBTOTAL</th>
                <th class="p-2.5 border-r-2 border-black text-right w-16">PPN</th>
                <th class="p-2.5 border-r-2 border-black text-right w-24">DP</th>
                <th class="p-2.5 border-r-2 border-black text-right w-32">GRAND TOTAL</th>
                <th class="p-2.5 text-center w-20">METODE</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $sum_subtotal = 0;
            $sum_ppn = 0;
            $sum_dp = 0;
            $sum_grand_total = 0;

            if ($result && $result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    $subtotal = floatval($row['subtotal']);
                    $ppn_val = ($row['ppn'] / 100) * $subtotal;
                    $dp = floatval($row['dp']);
                    $grand_total = floatval($row['grand_total']);

                    $sum_subtotal += $subtotal;
                    $sum_ppn += $ppn_val;
                    $sum_dp += $dp;
                    $sum_grand_total += $grand_total;
            ?>
                    <tr class="border-b-2 border-black hover:bg-stone-50 font-bold">
                        <td class="p-2 border-r-2 border-black text-center"><?php echo $no++; ?></td>
                        <td class="p-2 border-r-2 border-black"><?php echo htmlspecialchars($row['no_faktur']); ?></td>
                        <td class="p-2 border-r-2 border-black"><?php echo formatDate($row['tgl_faktur']); ?></td>
                        <td class="p-2 border-r-2 border-black"><?php echo htmlspecialchars($row['nama_perusahaan']); ?></td>
                        <td class="p-2 border-r-2 border-black"><?php echo htmlspecialchars($row['nama_customer'] . " (" . $row['perusahaan_cust'] . ")"); ?></td>
                        <td class="p-2 border-r-2 border-black text-right"><?php echo formatRupiah($subtotal); ?></td>
                        <td class="p-2 border-r-2 border-black text-right"><?php echo htmlspecialchars($row['ppn']) . "%"; ?></td>
                        <td class="p-2 border-r-2 border-black text-right"><?php echo formatRupiah($dp); ?></td>
                        <td class="p-2 border-r-2 border-black text-right font-black text-red-500"><?php echo formatRupiah($grand_total); ?></td>
                        <td class="p-2 text-center uppercase font-black"><span class="px-1.5 py-0.5 border border-black bg-stone-100"><?php echo htmlspecialchars($row['metode_bayar']); ?></span></td>
                    </tr>
            <?php
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="10" class="p-6 text-center font-black text-sm uppercase bg-stone-50">
                        Tidak ada data transaksi yang cocok dengan filter yang dipilih.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <!-- Baris Ringkasan Total Laporan -->
        <tfoot>
            <tr class="bg-stone-100 font-black border-t-4 border-black text-xs">
                <td colspan="5" class="p-3 border-r-2 border-black text-right uppercase">Total Rekapitulasi:</td>
                <td class="p-3 border-r-2 border-black text-right"><?php echo formatRupiah($sum_subtotal); ?></td>
                <td class="p-3 border-r-2 border-black text-right text-stone-500">-</td>
                <td class="p-3 border-r-2 border-black text-right text-blue-600"><?php echo formatRupiah($sum_dp); ?></td>
                <td class="p-3 border-r-2 border-black text-right text-red-600 bg-yellow-200"><?php echo formatRupiah($sum_grand_total); ?></td>
                <td class="p-3 text-center">-</td>
            </tr>
        </tfoot>
    </table>

    <!-- Tanda Tangan Laporan (Hanya tampil saat print) -->
    <div class="hidden print:flex justify-between mt-16 text-xs font-bold">
        <div class="text-center w-48">
            <p class="mb-16">Dibuat Oleh,</p>
            <div class="border-b-2 border-black w-full"></div>
            <p class="mt-2 uppercase">Sales / Administrator</p>
        </div>
        <div class="text-center w-48">
            <p class="mb-16">Diketahui Oleh,</p>
            <div class="border-b-2 border-black w-full"></div>
            <p class="mt-2 uppercase">Pimpinan / Manager</p>
        </div>
    </div>
</div>
