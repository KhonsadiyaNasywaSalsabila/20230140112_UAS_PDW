<?php
// Pastikan semua perhitungan waktu menggunakan zona waktu yang benar (WIB)
date_default_timezone_set('Asia/Jakarta');

require_once '../config.php';
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header.php';

// --- LOGIKA UNTUK KARTU RINGKASAN ---
$total_modul = $conn->query("SELECT COUNT(id) as total FROM modul")->fetch_assoc()['total'];
$total_laporan = $conn->query("SELECT COUNT(id) as total FROM laporan")->fetch_assoc()['total'];
$laporan_belum_dinilai = $conn->query("SELECT COUNT(id) as total FROM laporan WHERE nilai IS NULL")->fetch_assoc()['total'];

// --- LOGIKA UNTUK AKTIVITAS TERBARU ---
$aktivitas_query = "SELECT u.nama as nama_mahasiswa, m.judul_modul, l.tanggal_kumpul
                    FROM laporan l
                    JOIN users u ON l.id_mahasiswa = u.id
                    JOIN modul m ON l.id_modul = m.id
                    ORDER BY l.tanggal_kumpul DESC
                    LIMIT 5";
$aktivitas_result = $conn->query($aktivitas_query);


// --- FUNGSI BANTUAN (LENGKAP) ---

// Fungsi untuk membuat inisial dari nama
function get_initials($name) {
    $words = explode(" ", $name);
    $initials = "";
    $i = 0;
    foreach ($words as $w) {
        if ($i < 2) {
            $initials .= strtoupper($w[0]);
            $i++;
        }
    }
    return $initials;
}

// Fungsi untuk format "time ago"
function time_ago($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;

    $seconds_in_minute = 60;
    $seconds_in_hour = 3600;
    $seconds_in_day = 86400;

    if ($time_difference >= $seconds_in_day) {
        $days = floor($time_difference / $seconds_in_day);
        return "$days hari lalu";
    } elseif ($time_difference >= $seconds_in_hour) {
        $hours = floor($time_difference / $seconds_in_hour);
        return "$hours jam lalu";
    } elseif ($time_difference >= $seconds_in_minute) {
        $minutes = floor($time_difference / $seconds_in_minute);
        return "$minutes menit lalu";
    } else {
        return "Baru saja";
    }
}
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-2xl shadow-lg transform hover:scale-105 transition-transform duration-300">
        <div class="flex items-center">
            <div class="bg-white/30 p-3 rounded-xl">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
            </div>
            <div class="ml-4">
                <p class="text-lg font-medium">Total Modul Diajarkan</p>
                <p class="text-4xl font-bold"><?php echo $total_modul; ?></p>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-2xl shadow-lg transform hover:scale-105 transition-transform duration-300">
        <div class="flex items-center">
            <div class="bg-white/30 p-3 rounded-xl">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="ml-4">
                <p class="text-lg font-medium">Total Laporan Masuk</p>
                <p class="text-4xl font-bold"><?php echo $total_laporan; ?></p>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white p-6 rounded-2xl shadow-lg transform hover:scale-105 transition-transform duration-300">
        <div class="flex items-center">
            <div class="bg-white/30 p-3 rounded-xl">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="ml-4">
                <p class="text-lg font-medium">Laporan Belum Dinilai</p>
                <p class="text-4xl font-bold"><?php echo $laporan_belum_dinilai; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white p-8 rounded-2xl shadow-lg mt-10">
    <h3 class="text-2xl font-bold text-gray-800 mb-6">Aktivitas Laporan Terbaru</h3>
    <div class="space-y-6">
        <?php if ($aktivitas_result->num_rows > 0): ?>
            <?php while($aktivitas = $aktivitas_result->fetch_assoc()): ?>
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-slate-200 flex items-center justify-center mr-4 flex-shrink-0">
                    <span class="font-bold text-slate-500 text-lg"><?php echo get_initials($aktivitas['nama_mahasiswa']); ?></span>
                </div>
                <div>
                    <p class="text-gray-800 text-lg">
                        <strong class="font-semibold"><?php echo htmlspecialchars($aktivitas['nama_mahasiswa']); ?></strong> 
                        <span class="text-gray-500">mengumpulkan laporan untuk</span> 
                        <strong class="font-semibold"><?php echo htmlspecialchars($aktivitas['judul_modul']); ?></strong>
                    </p>
                    <p class="text-sm text-gray-500 mt-1"><?php echo time_ago($aktivitas['tanggal_kumpul']); ?></p>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-500">Belum ada aktivitas laporan.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>