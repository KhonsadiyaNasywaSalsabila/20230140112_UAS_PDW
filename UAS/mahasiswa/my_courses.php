<?php
require_once '../config.php';
$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';

$id_mahasiswa = $_SESSION['user_id'];

// Ambil praktikum yang diikuti oleh mahasiswa yang login
$stmt = $conn->prepare("SELECT mp.id, mp.nama_praktikum, mp.deskripsi 
                        FROM mata_praktikum mp
                        JOIN pendaftaran_praktikum pp ON mp.id = pp.id_praktikum
                        WHERE pp.id_mahasiswa = ?");
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <?php
            // --- LOGIKA BARU: Ambil deadline untuk modul yang BELUM DIKUMPULKAN ---
            $stmt_deadline = $conn->prepare(
                "SELECT m.judul_modul, m.deadline 
                 FROM modul m
                 LEFT JOIN laporan l ON m.id = l.id_modul AND l.id_mahasiswa = ?
                 WHERE m.id_praktikum = ? 
                   AND l.id IS NULL 
                   AND m.deadline > NOW() 
                 ORDER BY m.deadline ASC"
            );
            // Binding dua parameter: id_mahasiswa dan id_praktikum
            $stmt_deadline->bind_param("ii", $id_mahasiswa, $row['id']);
            $stmt_deadline->execute();
            $deadlines_result = $stmt_deadline->get_result();
        ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col">
            <div class="p-6 flex-grow">
                <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($row['nama_praktikum']); ?></h3>
                <p class="text-gray-600 mb-4 whitespace-pre-line"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                
                <?php if ($deadlines_result->num_rows > 0): ?>
                    <div class="bg-red-50 border border-red-200 text-red-800 p-3 rounded-lg text-sm mb-4">
                        <p class="font-bold mb-2">Deadline Mendatang:</p>
                        <ul class="space-y-1 list-disc list-inside">
                            <?php while($deadline_item = $deadlines_result->fetch_assoc()): ?>
                                <li>
                                    <strong class="font-semibold"><?php echo htmlspecialchars($deadline_item['judul_modul']); ?>:</strong>
                                    <?php echo date('d M Y, H:i', strtotime($deadline_item['deadline'])); ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            <div class="p-6 pt-0 mt-auto">
                <a href="course_detail.php?id=<?php echo $row['id']; ?>" class="block w-full text-center bg-cyan-500 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded transition-colors duration-300">
                    Lihat Detail & Tugas
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="col-span-full text-center text-gray-500">Anda belum mendaftar praktikum apapun. Silakan <a href="courses.php" class="text-blue-600 hover:underline">cari praktikum</a>.</p>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>