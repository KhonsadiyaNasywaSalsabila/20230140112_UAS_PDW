<?php
require_once '../config.php';

// Cek apakah ada ID di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: laporan.php");
    exit();
}
$id_laporan = $_GET['id'];

// Logika untuk UPDATE data saat form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_nilai'])) {
    $nilai = $_POST['nilai'];
    $feedback = $_POST['feedback'];
    
    $stmt = $conn->prepare("UPDATE laporan SET nilai = ?, feedback = ? WHERE id = ?");
    $stmt->bind_param("isi", $nilai, $feedback, $id_laporan);
    $stmt->execute();
    
    // Redirect kembali ke halaman utama setelah update
    header("Location: laporan.php");
    exit();
}

// Ambil detail laporan yang akan dinilai
$sql = "SELECT l.*, u.nama as nama_mahasiswa, mp.nama_praktikum, m.judul_modul
        FROM laporan l
        JOIN users u ON l.id_mahasiswa = u.id
        JOIN modul m ON l.id_modul = m.id
        JOIN mata_praktikum mp ON m.id_praktikum = mp.id
        WHERE l.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_laporan);
$stmt->execute();
$laporan = $stmt->get_result()->fetch_assoc();

// Jika data laporan dengan ID tersebut tidak ada, redirect
if (!$laporan) {
    header("Location: laporan.php");
    exit();
}

$pageTitle = 'Beri Nilai Laporan';
$activePage = 'laporan';
require_once 'templates/header.php';
?>

<div class="bg-white p-8 rounded-2xl shadow-lg max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Detail Laporan</h2>
    
    <div class="mb-8 border-b border-gray-200 pb-6">
        <div class="grid grid-cols-1 sm:grid-cols-[150px_auto] gap-x-4 gap-y-2 text-lg">
            <p class="font-bold text-gray-800">Mahasiswa:</p> <p class="text-gray-700"><?php echo htmlspecialchars($laporan['nama_mahasiswa']); ?></p>
            <p class="font-bold text-gray-800">Praktikum:</p> <p class="text-gray-700"><?php echo htmlspecialchars($laporan['nama_praktikum']); ?></p>
            <p class="font-bold text-gray-800">Modul:</p> <p class="text-gray-700"><?php echo htmlspecialchars($laporan['judul_modul']); ?></p>
        </div>
        <a href="../uploads/laporan/<?php echo $laporan['file_laporan']; ?>" target="_blank" class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-lg transition-transform transform hover:scale-105">
            Unduh File Laporan
        </a>
    </div>

    <h2 class="text-2xl font-bold text-gray-800 mb-6">Form Penilaian</h2>
    <form action="nilai.php?id=<?php echo $id_laporan; ?>" method="POST">
        <div class="mb-4">
            <label for="nilai" class="block text-gray-700 font-bold mb-2">Nilai (0-100)</label>
            <input type="number" id="nilai" name="nilai" min="0" max="100" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo htmlspecialchars($laporan['nilai'] ?? ''); ?>" required>
        </div>
        <div class="mb-6">
            <label for="feedback" class="block text-gray-700 font-bold mb-2">Feedback (Opsional)</label>
            <textarea id="feedback" name="feedback" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Berikan masukan untuk mahasiswa..."><?php echo htmlspecialchars($laporan['feedback'] ?? ''); ?></textarea>
        </div>
        <div class="flex items-center">
            <button type="submit" name="simpan_nilai" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-all transform hover:scale-105">
                Simpan Nilai
            </button>
            <a href="laporan.php" class="ml-4 text-gray-600 hover:text-gray-800 font-medium">Batal</a>
        </div>
    </form>
</div>

<?php
require_once 'templates/footer.php';
?>