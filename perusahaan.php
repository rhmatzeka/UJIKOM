<?php
// perusahaan.php: Modul Manajemen Perusahaan (Tailwind CSS Edition).
// Menggunakan Tailwind Utility Classes untuk kode visual yang sesedikit mungkin.

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = $error = '';

// MENANGANI FORM SUBMIT (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $conn->real_escape_string($_POST['nama_perusahaan']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $no_telp = $conn->real_escape_string($_POST['no_telp']);
    $fax = $conn->real_escape_string($_POST['fax']);

    if (!empty($nama) && !empty($alamat) && !empty($no_telp)) {
        if ($action == 'add') {
            $q = "INSERT INTO perusahaan (nama_perusahaan, alamat, no_telp, fax) VALUES ('$nama', '$alamat', '$no_telp', '$fax')";
            $msg = "Perusahaan berhasil ditambahkan!";
        } else {
            $id = intval($_POST['id_perusahaan']);
            $q = "UPDATE perusahaan SET nama_perusahaan='$nama', alamat='$alamat', no_telp='$no_telp', fax='$fax' WHERE id_perusahaan=$id";
            $msg = "Perusahaan berhasil diperbarui!";
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
    if ($conn->query("SELECT * FROM faktur WHERE id_perusahaan = $id")->num_rows > 0) {
        $error = "Gagal menghapus! Data masih digunakan pada transaksi.";
    } else {
        $conn->query("DELETE FROM perusahaan WHERE id_perusahaan = $id") ? $message = "Perusahaan berhasil dihapus!" : $error = "Gagal: " . $conn->error;
    }
    $action = 'list';
}

// TAMPILAN 1: LIST / DAFTAR TABEL
if ($action == 'list') {
    $result = $conn->query("SELECT * FROM perusahaan ORDER BY id_perusahaan DESC");
    ?>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-black uppercase">Kelola Perusahaan</h2>
        <a href="index.php?page=perusahaan&action=add" class="px-4 py-2 border-4 border-black bg-yellow-300 shadow-brutal hover:translate-x-[-2px] hover:translate-y-[-2px] hover:shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] transition-all uppercase text-sm">Tambah</a>
    </div>

    <?php if ($message): ?><div class="p-4 mb-4 bg-green-200 border-4 border-black font-black uppercase shadow-brutal-sm"><?php echo $message; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="p-4 mb-4 bg-red-200 border-4 border-black font-black uppercase shadow-brutal-sm text-black"><?php echo $error; ?></div><?php endif; ?>

    <table class="w-full border-4 border-black shadow-brutal text-sm">
        <thead>
            <tr class="bg-yellow-300 border-b-4 border-black text-left">
                <th class="p-3 border-r-2 border-black">ID</th>
                <th class="p-3 border-r-2 border-black">Nama Perusahaan</th>
                <th class="p-3 border-r-2 border-black">No. Telp</th>
                <th class="p-3 border-r-2 border-black">Fax</th>
                <th class="p-3 border-r-2 border-black">Alamat</th>
                <th class="p-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
                <tr class="border-b-2 border-black hover:bg-stone-50">
                    <td class="p-3 border-r-2 border-black"><?php echo $row['id_perusahaan']; ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo htmlspecialchars($row['nama_perusahaan']); ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo htmlspecialchars($row['no_telp']); ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo htmlspecialchars($row['fax'] ?: '-'); ?></td>
                    <td class="p-3 border-r-2 border-black"><?php echo nl2br(htmlspecialchars($row['alamat'])); ?></td>
                    <td class="p-3 flex gap-2">
                        <a href="index.php?page=perusahaan&action=detail&id=<?php echo $row['id_perusahaan']; ?>" class="px-2 py-1 border-2 border-black bg-sky-300 text-xs shadow-brutal-sm">Detail</a>
                        <a href="index.php?page=perusahaan&action=edit&id=<?php echo $row['id_perusahaan']; ?>" class="px-2 py-1 border-2 border-black bg-white text-xs shadow-brutal-sm">Ubah</a>
                        <a href="index.php?page=perusahaan&action=delete&id=<?php echo $row['id_perusahaan']; ?>" class="px-2 py-1 border-2 border-black bg-red-400 text-xs shadow-brutal-sm" onclick="return confirm('Hapus?')">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="6" class="p-3 text-center">Belum ada data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

<?php
// TAMPILAN 2: TAMBAH & EDIT FORM (DIGABUNGKAN UNTUK EFEKTIVITAS KODE)
} elseif ($action == 'add' || $action == 'edit') {
    $id = $nama = $no_telp = $fax = $alamat = '';
    if ($action == 'edit' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $data = $conn->query("SELECT * FROM perusahaan WHERE id_perusahaan = $id")->fetch_assoc();
        if ($data) {
            $nama = $data['nama_perusahaan'];
            $no_telp = $data['no_telp'];
            $fax = $data['fax'];
            $alamat = $data['alamat'];
        }
    }
    ?>
    <h2 class="text-xl font-black uppercase mb-4"><?php echo $action == 'add' ? 'Tambah' : 'Ubah'; ?> Perusahaan</h2>
    <?php if ($error): ?><div class="p-4 mb-4 bg-red-200 border-4 border-black font-black uppercase shadow-brutal-sm"><?php echo $error; ?></div><?php endif; ?>

    <form action="index.php?page=perusahaan&action=<?php echo $action; ?>" method="POST" class="p-6 border-4 border-black shadow-brutal bg-white flex flex-col gap-4">
        <input type="hidden" name="id_perusahaan" value="<?php echo $id; ?>">
        
        <div class="flex flex-col gap-1">
            <label class="text-xs uppercase">Nama Perusahaan *</label>
            <input type="text" name="nama_perusahaan" value="<?php echo htmlspecialchars($nama); ?>" required class="p-2 border-2 border-black shadow-brutal-sm outline-none">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs uppercase">No. Telepon *</label>
            <input type="text" name="no_telp" value="<?php echo htmlspecialchars($no_telp); ?>" required class="p-2 border-2 border-black shadow-brutal-sm outline-none">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs uppercase">Fax</label>
            <input type="text" name="fax" value="<?php echo htmlspecialchars($fax); ?>" class="p-2 border-2 border-black shadow-brutal-sm outline-none">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs uppercase">Alamat Lengkap *</label>
            <textarea name="alamat" rows="4" required class="p-2 border-2 border-black shadow-brutal-sm outline-none"><?php echo htmlspecialchars($alamat); ?></textarea>
        </div>

        <div class="mt-4 flex gap-4">
            <button type="submit" class="px-4 py-2 border-4 border-black bg-yellow-300 shadow-brutal">Simpan</button>
            <a href="index.php?page=perusahaan" class="px-4 py-2 border-4 border-black bg-red-400 shadow-brutal">Batal</a>
        </div>
    </form>

<?php
// TAMPILAN 3: DETAIL PERUSAHAAN
} elseif ($action == 'detail' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $data = $conn->query("SELECT * FROM perusahaan WHERE id_perusahaan = $id")->fetch_assoc();
    ?>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-black uppercase">Detail Perusahaan</h2>
        <a href="index.php?page=perusahaan" class="px-4 py-2 border-4 border-black bg-yellow-300 shadow-brutal">Kembali</a>
    </div>

    <div class="p-6 border-4 border-black shadow-brutal bg-white">
        <div class="grid grid-cols-3 border-b-2 border-black py-2"><span class="font-bold">ID Perusahaan</span><span class="col-span-2">: <?php echo $data['id_perusahaan']; ?></span></div>
        <div class="grid grid-cols-3 border-b-2 border-black py-2"><span class="font-bold">Nama Perusahaan</span><span class="col-span-2">: <?php echo htmlspecialchars($data['nama_perusahaan']); ?></span></div>
        <div class="grid grid-cols-3 border-b-2 border-black py-2"><span class="font-bold">No. Telepon</span><span class="col-span-2">: <?php echo htmlspecialchars($data['no_telp']); ?></span></div>
        <div class="grid grid-cols-3 border-b-2 border-black py-2"><span class="font-bold">Fax</span><span class="col-span-2">: <?php echo htmlspecialchars($data['fax'] ?: '-'); ?></span></div>
        <div class="grid grid-cols-3 py-2"><span class="font-bold">Alamat</span><span class="col-span-2">: <?php echo nl2br(htmlspecialchars($data['alamat'])); ?></span></div>
        
        <div class="mt-6 flex gap-4">
            <a href="index.php?page=perusahaan&action=edit&id=<?php echo $data['id_perusahaan']; ?>" class="px-4 py-2 border-4 border-black bg-sky-300 shadow-brutal">Ubah</a>
            <a href="index.php?page=perusahaan&action=delete&id=<?php echo $data['id_perusahaan']; ?>" class="px-4 py-2 border-4 border-black bg-red-400 shadow-brutal" onclick="return confirm('Hapus?')">Hapus</a>
        </div>
    </div>
<?php } ?>
