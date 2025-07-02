<?php
require_once '../config.php';
$pageTitle = 'Detail Praktikum';
$activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';

// Cek ID Praktikum dari URL
if (!isset($_GET['id'])) {
    header("Location: my_courses.php");
    exit();
}
$id_praktikum = $_GET['id'];
$id_mahasiswa = $_SESSION['user_id'];

// Logika untuk upload laporan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_laporan'])) {
    $id_modul = $_POST['id_modul'];
    $target_dir = "../uploads/laporan/";
    $file_name = time() . '_' . basename($_FILES["file_laporan"]["name"]);
    $target_file = $target_dir . $file_name;
    if (move_uploaded_file($_FILES["file_laporan"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO laporan (id_modul, id_mahasiswa, file_laporan) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_modul, $id_mahasiswa, $file_name);
        $stmt->execute();
    }
}

// Ambil data detail praktikum
$stmt_praktikum = $conn->prepare("SELECT nama_praktikum FROM mata_praktikum WHERE id = ?");
$stmt_praktikum->bind_param("i", $id_praktikum);
$stmt_praktikum->execute();
$praktikum = $stmt_praktikum->get_result()->fetch_assoc();

// Ambil semua modul untuk praktikum ini
$stmt_modul = $conn->prepare("SELECT * FROM modul WHERE id_praktikum = ? ORDER BY created_at ASC");
$stmt_modul->bind_param("i", $id_praktikum);
$stmt_modul->execute();
$result_modul = $stmt_modul->get_result();
?>

<h1 class="text-4xl font-extrabold text-gray-800 mb-8"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></h1>

<div class="space-y-8">
    <?php while($modul = $result_modul->fetch_assoc()): ?>
        <?php
        $stmt_laporan = $conn->prepare("SELECT * FROM laporan WHERE id_modul = ? AND id_mahasiswa = ?");
        $stmt_laporan->bind_param("ii", $modul['id'], $id_mahasiswa);
        $stmt_laporan->execute();
        $laporan = $stmt_laporan->get_result()->fetch_assoc();
        
        $deadline_lewat = false;
        if (!empty($modul['deadline'])) {
            $deadline_lewat = time() > strtotime($modul['deadline']);
        }
        ?>
    <div class="bg-white p-8 rounded-2xl shadow-lg">
        <div class="grid md:grid-cols-2 gap-8 items-start">
            
            <div>
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($modul['judul_modul']); ?></h3>
                    <?php if (!empty($modul['deadline'])): ?>
                        <span class="text-sm font-semibold px-3 py-1 rounded-full <?php echo $deadline_lewat ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'; ?>">
                            Deadline: <?php echo date('d M Y, H:i', strtotime($modul['deadline'])); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <p class="text-gray-600 whitespace-pre-line leading-relaxed"><?php echo htmlspecialchars($modul['deskripsi_modul']); ?></p>
            </div>

            <div class="bg-slate-50 p-6 rounded-xl border">
                <?php if ($laporan): ?>
                    <h4 class="font-bold text-lg text-green-700 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Laporan Terkumpul
                    </h4>
                    <p class="text-sm text-gray-500 mb-4">Pada: <?php echo date('d M Y, H:i', strtotime($laporan['tanggal_kumpul'])); ?></p>
                    
                    <?php if (is_null($laporan['nilai'])): ?>
                        <div class="bg-yellow-100 text-yellow-800 p-4 rounded-lg text-center">
                            <p class="font-semibold">Menunggu Penilaian</p>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <p class="text-sm text-gray-500">Nilai Anda</p>
                            <p class="text-6xl font-extrabold text-blue-600 my-2"><?php echo htmlspecialchars($laporan['nilai']); ?></p>
                            <?php if(!empty($laporan['feedback'])): ?>
                                <p class="text-sm text-gray-700 mt-2 p-3 bg-gray-100 rounded-md"><strong>Feedback:</strong> <?php echo htmlspecialchars($laporan['feedback']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php elseif ($deadline_lewat): ?>
                    <div class="text-center text-red-600 font-semibold p-4">
                        Batas Waktu Pengumpulan Telah Berakhir.
                    </div>
                <?php else: ?>
                     <h4 class="font-bold text-lg text-gray-800 mb-4">Aksi</h4>
                     <?php if (!empty($modul['file_materi'])): ?>
                        <a href="../uploads/materi/<?php echo $modul['file_materi']; ?>" target="_blank" class="block w-full text-center mb-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-transform transform hover:scale-105">Unduh Materi</a>
                     <?php endif; ?>
                     
                     <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_modul" value="<?php echo $modul['id']; ?>">
                        <label for="file-laporan-<?php echo $modul['id']; ?>" class="block text-sm font-medium text-gray-700 mb-2">Unggah Laporan Anda:</label>
                        <input type="file" name="file_laporan" id="file-laporan-<?php echo $modul['id']; ?>" class="file-upload-input w-full text-gray-700 bg-white border border-gray-300 rounded-lg cursor-pointer focus:outline-none file:mr-4 file:py-2.5 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" data-modul-id="<?php echo $modul['id']; ?>" required>
                        
                        <div id="preview-container-<?php echo $modul['id']; ?>" class="mt-4 p-3 border border-dashed border-gray-300 rounded-lg min-h-[80px] flex justify-center items-center">
                            <p class="text-gray-400 text-sm">Pratinjau akan muncul di sini</p>
                        </div>
                        
                        <button type="submit" class="w-full mt-4 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-transform transform hover:scale-105">Kumpul Laporan</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<script>
    document.querySelectorAll('.file-upload-input').forEach(input => {
        input.addEventListener('change', function(e) {
            const modulId = e.target.dataset.modulId;
            const previewContainer = document.getElementById('preview-container-' + modulId);
            previewContainer.innerHTML = ''; 

            if (e.target.files.length === 0) {
                previewContainer.innerHTML = '<p class="text-gray-400 text-sm">Pratinjau akan muncul di sini</p>';
                return;
            }

            const file = e.target.files[0];
            const fileType = file.name.split('.').pop().toLowerCase();
            const fileURL = URL.createObjectURL(file);

            const icons = {
                word: '<svg class="w-12 h-12 mx-auto text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zM7.335 11.25a.75.75 0 01.03-1.06l1.4-1.423a.75.75 0 011.06 0l1.4 1.423a.75.75 0 01.03 1.06l-2.13 2.153a.75.75 0 01-1.06 0L7.335 11.25z"/></svg>',
                default: '<svg class="w-12 h-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>'
            };

            const createIconPreview = (iconSvg, fileName) => {
                const fallback = document.createElement('div');
                fallback.className = 'text-center text-gray-600';
                fallback.innerHTML = `${iconSvg}<p class="mt-1 text-xs font-semibold truncate">${fileName}</p>`;
                return fallback;
            };

            if (fileType === 'pdf') {
                const embed = document.createElement('embed');
                embed.src = fileURL;
                embed.type = 'application/pdf';
                embed.className = 'w-full h-80';
                previewContainer.appendChild(embed);
            } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType)) {
                const img = document.createElement('img');
                img.src = fileURL;
                img.className = 'max-w-full max-h-80 rounded-lg object-contain';
                previewContainer.appendChild(img);
            } else if (['doc', 'docx'].includes(fileType)) {
                previewContainer.appendChild(createIconPreview(icons.word, file.name));
            } else {
                previewContainer.appendChild(createIconPreview(icons.default, file.name));
            }
        });
    });
</script>

<?php
require_once 'templates/footer_mahasiswa.php';
?>