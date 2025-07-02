<?php
require_once '../config.php';
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header_mahasiswa.php'; 

// Dapatkan ID mahasiswa dari session
$id_mahasiswa = $_SESSION['user_id'];

// --- LOGIKA UNTUK KARTU RINGKASAN DINAMIS ---
// 1. Hitung jumlah praktikum yang diikuti
$stmt_praktikum = $conn->prepare("SELECT COUNT(id_pendaftaran) as total FROM pendaftaran_praktikum WHERE id_mahasiswa = ?");
$stmt_praktikum->bind_param("i", $id_mahasiswa);
$stmt_praktikum->execute();
$result_praktikum = $stmt_praktikum->get_result()->fetch_assoc();
$jumlah_praktikum_diikuti = $result_praktikum['total'];

// 2. Hitung jumlah tugas yang sudah dikumpulkan (selesai)
$stmt_tugas_selesai = $conn->prepare("SELECT COUNT(id) as total FROM laporan WHERE id_mahasiswa = ?");
$stmt_tugas_selesai->bind_param("i", $id_mahasiswa);
$stmt_tugas_selesai->execute();
$result_tugas_selesai = $stmt_tugas_selesai->get_result()->fetch_assoc();
$jumlah_tugas_selesai = $result_tugas_selesai['total'];

// 3. Hitung jumlah tugas yang menunggu
$stmt_total_modul = $conn->prepare(
    "SELECT COUNT(m.id) as total 
     FROM modul m 
     JOIN pendaftaran_praktikum pp ON m.id_praktikum = pp.id_praktikum 
     WHERE pp.id_mahasiswa = ?"
);
$stmt_total_modul->bind_param("i", $id_mahasiswa);
$stmt_total_modul->execute();
$result_total_modul = $stmt_total_modul->get_result()->fetch_assoc();
$total_modul = $result_total_modul['total'];
$jumlah_tugas_menunggu = $total_modul - $jumlah_tugas_selesai;


// --- LOGIKA UNTUK NOTIFIKASI DINAMIS ---
$notifikasi = [];

// 1. Ambil notifikasi: Nilai telah diberikan
$stmt_nilai = $conn->prepare(
    "SELECT m.judul_modul, m.id_praktikum, l.tanggal_kumpul 
     FROM laporan l JOIN modul m ON l.id_modul = m.id
     WHERE l.id_mahasiswa = ? AND l.nilai IS NOT NULL ORDER BY l.tanggal_kumpul DESC LIMIT 3"
);
$stmt_nilai->bind_param("i", $id_mahasiswa);
$stmt_nilai->execute();
$result_nilai = $stmt_nilai->get_result();
while ($row = $result_nilai->fetch_assoc()) {
    $notifikasi[] = ['tipe' => 'nilai', 'teks' => 'Nilai untuk <a href="course_detail.php?id='.$row['id_praktikum'].'" class="font-semibold text-blue-600 hover:underline">' . htmlspecialchars($row['judul_modul']) . '</a> telah diberikan.', 'waktu' => $row['tanggal_kumpul']];
}

// 2. Ambil notifikasi: Deadline mendekat (dalam 3 hari ke depan)
$stmt_deadline = $conn->prepare(
    "SELECT m.judul_modul, m.id_praktikum, m.deadline FROM modul m
     JOIN pendaftaran_praktikum pp ON m.id_praktikum = pp.id_praktikum
     LEFT JOIN laporan l ON m.id = l.id_modul AND l.id_mahasiswa = pp.id_mahasiswa
     WHERE pp.id_mahasiswa = ? AND l.id IS NULL AND m.deadline IS NOT NULL AND m.deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 3 DAY)
     ORDER BY m.deadline ASC LIMIT 3"
);
$stmt_deadline->bind_param("i", $id_mahasiswa);
$stmt_deadline->execute();
$result_deadline = $stmt_deadline->get_result();
while ($row = $result_deadline->fetch_assoc()) {
    $notifikasi[] = ['tipe' => 'deadline', 'teks' => 'Batas waktu pengumpulan untuk <a href="course_detail.php?id='.$row['id_praktikum'].'" class="font-semibold text-blue-600 hover:underline">' . htmlspecialchars($row['judul_modul']) . '</a> semakin dekat!', 'waktu' => $row['deadline']];
}

// 3. Ambil notifikasi: Pendaftaran berhasil
$stmt_daftar = $conn->prepare(
    "SELECT mp.nama_praktikum, mp.id as id_praktikum, pp.tanggal_pendaftaran FROM pendaftaran_praktikum pp
     JOIN mata_praktikum mp ON pp.id_praktikum = mp.id
     WHERE pp.id_mahasiswa = ? ORDER BY pp.tanggal_pendaftaran DESC LIMIT 2"
);
$stmt_daftar->bind_param("i", $id_mahasiswa);
$stmt_daftar->execute();
$result_daftar = $stmt_daftar->get_result();
while ($row = $result_daftar->fetch_assoc()) {
    $notifikasi[] = ['tipe' => 'pendaftaran', 'teks' => 'Anda berhasil mendaftar pada mata praktikum <a href="course_detail.php?id='.$row['id_praktikum'].'" class="font-semibold text-blue-600 hover:underline">' . htmlspecialchars($row['nama_praktikum']) . '</a>.', 'waktu' => $row['tanggal_pendaftaran']];
}

// Urutkan semua notifikasi berdasarkan waktu (terbaru lebih dulu)
usort($notifikasi, function($a, $b) {
    return strtotime($b['waktu']) - strtotime($a['waktu']);
});
$notifikasi = array_slice($notifikasi, 0, 5);
?>

<div class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white p-8 rounded-xl shadow-lg mb-8">
    <h1 class="text-3xl font-bold">Selamat Datang Kembali, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h1>
    <p class="mt-2 opacity-90">Terus semangat dalam menyelesaikan semua modul praktikummu.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-blue-600"><?php echo $jumlah_praktikum_diikuti; ?></div>
        <div class="mt-2 text-lg text-gray-600">Praktikum Diikuti</div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-green-500"><?php echo $jumlah_tugas_selesai; ?></div>
        <div class="mt-2 text-lg text-gray-600">Tugas Selesai</div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-yellow-500"><?php echo $jumlah_tugas_menunggu; ?></div>
        <div class="mt-2 text-lg text-gray-600">Tugas Menunggu</div>
    </div>
</div>

<div class="bg-white p-6 rounded-xl shadow-md">
    <h3 class="text-2xl font-bold text-gray-800 mb-4">Notifikasi Terbaru</h3>
    <?php if (empty($notifikasi)): ?>
        <p class="text-gray-500">Tidak ada notifikasi baru untuk saat ini.</p>
    <?php else: ?>
        <ul class="space-y-4">
            <?php foreach ($notifikasi as $item): ?>
                <li class="flex items-start p-3 border-b border-gray-100 last:border-b-0">
                    <?php
                        $icon = '';
                        switch ($item['tipe']) {
                            case 'nilai': $icon = '🔔'; break;
                            case 'deadline': $icon = '⏳'; break;
                            case 'pendaftaran': $icon = '✅'; break;
                        }
                    ?>
                    <span class="text-xl mr-4"><?php echo $icon; ?></span>
                    <div><?php echo $item['teks']; ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>