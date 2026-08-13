<?php
// produk.php: Modul Manajemen Produk (Tailwind CSS Edition).
// Menggunakan Tailwind Utility Classes untuk kode visual yang sesedikit mungkin.

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = $error = '';

// MENANGANI FORM SUBMIT (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $conn->real_escape_string($_POST['nama_produk']);
    $price = floatval($_POST['price']);
    $jenis = $conn->real_escape_string($_POST['jenis']);
    $stock = intval($_POST['stock']);

    if (!empty($nama) && !empty($jenis) && $price >= 0 && $stock >= 0) {
        if ($action == 'add') {
            $q = "INSERT INTO produk (nama_produk, price, jenis, stock) VALUES ('$nama', $price, '$jenis', $stock)";
            $msg = "Produk berhasil ditambahkan!";
        } else {
            $id = intval($_POST['id_produk']);
            $q = "UPDATE produk SET nama_produk='$nama', price=$price, jenis='$jenis', stock=$stock WHERE id_produk=$id";
            $msg = "Produk berhasil diperbarui!";
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
    if ($conn->query("SELECT * FROM detail_faktur WHERE id_produk = $id")->num_rows > 0) {
        $error = "Gagal menghapus! Produk masih digunakan dalam data transaksi faktur.";
    } else {
        $conn->query("DELETE FROM produk WHERE id_produk = $id") ? $message = "Produk berhasil dihapus!" : $error = "Gagal: " . $conn->error;
    }
    $action = 'list';
}

// TAMPILAN 1: LIST / DAFTAR TABEL
if ($action == 'list') {
    $result = $conn->query("SELECT * FROM produk ORDER BY id_produk DESC");
    ?>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-black uppercase">Kelola Produk</h2>
        <a href="index.php?page=produk&action=add" class="px-4 py-2 border-4 border-black bg-yellow-300 shadow-brutal hover:translate-x-[-2px] hover:translate-y-[-2px] hover:shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] transition-all uppercase text-sm">Tambah</a>
    </div>

    <?php if ($message): ?><div class="p-4 mb-4 bg-green-200 border-4 border-black font-black uppercase shadow-brutal-sm"><?php echo $message; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="p-4 mb-4 bg-red-200 border-4 border-black font-black uppercase shadow-brutal-sm text-black"><?php echo $error; ?></div><?php endif; ?>

    <table class="w-full border-4 border-black shadow-brutal text-sm">
        <thead>
            <tr class="bg-yellow-300 border-b-4 border-black text-left">
                <th class="p-3 border-r-2 border-black">ID</th>
                <th class="p-3 border-r-2 border-black">Nama Produk</th>
                <th class="p-3 border-r-2 border-black">Jenis</th>
                <th class="p-3 border-r-2 border-black">Harga Jual</th>
                <th class="p-3 border-r-2 border-black">Stok</th>
                <th class="p-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
                <tr class="border-b-2 border-black hover:bg-stone-50">
                    <td class="p-3 border-r-2 border-black"><?php echo $row['id_produk']; ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo htmlspecialchars($row['jenis']); ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo formatRupiah($row['price']); ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo $row['stock']; ?></td>
                    <td class="p-3 flex gap-2">
                        <a href="index.php?page=produk&action=edit&id=<?php echo $row['id_produk']; ?>" class="px-2 py-1 border-2 border-black bg-white text-xs shadow-brutal-sm">Ubah</a>
                        <a href="index.php?page=produk&action=delete&id=<?php echo $row['id_produk']; ?>" class="px-2 py-1 border-2 border-black bg-red-400 text-xs shadow-brutal-sm" onclick="return confirm('Hapus?')">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="6" class="p-3 text-center">Belum ada data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

<?php
// TAMPILAN 2: TAMBAH & EDIT FORM
} elseif ($action == 'add' || $action == 'edit') {
    $id = $nama = $jenis = $price = $stock = '';
    if ($action == 'edit' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $data = $conn->query("SELECT * FROM produk WHERE id_produk = $id")->fetch_assoc();
        if ($data) {
            $nama = $data['nama_produk'];
            $jenis = $data['jenis'];
            $price = $data['price'];
            $stock = $data['stock'];
        }
    }
    ?>
    <h2 class="text-xl font-black uppercase mb-4"><?php echo $action == 'add' ? 'Tambah' : 'Ubah'; ?> Produk</h2>
    <?php if ($error): ?><div class="p-4 mb-4 bg-red-200 border-4 border-black font-black uppercase shadow-brutal-sm"><?php echo $error; ?></div><?php endif; ?>

    <form action="index.php?page=produk&action=<?php echo $action; ?>" method="POST" class="p-6 border-4 border-black shadow-brutal bg-white flex flex-col gap-4">
        <input type="hidden" name="id_produk" value="<?php echo $id; ?>">
        
        <div class="flex flex-col gap-1">
            <label class="text-xs uppercase">Nama Produk *</label>
            <input type="text" name="nama_produk" value="<?php echo htmlspecialchars($nama); ?>" required class="p-2 border-2 border-black shadow-brutal-sm outline-none">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs uppercase">Jenis / Kategori *</label>
            <input type="text" name="jenis" value="<?php echo htmlspecialchars($jenis); ?>" required class="p-2 border-2 border-black shadow-brutal-sm outline-none">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs uppercase">Harga Jual *</label>
            <input type="number" name="price" step="0.01" min="0" value="<?php echo $price; ?>" required class="p-2 border-2 border-black shadow-brutal-sm outline-none">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs uppercase">Stok Awal *</label>
            <input type="number" name="stock" min="0" value="<?php echo $stock; ?>" required class="p-2 border-2 border-black shadow-brutal-sm outline-none">
        </div>

        <div class="mt-4 flex gap-4">
            <button type="submit" class="px-4 py-2 border-4 border-black bg-yellow-300 shadow-brutal">Simpan</button>
            <a href="index.php?page=produk" class="px-4 py-2 border-4 border-black bg-red-400 shadow-brutal">Batal</a>
        </div>
    </form>
<?php } ?>
