CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','asisten') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- TABEL BARU: `mata_praktikum` (untuk fitur Mengelola Mata Praktikum)
--
CREATE TABLE `mata_praktikum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_praktikum` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- TABEL BARU: `modul` (untuk fitur Mengelola Modul)
--
CREATE TABLE `modul` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_praktikum` int(11) NOT NULL,
  `judul_modul` varchar(255) NOT NULL,
  `deskripsi_modul` text DEFAULT NULL,
  `file_materi` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_praktikum` (`id_praktikum`),
  CONSTRAINT `modul_ibfk_1` FOREIGN KEY (`id_praktikum`) REFERENCES `mata_praktikum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- TABEL BARU: `pendaftaran_praktikum` (untuk fitur Mendaftar ke Praktikum)
--
CREATE TABLE `pendaftaran_praktikum` (
  `id_pendaftaran` int(11) NOT NULL AUTO_INCREMENT,
  `id_mahasiswa` int(11) NOT NULL,
  `id_praktikum` int(11) NOT NULL,
  `tanggal_pendaftaran` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pendaftaran`),
  UNIQUE KEY `pendaftaran_unik` (`id_mahasiswa`,`id_praktikum`),
  KEY `id_mahasiswa` (`id_mahasiswa`),
  KEY `id_praktikum` (`id_praktikum`),
  CONSTRAINT `pendaftaran_praktikum_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pendaftaran_praktikum_ibfk_2` FOREIGN KEY (`id_praktikum`) REFERENCES `mata_praktikum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- TABEL BARU: `laporan` (untuk fitur Mengumpulkan Laporan & Memberi Nilai)
--
CREATE TABLE `laporan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_modul` int(11) NOT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `file_laporan` varchar(255) NOT NULL,
  `tanggal_kumpul` timestamp NOT NULL DEFAULT current_timestamp(),
  `nilai` int(3) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_modul` (`id_modul`),
  KEY `id_mahasiswa` (`id_mahasiswa`),
  CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_modul`) REFERENCES `modul` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laporan_ibfk_2` FOREIGN KEY (`id_mahasiswa`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Penambahan Kolom Deadline pada tabel Modul
--
ALTER TABLE modul
ADD COLUMN deadline DATETIME DEFAULT NULL;
