<?php
// customer.php: Modul Manajemen Customer (Tailwind CSS Edition).
// Menggunakan Tailwind Utility Classes untuk kode visual yang sesedikit mungkin.

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = $error = '';

// MENANGANI FORM SUBMIT (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $conn->real_escape_string($_POST['nama_customer']);
    $perusahaan_cust = $conn->real_escape_string($_POST['perusahaan_cust']);
    $alamat = $conn->real_escape_string($_POST['alamat']);

    if (!empty($nama) && !empty($perusahaan_cust) && !empty($alamat)) {
        if ($action == 'add') {
            $q = "INSERT INTO customer (nama_customer, perusahaan_cust, alamat) VALUES ('$nama', '$perusahaan_cust', '$alamat')";
            $msg = "Customer berhasil ditambahkan!";
        } else {
            $id = intval($_POST['id_customer']);
            $q = "UPDATE customer SET nama_customer='$nama', perusahaan_cust='$perusahaan_cust', alamat='$alamat' WHERE id_customer=$id";
            $msg = "Customer berhasil diperbarui!";
        }

        if ($conn->query($q)) {
            $message = $msg;
            $action = 'list';
        } else {
            $error = "Gagal memproses data: " . $conn->error;
        }
    } else {
        $error = "Harap isi semua kolom wajib!";
    }
}

// MENANGANI HAPUS DATA
if ($action == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Validasi Integritas Relasi
    if ($conn->query("SELECT * FROM faktur WHERE id_customer = $id")->num_rows > 0) {
        $error = "Gagal menghapus! Data masih digunakan pada transaksi.";
    } else {
        $conn->query("DELETE FROM customer WHERE id_customer = $id") ? $message = "Customer berhasil dihapus!" : $error = "Gagal: " . $conn->error;
    }
    $action = 'list';
}

// TAMPILAN 1: LIST / DAFTAR TABEL
if ($action == 'list') {
    $result = $conn->query("SELECT * FROM customer ORDER BY id_customer DESC");
    ?>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-black uppercase">Kelola Customer</h2>
        <a href="index.php?page=customer&action=add" class="px-4 py-2 border-4 border-black bg-yellow-300 shadow-brutal hover:translate-x-[-2px] hover:translate-y-[-2px] hover:shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] transition-all uppercase text-sm">Tambah</a>
    </div>

    <?php if ($message): ?><div class="p-4 mb-4 bg-green-200 border-4 border-black font-black uppercase shadow-brutal-sm"><?php echo $message; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="p-4 mb-4 bg-red-200 border-4 border-black font-black uppercase shadow-brutal-sm text-black"><?php echo $error; ?></div><?php endif; ?>

    <table class="w-full border-4 border-black shadow-brutal text-sm">
        <thead>
            <tr class="bg-yellow-300 border-b-4 border-black text-left">
                <th class="p-3 border-r-2 border-black">ID</th>
                <th class="p-3 border-r-2 border-black">Nama Customer</th>
                <th class="p-3 border-r-2 border-black">Perusahaan</th>
                <th class="p-3 border-r-2 border-black">Alamat</th>
                <th class="p-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
                <tr class="border-b-2 border-black hover:bg-stone-50">
                    <td class="p-3 border-r-2 border-black"><?php echo $row['id_customer']; ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo htmlspecialchars($row['nama_customer']); ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo htmlspecialchars($row['perusahaan_cust']); ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo nl2br(htmlspecialchars($row['alamat'])); ?></td>
                    <td class="p-3 flex gap-2">
                        <a href="index.php?page=customer&action=detail&id=<?php echo $row['id_customer']; ?>" class="px-2 py-1 border-2 border-black bg-sky-300 text-xs shadow-brutal-sm">Detail</a>
                        <a href="index.php?page=customer&action=preview&id=<?php echo $row['id_customer']; ?>" class="px-2 py-1 border-2 border-black bg-green-300 text-xs shadow-brutal-sm">Cetak</a>
                        <a href="index.php?page=customer&action=edit&id=<?php echo $row['id_customer']; ?>" class="px-2 py-1 border-2 border-black bg-white text-xs shadow-brutal-sm">Ubah</a>
                        <a href="index.php?page=customer&action=delete&id=<?php echo $row['id_customer']; ?>" class="px-2 py-1 border-2 border-black bg-red-400 text-xs shadow-brutal-sm" onclick="return confirm('Hapus?')">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="5" class="p-3 text-center">Belum ada data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

<?php
// TAMPILAN 2: TAMBAH & EDIT FORM
} elseif ($action == 'add' || $action == 'edit') {
    $id = $nama = $perusahaan_cust = $alamat = '';
    if ($action == 'edit' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $data = $conn->query("SELECT * FROM customer WHERE id_customer = $id")->fetch_assoc();
        if ($data) {
            $nama = $data['nama_customer'];
            $perusahaan_cust = $data['perusahaan_cust'];
            $alamat = $data['alamat'];
        }
    }
    ?>
    <h2 class="text-xl font-black uppercase mb-4"><?php echo $action == 'add' ? 'Tambah' : 'Ubah'; ?> Customer</h2>
    <?php if ($error): ?><div class="p-4 mb-4 bg-red-200 border-4 border-black font-black uppercase shadow-brutal-sm"><?php echo $error; ?></div><?php endif; ?>

    <form action="index.php?page=customer&action=<?php echo $action; ?>" method="POST" class="p-6 border-4 border-black shadow-brutal bg-white flex flex-col gap-4">
        <input type="hidden" name="id_customer" value="<?php echo $id; ?>">
        
        <div class="flex flex-col gap-1">
            <label class="text-xs uppercase">Nama Customer *</label>
            <input type="text" name="nama_customer" value="<?php echo htmlspecialchars($nama); ?>" required class="p-2 border-2 border-black shadow-brutal-sm outline-none">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs uppercase">Perusahaan Customer *</label>
            <input type="text" name="perusahaan_cust" value="<?php echo htmlspecialchars($perusahaan_cust); ?>" required class="p-2 border-2 border-black shadow-brutal-sm outline-none">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs uppercase">Alamat *</label>
            <textarea name="alamat" rows="4" required class="p-2 border-2 border-black shadow-brutal-sm outline-none"><?php echo htmlspecialchars($alamat); ?></textarea>
        </div>

        <div class="mt-4 flex gap-4">
            <button type="submit" class="px-4 py-2 border-4 border-black bg-yellow-300 shadow-brutal">Simpan</button>
            <a href="index.php?page=customer" class="px-4 py-2 border-4 border-black bg-red-400 shadow-brutal">Batal</a>
        </div>
    </form>

<?php
// TAMPILAN 3: DETAIL CUSTOMER
} elseif ($action == 'detail' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $data = $conn->query("SELECT * FROM customer WHERE id_customer = $id")->fetch_assoc();
    ?>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-black uppercase">Detail Customer</h2>
        <a href="index.php?page=customer" class="px-4 py-2 border-4 border-black bg-yellow-300 shadow-brutal">Kembali</a>
    </div>

    <div class="p-6 border-4 border-black shadow-brutal bg-white">
        <div class="grid grid-cols-3 border-b-2 border-black py-2"><span class="font-bold">ID Customer</span><span class="col-span-2">: <?php echo $data['id_customer']; ?></span></div>
        <div class="grid grid-cols-3 border-b-2 border-black py-2"><span class="font-bold">Nama Customer</span><span class="col-span-2">: <?php echo htmlspecialchars($data['nama_customer']); ?></span></div>
        <div class="grid grid-cols-3 border-b-2 border-black py-2"><span class="font-bold">Perusahaan Customer</span><span class="col-span-2">: <?php echo htmlspecialchars($data['perusahaan_cust']); ?></span></div>
        <div class="grid grid-cols-3 py-2"><span class="font-bold">Alamat</span><span class="col-span-2">: <?php echo nl2br(htmlspecialchars($data['alamat'])); ?></span></div>
        
        <div class="mt-6 flex gap-4">
            <a href="index.php?page=customer&action=edit&id=<?php echo $data['id_customer']; ?>" class="px-4 py-2 border-4 border-black bg-sky-300 shadow-brutal">Ubah</a>
            <a href="index.php?page=customer&action=preview&id=<?php echo $data['id_customer']; ?>" class="px-4 py-2 border-4 border-black bg-green-300 shadow-brutal">Cetak Kartu</a>
            <a href="index.php?page=customer&action=delete&id=<?php echo $data['id_customer']; ?>" class="px-4 py-2 border-4 border-black bg-red-400 shadow-brutal" onclick="return confirm('Hapus?')">Hapus</a>
        </div>
    </div>

<?php
// TAMPILAN 4: PREVIEW / CETAK DATA KARTU CUSTOMER
} elseif ($action == 'preview' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $data = $conn->query("SELECT * FROM customer WHERE id_customer = $id")->fetch_assoc();
    ?>
    <div class="no-print flex justify-between items-center mb-6">
        <h2 class="text-xl font-black uppercase">Cetak Customer</h2>
        <div>
            <button onclick="window.print()" class="px-4 py-2 border-4 border-black bg-yellow-300 shadow-brutal">Cetak Sekarang</button>
            <a href="index.php?page=customer" class="px-4 py-2 border-4 border-black bg-red-400 shadow-brutal">Kembali</a>
        </div>
    </div>

    <!-- Tampilan Kartu Identitas Customer (Brutalist print sheet) -->
    <div class="max-w-[500px] mx-auto border-4 border-black p-6 bg-white shadow-brutal relative mt-10">
        <div class="absolute -top-4 left-6 bg-yellow-300 border-2 border-black px-2 py-0.5 text-xs font-black uppercase shadow-brutal-sm">
            Kartu Data Customer
        </div>
        <div class="text-lg font-black uppercase mb-4 border-b-4 border-black pb-2 mt-2">
            <?php echo htmlspecialchars($data['perusahaan_cust']); ?>
        </div>
        <div class="flex flex-col gap-3 text-sm">
            <div>
                <span class="text-stone-500 block text-xs uppercase">ID Customer:</span>
                <span class="font-black text-lg"><?php echo str_pad($data['id_customer'], 5, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div>
                <span class="text-stone-500 block text-xs uppercase">Nama Lengkap:</span>
                <span class="font-black"><?php echo htmlspecialchars($data['nama_customer']); ?></span>
            </div>
            <div>
                <span class="text-stone-500 block text-xs uppercase">Alamat Pelanggan:</span>
                <span class="font-bold"><?php echo nl2br(htmlspecialchars($data['alamat'])); ?></span>
            </div>
        </div>
        <div class="mt-8 text-right text-[10px] text-stone-600 border-t-2 border-black pt-2">
            Dicetak: <?php echo date('d-m-Y H:i:s'); ?>
        </div>
    </div>
<?php } ?>
