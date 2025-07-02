<?php
require_once '../config.php';
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';
require_once 'templates/header.php';

// --- PHP LOGIC (Tidak ada perubahan di sini) ---
$filter_praktikum = isset($_GET['praktikum']) ? $_GET['praktikum'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT l.id, u.nama as nama_mahasiswa, mp.nama_praktikum, m.judul_modul, l.tanggal_kumpul, l.nilai, m.deadline
        FROM laporan l
        JOIN users u ON l.id_mahasiswa = u.id
        JOIN modul m ON l.id_modul = m.id
        JOIN mata_praktikum mp ON m.id_praktikum = mp.id
        WHERE 1=1";

if (!empty($filter_praktikum)) {
    $sql .= " AND mp.id = " . intval($filter_praktikum);
}
if ($filter_status === 'dinilai') {
    $sql .= " AND l.nilai IS NOT NULL";
} elseif ($filter_status === 'belum_dinilai') {
    $sql .= " AND l.nilai IS NULL";
}
$sql .= " ORDER BY l.tanggal_kumpul DESC";

$laporan_result = $conn->query($sql);
$praktikum_result = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum");

?>

<div class="bg-white p-8 rounded-2xl shadow-lg mb-10">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Filter Laporan</h2>
    <form action="laporan.php" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
        <div>
            <label for="praktikum" class="block text-gray-700 font-bold mb-2">Praktikum</label>
            <select name="praktikum" id="praktikum" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Praktikum</option>
                <?php while($p = $praktikum_result->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo ($filter_praktikum == $p['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['nama_praktikum']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="status" class="block text-gray-700 font-bold mb-2">Status Penilaian</label>
            <select name="status" id="status" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Status</option>
                <option value="dinilai" <?php echo ($filter_status == 'dinilai') ? 'selected' : ''; ?>>Sudah Dinilai</option>
                <option value="belum_dinilai" <?php echo ($filter_status == 'belum_dinilai') ? 'selected' : ''; ?>>Belum Dinilai</option>
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition-all transform hover:scale-105 w-full md:w-auto">Filter</button>
    </form>
</div>

<div class="bg-white p-8 rounded-2xl shadow-lg">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Daftar Laporan Masuk</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-slate-100">
                <tr>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase" style="min-width: 150px;">Mahasiswa</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase" style="min-width: 200px;">Modul</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 uppercase" style="min-width: 140px;">Tanggal Kumpul</th>
                    <th class="py-3 px-4 text-center text-sm font-semibold text-gray-600 uppercase">Pengumpulan</th>
                    <th class="py-3 px-4 text-center text-sm font-semibold text-gray-600 uppercase">Status Nilai</th>
                    <th class="py-3 px-4 text-center text-sm font-semibold text-gray-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while($row = $laporan_result->fetch_assoc()): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3 px-4 font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></td>
                    <td class="py-3 px-4 text-gray-700">
                        <div class="font-semibold"><?php echo htmlspecialchars($row['judul_modul']); ?></div>
                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($row['nama_praktikum']); ?></div>
                    </td>
                    <td class="py-3 px-4 text-gray-600"><?php echo date('d M Y, H:i', strtotime($row['tanggal_kumpul'])); ?></td>
                    <td class="py-3 px-4 text-center">
                        <?php
                        if (!empty($row['deadline'])) {
                            if (strtotime($row['tanggal_kumpul']) > strtotime($row['deadline'])) {
                                echo '<span class="bg-red-100 text-red-700 font-bold px-3 py-1.5 rounded-full text-xs">Terlambat</span>';
                            } else {
                                echo '<span class="bg-green-100 text-green-700 font-bold px-3 py-1.5 rounded-full text-xs">Tepat Waktu</span>';
                            }
                        } else { echo '<span class="text-gray-400">-</span>'; }
                        ?>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <?php if(is_null($row['nilai'])): ?>
                            <span class="bg-yellow-100 text-yellow-700 font-bold px-3 py-1.5 rounded-full text-xs">Belum Dinilai</span>
                        <?php else: ?>
                            <span class="bg-blue-100 text-blue-700 font-bold px-3 py-1.5 rounded-full text-xs">Sudah Dinilai</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-4 text-center whitespace-nowrap">
                        <a href="nilai.php?id=<?php echo $row['id']; ?>" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-3 rounded-md text-xs transition-transform transform hover:scale-105">
                            <?php echo is_null($row['nilai']) ? 'Beri Nilai' : 'Detail'; ?>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>