<?php
require_once '../config.php';
// ... (PHP logic untuk create/delete tetap sama) ...
$pageTitle = 'Manajemen Pengguna';
$activePage = 'pengguna';
require_once 'templates/header.php';
$users_result = $conn->query("SELECT id, nama, email, role, created_at FROM users ORDER BY created_at DESC");
?>

<div class="bg-white p-8 rounded-2xl shadow-lg mb-10">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Tambah Pengguna Baru</h2>
    <form action="pengguna.php" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="nama" class="block text-gray-700 font-bold mb-2">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div>
                <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                <input type="email" id="email" name="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div>
                <label for="password" class="block text-gray-700 font-bold mb-2">Password</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div>
                <label for="role" class="block text-gray-700 font-bold mb-2">Role</label>
                <select id="role" name="role" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    <option value="mahasiswa">Mahasiswa</option>
                    <option value="asisten">Asisten</option>
                </select>
            </div>
        </div>
        <button type="submit" name="tambah_pengguna" class="mt-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition-all transform hover:scale-105">
            Tambah Pengguna
        </button>
    </form>
</div>

<div class="bg-white p-8 rounded-2xl shadow-lg">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Daftar Pengguna</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-slate-100">
                <tr>
                    <th class="py-3 px-6 text-left text-sm font-semibold text-gray-600 uppercase">Nama</th>
                    <th class="py-3 px-6 text-left text-sm font-semibold text-gray-600 uppercase">Email</th>
                    <th class="py-3 px-6 text-left text-sm font-semibold text-gray-600 uppercase">Role</th>
                    <th class="py-3 px-6 text-center text-sm font-semibold text-gray-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while($row = $users_result->fetch_assoc()): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-4 px-6 font-medium text-gray-900"><?php echo htmlspecialchars($row['nama']); ?></td>
                    <td class="py-4 px-6 text-gray-600"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td class="py-4 px-6 text-gray-600"><?php echo ucfirst($row['role']); ?></td>
                    <td class="py-4 px-6 text-center whitespace-nowrap">
                         <?php if ($row['id'] != $_SESSION['user_id']): ?>
                            <a href="pengguna.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus pengguna ini?');" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md text-sm transition-transform transform hover:scale-105">Hapus</a>
                        <?php else: ?>
                            <span class="text-xs font-semibold text-gray-400">(Akun Anda)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>