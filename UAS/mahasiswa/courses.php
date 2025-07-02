<?php
require_once '../config.php';
$pageTitle = 'Cari Praktikum';
$activePage = 'courses';
require_once 'templates/header_mahasiswa.php';

$id_mahasiswa = $_SESSION['user_id'];
$message = '';

// Logika untuk mendaftar praktikum
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['daftar'])) {
    $id_praktikum = $_POST['id_praktikum'];
    
    // Cek apakah sudah terdaftar
    $cek_stmt = $conn->prepare("SELECT id_pendaftaran FROM pendaftaran_praktikum WHERE id_mahasiswa = ? AND id_praktikum = ?");
    $cek_stmt->bind_param("ii", $id_mahasiswa, $id_praktikum);
    $cek_stmt->execute();
    $result_cek = $cek_stmt->get_result();
    
    if ($result_cek->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO pendaftaran_praktikum (id_mahasiswa, id_praktikum) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_mahasiswa, $id_praktikum);
        $stmt->execute();
        $message = "Berhasil mendaftar!";
    } else {
        $message = "Anda sudah terdaftar pada praktikum ini.";
    }
}

// --- LOGIKA BARU: Ambil semua ID praktikum yang sudah diikuti mahasiswa ---
$enrolled_courses_ids = [];
$stmt_enrolled = $conn->prepare("SELECT id_praktikum FROM pendaftaran_praktikum WHERE id_mahasiswa = ?");
$stmt_enrolled->bind_param("i", $id_mahasiswa);
$stmt_enrolled->execute();
$result_enrolled = $stmt_enrolled->get_result();
while ($row_enrolled = $result_enrolled->fetch_assoc()) {
    $enrolled_courses_ids[] = $row_enrolled['id_praktikum'];
}


// Ambil semua data praktikum
$result_all_courses = $conn->query("SELECT * FROM mata_praktikum ORDER BY nama_praktikum ASC");
?>

<?php if (!empty($message)): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
    <span class="block sm:inline"><?php echo $message; ?></span>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php while($course = $result_all_courses->fetch_assoc()): ?>
    <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col transform hover:-translate-y-2 transition-transform duration-300">
        <div class="p-6 flex-grow">
            <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($course['nama_praktikum']); ?></h3>
            <p class="text-gray-600 mb-4 whitespace-pre-line"><?php echo htmlspecialchars($course['deskripsi']); ?></p>
        </div>
        <div class="p-6 pt-0 mt-auto">
            <?php 
                // --- Periksa apakah ID kursus saat ini ada di dalam array yang sudah diikuti ---
                if (in_array($course['id'], $enrolled_courses_ids)): 
            ?>
                <button class="w-full bg-gray-400 text-white font-bold py-3 px-4 rounded-lg cursor-not-allowed">
                    Sudah Terdaftar
                </button>
            <?php else: ?>
                <form action="" method="POST">
                    <input type="hidden" name="id_praktikum" value="<?php echo $course['id']; ?>">
                    <button type="submit" name="daftar" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-300">
                        Daftar Praktikum
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>