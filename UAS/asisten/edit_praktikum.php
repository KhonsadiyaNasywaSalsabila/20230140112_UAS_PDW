<?php
require_once '../config.php';

// Cek apakah ada ID di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: praktikum.php");
    exit();
}
$id = $_GET['id'];

// Logika untuk UPDATE data saat form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama_praktikum'];
    $deskripsi = $_POST['deskripsi'];
    
    $stmt = $conn->prepare("UPDATE mata_praktikum SET nama_praktikum = ?, deskripsi = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nama, $deskripsi, $id);
    $stmt->execute();
    
    // Redirect kembali ke halaman utama setelah update
    header("Location: praktikum.php");
    exit();
}

// Ambil data praktikum yang akan diedit untuk ditampilkan di form
$stmt = $conn->prepare("SELECT * FROM mata_praktikum WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    // Jika data tidak ditemukan, redirect
    header("Location: praktikum.php");
    exit();
}
$praktikum = $result->fetch_assoc();

$pageTitle = 'Edit Praktikum';
$activePage = 'praktikum';
require_once 'templates/header.php';
?>

<div class="bg-white p-8 rounded-2xl shadow-lg max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Mata Praktikum</h2>
    <form action="edit_praktikum.php?id=<?php echo $id; ?>" method="POST">
        <div class="mb-4">
            <label for="nama_praktikum" class="block text-gray-700 font-bold mb-2">Nama Praktikum</label>
            <input type="text" id="nama_praktikum" name="nama_praktikum" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo htmlspecialchars($praktikum['nama_praktikum']); ?>" required>
        </div>
        <div class="mb-6">
            <label for="deskripsi" class="block text-gray-700 font-bold mb-2">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="5" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></textarea>
        </div>
        <div class="flex items-center">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-all transform hover:scale-105">
                Simpan Perubahan
            </button>
            <a href="praktikum.php" class="ml-4 text-gray-600 hover:text-gray-800 font-medium">Batal</a>
        </div>
    </form>
</div>

<?php
require_once 'templates/footer.php';
?>