<?php
require_once '../config.php';

// --- LOGIKA UNTUK PROSES FORM (YANG HILANG SEBELUMNYA) ---

// Logika untuk Tambah data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $nama = $_POST['nama_praktikum'];
    $deskripsi = $_POST['deskripsi'];
    $stmt = $conn->prepare("INSERT INTO mata_praktikum (nama_praktikum, deskripsi) VALUES (?, ?)");
    $stmt->bind_param("ss", $nama, $deskripsi);
    $stmt->execute();
    
    header("Location: praktikum.php");
    exit();
}

// Logika untuk Delete data
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM mata_praktikum WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    header("Location: praktikum.php");
    exit();
}

$pageTitle = 'Manajemen Praktikum';
$activePage = 'praktikum';
require_once 'templates/header.php';

// Ambil semua data praktikum untuk ditampilkan
$result = $conn->query("SELECT * FROM mata_praktikum ORDER BY id DESC");
?>

<div class="bg-white p-8 rounded-2xl shadow-lg mb-10">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Tambah Mata Praktikum Baru</h2>
    <form action="praktikum.php" method="POST">
        <div class="mb-4">
            <label for="nama_praktikum" class="block text-gray-700 font-bold mb-2">Nama Praktikum</label>
            <input type="text" id="nama_praktikum" name="nama_praktikum" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-shadow" placeholder="Contoh: Pemrograman Web Lanjut" required>
        </div>
        <div class="mb-6">
            <label for="deskripsi" class="block text-gray-700 font-bold mb-2">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-shadow" placeholder="Jelaskan secara singkat tentang mata praktikum ini..."></textarea>
        </div>
        <button type="submit" name="tambah" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition-all transform hover:scale-105">
            Tambah Praktikum
        </button>
    </form>
</div>

<div class="bg-white p-8 rounded-2xl shadow-lg">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Daftar Mata Praktikum</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-slate-100">
                <tr>
                    <th class="w-1/4 py-3 px-6 text-left text-sm font-semibold text-gray-600 uppercase">Nama Praktikum</th>
                    <th class="w-1/2 py-3 px-6 text-left text-sm font-semibold text-gray-600 uppercase">Deskripsi</th>
                    <th class="w-1/4 py-3 px-6 text-center text-sm font-semibold text-gray-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-4 px-6 font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_praktikum']); ?></td>
                    <td class="py-4 px-6 text-gray-600 whitespace-pre-line"><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                    <td class="py-4 px-6 text-center whitespace-nowrap">
                        <a href="edit_praktikum.php?id=<?php echo $row['id']; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-md text-sm transition-transform transform hover:scale-105">Edit</a>
                        <a href="praktikum.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus?');" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md text-sm transition-transform transform hover:scale-105 ml-2">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>