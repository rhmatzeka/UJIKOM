<?php
// penjualan.php: Modul Transaksi Penjualan & Faktur (Tailwind CSS + jQuery Edition).
// Menyediakan manajemen faktur, penambahan item transaksi dinamis (jQuery), update stok otomatis (ACID), dan print preview.

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = $error = '';

// Load data master untuk form drop-down
$perusahaan_list = $conn->query("SELECT * FROM perusahaan ORDER BY nama_perusahaan ASC");
$customer_list = $conn->query("SELECT * FROM customer ORDER BY nama_customer ASC");

// Fetch products mapping for jQuery dynamic item insertion
$products_array = [];
$p_res = $conn->query("SELECT * FROM produk");
while ($p_row = $p_res->fetch_assoc()) {
    $products_array[] = $p_row;
}

// MENANGANI FORM SUBMIT (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. TAMBAH TRANSAKSI PENJUALAN BARU
    if ($action == 'add') {
        $no_faktur = $conn->real_escape_string($_POST['no_faktur']);
        $tgl_faktur = $conn->real_escape_string($_POST['tgl_faktur']);
        $due_date = $conn->real_escape_string($_POST['due_date']);
        $metode_bayar = $conn->real_escape_string($_POST['metode_bayar']);
        $ppn = floatval($_POST['ppn']);
        $dp = floatval($_POST['dp']);
        $user = $conn->real_escape_string($_POST['user']);
        $id_customer = intval($_POST['id_customer']);
        $id_perusahaan = intval($_POST['id_perusahaan']);

        if ($conn->query("SELECT * FROM faktur WHERE no_faktur = '$no_faktur'")->num_rows > 0) {
            $error = "Nomor Faktur sudah terdaftar!";
        } else {
            // Database Transaction (ACID)
            $conn->begin_transaction();
            try {
                $subtotal = 0;
                $items = isset($_POST['items']) ? $_POST['items'] : [];
                
                if (empty($items)) {
                    throw new Exception("Harap masukkan minimal 1 produk!");
                }

                $detail_queries = [];
                foreach ($items as $item) {
                    $id_produk = intval($item['id_produk']);
                    $qty = intval($item['qty']);
                    
                    $p_info = $conn->query("SELECT price, stock FROM produk WHERE id_produk = $id_produk")->fetch_assoc();
                    if (!$p_info) throw new Exception("Produk tidak ditemukan!");
                    if ($p_info['stock'] < $qty) throw new Exception("Stok tidak mencukupi!");
                    
                    $price = $p_info['price'];
                    $subtotal += ($price * $qty);

                    $detail_queries[] = "INSERT INTO detail_faktur (id_produk, no_faktur, qty, price) VALUES ($id_produk, '$no_faktur', $qty, $price)";
                    $detail_queries[] = "UPDATE produk SET stock = stock - $qty WHERE id_produk = $id_produk";
                }

                $grand_total = $subtotal + (($ppn / 100) * $subtotal) - $dp;

                // Insert Faktur
                if (!$conn->query("INSERT INTO faktur (no_faktur, tgl_faktur, due_date, metode_bayar, ppn, dp, grand_total, user, id_customer, id_perusahaan) VALUES ('$no_faktur', '$tgl_faktur', '$due_date', '$metode_bayar', $ppn, $dp, $grand_total, '$user', $id_customer, $id_perusahaan)")) {
                    throw new Exception("Gagal menyimpan faktur: " . $conn->error);
                }

                // Insert Detail & Update Stock
                foreach ($detail_queries as $q) {
                    if (!$conn->query($q)) throw new Exception("Gagal menyimpan detail/stok: " . $conn->error);
                }

                $conn->commit();
                $message = "Transaksi berhasil disimpan!";
                $action = 'list';
            } catch (Exception $e) {
                $conn->rollback();
                $error = $e->getMessage();
            }
        }
    
    // 2. UBAH TRANSAKSI PENJUALAN
    } elseif ($action == 'edit') {
        $no_faktur_old = $conn->real_escape_string($_POST['no_faktur_old']);
        $no_faktur = $conn->real_escape_string($_POST['no_faktur']);
        $tgl_faktur = $conn->real_escape_string($_POST['tgl_faktur']);
        $due_date = $conn->real_escape_string($_POST['due_date']);
        $metode_bayar = $conn->real_escape_string($_POST['metode_bayar']);
        $ppn = floatval($_POST['ppn']);
        $dp = floatval($_POST['dp']);
        $user = $conn->real_escape_string($_POST['user']);
        $id_customer = intval($_POST['id_customer']);
        $id_perusahaan = intval($_POST['id_perusahaan']);

        $conn->begin_transaction();
        try {
            // Restore previous stock
            $old_details = $conn->query("SELECT * FROM detail_faktur WHERE no_faktur = '$no_faktur_old'");
            while ($od = $old_details->fetch_assoc()) {
                $conn->query("UPDATE produk SET stock = stock + {$od['qty']} WHERE id_produk = {$od['id_produk']}");
            }
            $conn->query("DELETE FROM detail_faktur WHERE no_faktur = '$no_faktur_old'");

            $subtotal = 0;
            $items = isset($_POST['items']) ? $_POST['items'] : [];
            if (empty($items)) throw new Exception("Harap masukkan minimal 1 produk!");

            $detail_queries = [];
            foreach ($items as $item) {
                $id_produk = intval($item['id_produk']);
                $qty = intval($item['qty']);
                
                $p_info = $conn->query("SELECT price, stock FROM produk WHERE id_produk = $id_produk")->fetch_assoc();
                if (!$p_info) throw new Exception("Produk tidak ditemukan!");
                if ($p_info['stock'] < $qty) throw new Exception("Stok tidak mencukupi!");
                
                $price = $p_info['price'];
                $subtotal += ($price * $qty);

                $detail_queries[] = "INSERT INTO detail_faktur (id_produk, no_faktur, qty, price) VALUES ($id_produk, '$no_faktur', $qty, $price)";
                $detail_queries[] = "UPDATE produk SET stock = stock - $qty WHERE id_produk = $id_produk";
            }

            $grand_total = $subtotal + (($ppn / 100) * $subtotal) - $dp;

            if (!$conn->query("UPDATE faktur SET no_faktur='$no_faktur', tgl_faktur='$tgl_faktur', due_date='$due_date', metode_bayar='$metode_bayar', ppn=$ppn, dp=$dp, grand_total=$grand_total, user='$user', id_customer=$id_customer, id_perusahaan=$id_perusahaan WHERE no_faktur='$no_faktur_old'")) {
                throw new Exception("Gagal memperbarui faktur: " . $conn->error);
            }

            foreach ($detail_queries as $q) {
                if (!$conn->query($q)) throw new Exception("Gagal menyimpan detail baru: " . $conn->error);
            }

            $conn->commit();
            $message = "Transaksi berhasil diperbarui!";
            $action = 'list';
        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}

// MENANGANI HAPUS TRANSAKSI
if ($action == 'delete' && isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $conn->begin_transaction();
    try {
        // Restore stock
        $old_details = $conn->query("SELECT * FROM detail_faktur WHERE no_faktur = '$id'");
        while ($od = $old_details->fetch_assoc()) {
            $conn->query("UPDATE produk SET stock = stock + {$od['qty']} WHERE id_produk = {$od['id_produk']}");
        }
        $conn->query("DELETE FROM faktur WHERE no_faktur = '$id'");
        $conn->commit();
        $message = "Transaksi berhasil dihapus!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
    $action = 'list';
}

// TAMPILAN 1: DAFTAR TRANSAKSI
if ($action == 'list') {
    $result = $conn->query("SELECT f.*, c.nama_customer, c.perusahaan_cust, p.nama_perusahaan FROM faktur f JOIN customer c ON f.id_customer = c.id_customer JOIN perusahaan p ON f.id_perusahaan = p.id_perusahaan ORDER BY f.tgl_faktur DESC");
    ?>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-black uppercase">Daftar Penjualan</h2>
        <a href="index.php?page=penjualan&action=add" class="px-4 py-2 border-4 border-black bg-yellow-300 shadow-brutal text-sm">Tambah Penjualan</a>
    </div>

    <?php if ($message): ?><div class="p-4 mb-4 bg-green-200 border-4 border-black font-black uppercase shadow-brutal-sm"><?php echo $message; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="p-4 mb-4 bg-red-200 border-4 border-black font-black uppercase shadow-brutal-sm text-black"><?php echo $error; ?></div><?php endif; ?>

    <table class="w-full border-4 border-black shadow-brutal text-sm">
        <thead>
            <tr class="bg-yellow-300 border-b-4 border-black text-left">
                <th class="p-3 border-r-2 border-black">No Faktur</th>
                <th class="p-3 border-r-2 border-black">Tanggal</th>
                <th class="p-3 border-r-2 border-black">Jatuh Tempo</th>
                <th class="p-3 border-r-2 border-black">Customer</th>
                <th class="p-3 border-r-2 border-black">Total Faktur</th>
                <th class="p-3 border-r-2 border-black">Metode</th>
                <th class="p-3 border-r-2 border-black">Sales</th>
                <th class="p-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
                <tr class="border-b-2 border-black hover:bg-stone-50">
                    <td class="p-3 border-r-2 border-black"><?php echo htmlspecialchars($row['no_faktur']); ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo formatDate($row['tgl_faktur']); ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo formatDate($row['due_date']); ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo htmlspecialchars($row['nama_customer'] . " (" . $row['perusahaan_cust'] . ")"); ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo formatRupiah($row['grand_total']); ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo htmlspecialchars($row['metode_bayar']); ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo htmlspecialchars($row['user']); ?></td>
                    <td class="p-3 flex gap-2">
                        <a href="index.php?page=penjualan&action=cetak&id=<?php echo urlencode($row['no_faktur']); ?>" class="px-2 py-1 border-2 border-black bg-sky-300 text-xs shadow-brutal-sm">Cetak</a>
                        <a href="index.php?page=penjualan&action=edit&id=<?php echo urlencode($row['no_faktur']); ?>" class="px-2 py-1 border-2 border-black bg-white text-xs shadow-brutal-sm">Ubah</a>
                        <a href="index.php?page=penjualan&action=delete&id=<?php echo urlencode($row['no_faktur']); ?>" class="px-2 py-1 border-2 border-black bg-red-400 text-xs shadow-brutal-sm" onclick="return confirm('Hapus transaksi? Stok dikembalikan.')">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="8" class="p-3 text-center">Belum ada transaksi.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

<?php
// TAMPILAN 2: FORM TAMBAH & EDIT TRANSAKSI (JQUERY DYNAMIC INTERACTION)
} elseif ($action == 'add' || $action == 'edit') {
    $no_faktur = "INV-" . date('YmdHis');
    $tgl_faktur = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+14 days'));
    $user = 'Admin';
    $id_perusahaan = $id_customer = $metode_bayar = '';
    $ppn = 11;
    $dp = 0;
    $details_array = [];

    if ($action == 'edit' && isset($_GET['id'])) {
        $id = $conn->real_escape_string($_GET['id']);
        $f_data = $conn->query("SELECT * FROM faktur WHERE no_faktur = '$id'")->fetch_assoc();
        if ($f_data) {
            $no_faktur = $f_data['no_faktur'];
            $tgl_faktur = $f_data['tgl_faktur'];
            $due_date = $f_data['due_date'];
            $user = $f_data['user'];
            $id_perusahaan = $f_data['id_perusahaan'];
            $id_customer = $f_data['id_customer'];
            $metode_bayar = $f_data['metode_bayar'];
            $ppn = $f_data['ppn'];
            $dp = $f_data['dp'];
            
            $d_res = $conn->query("SELECT * FROM detail_faktur WHERE no_faktur = '$id'");
            while ($dr = $d_res->fetch_assoc()) $details_array[] = $dr;
        }
    }
    ?>
    <h2 class="text-xl font-black uppercase mb-4"><?php echo $action == 'add' ? 'Tambah' : 'Ubah'; ?> Penjualan</h2>
    <?php if ($error): ?><div class="p-4 mb-4 bg-red-200 border-4 border-black font-black uppercase shadow-brutal-sm"><?php echo $error; ?></div><?php endif; ?>

    <form action="index.php?page=penjualan&action=<?php echo $action; ?>" method="POST" class="p-6 border-4 border-black shadow-brutal bg-white flex flex-col gap-6" id="fakturForm">
        <input type="hidden" name="no_faktur_old" value="<?php echo htmlspecialchars($no_faktur); ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs uppercase">No. Faktur *</label>
                    <input type="text" name="no_faktur" value="<?php echo htmlspecialchars($no_faktur); ?>" required class="p-2 border-2 border-black outline-none shadow-brutal-sm">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs uppercase">Tanggal Faktur *</label>
                    <input type="date" name="tgl_faktur" value="<?php echo $tgl_faktur; ?>" required class="p-2 border-2 border-black outline-none shadow-brutal-sm">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs uppercase">Tanggal Jatuh Tempo *</label>
                    <input type="date" name="due_date" value="<?php echo $due_date; ?>" required class="p-2 border-2 border-black outline-none shadow-brutal-sm">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs uppercase">User / Sales *</label>
                    <input type="text" name="user" value="<?php echo htmlspecialchars($user); ?>" required class="p-2 border-2 border-black outline-none shadow-brutal-sm">
                </div>
            </div>
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs uppercase">Pilih Perusahaan Penerbit *</label>
                    <select name="id_perusahaan" required class="p-2 border-2 border-black outline-none shadow-brutal-sm">
                        <option value="">-- Pilih Perusahaan --</option>
                        <?php while ($p = $perusahaan_list->fetch_assoc()): ?>
                            <option value="<?php echo $p['id_perusahaan']; ?>" <?php echo $p['id_perusahaan'] == $id_perusahaan ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['nama_perusahaan']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs uppercase">Pilih Customer *</label>
                    <select name="id_customer" required class="p-2 border-2 border-black outline-none shadow-brutal-sm">
                        <option value="">-- Pilih Customer --</option>
                        <?php while ($c = $customer_list->fetch_assoc()): ?>
                            <option value="<?php echo $c['id_customer']; ?>" <?php echo $c['id_customer'] == $id_customer ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['nama_customer'] . " - " . $c['perusahaan_cust']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs uppercase">Metode Pembayaran *</label>
                    <select name="metode_bayar" required class="p-2 border-2 border-black outline-none shadow-brutal-sm">
                        <option value="Transfer Bank" <?php echo $metode_bayar == 'Transfer Bank' ? 'selected' : ''; ?>>Transfer Bank</option>
                        <option value="Cash" <?php echo $metode_bayar == 'Cash' ? 'selected' : ''; ?>>Cash / Tunai</option>
                        <option value="Kredit" <?php echo $metode_bayar == 'Kredit' ? 'selected' : ''; ?>>Kredit</option>
                    </select>
                </div>
            </div>
        </div>

        <hr class="border-2 border-black my-2">
        <h3 class="text-md font-black uppercase">Keranjang Produk</h3>
        
        <table class="w-full border-2 border-black text-sm" id="itemsTable">
            <thead>
                <tr class="bg-stone-100 border-b-2 border-black text-left">
                    <th class="p-2 border-r-2 border-black">Nama Produk</th>
                    <th class="p-2 border-r-2 border-black w-[180px]">Harga Satuan</th>
                    <th class="p-2 border-r-2 border-black w-[100px]">Qty</th>
                    <th class="p-2 border-r-2 border-black w-[200px]">Total</th>
                    <th class="p-2 w-[80px]">Aksi</th>
                </tr>
            </thead>
            <tbody id="itemsContainer">
                <!-- jQuery will append rows dynamically here -->
            </tbody>
        </table>

        <div>
            <button type="button" class="px-3 py-1.5 border-2 border-black bg-sky-300 shadow-brutal-sm text-xs" id="btnAddRow">Tambah Baris Produk</button>
        </div>

        <!-- Mini Calculator Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2">
            <div></div>
            <div class="p-4 border-4 border-black bg-stone-50 shadow-brutal flex flex-col gap-3">
                <div class="flex flex-col gap-1">
                    <label class="text-xs uppercase">PPN (%)</label>
                    <input type="number" name="ppn" id="ppn" step="0.01" min="0" value="<?php echo $ppn; ?>" class="p-2 border-2 border-black outline-none">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs uppercase">Uang Muka / Down Payment (DP)</label>
                    <input type="number" name="dp" id="dp" step="0.01" min="0" value="<?php echo $dp; ?>" class="p-2 border-2 border-black outline-none">
                </div>
                
                <div class="border-t-2 border-black pt-3 mt-1 flex flex-col gap-1.5 text-sm">
                    <div class="flex justify-between"><span>Subtotal:</span><strong id="lblSubtotal">Rp 0</strong></div>
                    <div class="flex justify-between"><span>PPN:</span><strong id="lblPPN">Rp 0</strong></div>
                    <div class="flex justify-between text-base border-t-4 border-double border-black pt-2 mt-1"><span>GRAND TOTAL:</span><strong class="text-red-500" id="lblGrandTotal">Rp 0</strong></div>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="px-4 py-2 border-4 border-black bg-yellow-300 shadow-brutal">Simpan Transaksi</button>
            <a href="index.php?page=penjualan" class="px-4 py-2 border-4 border-black bg-red-400 shadow-brutal">Batal</a>
        </div>
    </form>

    <!-- jQuery Dynamic UI Interactions (Best Practice & Pre-existing Library) -->
    <script>
        const productsList = <?php echo json_encode($products_array); ?>;
        let rowCount = 0;

        // Fungsi menambah baris item belanja
        function addProductRow(productId = '', qty = 1) {
            let options = '<option value="">-- Pilih Produk --</option>';
            productsList.forEach(p => {
                let sel = p.id_produk == productId ? 'selected' : '';
                options += `<option value="${p.id_produk}" data-price="${p.price}" ${sel}>${p.nama_produk} (Stok: ${p.stock})</option>`;
            });

            const rowHtml = `
                <tr class="border-b-2 border-black item-row" id="row-${rowCount}">
                    <td class="p-2 border-r-2 border-black">
                        <select name="items[${rowCount}][id_produk]" required class="w-full p-1 border-2 border-black opt-produk">${options}</select>
                    </td>
                    <td class="p-2 border-r-2 border-black">
                        <input type="number" readonly class="w-full p-1 border-2 border-black bg-stone-100 inp-harga" value="0">
                    </td>
                    <td class="p-2 border-r-2 border-black">
                        <input type="number" name="items[${rowCount}][qty]" required min="1" value="${qty}" class="w-full p-1 border-2 border-black inp-qty">
                    </td>
                    <td class="p-2 border-r-2 border-black font-bold">
                        <span class="lbl-total-baris">Rp 0</span>
                        <input type="hidden" class="val-total-baris" value="0">
                    </td>
                    <td class="p-2 text-center">
                        <button type="button" class="px-2 py-0.5 border-2 border-black bg-red-400 text-xs btn-del-row">Hapus</button>
                    </td>
                </tr>`;
            
            $('#itemsContainer').append(rowHtml);
            if(productId !== '') updateRowCalculations($(`#row-${rowCount}`));
            rowCount++;
        }

        // Hitung ulang baris item
        function updateRowCalculations(row) {
            const opt = row.find('.opt-produk option:selected');
            const price = parseFloat(opt.data('price')) || 0;
            const qty = parseInt(row.find('.inp-qty').value || row.find('.inp-qty').val()) || 0;
            const total = price * qty;

            row.find('.inp-harga').val(price);
            row.find('.val-total-baris').val(total);
            row.find('.lbl-total-baris').text(new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(total));
            
            calculateInvoiceSummary();
        }

        // Hitung total keseluruhan invoice
        function calculateInvoiceSummary() {
            let subtotal = 0;
            $('.val-total-baris').each(function() {
                subtotal += parseFloat($(this).val()) || 0;
            });

            const ppnPercent = parseFloat($('#ppn').val()) || 0;
            const dp = parseFloat($('#dp').val()) || 0;

            const ppnVal = (ppnPercent / 100) * subtotal;
            const grandTotal = subtotal + ppnVal - dp;

            $('#lblSubtotal').text(new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(subtotal));
            $('#lblPPN').text(new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(ppnVal));
            $('#lblGrandTotal').text(new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(grandTotal >= 0 ? grandTotal : 0));
        }

        $(document).ready(function() {
            // Event tambah baris
            $('#btnAddRow').click(function() { addProductRow(); });
            
            // Event delegasi jQuery saat produk diganti / qty diubah
            $(document).on('change', '.opt-produk', function() { updateRowCalculations($(this).closest('.item-row')); });
            $(document).on('input', '.inp-qty', function() { updateRowCalculations($(this).closest('.item-row')); });
            $(document).on('input', '#ppn, #dp', function() { calculateInvoiceSummary(); });

            // Event hapus baris
            $(document).on('click', '.btn-del-row', function() {
                $(this).closest('.item-row').remove();
                calculateInvoiceSummary();
            });

            // Memuat baris awal
            const existing = <?php echo json_encode($details_array); ?>;
            if (existing.length > 0) {
                existing.forEach(d => addProductRow(d.id_produk, d.qty));
            } else {
                addProductRow();
            }
        });
    </script>
<?php
// TAMPILAN 3: CETAK FAKTUR PENJUALAN
} elseif ($action == 'cetak' && isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    
    // Get Faktur with Company and Customer
    $faktur = $conn->query("SELECT f.*, c.nama_customer, c.perusahaan_cust, c.alamat AS alamat_cust, p.nama_perusahaan, p.alamat AS alamat_pers, p.no_telp AS telp_pers, p.fax AS fax_pers FROM faktur f JOIN customer c ON f.id_customer = c.id_customer JOIN perusahaan p ON f.id_perusahaan = p.id_perusahaan WHERE f.no_faktur = '$id'")->fetch_assoc();

    // Get Details
    $details = $conn->query("SELECT df.*, pr.nama_produk, pr.jenis FROM detail_faktur df JOIN produk pr ON df.id_produk = pr.id_produk WHERE df.no_faktur = '$id'");
    
    $subtotal = 0;
    $details_list = [];
    while ($d = $details->fetch_assoc()) {
        $subtotal += ($d['qty'] * $d['price']);
        $details_list[] = $d;
    }
    
    $ppn_val = ($faktur['ppn'] / 100) * $subtotal;
    ?>
    <div class="no-print flex justify-between items-center mb-6">
        <h2 class="text-xl font-black uppercase">Cetak Faktur</h2>
        <div>
            <button onclick="window.print()" class="px-4 py-2 border-4 border-black bg-yellow-300 shadow-brutal text-sm">Cetak Sekarang</button>
            <a href="index.php?page=penjualan" class="px-4 py-2 border-4 border-black bg-red-400 shadow-brutal text-sm">Kembali</a>
        </div>
    </div>

    <!-- Printable Invoice Sheet -->
    <div class="border-4 border-black p-10 bg-white shadow-brutal">
        <div class="flex justify-between mb-8 border-b-4 border-black pb-4">
            <div>
                <h1 class="text-2xl font-black uppercase"><?php echo htmlspecialchars($faktur['nama_perusahaan']); ?></h1>
                <p class="text-xs mt-1 text-stone-600"><?php echo htmlspecialchars($faktur['alamat_pers']); ?></p>
                <p class="text-xs text-stone-600">Telp: <?php echo htmlspecialchars($faktur['telp_pers']); ?> | Fax: <?php echo htmlspecialchars($faktur['fax_pers'] ?: '-'); ?></p>
            </div>
            <div class="text-right">
                <div class="inline-block bg-yellow-300 border-4 border-black px-4 py-2 text-xl font-black shadow-brutal-sm uppercase">FAKTUR</div>
                <div class="mt-4 text-xs font-bold">NO: <?php echo htmlspecialchars($faktur['no_faktur']); ?></div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="border-4 border-black p-4 bg-stone-50">
                <div class="bg-black text-white text-[10px] px-2 py-0.5 inline-block uppercase mb-2">Ditagihkan Kepada:</div>
                <h3 class="font-black text-md"><?php echo htmlspecialchars($faktur['nama_customer']); ?></h3>
                <p class="text-sm font-bold"><?php echo htmlspecialchars($faktur['perusahaan_cust']); ?></p>
                <p class="text-xs text-stone-600 mt-1"><?php echo nl2br(htmlspecialchars($faktur['alamat_cust'])); ?></p>
            </div>
            <div class="border-4 border-black p-4 bg-stone-50 flex flex-col justify-between">
                <div>
                    <div class="bg-black text-white text-[10px] px-2 py-0.5 inline-block uppercase mb-2">Informasi Faktur:</div>
                    <div class="text-xs flex flex-col gap-1 mt-1">
                        <div class="flex"><span class="w-[120px] font-bold">Tgl Faktur:</span><span><?php echo formatDate($faktur['tgl_faktur']); ?></span></div>
                        <div class="flex"><span class="w-[120px] font-bold">Jatuh Tempo:</span><span><?php echo formatDate($faktur['due_date']); ?></span></div>
                        <div class="flex"><span class="w-[120px] font-bold">Metode Bayar:</span><span class="uppercase"><?php echo htmlspecialchars($faktur['metode_bayar']); ?></span></div>
                    </div>
                </div>
            </div>
        </div>

        <table class="w-full border-4 border-black mb-6">
            <thead>
                <tr class="bg-yellow-300 border-b-2 border-black text-left text-xs">
                    <th class="p-2 border-r-2 border-black text-center w-12">NO</th>
                    <th class="p-2 border-r-2 border-black">NAMA PRODUK</th>
                    <th class="p-2 border-r-2 border-black text-right w-36">HARGA SATUAN</th>
                    <th class="p-2 border-r-2 border-black text-center w-24">QTY</th>
                    <th class="p-2 text-right w-40">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($details_list as $item): 
                    $total_item = $item['qty'] * $item['price'];
                ?>
                    <tr class="border-b-2 border-black text-xs">
                        <td class="p-2 border-r-2 border-black text-center"><?php echo $no++; ?></td>
                        <td class="p-2 border-r-2 border-black"><?php echo htmlspecialchars($item['nama_produk']); ?> (<?php echo htmlspecialchars($item['jenis']); ?>)</td>
                        <td class="p-2 border-r-2 border-black text-right"><?php echo formatRupiah($item['price']); ?></td>
                        <td class="p-2 border-r-2 border-black text-center"><?php echo $item['qty']; ?></td>
                        <td class="p-2 text-right font-bold"><?php echo formatRupiah($total_item); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            <div class="border-4 border-black p-4 text-xs flex flex-col gap-1 justify-center">
                <div class="font-bold border-b-2 border-black pb-1 mb-1">KETENTUAN:</div>
                <p>1. Pembayaran sah jika dana sudah masuk ke rekening.</p>
                <p>2. Barang yang sudah dibeli tidak dapat ditukar.</p>
                <p>3. Dicetak otomatis oleh sales: <?php echo htmlspecialchars($faktur['user']); ?></p>
            </div>
            <div class="border-4 border-black p-4 bg-stone-50 text-xs flex flex-col gap-1.5">
                <div class="flex justify-between"><span>Subtotal:</span><span><?php echo formatRupiah($subtotal); ?></span></div>
                <div class="flex justify-between"><span>PPN (<?php echo $faktur['ppn']; ?>%):</span><span><?php echo formatRupiah($ppn_val); ?></span></div>
                <div class="flex justify-between border-b-2 border-black pb-1.5"><span>Uang Muka (DP):</span><span>- <?php echo formatRupiah($faktur['dp']); ?></span></div>
                <div class="flex justify-between text-sm font-black mt-1"><span>TOTAL BAYAR:</span><span class="text-red-500"><?php echo formatRupiah($faktur['grand_total']); ?></span></div>
            </div>
        </div>

        <div class="flex justify-between mt-12 text-xs">
            <div class="text-center w-48">
                <p class="mb-14">Penerima / Pelanggan,</p>
                <div class="border-b-2 border-black w-full"></div>
                <p class="font-black mt-2"><?php echo htmlspecialchars($faktur['nama_customer']); ?></p>
            </div>
            <div class="text-center w-48">
                <p class="mb-14">Hormat Kami,</p>
                <div class="border-b-2 border-black w-full"></div>
                <p class="font-black mt-2"><?php echo htmlspecialchars($faktur['nama_perusahaan']); ?></p>
            </div>
        </div>
    </div>
<?php } ?>
