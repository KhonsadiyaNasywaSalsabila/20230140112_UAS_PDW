<?php
date_default_timezone_set('Asia/Jakarta');
require_once '../config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: modul.php");
    exit();
}
$id_modul = $_GET['id'];
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_perubahan'])) {
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
        $nama_file_lama = $_POST['nama_file_lama'];
        $nama_file_baru = $nama_file_lama;

        if (isset($_FILES['file_materi_baru']) && $_FILES['file_materi_baru']['error'] == 0) {
            if (!empty($nama_file_lama) && file_exists("../uploads/materi/" . $nama_file_lama)) {
                unlink("../uploads/materi/" . $nama_file_lama);
            }
            $target_dir = "../uploads/materi/";
            $nama_file_baru = time() . '_' . basename($_FILES["file_materi_baru"]["name"]);
            $target_file = $target_dir . $nama_file_baru;
            move_uploaded_file($_FILES["file_materi_baru"]["tmp_name"], $target_file);
        }

        $stmt = $conn->prepare("UPDATE modul SET id_praktikum=?, judul_modul=?, deskripsi_modul=?, file_materi=?, deadline=? WHERE id=?");
        $stmt->bind_param("issssi", $id_praktikum, $judul_modul, $deskripsi_modul, $nama_file_baru, $deadline, $id_modul);
        $stmt->execute();
        header("Location: modul.php");
        exit();
    }
}

$pageTitle = 'Edit Modul';
$activePage = 'modul';
require_once 'templates/header.php';

$stmt = $conn->prepare("SELECT * FROM modul WHERE id = ?");
$stmt->bind_param("i", $id_modul);
$stmt->execute();
$modul = $stmt->get_result()->fetch_assoc();
if (!$modul) {
    header("Location: modul.php");
    exit();
}
$praktikum_result = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum");
$now_for_input = date('Y-m-d\TH:i');
?>

<div class="bg-white p-8 rounded-2xl shadow-lg max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Modul</h2>
    <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Gagal Menyimpan</p>
            <p><?php echo $error_message; ?></p>
        </div>
    <?php endif; ?>
    <form action="edit_modul.php?id=<?php echo $id_modul; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="nama_file_lama" value="<?php echo htmlspecialchars($modul['file_materi']); ?>">
        
        <div class="space-y-4">
            <div>
                <label for="id_praktikum" class="block text-gray-700 font-bold mb-2">Praktikum</label>
                <select id="id_praktikum" name="id_praktikum" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    <?php while ($p = $praktikum_result->fetch_assoc()): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $modul['id_praktikum']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['nama_praktikum']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label for="judul_modul" class="block text-gray-700 font-bold mb-2">Judul Modul</label>
                <input type="text" id="judul_modul" name="judul_modul" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo htmlspecialchars($modul['judul_modul']); ?>" required>
            </div>
            <div>
                <label for="deskripsi_modul" class="block text-gray-700 font-bold mb-2">Deskripsi Tugas</label>
                <textarea id="deskripsi_modul" name="deskripsi_modul" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($modul['deskripsi_modul']); ?></textarea>
            </div>
            <div>
                <label for="deadline" class="block text-gray-700 font-bold mb-2">Deadline</label>
                <input type="datetime-local" id="deadline" name="deadline" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?php echo !empty($modul['deadline']) ? date('Y-m-d\TH:i', strtotime($modul['deadline'])) : ''; ?>" min="<?php echo $now_for_input; ?>">
            </div>
            <div>
                <label for="file_materi_baru" class="block text-gray-700 font-bold mb-2">Ganti File Materi (Opsional)</label>
                <?php if (!empty($modul['file_materi'])): ?>
                    <p class="text-sm text-gray-500 mb-2">File saat ini: <a href="../uploads/materi/<?php echo htmlspecialchars($modul['file_materi']); ?>" class="text-indigo-600 hover:underline" target="_blank"><?php echo htmlspecialchars($modul['file_materi']); ?></a></p>
                <?php endif; ?>
                <input type="file" id="file_materi_baru" name="file_materi_baru" class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg cursor-pointer file:mr-4 file:py-3 file:px-4 file:border-0 file:font-semibold">
            </div>
        </div>

        <div class="flex items-center mt-8 pt-6 border-t">
            <button type="submit" name="simpan_perubahan" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-all transform hover:scale-105">
                Simpan Perubahan
            </button>
            <a href="modul.php" class="ml-4 text-gray-600 hover:text-gray-800 font-medium">Batal</a>
        </div>
    </form>
</div>

<?php require_once 'templates/footer.php'; ?>