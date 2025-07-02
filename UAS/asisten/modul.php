<?php
date_default_timezone_set('Asia/Jakarta');
require_once '../config.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_modul'])) {
    $deadline_str = $_POST['deadline'];
    $deadline_valid = true;
    if (!empty($deadline_str)) {
        if (strtotime($deadline_str) < time()) {
            $deadline_valid = false;
            $error_message = "Deadline tidak boleh tanggal atau waktu yang sudah lewat.";
        }
    }
    if ($deadline_valid) {
        $id_praktikum = $_POST['id_praktikum'];
        $judul_modul = $_POST['judul_modul'];
        $deskripsi_modul = $_POST['deskripsi_modul'];
        $deadline = !empty($deadline_str) ? $deadline_str : NULL;
        $nama_file = '';

        if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == 0) {
            $target_dir = "../uploads/materi/";
            $nama_file = time() . '_' . basename($_FILES["file_materi"]["name"]);
            $target_file = $target_dir . $nama_file;
            move_uploaded_file($_FILES["file_materi"]["tmp_name"], $target_file);
        }

        $stmt = $conn->prepare("INSERT INTO modul (id_praktikum, judul_modul, deskripsi_modul, file_materi, deadline) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $id_praktikum, $judul_modul, $deskripsi_modul, $nama_file, $deadline);
        $stmt->execute();
        header("Location: modul.php");
        exit();
    }
}

if (isset($_GET['hapus'])) {
    $id_modul = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM modul WHERE id = ?");
    $stmt->bind_param("i", $id_modul);
    $stmt->execute();
    header("Location: modul.php");
    exit();
}

$pageTitle = 'Manajemen Modul';
$activePage = 'modul';
require_once 'templates/header.php';

$praktikum_result = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum");
$modul_result = $conn->query("SELECT m.id, m.judul_modul, m.file_materi, m.deadline, mp.nama_praktikum FROM modul m JOIN mata_praktikum mp ON m.id_praktikum = mp.id ORDER BY mp.nama_praktikum, m.created_at");
$now_for_input = date('Y-m-d\TH:i');
?>

<div class="bg-white p-8 rounded-2xl shadow-lg mb-10">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Tambah Modul Baru</h2>
    <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Gagal Menambahkan</p>
            <p><?php echo $error_message; ?></p>
        </div>
    <?php endif; ?>
    <form action="modul.php" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="id_praktikum" class="block text-gray-700 font-bold mb-2">Pilih Praktikum</label>
            <select id="id_praktikum" name="id_praktikum" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                <option value="">-- Pilih Mata Praktikum --</option>
                <?php mysqli_data_seek($praktikum_result, 0); ?>
                <?php while ($p = $praktikum_result->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nama_praktikum']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="judul_modul" class="block text-gray-700 font-bold mb-2">Judul Modul</label>
            <input type="text" id="judul_modul" name="judul_modul" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
        </div>
         <div class="mb-4">
            <label for="deskripsi_modul" class="block text-gray-700 font-bold mb-2">Deskripsi Tugas</label>
            <textarea id="deskripsi_modul" name="deskripsi_modul" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Jelaskan detail tugas untuk modul ini..."></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="deadline" class="block text-gray-700 font-bold mb-2">Deadline (Opsional)</label>
                <input type="datetime-local" id="deadline" name="deadline" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" min="<?php echo $now_for_input; ?>">
            </div>
            <div>
                <label for="file_materi" class="block text-gray-700 font-bold mb-2">File Materi (Semua Tipe)</label>
                <input type="file" id="file_materi" name="file_materi" class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg cursor-pointer focus:outline-none file:mr-4 file:py-3 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
            </div>
        </div>
        <div id="file-preview-container" class="mt-4 p-4 border border-dashed border-gray-300 rounded-lg min-h-[100px] flex justify-center items-center">
            <p class="text-gray-500">Pratinjau file akan muncul di sini</p>
        </div>
        <button type="submit" name="tambah_modul" class="mt-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition-all transform hover:scale-105">
            Tambah Modul
        </button>
    </form>
</div>

<div class="bg-white p-8 rounded-2xl shadow-lg">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Daftar Modul</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-slate-100">
                <tr>
                    <th class="py-3 px-6 text-left text-sm font-semibold text-gray-600 uppercase">Praktikum</th>
                    <th class="py-3 px-6 text-left text-sm font-semibold text-gray-600 uppercase">Judul Modul</th>
                    <th class="py-3 px-6 text-left text-sm font-semibold text-gray-600 uppercase">Deadline</th>
                    <th class="py-3 px-6 text-center text-sm font-semibold text-gray-600 uppercase">File</th>
                    <th class="py-3 px-6 text-center text-sm font-semibold text-gray-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                 <?php mysqli_data_seek($modul_result, 0); ?>
                 <?php while($row = $modul_result->fetch_assoc()): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-4 px-6 font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_praktikum']); ?></td>
                    <td class="py-4 px-6 text-gray-700"><?php echo htmlspecialchars($row['judul_modul']); ?></td>
                    <td class="py-4 px-6 text-gray-600"><?php echo $row['deadline'] ? date('d M Y, H:i', strtotime($row['deadline'])) : '-'; ?></td>
                    <td class="py-4 px-6 text-center"><?php echo $row['file_materi'] ? '<a href="../uploads/materi/'.$row['file_materi'].'" class="text-indigo-600 hover:underline" target="_blank">Lihat</a>' : '-'; ?></td>
                    <td class="py-4 px-6 text-center whitespace-nowrap">
                        <a href="edit_modul.php?id=<?php echo $row['id']; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-md text-sm transition-transform transform hover:scale-105">Edit</a>
                        <a href="modul.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus?');" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md text-sm transition-transform transform hover:scale-105 ml-2">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.getElementById('file_materi').addEventListener('change', function(e) {
        const previewContainer = document.getElementById('file-preview-container');
        previewContainer.innerHTML = '';
        if (e.target.files.length === 0) {
            previewContainer.innerHTML = '<p class="text-gray-500">Pratinjau file akan muncul di sini</p>';
            return;
        }
        const file = e.target.files[0];
        const fileType = file.name.split('.').pop().toLowerCase();
        const fileURL = URL.createObjectURL(file);
        const icons = {
            word: '<svg class="w-16 h-16 mx-auto text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zM7.335 11.25a.75.75 0 01.03-1.06l1.4-1.423a.75.75 0 011.06 0l1.4 1.423a.75.75 0 01.03 1.06l-2.13 2.153a.75.75 0 01-1.06 0L7.335 11.25z"/></svg>',
            excel: '<svg class="w-16 h-16 mx-auto text-green-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zM9 12V8h2v4H9zm0 3v-2h2v2H9z"/></svg>',
            default: '<svg class="w-16 h-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>'
        };
        const createIconPreview = (iconSvg, fileName) => {
            const fallback = document.createElement('div');
            fallback.className = 'text-center text-gray-600';
            fallback.innerHTML = `${iconSvg}<p class="mt-2 font-semibold truncate">${fileName}</p>`;
            return fallback;
        };
        if (fileType === 'pdf') {
            const embed = document.createElement('embed');
            embed.src = fileURL;
            embed.type = 'application/pdf';
            embed.className = 'w-full h-96';
            previewContainer.appendChild(embed);
        } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType)) {
            const img = document.createElement('img');
            img.src = fileURL;
            img.className = 'max-w-full max-h-96 rounded-lg object-contain';
            previewContainer.appendChild(img);
        } else if (['doc', 'docx'].includes(fileType)) {
            previewContainer.appendChild(createIconPreview(icons.word, file.name));
        } else if (['xls', 'xlsx'].includes(fileType)) {
            previewContainer.appendChild(createIconPreview(icons.excel, file.name));
        } else {
            previewContainer.appendChild(createIconPreview(icons.default, file.name));
        }
    });
</script>

<?php require_once 'templates/footer.php'; ?>