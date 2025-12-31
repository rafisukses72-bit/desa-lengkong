-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 30, 2025 at 08:51 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_pos_system`
--
CREATE DATABASE IF NOT EXISTS `db_pos_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `db_pos_system`;

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int NOT NULL,
  `kode_produk` varchar(20) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `stok` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `kode_produk`, `nama_produk`, `harga`, `stok`, `created_at`) VALUES
(1, 'P001', 'Buku Tulis Sinar Dunia', 5000.00, 99, '2025-12-30 01:51:28'),
(2, 'P002', 'Pulpen Standard Hitam', 3000.00, 150, '2025-12-30 01:51:28'),
(3, 'P003', 'Penggaris 30cm Plastik', 7000.00, 79, '2025-12-30 01:51:28'),
(4, 'P004', 'Penghapus Faber Castell', 2000.00, 120, '2025-12-30 01:51:28'),
(5, 'P005', 'Spidol Whiteboard', 15000.00, 50, '2025-12-30 01:51:28'),
(6, 'P006', 'Stabilo Boss', 8000.00, 75, '2025-12-30 01:51:28'),
(7, 'P007', 'Binder Clip Besar', 5000.00, 200, '2025-12-30 01:51:28'),
(8, 'P008', 'Kertas A4 70gr', 45000.00, 40, '2025-12-30 01:51:28'),
(9, 'P009', 'Isi Stapler No. 10', 10000.00, 100, '2025-12-30 01:51:28'),
(10, 'P010', 'Map Plastik', 1500.00, 180, '2025-12-30 01:51:28');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_detail`
--

CREATE TABLE `transaksi_detail` (
  `id` int NOT NULL,
  `transaksi_id` int DEFAULT NULL,
  `produk_id` int DEFAULT NULL,
  `kode_produk` varchar(20) DEFAULT NULL,
  `nama_produk` varchar(100) DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi_detail`
--

INSERT INTO `transaksi_detail` (`id`, `transaksi_id`, `produk_id`, `kode_produk`, `nama_produk`, `harga`, `jumlah`, `subtotal`) VALUES
(1, 1, 3, 'P003', 'Penggaris 30cm Plastik', 7000.00, 1, 7000.00),
(2, 1, 1, 'P001', 'Buku Tulis Sinar Dunia', 5000.00, 1, 5000.00);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_header`
--

CREATE TABLE `transaksi_header` (
  `id` int NOT NULL,
  `kode_transaksi` varchar(50) NOT NULL,
  `user_id` int DEFAULT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_bayar` decimal(12,2) DEFAULT '0.00',
  `status` enum('pending','selesai','batal') DEFAULT 'selesai'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi_header`
--

INSERT INTO `transaksi_header` (`id`, `kode_transaksi`, `user_id`, `tanggal`, `total_bayar`, `status`) VALUES
(1, 'TRX-20251230015159797', 1, '2025-12-30 08:51:59', 12000.00, 'selesai');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `role` enum('admin','kasir') DEFAULT 'kasir',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `created_at`) VALUES
(1, 'admin', NULL, 'Administrator', 'admin', '2025-12-30 01:51:28'),
(2, 'kasir1', NULL, 'Kasir Toko', 'kasir', '2025-12-30 01:51:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_produk` (`kode_produk`);

--
-- Indexes for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indexes for table `transaksi_header`
--
ALTER TABLE `transaksi_header`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transaksi_header`
--
ALTER TABLE `transaksi_header`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD CONSTRAINT `transaksi_detail_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi_header` (`id`),
  ADD CONSTRAINT `transaksi_detail_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`);

--
-- Constraints for table `transaksi_header`
--
ALTER TABLE `transaksi_header`
  ADD CONSTRAINT `transaksi_header_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
--
-- Database: `desa_lengkong`
--
CREATE DATABASE IF NOT EXISTS `desa_lengkong` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `desa_lengkong`;

-- --------------------------------------------------------

--
-- Table structure for table `agenda`
--

CREATE TABLE `agenda` (
  `id` int NOT NULL,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `jenis` enum('rapat','posyandu','penyuluhan','kegiatan','lainnya') DEFAULT 'kegiatan',
  `peserta` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banner`
--

CREATE TABLE `banner` (
  `id` int NOT NULL,
  `judul` varchar(200) DEFAULT NULL,
  `deskripsi` text,
  `gambar` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `urutan` int DEFAULT '0',
  `aktif` tinyint DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `banner`
--

INSERT INTO `banner` (`id`, `judul`, `deskripsi`, `gambar`, `link`, `urutan`, `aktif`) VALUES
(1, 'Selamat Datang di Desa Lengkong', 'Website resmi Pemerintah Desa Lengkong', 'banner1.jpg', NULL, 1, 1),
(2, 'Desa yang Maju dan Mandiri', 'Mewujudkan masyarakat yang sejahtera', 'banner2.jpg', NULL, 2, 1),
(3, 'Pelayanan Publik Terbaik', 'Melayani dengan hati untuk masyarakat', 'banner3.jpg', NULL, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `berita`
--

CREATE TABLE `berita` (
  `id` int NOT NULL,
  `judul` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `konten` longtext NOT NULL,
  `excerpt` text,
  `kategori` enum('umum','kegiatan','pengumuman','pembangunan') DEFAULT 'umum',
  `gambar` varchar(255) DEFAULT NULL,
  `penulis` varchar(100) DEFAULT NULL,
  `views` int DEFAULT '0',
  `is_featured` tinyint DEFAULT '0',
  `status` enum('draft','published') DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `galeri`
--

CREATE TABLE `galeri` (
  `id` int NOT NULL,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text,
  `gambar` varchar(255) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `layanan`
--

CREATE TABLE `layanan` (
  `id` int NOT NULL,
  `nama_layanan` varchar(100) NOT NULL,
  `deskripsi` text,
  `persyaratan` text,
  `biaya` varchar(50) DEFAULT NULL,
  `estimasi_waktu` varchar(50) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `urutan` int DEFAULT '0',
  `aktif` tinyint DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `layanan`
--

INSERT INTO `layanan` (`id`, `nama_layanan`, `deskripsi`, `persyaratan`, `biaya`, `estimasi_waktu`, `icon`, `urutan`, `aktif`) VALUES
(1, 'Surat Pengantar', 'Surat pengantar untuk berbagai keperluan', 'Fotokopi KTP dan KK', 'Gratis', '1-2 Hari', 'fa-file-alt', 1, 1),
(2, 'Surat Keterangan Domisili', 'Surat keterangan tempat tinggal', 'Fotokopi KTP dan KK, Surat pengantar RT/RW', 'Gratis', '2-3 Hari', 'fa-home', 2, 1),
(3, 'Surat Keterangan Tidak Mampu', 'Surat keterangan tidak mampu (SKTM)', 'Fotokopi KTP dan KK, Surat pengantar RT/RW', 'Gratis', '3-4 Hari', 'fa-hand-holding-heart', 3, 1),
(4, 'Surat Keterangan Usaha', 'Surat keterangan usaha', 'Fotokopi KTP dan KK, Foto usaha', 'Gratis', '2-3 Hari', 'fa-store', 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_surat`
--

CREATE TABLE `pengajuan_surat` (
  `id` int NOT NULL,
  `kode_pengajuan` varchar(20) NOT NULL,
  `user_id` int DEFAULT NULL,
  `nama_pemohon` varchar(100) NOT NULL,
  `nik` varchar(16) NOT NULL,
  `alamat` text NOT NULL,
  `jenis_layanan` varchar(100) NOT NULL,
  `keperluan` text,
  `berkas` varchar(255) DEFAULT NULL,
  `status` enum('pending','diproses','selesai','ditolak') DEFAULT 'pending',
  `catatan_admin` text,
  `tanggal_pengajuan` datetime DEFAULT CURRENT_TIMESTAMP,
  `tanggal_selesai` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int NOT NULL,
  `nama_desa` varchar(100) DEFAULT 'Desa Lengkong',
  `motto_desa` varchar(255) DEFAULT NULL,
  `alamat_kantor` text,
  `telepon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `facebook` varchar(100) DEFAULT NULL,
  `instagram` varchar(100) DEFAULT NULL,
  `youtube` varchar(100) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `tentang_desa` text,
  `sejarah_desa` text,
  `visi` text,
  `misi` text,
  `logo` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `theme_color` varchar(7) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama_desa`, `motto_desa`, `alamat_kantor`, `telepon`, `email`, `website`, `facebook`, `instagram`, `youtube`, `whatsapp`, `latitude`, `longitude`, `tentang_desa`, `sejarah_desa`, `visi`, `misi`, `logo`, `favicon`, `theme_color`, `updated_at`) VALUES
(1, 'Desa Lengkong', 'Maju, Mandiri, Sejahtera', 'Jl. Raya Lengkong No. 1, Kuningan', '0265-123456', 'desa@lengkong.kuningankab.go.id', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '#2c3e50', '2025-12-30 08:43:47');

-- --------------------------------------------------------

--
-- Table structure for table `pesan`
--

CREATE TABLE `pesan` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `subjek` varchar(200) DEFAULT NULL,
  `isi_pesan` text NOT NULL,
  `status` enum('belum_dibaca','sudah_dibaca') DEFAULT 'belum_dibaca',
  `tanggal_kirim` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `potensi`
--

CREATE TABLE `potensi` (
  `id` int NOT NULL,
  `nama` varchar(200) NOT NULL,
  `jenis` enum('umkm','wisata','pertanian','kerajinan','kuliner') DEFAULT 'umkm',
  `deskripsi` text,
  `konten` longtext,
  `gambar` varchar(255) DEFAULT NULL,
  `alamat` text,
  `kontak` varchar(50) DEFAULT NULL,
  `pemilik` varchar(100) DEFAULT NULL,
  `harga` varchar(100) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `views` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rt_rw`
--

CREATE TABLE `rt_rw` (
  `id` int NOT NULL,
  `jenis` enum('RT','RW') DEFAULT 'RT',
  `nomor` varchar(3) NOT NULL,
  `ketua` varchar(100) DEFAULT NULL,
  `wilayah` text,
  `jumlah_kk` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rt_rw`
--

INSERT INTO `rt_rw` (`id`, `jenis`, `nomor`, `ketua`, `wilayah`, `jumlah_kk`) VALUES
(1, 'RW', '01', 'Bapak RW 01', NULL, 85),
(2, 'RT', '01', 'Bapak RT 01', NULL, 25),
(3, 'RT', '02', 'Bapak RT 02', NULL, 30),
(4, 'RT', '03', 'Bapak RT 03', NULL, 30);

-- --------------------------------------------------------

--
-- Table structure for table `struktur_desa`
--

CREATE TABLE `struktur_desa` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `urutan` int DEFAULT '0',
  `deskripsi` text,
  `tugas` text,
  `kontak` varchar(50) DEFAULT NULL,
  `status` enum('aktif','pensiun') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `struktur_desa`
--

INSERT INTO `struktur_desa` (`id`, `nama`, `jabatan`, `foto`, `urutan`, `deskripsi`, `tugas`, `kontak`, `status`) VALUES
(1, 'Dr. H. Ahmad Sujadi, M.Si', 'Kepala Desa', NULL, 1, NULL, NULL, NULL, 'aktif'),
(2, 'Drs. Bambang Sutrisno', 'Sekretaris Desa', NULL, 2, NULL, NULL, NULL, 'aktif'),
(3, 'Siti Nurhaliza, S.E.', 'Bendahara Desa', NULL, 3, NULL, NULL, NULL, 'aktif'),
(4, 'Dr. Rina Marlina, M.Pd', 'Kasi Pemerintahan', NULL, 4, NULL, NULL, NULL, 'aktif');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `role` enum('admin','warga') DEFAULT 'warga',
  `foto_profil` varchar(255) DEFAULT NULL,
  `alamat` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `email`, `telepon`, `role`, `foto_profil`, `alamat`, `created_at`) VALUES
(1, 'admin', '$2y$10$Hh8HQfLJZ7vV6fX9VYqQN.DDd7J7c6V8vW6aK5bN4m3v2c1x0z9A8S', 'Administrator', 'admin@desa.lengkong', NULL, 'admin', NULL, NULL, '2025-12-30 08:43:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agenda`
--
ALTER TABLE `agenda`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banner`
--
ALTER TABLE `banner`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `berita`
--
ALTER TABLE `berita`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `galeri`
--
ALTER TABLE `galeri`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengajuan_surat`
--
ALTER TABLE `pengajuan_surat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_pengajuan` (`kode_pengajuan`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pesan`
--
ALTER TABLE `pesan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `potensi`
--
ALTER TABLE `potensi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rt_rw`
--
ALTER TABLE `rt_rw`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `struktur_desa`
--
ALTER TABLE `struktur_desa`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agenda`
--
ALTER TABLE `agenda`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banner`
--
ALTER TABLE `banner`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `berita`
--
ALTER TABLE `berita`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `layanan`
--
ALTER TABLE `layanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pengajuan_surat`
--
ALTER TABLE `pengajuan_surat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pesan`
--
ALTER TABLE `pesan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `potensi`
--
ALTER TABLE `potensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rt_rw`
--
ALTER TABLE `rt_rw`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `struktur_desa`
--
ALTER TABLE `struktur_desa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pengajuan_surat`
--
ALTER TABLE `pengajuan_surat`
  ADD CONSTRAINT `pengajuan_surat_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
--
-- Database: `pondok_payment`
--
CREATE DATABASE IF NOT EXISTS `pondok_payment` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `pondok_payment`;

-- --------------------------------------------------------

--
-- Table structure for table `kategori_pembayaran`
--

CREATE TABLE `kategori_pembayaran` (
  `id` int NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `keterangan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int NOT NULL,
  `nama_pondok` varchar(200) NOT NULL,
  `alamat` text,
  `telepon` varchar(20) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `tahun_ajaran` varchar(50) DEFAULT NULL,
  `nomor_struk_prefix` varchar(10) DEFAULT 'PS',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama_pondok`, `alamat`, `telepon`, `logo_url`, `tahun_ajaran`, `nomor_struk_prefix`, `created_at`, `updated_at`) VALUES
(1, 'Pondok Pesantren Al-Ikhlas', 'Jl. Pendidikan No. 123', '081234567890', NULL, '2024/2025', 'PS', '2025-12-30 01:48:33', '2025-12-30 01:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `produk_pembayaran`
--

CREATE TABLE `produk_pembayaran` (
  `id` int NOT NULL,
  `kategori_id` int DEFAULT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `tipe_harga` enum('tetap','manual') DEFAULT 'tetap',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `santri`
--

CREATE TABLE `santri` (
  `nis` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `kelas` varchar(50) NOT NULL,
  `asrama` varchar(50) NOT NULL,
  `status` enum('aktif','alumni') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `santri`
--

INSERT INTO `santri` (`nis`, `nama_lengkap`, `kelas`, `asrama`, `status`, `created_at`, `updated_at`) VALUES
('12345678', 'Arini Ulfathul Hasanah', 'III Ibtida', 'Fatimah', 'aktif', '2025-12-30 01:51:12', '2025-12-30 01:51:12'),
('2024001', 'Ahmad Fauzi', 'X IPA', 'Asrama Putra A', 'aktif', '2025-12-30 01:48:51', '2025-12-30 01:48:51'),
('2024002', 'Siti Rahma', 'X IPS', 'Asrama Putri A', 'aktif', '2025-12-30 01:48:51', '2025-12-30 01:48:51'),
('2024003', 'Budi Santoso', 'XI IPA', 'Asrama Putra B', 'aktif', '2025-12-30 01:48:51', '2025-12-30 01:48:51'),
('2024004', 'Dewi Sartika', 'XI IPS', 'Asrama Putri B', 'aktif', '2025-12-30 01:48:51', '2025-12-30 01:48:51'),
('2024005', 'Muhammad Ali', 'XII IPA', 'Asrama Putra C', 'aktif', '2025-12-30 01:48:51', '2025-12-30 01:48:51');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int NOT NULL,
  `kode_transaksi` varchar(50) NOT NULL,
  `santri_nis` varchar(20) NOT NULL,
  `user_id` int NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `diskon` decimal(12,2) DEFAULT '0.00',
  `metode_bayar` enum('tunai','transfer') DEFAULT 'tunai',
  `jumlah_bayar` decimal(12,2) NOT NULL,
  `kembalian` decimal(12,2) NOT NULL,
  `status` enum('selesai','pending','batal') DEFAULT 'selesai',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_detail`
--

CREATE TABLE `transaksi_detail` (
  `id` int NOT NULL,
  `transaksi_id` int NOT NULL,
  `produk_id` int NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `jumlah` int DEFAULT '1',
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','kasir') DEFAULT 'kasir',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$YourHashedPasswordHere', 'Administrator', 'admin', 'aktif', '2025-12-30 01:48:32', '2025-12-30 01:48:32'),
(2, 'kasir', '$2y$10$O5FLNvKr/fyWmnebub0u9uA39ZzUfc9TcNnJICk8qs5XkGWNzJj7i', 'Kasir Utama', 'kasir', 'aktif', '2025-12-30 01:48:34', '2025-12-30 01:48:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kategori_pembayaran`
--
ALTER TABLE `kategori_pembayaran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `produk_pembayaran`
--
ALTER TABLE `produk_pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indexes for table `santri`
--
ALTER TABLE `santri`
  ADD PRIMARY KEY (`nis`),
  ADD KEY `idx_nama` (`nama_lengkap`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_kode` (`kode_transaksi`),
  ADD KEY `idx_tanggal` (`created_at`),
  ADD KEY `idx_santri` (`santri_nis`);

--
-- Indexes for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kategori_pembayaran`
--
ALTER TABLE `kategori_pembayaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `produk_pembayaran`
--
ALTER TABLE `produk_pembayaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `produk_pembayaran`
--
ALTER TABLE `produk_pembayaran`
  ADD CONSTRAINT `produk_pembayaran_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_pembayaran` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`santri_nis`) REFERENCES `santri` (`nis`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD CONSTRAINT `transaksi_detail_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_detail_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk_pembayaran` (`id`) ON DELETE CASCADE;
--
-- Database: `rs_modern`
--
CREATE DATABASE IF NOT EXISTS `rs_modern` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `rs_modern`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `foto_profil` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'default.png',
  `level` enum('superadmin','admin') COLLATE utf8mb4_general_ci DEFAULT 'admin',
  `terakhir_login` datetime DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`, `nama_lengkap`, `email`, `foto_profil`, `level`, `terakhir_login`, `dibuat_pada`) VALUES
(1, 'admin', '$2y$10$YourHashHere', 'Administrator Utama', 'admin@rsmodern.com', 'default.png', 'superadmin', NULL, '2025-12-29 07:19:56');

-- --------------------------------------------------------

--
-- Table structure for table `artikel`
--

CREATE TABLE `artikel` (
  `id_artikel` int NOT NULL,
  `judul` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `konten` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `gambar_sampul` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kategori` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `penulis` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `views` int DEFAULT '0',
  `status` enum('draft','publish') COLLATE utf8mb4_general_ci DEFAULT 'draft',
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diperbarui_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dokter`
--

CREATE TABLE `dokter` (
  `id_dokter` int NOT NULL,
  `id_poli` int DEFAULT NULL,
  `kode_dokter` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `spesialisasi` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_general_ci NOT NULL,
  `no_telepon` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `foto_dokter` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `biaya_konsultasi` decimal(10,2) NOT NULL,
  `status` enum('aktif','cuti','nonaktif') COLLATE utf8mb4_general_ci DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_dokter`
--

CREATE TABLE `jadwal_dokter` (
  `id_jadwal` int NOT NULL,
  `id_dokter` int NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') COLLATE utf8mb4_general_ci NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `kuota` int DEFAULT '20'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kamar`
--

CREATE TABLE `kamar` (
  `id_kamar` int NOT NULL,
  `no_kamar` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `id_poli` int DEFAULT NULL,
  `jenis_kamar` enum('kelas_1','kelas_2','kelas_3','vip','vvip') COLLATE utf8mb4_general_ci NOT NULL,
  `kapasitas` int DEFAULT '1',
  `terisi` int DEFAULT '0',
  `fasilitas` text COLLATE utf8mb4_general_ci,
  `harga_per_hari` decimal(10,2) NOT NULL,
  `status` enum('tersedia','terisi','maintenance') COLLATE utf8mb4_general_ci DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `obat`
--

CREATE TABLE `obat` (
  `id_obat` int NOT NULL,
  `kode_obat` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_obat` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_obat` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `satuan` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `stok` int DEFAULT '0',
  `harga` decimal(10,2) NOT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `status` enum('tersedia','habis') COLLATE utf8mb4_general_ci DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pasien`
--

CREATE TABLE `pasien` (
  `id_pasien` int NOT NULL,
  `no_rekam_medis` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nik` varchar(16) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_general_ci NOT NULL,
  `tempat_lahir` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` text COLLATE utf8mb4_general_ci NOT NULL,
  `no_telepon` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `golongan_darah` enum('A','B','AB','O') COLLATE utf8mb4_general_ci NOT NULL,
  `alergi` text COLLATE utf8mb4_general_ci,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `foto_profil` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'default.png',
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_general_ci DEFAULT 'aktif',
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int NOT NULL,
  `id_pendaftaran` int NOT NULL,
  `no_pembayaran` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `biaya_konsultasi` decimal(10,2) NOT NULL,
  `biaya_obat` decimal(10,2) DEFAULT '0.00',
  `total_biaya` decimal(10,2) NOT NULL,
  `metode_bayar` enum('tunai','transfer','kartu_kredit','asuransi') COLLATE utf8mb4_general_ci DEFAULT 'tunai',
  `status` enum('pending','lunas','gagal') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `bukti_bayar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dibayar_pada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pemeriksaan`
--

CREATE TABLE `pemeriksaan` (
  `id_pemeriksaan` int NOT NULL,
  `id_pendaftaran` int NOT NULL,
  `tekanan_darah` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `suhu_badan` decimal(4,2) DEFAULT NULL,
  `nadi` int DEFAULT NULL,
  `pernafasan` int DEFAULT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `tinggi_badan` int DEFAULT NULL,
  `diagnosis` text COLLATE utf8mb4_general_ci NOT NULL,
  `catatan_dokter` text COLLATE utf8mb4_general_ci,
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id_pendaftaran` int NOT NULL,
  `no_pendaftaran` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `id_pasien` int NOT NULL,
  `id_dokter` int NOT NULL,
  `id_poli` int NOT NULL,
  `tanggal_daftar` date NOT NULL,
  `jam_daftar` time NOT NULL,
  `keluhan` text COLLATE utf8mb4_general_ci NOT NULL,
  `nomor_antrian` int NOT NULL,
  `estimasi_waktu` time DEFAULT NULL,
  `status` enum('menunggu','diproses','selesai','batal') COLLATE utf8mb4_general_ci DEFAULT 'menunggu',
  `dibuat_pada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `poli`
--

CREATE TABLE `poli` (
  `id_poli` int NOT NULL,
  `kode_poli` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_poli` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `foto_poli` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_general_ci DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poli`
--

INSERT INTO `poli` (`id_poli`, `kode_poli`, `nama_poli`, `deskripsi`, `foto_poli`, `status`) VALUES
(1, 'UMUM', 'Poli Umum', 'Pelayanan kesehatan umum untuk segala usia', NULL, 'aktif'),
(2, 'GIGI', 'Poli Gigi', 'Perawatan dan pengobatan gigi', NULL, 'aktif'),
(3, 'JANTUNG', 'Poli Jantung', 'Spesialis jantung dan pembuluh darah', NULL, 'aktif');

-- --------------------------------------------------------

--
-- Table structure for table `resep_obat`
--

CREATE TABLE `resep_obat` (
  `id_resep` int NOT NULL,
  `id_pemeriksaan` int NOT NULL,
  `id_obat` int NOT NULL,
  `jumlah` int NOT NULL,
  `aturan_pakai` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `slider`
--

CREATE TABLE `slider` (
  `id_slider` int NOT NULL,
  `judul` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `urutan` int DEFAULT '0',
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_general_ci DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `slider`
--

INSERT INTO `slider` (`id_slider`, `judul`, `deskripsi`, `gambar`, `link`, `urutan`, `status`) VALUES
(1, 'Pelayanan Terbaik Untuk Anda', 'Kami memberikan pelayanan kesehatan terbaik dengan tim dokter profesional', 'slide1.jpg', NULL, 1, 'aktif'),
(2, 'Teknologi Modern', 'Didukung dengan peralatan medis terkini untuk diagnosa yang akurat', 'slide2.jpg', NULL, 2, 'aktif');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `artikel`
--
ALTER TABLE `artikel`
  ADD PRIMARY KEY (`id_artikel`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`id_dokter`),
  ADD UNIQUE KEY `kode_dokter` (`kode_dokter`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_poli` (`id_poli`);

--
-- Indexes for table `jadwal_dokter`
--
ALTER TABLE `jadwal_dokter`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_dokter` (`id_dokter`);

--
-- Indexes for table `kamar`
--
ALTER TABLE `kamar`
  ADD PRIMARY KEY (`id_kamar`),
  ADD UNIQUE KEY `no_kamar` (`no_kamar`),
  ADD KEY `id_poli` (`id_poli`);

--
-- Indexes for table `obat`
--
ALTER TABLE `obat`
  ADD PRIMARY KEY (`id_obat`),
  ADD UNIQUE KEY `kode_obat` (`kode_obat`);

--
-- Indexes for table `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`id_pasien`),
  ADD UNIQUE KEY `no_rekam_medis` (`no_rekam_medis`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `id_pendaftaran` (`id_pendaftaran`),
  ADD UNIQUE KEY `no_pembayaran` (`no_pembayaran`);

--
-- Indexes for table `pemeriksaan`
--
ALTER TABLE `pemeriksaan`
  ADD PRIMARY KEY (`id_pemeriksaan`),
  ADD UNIQUE KEY `id_pendaftaran` (`id_pendaftaran`);

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id_pendaftaran`),
  ADD UNIQUE KEY `no_pendaftaran` (`no_pendaftaran`),
  ADD KEY `id_pasien` (`id_pasien`),
  ADD KEY `id_dokter` (`id_dokter`),
  ADD KEY `id_poli` (`id_poli`);

--
-- Indexes for table `poli`
--
ALTER TABLE `poli`
  ADD PRIMARY KEY (`id_poli`),
  ADD UNIQUE KEY `kode_poli` (`kode_poli`);

--
-- Indexes for table `resep_obat`
--
ALTER TABLE `resep_obat`
  ADD PRIMARY KEY (`id_resep`),
  ADD KEY `id_pemeriksaan` (`id_pemeriksaan`),
  ADD KEY `id_obat` (`id_obat`);

--
-- Indexes for table `slider`
--
ALTER TABLE `slider`
  ADD PRIMARY KEY (`id_slider`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `artikel`
--
ALTER TABLE `artikel`
  MODIFY `id_artikel` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dokter`
--
ALTER TABLE `dokter`
  MODIFY `id_dokter` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal_dokter`
--
ALTER TABLE `jadwal_dokter`
  MODIFY `id_jadwal` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kamar`
--
ALTER TABLE `kamar`
  MODIFY `id_kamar` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `obat`
--
ALTER TABLE `obat`
  MODIFY `id_obat` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pasien`
--
ALTER TABLE `pasien`
  MODIFY `id_pasien` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pemeriksaan`
--
ALTER TABLE `pemeriksaan`
  MODIFY `id_pemeriksaan` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id_pendaftaran` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `poli`
--
ALTER TABLE `poli`
  MODIFY `id_poli` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `resep_obat`
--
ALTER TABLE `resep_obat`
  MODIFY `id_resep` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `slider`
--
ALTER TABLE `slider`
  MODIFY `id_slider` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dokter`
--
ALTER TABLE `dokter`
  ADD CONSTRAINT `dokter_ibfk_1` FOREIGN KEY (`id_poli`) REFERENCES `poli` (`id_poli`) ON DELETE SET NULL;

--
-- Constraints for table `jadwal_dokter`
--
ALTER TABLE `jadwal_dokter`
  ADD CONSTRAINT `jadwal_dokter_ibfk_1` FOREIGN KEY (`id_dokter`) REFERENCES `dokter` (`id_dokter`) ON DELETE CASCADE;

--
-- Constraints for table `kamar`
--
ALTER TABLE `kamar`
  ADD CONSTRAINT `kamar_ibfk_1` FOREIGN KEY (`id_poli`) REFERENCES `poli` (`id_poli`) ON DELETE SET NULL;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_pendaftaran`) REFERENCES `pendaftaran` (`id_pendaftaran`) ON DELETE CASCADE;

--
-- Constraints for table `pemeriksaan`
--
ALTER TABLE `pemeriksaan`
  ADD CONSTRAINT `pemeriksaan_ibfk_1` FOREIGN KEY (`id_pendaftaran`) REFERENCES `pendaftaran` (`id_pendaftaran`) ON DELETE CASCADE;

--
-- Constraints for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `pendaftaran_ibfk_1` FOREIGN KEY (`id_pasien`) REFERENCES `pasien` (`id_pasien`) ON DELETE CASCADE,
  ADD CONSTRAINT `pendaftaran_ibfk_2` FOREIGN KEY (`id_dokter`) REFERENCES `dokter` (`id_dokter`) ON DELETE CASCADE,
  ADD CONSTRAINT `pendaftaran_ibfk_3` FOREIGN KEY (`id_poli`) REFERENCES `poli` (`id_poli`) ON DELETE CASCADE;

--
-- Constraints for table `resep_obat`
--
ALTER TABLE `resep_obat`
  ADD CONSTRAINT `resep_obat_ibfk_1` FOREIGN KEY (`id_pemeriksaan`) REFERENCES `pemeriksaan` (`id_pemeriksaan`) ON DELETE CASCADE,
  ADD CONSTRAINT `resep_obat_ibfk_2` FOREIGN KEY (`id_obat`) REFERENCES `obat` (`id_obat`) ON DELETE CASCADE;
--
-- Database: `sistem_desa_sekolah`
--
CREATE DATABASE IF NOT EXISTS `sistem_desa_sekolah` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `sistem_desa_sekolah`;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 3, 'login', 'User login: raf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:08:19'),
(2, 3, 'admin_login_failed', 'Password salah untuk username: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:19:48'),
(3, 3, 'admin_login_locked', 'IP ::1 diblokir karena terlalu banyak percobaan gagal', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:19:48'),
(4, 3, 'admin_login_failed', 'Password salah untuk username: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:20:02'),
(5, 3, 'admin_login_locked', 'IP ::1 diblokir karena terlalu banyak percobaan gagal', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 06:20:02'),
(6, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:06:38'),
(7, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 07:37:32'),
(8, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 08:15:19'),
(9, 3, 'login', 'User login: raf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-25 08:45:12'),
(10, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 09:28:56'),
(11, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 09:59:19'),
(12, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 10:29:24'),
(13, 1, 'edit_penduduk', 'Mengedit data penduduk: Muhamad Rafi (3492929428420868)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 10:52:59'),
(14, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 11:11:38'),
(15, 1, 'tambah_penduduk', 'Menambah data penduduk: arini (3348498948364936)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 11:36:37'),
(16, 1, 'edit_penduduk', 'Mengedit data penduduk: Muhamad Rafi (3492929428420868)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 11:39:48'),
(17, 1, 'edit_penduduk', 'Mengedit data penduduk: Muhamad Rafi (3492929428420868)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 11:39:57'),
(18, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 11:42:24'),
(19, 3, 'login', 'User login: raf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 11:49:02'),
(20, 1, 'tambah_berita', 'Menambah berita: Peringati Hari Santri Nasional 2025, Ponpes Al-Majid Cirendang Gelar Beragam Perlombaan di GOR H. Tjetje Priatna', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 12:09:32'),
(21, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 12:22:21'),
(22, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 12:53:35'),
(23, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 13:24:25'),
(24, 1, 'logout', 'User logout: admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 13:57:37'),
(25, 1, 'tambah_galeri', 'Menambah foto ke galeri: Ponpes Modern Al-Majid Cirendang Gelar Ujian Semester Ganjil 2025, Integrasikan Ujian Tulis, Lisan, dan Praktik Keagamaan', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 14:08:58'),
(26, 3, 'login', 'User login: raf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 14:10:42'),
(27, 1, 'tambah_galeri', 'Menambah foto ke galeri: WhatsApp Image 2025-12-18 at 06.58.35 (1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 14:31:57');

-- --------------------------------------------------------

--
-- Table structure for table `anggaran`
--

CREATE TABLE `anggaran` (
  `id` int NOT NULL,
  `tahun` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` enum('pendapatan','belanja') COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subkategori` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `anggaran` decimal(15,2) NOT NULL,
  `realisasi` decimal(15,2) DEFAULT '0.00',
  `persentase` decimal(5,2) DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aspirasi`
--

CREATE TABLE `aspirasi` (
  `id` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis` enum('kritik','saran','pengaduan','lainnya') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subjek` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pesan` text COLLATE utf8mb4_general_ci,
  `lampiran` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('baru','dibaca','diproses','selesai') COLLATE utf8mb4_general_ci DEFAULT 'baru',
  `balasan` text COLLATE utf8mb4_general_ci,
  `dibaca_pada` timestamp NULL DEFAULT NULL,
  `direspon_pada` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backup_log`
--

CREATE TABLE `backup_log` (
  `id` int NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `filepath` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_size` bigint DEFAULT NULL,
  `backup_type` enum('full','partial','auto') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backup_logs`
--

CREATE TABLE `backup_logs` (
  `id` int NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` bigint DEFAULT NULL,
  `type` enum('database','files','full') COLLATE utf8mb4_unicode_ci DEFAULT 'database',
  `status` enum('success','failed','pending') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `message` text COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `berita`
--

CREATE TABLE `berita` (
  `id` int NOT NULL,
  `judul` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `konten` longtext COLLATE utf8mb4_general_ci,
  `excerpt` text COLLATE utf8mb4_general_ci,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kategori` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `penulis_id` int DEFAULT NULL,
  `penulis_nama` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `views` int DEFAULT '0',
  `is_published` tinyint(1) DEFAULT '1',
  `is_highlight` tinyint(1) DEFAULT '0',
  `meta_keywords` text COLLATE utf8mb4_general_ci,
  `meta_description` text COLLATE utf8mb4_general_ci,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `berita`
--

INSERT INTO `berita` (`id`, `judul`, `slug`, `konten`, `excerpt`, `gambar`, `kategori`, `penulis_id`, `penulis_nama`, `views`, `is_published`, `is_highlight`, `meta_keywords`, `meta_description`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 'Peringati Hari Santri Nasional 2025, Ponpes Al-Majid Cirendang Gelar Beragam Perlombaan di GOR H. Tjetje Priatna', 'peringati-hari-santri-nasional-2025-ponpes-al-majid-cirendang-gelar-beragam-perlombaan-di-gor-h-tjetje-priatna', 'Hari Santri Nasional', 'Hari Santri Nasional', 'berita_1766664572_694d297c04a3e.jpg', 'Hari Santri', 1, 'Administrator Sistem', 4, 1, 1, NULL, NULL, '2025-12-25 12:09:32', '2025-12-25 12:09:32', '2025-12-25 12:18:23');

-- --------------------------------------------------------

--
-- Table structure for table `berita_comments`
--

CREATE TABLE `berita_comments` (
  `id` int NOT NULL,
  `berita_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `komentar` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_approved` tinyint(1) DEFAULT '0',
  `parent_id` int DEFAULT NULL,
  `likes` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `berita_tags`
--

CREATE TABLE `berita_tags` (
  `id` int NOT NULL,
  `berita_id` int NOT NULL,
  `tag` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `berita_tags`
--

INSERT INTO `berita_tags` (`id`, `berita_id`, `tag`, `created_at`) VALUES
(1, 1, '#smkpertiwikuningan', '2025-12-25 12:09:32');

-- --------------------------------------------------------

--
-- Table structure for table `galeri`
--

CREATE TABLE `galeri` (
  `id` int NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_file` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'image',
  `kategori` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `uploader_id` int DEFAULT NULL,
  `uploader_nama` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `views` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `galeri`
--

INSERT INTO `galeri` (`id`, `judul`, `deskripsi`, `gambar`, `jenis_file`, `kategori`, `uploader_id`, `uploader_nama`, `views`, `created_at`, `updated_at`) VALUES
(1, 'Ponpes Modern Al-Majid Cirendang Gelar Ujian Semester Ganjil 2025, Integrasikan Ujian Tulis, Lisan, dan Praktik Keagamaan', 'bjk', 'galeri_1766671738_694d457a0fb16.jpg', 'image', 'b b', 1, 'Administrator Sistem', 0, '2025-12-25 14:08:58', '2025-12-25 14:08:58'),
(2, 'WhatsApp Image 2025-12-18 at 06.58.35 (1)', NULL, 'galeri_1766673117_694d4add0f253.jpeg', 'image', 'bjbxsdcdmb', 1, 'Administrator Sistem', 0, '2025-12-25 14:31:57', '2025-12-25 14:31:57');

-- --------------------------------------------------------

--
-- Table structure for table `galeri_album`
--

CREATE TABLE `galeri_album` (
  `id` int NOT NULL,
  `nama_album` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `galeri_album_items`
--

CREATE TABLE `galeri_album_items` (
  `id` int NOT NULL,
  `album_id` int NOT NULL,
  `galeri_id` int NOT NULL,
  `urutan` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jenis_layanan`
--

CREATE TABLE `jenis_layanan` (
  `id` int NOT NULL,
  `nama_layanan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `persyaratan` text COLLATE utf8mb4_general_ci,
  `estimasi_waktu` int DEFAULT NULL COMMENT 'Estimasi dalam hari',
  `biaya` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) DEFAULT '1',
  `form_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'Struktur form dinamis',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `jenis_layanan`
--

INSERT INTO `jenis_layanan` (`id`, `nama_layanan`, `deskripsi`, `persyaratan`, `estimasi_waktu`, `biaya`, `is_active`, `form_fields`, `created_at`) VALUES
(1, 'Surat Keterangan Domisili', 'Surat keterangan tempat tinggal', '1. Fotokopi KTP\n2. Fotokopi KK\n3. Surat pengantar RT', 2, 0.00, 1, NULL, '2025-12-25 02:45:16'),
(2, 'Surat Keterangan Tidak Mampu', 'Surat untuk keperluan bantuan sosial', '1. Fotokopi KTP\n2. Fotokopi KK\n3. Surat pengantar RT\n4. Bukti penghasilan', 3, 0.00, 1, NULL, '2025-12-25 02:45:16'),
(3, 'Surat Keterangan Usaha', 'Surat keterangan memiliki usaha', '1. Fotokopi KTP\n2. Fotokopi KK\n3. Surat pengantar RT\n4. Foto usaha', 2, 5000.00, 1, NULL, '2025-12-25 02:45:16'),
(4, 'Surat Keterangan Belum Menikah', 'Surat untuk keperluan administrasi', '1. Fotokopi KTP\n2. Fotokopi KK\n3. Surat pengantar RT', 1, 0.00, 1, NULL, '2025-12-25 02:45:16'),
(5, 'Surat Izin Keramaian', 'Surat izin mengadakan acara', '1. Fotokopi KTP\n2. Surat permohonan\n3. Denah lokasi', 5, 10000.00, 1, NULL, '2025-12-25 02:45:16');

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id` int NOT NULL,
  `nama_kegiatan` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `waktu` time DEFAULT NULL,
  `lokasi` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `peserta` text COLLATE utf8mb4_general_ci COMMENT 'Target peserta',
  `penanggung_jawab` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anggaran` decimal(15,2) DEFAULT '0.00',
  `status` enum('rencana','berlangsung','selesai','dibatalkan') COLLATE utf8mb4_general_ci DEFAULT 'rencana',
  `dokumentasi` text COLLATE utf8mb4_general_ci COMMENT 'JSON array foto',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `keluarga`
--

CREATE TABLE `keluarga` (
  `id` int NOT NULL,
  `no_kk` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kepala_keluarga_id` int DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `rt` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rw` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dusun` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jumlah_anggota` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `layanan`
--

CREATE TABLE `layanan` (
  `id` int NOT NULL,
  `kode_layanan` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `jenis_layanan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `dokumen_pendukung` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','diproses','disetujui','ditolak','selesai') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `catatan_admin` text COLLATE utf8mb4_general_ci,
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal_proses` timestamp NULL DEFAULT NULL,
  `tanggal_selesai` timestamp NULL DEFAULT NULL,
  `surat_pdf` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `layanan`
--

INSERT INTO `layanan` (`id`, `kode_layanan`, `user_id`, `jenis_layanan`, `keterangan`, `dokumen_pendukung`, `status`, `catatan_admin`, `tanggal_pengajuan`, `tanggal_proses`, `tanggal_selesai`, `surat_pdf`, `created_at`, `updated_at`) VALUES
(1, 'LYN-20251225-2164', 3, 'Surat Keterangan Domisili', 'Bangsat Lu Pada Mentri Korupsi Mulu', NULL, 'pending', NULL, '2025-12-25 05:21:16', NULL, NULL, NULL, '2025-12-25 05:21:16', '2025-12-25 05:21:16'),
(40, 'LYN-20251225-1153', 1, 'Surat Keterangan Domisili', 'kumaha aing', NULL, 'pending', NULL, '2025-12-25 13:59:41', NULL, NULL, NULL, '2025-12-25 13:59:41', '2025-12-25 13:59:41'),
(41, 'LYN-20251225-1467', 1, 'Surat Keterangan Domisili', 'kumaha aing', NULL, 'pending', NULL, '2025-12-25 13:59:43', NULL, NULL, NULL, '2025-12-25 13:59:43', '2025-12-25 13:59:43'),
(42, 'LYN-20251225-1587', 1, 'Surat Keterangan Domisili', 'kumaha aing', NULL, 'pending', NULL, '2025-12-25 13:59:45', NULL, NULL, NULL, '2025-12-25 13:59:45', '2025-12-25 13:59:45'),
(43, 'LYN-20251225-0248', 1, 'Surat Keterangan Domisili', 'kumaha aing', NULL, 'pending', NULL, '2025-12-25 13:59:47', NULL, NULL, NULL, '2025-12-25 13:59:47', '2025-12-25 13:59:47'),
(44, 'LYN-20251225-3103', 1, 'Surat Keterangan Domisili', 'kumaha aing', NULL, 'pending', NULL, '2025-12-25 13:59:49', NULL, NULL, NULL, '2025-12-25 13:59:49', '2025-12-25 13:59:49'),
(45, 'LYN-20251225-4667', 1, 'Surat Keterangan Domisili', 'kumaha aing', NULL, 'pending', NULL, '2025-12-25 13:59:51', NULL, NULL, NULL, '2025-12-25 13:59:51', '2025-12-25 13:59:51'),
(46, 'LYN-20251225-9226', 1, 'Surat Keterangan Domisili', 'kumaha aing', NULL, 'pending', NULL, '2025-12-25 13:59:58', NULL, NULL, NULL, '2025-12-25 13:59:58', '2025-12-25 13:59:58'),
(47, 'LYN-20251225-0057', 1, 'Surat Keterangan Domisili', 'kumaha aing', NULL, 'pending', NULL, '2025-12-25 14:00:00', NULL, NULL, NULL, '2025-12-25 14:00:00', '2025-12-25 14:00:00'),
(48, 'LYN-20251225-5716', 1, 'Surat Keterangan Domisili', 'kumaha aing', NULL, 'pending', NULL, '2025-12-25 14:00:02', NULL, NULL, NULL, '2025-12-25 14:00:02', '2025-12-25 14:00:02'),
(49, 'LYN-20251225-2750', 1, 'Surat Keterangan Domisili', 'kumaha aing', NULL, 'pending', NULL, '2025-12-25 14:00:04', NULL, NULL, NULL, '2025-12-25 14:00:04', '2025-12-25 14:00:04'),
(50, 'LYN-20251225-5179', 1, 'Surat Keterangan Domisili', 'kumaha aing', NULL, 'pending', NULL, '2025-12-25 14:00:06', NULL, NULL, NULL, '2025-12-25 14:00:06', '2025-12-25 14:00:06'),
(51, 'LYN-20251225-9548', 1, 'Surat Keterangan Domisili', 'kumaha aing', NULL, 'pending', NULL, '2025-12-25 14:00:08', NULL, NULL, NULL, '2025-12-25 14:00:08', '2025-12-25 14:00:08'),
(52, 'LYN-20251225-7607', 1, 'Surat Keterangan Domisili', 'kumaha aing', NULL, 'pending', NULL, '2025-12-25 14:00:11', NULL, NULL, NULL, '2025-12-25 14:00:11', '2025-12-25 14:00:11');

-- --------------------------------------------------------

--
-- Table structure for table `layanan_tracking`
--

CREATE TABLE `layanan_tracking` (
  `id` int NOT NULL,
  `layanan_id` int NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_activity`
--

CREATE TABLE `log_activity` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `table_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `record_id` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log_activity`
--

INSERT INTO `log_activity` (`id`, `user_id`, `action`, `table_name`, `record_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 3, 'register', NULL, NULL, 'Pendaftaran akun baru: raf', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-25 04:22:39');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penduduk`
--

CREATE TABLE `penduduk` (
  `id` int NOT NULL,
  `nik` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tempat_lahir` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `agama` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pendidikan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pekerjaan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_perkawinan` enum('Belum Menikah','Menikah','Cerai Hidup','Cerai Mati') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kewarganegaraan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Indonesia',
  `alamat` text COLLATE utf8mb4_general_ci,
  `rt` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rw` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dusun` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kelurahan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kecamatan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kabupaten` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `provinsi` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_telepon` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_tinggal` enum('Tetap','Sementara','Pendatang') COLLATE utf8mb4_general_ci DEFAULT 'Tetap',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penduduk`
--

INSERT INTO `penduduk` (`id`, `nik`, `nama`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `agama`, `pendidikan`, `pekerjaan`, `status_perkawinan`, `kewarganegaraan`, `alamat`, `rt`, `rw`, `dusun`, `kelurahan`, `kecamatan`, `kabupaten`, `provinsi`, `no_telepon`, `email`, `foto`, `status_tinggal`, `created_at`, `updated_at`) VALUES
(1, '3492929428420868', 'Muhamad Rafi', 'Kuningan', '0000-00-00', 'L', 'Islam', 'SMA', 'Siswa', NULL, 'Indonesia', 'Dusun Puhun , RT.21/RW.08, Lengkong, Kec. Garawangi, Kabupaten Kuningan, Jawa Barat 45571', '021', '008', 'Dusun Puhun', NULL, NULL, NULL, NULL, '0895339144077', 'rafi.sukses72@gmail.com', NULL, 'Tetap', '2025-12-25 04:22:39', '2025-12-25 11:39:48'),
(2, '3348498948364936', 'arini', 'Jalaksana', '2025-12-25', 'P', 'Islam', 'S2', 'Ibu Rumah Tangga', NULL, 'Indonesia', 'Des Jalaksana', '021', '008', 'Dusun Jalaksana', NULL, NULL, NULL, NULL, '6285724133775', 'arini@gmail.com', NULL, 'Tetap', '2025-12-25 11:36:37', '2025-12-25 11:36:37');

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan`
--

CREATE TABLE `pengajuan` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `jenis_pengajuan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `lampiran` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','diproses','disetujui','ditolak','selesai') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `catatan_admin` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_general_ci,
  `setting_group` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'general',
  `setting_type` enum('text','number','email','url','textarea','boolean','json') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `label` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ordering` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `setting_key`, `setting_value`, `setting_group`, `setting_type`, `label`, `ordering`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Desa Maju Jaya', 'general', 'text', 'Nama Website', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(2, 'site_description', 'Website Resmi Desa Maju Jaya', 'general', 'text', 'Deskripsi Website', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(3, 'site_logo', 'logo.png', 'general', 'text', 'Logo Website', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(4, 'site_favicon', 'favicon.ico', 'general', 'text', 'Favicon', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(5, 'admin_email', 'admin@desa.example.com', 'general', 'email', 'Email Admin', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(6, 'phone_number', '+62 812-3456-7890', 'general', 'text', 'Telepon', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(7, 'address', 'Jl. Raya Desa No. 1, Kecamatan, Kabupaten', 'general', 'textarea', 'Alamat', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(8, 'theme_color', '#2c5aa0', 'design', 'text', 'Warna Tema', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(9, 'font_family', 'Poppins, sans-serif', 'design', 'text', 'Font Utama', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(10, 'enable_dark_mode', '1', 'design', 'boolean', 'Aktifkan Dark Mode', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(11, 'visi_desa', 'Mewujudkan Desa yang Maju, Mandiri, dan Sejahtera', 'profil', 'textarea', 'Visi Desa', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(12, 'misi_desa', '1. Meningkatkan kualitas pendidikan\n2. Mengembangkan potensi ekonomi\n3. Memperkuat infrastruktur', 'profil', 'textarea', 'Misi Desa', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(13, 'sejarah_desa', 'Desa Maju Jaya berdiri sejak tahun 1945...', 'profil', 'textarea', 'Sejarah Desa', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(14, 'google_maps_embed', '<iframe src=\"https://maps.google.com/embed?...\"></iframe>', 'profil', 'textarea', 'Embed Google Maps', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(15, 'jumlah_penduduk', '2500', 'statistik', 'number', 'Jumlah Penduduk', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(16, 'jumlah_rt', '10', 'statistik', 'number', 'Jumlah RT', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(17, 'jumlah_rw', '3', 'statistik', 'number', 'Jumlah RW', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(18, 'jumlah_layanan_aktif', '15', 'statistik', 'number', 'Layanan Aktif', 0, '2025-12-25 02:45:16', '2025-12-25 02:45:16');

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int NOT NULL,
  `judul` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `isi` text COLLATE utf8mb4_general_ci,
  `kategori` enum('Umum','Pentung','Darurat','Lainnya') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mulai_tampil` date DEFAULT NULL,
  `selesai_tampil` date DEFAULT NULL,
  `lampiran` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rt_rw`
--

CREATE TABLE `rt_rw` (
  `id` int NOT NULL,
  `rt` varchar(5) COLLATE utf8mb4_general_ci NOT NULL,
  `rw` varchar(5) COLLATE utf8mb4_general_ci NOT NULL,
  `ketua_rt` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ketua_rw` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat_sekre` text COLLATE utf8mb4_general_ci,
  `no_telp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jumlah_kk` int DEFAULT '0',
  `jumlah_penduduk` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_type` enum('text','number','email','url','textarea','json','boolean','select') COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `setting_group` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `setting_label` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `setting_description` text COLLATE utf8mb4_unicode_ci,
  `is_public` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_label`, `setting_description`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Sistem Desa Digital', 'text', 'general', 'Nama Situs', 'Nama website desa', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(2, 'site_description', 'Sistem Informasi Desa Digital', 'text', 'general', 'Deskripsi Situs', 'Deskripsi singkat website', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(3, 'site_keywords', 'desa, informasi, digital, pelayanan', 'text', 'general', 'Kata Kunci', 'Kata kunci untuk SEO', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(4, 'contact_phone', '+62 812-3456-7890', 'text', 'general', 'Telepon Kontak', 'Nomor telepon kantor desa', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(5, 'contact_address', 'Jl. Desa No. 1, Kecamatan, Kabupaten', 'textarea', 'general', 'Alamat Kantor', 'Alamat lengkap kantor desa', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(6, 'contact_email', 'admin@desa.local', 'email', 'general', 'Email Kontak', 'Email resmi desa', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(7, 'theme_color', '#667eea', 'text', 'appearance', 'Warna Tema', 'Warna utama tema', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(8, 'logo_url', 'http://localhost/desa/assets/img/logo.png', 'url', 'appearance', 'URL Logo', 'URL logo website', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(9, 'favicon_url', 'http://localhost/desa/assets/img/favicon.ico', 'url', 'appearance', 'URL Favicon', 'URL favicon', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(10, 'maintenance_mode', '0', 'boolean', 'system', 'Mode Maintenance', 'Aktifkan mode maintenance', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(11, 'registration_enabled', '1', 'boolean', 'system', 'Pendaftaran User', 'Izinkan pendaftaran user baru', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(12, 'email_notifications', '1', 'boolean', 'system', 'Notifikasi Email', 'Aktifkan notifikasi email', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(13, 'items_per_page', '10', 'number', 'system', 'Item per Halaman', 'Jumlah item per halaman', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(14, 'meta_title', 'Sistem Desa Digital', 'text', 'seo', 'Meta Title', 'Judul untuk SEO', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(15, 'meta_description', 'Sistem Informasi Desa Digital - Pelayanan publik berbasis web', 'textarea', 'seo', 'Meta Description', 'Deskripsi untuk SEO', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(16, 'facebook_url', 'https://facebook.com/desa', 'url', 'social', 'Facebook', 'URL Facebook', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(17, 'twitter_url', 'https://twitter.com/desa', 'url', 'social', 'Twitter', 'URL Twitter', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(18, 'instagram_url', 'https://instagram.com/desa', 'url', 'social', 'Instagram', 'URL Instagram', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(19, 'youtube_url', 'https://youtube.com/desa', 'url', 'social', 'YouTube', 'URL YouTube', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(20, 'smtp_host', 'smtp.gmail.com', 'text', 'email', 'SMTP Host', 'Host server SMTP', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(21, 'smtp_port', '587', 'number', 'email', 'SMTP Port', 'Port server SMTP', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(22, 'smtp_user', 'your-email@gmail.com', 'email', 'email', 'SMTP User', 'Username SMTP', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(23, 'smtp_secure', 'tls', 'text', 'email', 'SMTP Secure', 'Keamanan SMTP', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(24, 'backup_enabled', '1', 'boolean', 'backup', 'Backup Otomatis', 'Aktifkan backup otomatis', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(25, 'backup_schedule', 'daily', 'select', 'backup', 'Jadwal Backup', 'Jadwal backup: daily, weekly, monthly', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17'),
(26, 'max_backup_files', '30', 'number', 'backup', 'Max Backup Files', 'Maksimal file backup yang disimpan', 0, '2025-12-25 08:22:17', '2025-12-25 08:22:17');

-- --------------------------------------------------------

--
-- Table structure for table `struktur_organisasi`
--

CREATE TABLE `struktur_organisasi` (
  `id` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `jabatan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `urutan` int DEFAULT '0',
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `bidang` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_telepon` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `facebook` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `instagram` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `fullname` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','user','staff','super_admin') COLLATE utf8mb4_general_ci DEFAULT 'user',
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `avatar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'default-avatar.png',
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `fullname`, `role`, `phone`, `address`, `avatar`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@desa.example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator Sistem', 'super_admin', NULL, NULL, 'default-avatar.png', 1, NULL, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(2, 'staff', 'staff@desa.example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff Desa', 'staff', NULL, NULL, 'default-avatar.png', 1, NULL, '2025-12-25 02:45:16', '2025-12-25 02:45:16'),
(3, 'raf', 'rafi.sukses72@gmail.com', '$2y$10$F3AYn8WgT9Nf.i2Li4QTs.3RI5m9LGisyUqzXwTDM4CRlNwoc.pBe', 'Fira Hafidzotul Quran AL Arifah', 'user', '0895339144077', 'Dusun Puhun , RT.21/RW.08, Lengkong, Kec. Garawangi, Kabupaten Kuningan, Jawa Barat 45571', 'default.png', 1, '2025-12-25 14:10:42', '2025-12-25 04:22:39', '2025-12-25 14:10:42'),
(4, 'user', 'user@desa.local', '$2y$10$IDzZqKwhII789UqJAD6Sa.kFu3X1RzrDV0MPWQTFoeTpq0SkImggi', 'User Demo', 'user', '081234567890', NULL, 'default-avatar.png', 1, NULL, '2025-12-25 05:40:32', '2025-12-25 05:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci,
  `payload` text COLLATE utf8mb4_general_ci,
  `last_activity` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`, `created_at`) VALUES
('418fd41c237d5ec1bb83711c057d46e5c72c3d816731d9aa9958c81db51cc941', 3, NULL, NULL, '{\"ip\":\"::1\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\"}', 1766641987, '2025-12-25 05:53:07'),
('859a08c10cebd3b626e3bf309871995d75514622d73c186ba2f785b79cc5fe05', 3, NULL, NULL, '{\"ip\":\"::1\",\"agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\"}', 1766642899, '2025-12-25 06:08:19');

-- --------------------------------------------------------

--
-- Table structure for table `visitor_logs`
--

CREATE TABLE `visitor_logs` (
  `id` int NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `page_visited` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referrer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_method` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `os` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visited_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `anggaran`
--
ALTER TABLE `anggaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tahun` (`tahun`),
  ADD KEY `idx_jenis` (`jenis`),
  ADD KEY `idx_kategori` (`kategori`);

--
-- Indexes for table `aspirasi`
--
ALTER TABLE `aspirasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_jenis` (`jenis`);

--
-- Indexes for table `backup_log`
--
ALTER TABLE `backup_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `berita`
--
ALTER TABLE `berita`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `penulis_id` (`penulis_id`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_published` (`is_published`),
  ADD KEY `idx_highlight` (`is_highlight`);

--
-- Indexes for table `berita_comments`
--
ALTER TABLE `berita_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_berita` (`berita_id`),
  ADD KEY `idx_approved` (`is_approved`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `berita_tags`
--
ALTER TABLE `berita_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tag` (`berita_id`,`tag`),
  ADD KEY `idx_tag` (`tag`);

--
-- Indexes for table `galeri`
--
ALTER TABLE `galeri`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `galeri_album`
--
ALTER TABLE `galeri_album`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `galeri_album_items`
--
ALTER TABLE `galeri_album_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_album_galeri` (`album_id`,`galeri_id`),
  ADD KEY `idx_album` (`album_id`),
  ADD KEY `idx_urutan` (`urutan`),
  ADD KEY `galeri_id` (`galeri_id`);

--
-- Indexes for table `jenis_layanan`
--
ALTER TABLE `jenis_layanan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tanggal` (`tanggal_mulai`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `keluarga`
--
ALTER TABLE `keluarga`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_kk` (`no_kk`),
  ADD KEY `idx_no_kk` (`no_kk`),
  ADD KEY `kepala_keluarga_id` (`kepala_keluarga_id`);

--
-- Indexes for table `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_layanan` (`kode_layanan`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_tanggal` (`tanggal_pengajuan`);

--
-- Indexes for table `layanan_tracking`
--
ALTER TABLE `layanan_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_layanan` (`layanan_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `log_activity`
--
ALTER TABLE `log_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_read` (`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `penduduk`
--
ALTER TABLE `penduduk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD KEY `idx_nik` (`nik`),
  ADD KEY `idx_rt_rw` (`rt`,`rw`);

--
-- Indexes for table `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_group` (`setting_group`);

--
-- Indexes for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_tanggal` (`mulai_tampil`,`selesai_tampil`);

--
-- Indexes for table `rt_rw`
--
ALTER TABLE `rt_rw`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rt_rw` (`rt`,`rw`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_key` (`setting_key`),
  ADD KEY `idx_group` (`setting_group`);

--
-- Indexes for table `struktur_organisasi`
--
ALTER TABLE `struktur_organisasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_urutan` (`urutan`),
  ADD KEY `idx_jabatan` (`jabatan`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_last_activity` (`last_activity`);

--
-- Indexes for table `visitor_logs`
--
ALTER TABLE `visitor_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip` (`ip_address`),
  ADD KEY `idx_visited_at` (`visited_at`),
  ADD KEY `idx_page` (`page_visited`(255));

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `anggaran`
--
ALTER TABLE `anggaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aspirasi`
--
ALTER TABLE `aspirasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `backup_log`
--
ALTER TABLE `backup_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `backup_logs`
--
ALTER TABLE `backup_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `berita`
--
ALTER TABLE `berita`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `berita_comments`
--
ALTER TABLE `berita_comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `berita_tags`
--
ALTER TABLE `berita_tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `galeri_album`
--
ALTER TABLE `galeri_album`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `galeri_album_items`
--
ALTER TABLE `galeri_album_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jenis_layanan`
--
ALTER TABLE `jenis_layanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `keluarga`
--
ALTER TABLE `keluarga`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `layanan`
--
ALTER TABLE `layanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `layanan_tracking`
--
ALTER TABLE `layanan_tracking`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log_activity`
--
ALTER TABLE `log_activity`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penduduk`
--
ALTER TABLE `penduduk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengajuan`
--
ALTER TABLE `pengajuan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rt_rw`
--
ALTER TABLE `rt_rw`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `struktur_organisasi`
--
ALTER TABLE `struktur_organisasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `visitor_logs`
--
ALTER TABLE `visitor_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `backup_log`
--
ALTER TABLE `backup_log`
  ADD CONSTRAINT `backup_log_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD CONSTRAINT `backup_logs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `berita`
--
ALTER TABLE `berita`
  ADD CONSTRAINT `berita_ibfk_1` FOREIGN KEY (`penulis_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `berita_comments`
--
ALTER TABLE `berita_comments`
  ADD CONSTRAINT `berita_comments_ibfk_1` FOREIGN KEY (`berita_id`) REFERENCES `berita` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `berita_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `berita_comments_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `berita_comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `berita_tags`
--
ALTER TABLE `berita_tags`
  ADD CONSTRAINT `berita_tags_ibfk_1` FOREIGN KEY (`berita_id`) REFERENCES `berita` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `galeri_album_items`
--
ALTER TABLE `galeri_album_items`
  ADD CONSTRAINT `galeri_album_items_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `galeri_album` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `galeri_album_items_ibfk_2` FOREIGN KEY (`galeri_id`) REFERENCES `galeri` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `keluarga`
--
ALTER TABLE `keluarga`
  ADD CONSTRAINT `keluarga_ibfk_1` FOREIGN KEY (`kepala_keluarga_id`) REFERENCES `penduduk` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `layanan`
--
ALTER TABLE `layanan`
  ADD CONSTRAINT `layanan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `layanan_tracking`
--
ALTER TABLE `layanan_tracking`
  ADD CONSTRAINT `layanan_tracking_ibfk_1` FOREIGN KEY (`layanan_id`) REFERENCES `layanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `layanan_tracking_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `log_activity`
--
ALTER TABLE `log_activity`
  ADD CONSTRAINT `log_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD CONSTRAINT `pengajuan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD CONSTRAINT `pengumuman_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
