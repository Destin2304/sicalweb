-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 16 Jul 2026 pada 06.00
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_career_alumni`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- --------------------------------------------------------

--
-- Struktur dari tabel `alumni`
--

CREATE TABLE `alumni` (
  `id_alumni` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `tahun_lulus` year(4) DEFAULT NULL,
  `pekerjaan` varchar(150) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `provinsi` varchar(50) DEFAULT NULL,
  `kota` varchar(50) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `alumni`
--

INSERT INTO `alumni` (`id_alumni`, `username`, `password`, `foto_profil`, `nama`, `nim`, `jurusan`, `tahun_lulus`, `pekerjaan`, `alamat`, `email`, `no_hp`, `provinsi`, `kota`, `status`, `created_at`, `updated_at`) VALUES
(2, 'etty_hura', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Etty D. Hura', '2020001001', 'Teknik Informatika', '2023', NULL, NULL, 'etty@alumni.usm.ac.id', '08123456789', 'Sumatera Utara', 'Medan', 'aktif', '2026-07-13 16:20:35', '2026-07-13 16:20:35'),
(3, 'budi_santo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Budi Santoso', '2020001002', 'Sistem Informasi', '2023', NULL, NULL, 'budi@alumni.usm.ac.id', '08234567890', 'Jawa Barat', 'Bandung', 'aktif', '2026-07-13 16:20:35', '2026-07-13 16:20:35'),
(4, 'siti_nur', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Siti Nurhaliza', '2020001003', 'Manajemen', '2023', NULL, NULL, 'siti@alumni.usm.ac.id', '08345678901', 'Jakarta', 'Jakarta Pusat', 'aktif', '2026-07-13 16:20:35', '2026-07-13 16:20:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `lowongan`
--

CREATE TABLE `lowongan` (
  `id_lowongan` int(11) NOT NULL,
  `posisi` varchar(100) NOT NULL,
  `perusahaan` varchar(100) NOT NULL,
  `tipe` enum('Kerja','Magang') NOT NULL DEFAULT 'Kerja',
  `deskripsi` text NOT NULL,
  `kualifikasi` text DEFAULT NULL,
  `gaji` varchar(50) DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `tanggal_post` date NOT NULL,
  `status` enum('Aktif','Tutup') DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `lowongan`
--

INSERT INTO `lowongan` (`id_lowongan`, `posisi`, `perusahaan`, `tipe`, `deskripsi`, `kualifikasi`, `gaji`, `lokasi`, `tanggal_post`, `status`, `created_at`) VALUES
(1, 'Perawat Pelaksana', 'Rumah Sakit Columbia Asia Medan', 'Kerja', 'Dibutuhkan tenaga perawat pelaksana dengan kualifikasi minimal D3/S1 Keperawatan.', NULL, NULL, NULL, '2026-06-04', 'Aktif', '2026-07-13 16:26:56'),
(2, 'Apoteker', 'Kimia Farma Medan', 'Kerja', 'Dibutuhkan lulusan Profesi Apoteker untuk penempatan area Medan.', NULL, NULL, NULL, '2026-06-03', 'Aktif', '2026-07-13 16:26:56'),
(3, 'Internship Web Developer', 'Biro IT USM-Indonesia', 'Magang', 'Program magang 3 bulan untuk pengembangan sistem informasi internal kampus.', NULL, NULL, NULL, '2026-06-04', 'Aktif', '2026-07-13 16:26:56'),
(4, 'Asisten Laboratorium', 'Klinik Sari Mutiara', 'Magang', 'Membantu operasional laboratorium dan penelitian klinis.', NULL, NULL, NULL, '2026-05-26', 'Aktif', '2026-07-13 16:26:56'),
(5, 'Perawat Pelaksana', 'Rumah Sakit Columbia Asia Medan', 'Kerja', 'Mencari perawat berpengalaman untuk bekerja di unit perawatan intensif.', 'D3 Keperawatan, Minimal 2 tahun pengalaman, Sertifikat CPR', 'Rp 4.000.000 - Rp 5.500.000', 'Medan, Sumatera Utara', '0000-00-00', 'Aktif', '2026-07-13 16:27:26'),
(6, 'Internship Web Developer', 'Biro IT USM-Indonesia', 'Kerja', 'Program magang 6 bulan untuk fresh graduate. Belajar langsung di industri teknologi.', 'Fresh graduate dari program IT/TI, Mahir PHP/MySQL', 'Rp 2.500.000 - Rp 3.000.000', 'Medan, Sumatera Utara', '0000-00-00', 'Aktif', '2026-07-13 16:27:26'),
(7, 'Apoteker', 'Kimia Farma Medan', 'Kerja', 'Posisi apoteker di apotek dan unit farmasi.', 'S1 Farmasi, Sudah terdaftar di BNOP', 'Rp 3.500.000 - Rp 4.500.000', 'Medan, Sumatera Utara', '0000-00-00', 'Aktif', '2026-07-13 16:27:26'),
(8, 'Asisten Laboratorium', 'Klinik Sari Mutiara', 'Kerja', 'Membantu teknisi laboratorium dalam pemeriksaan sampel.', 'D3 Teknologi Laboratorium Medis', 'Rp 2.000.000 - Rp 3.000.000', 'Medan, Sumatera Utara', '0000-00-00', 'Aktif', '2026-07-13 16:27:26');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pra_tracer`
--

CREATE TABLE `pra_tracer` (
  `id` int(11) NOT NULL,
  `alumni_id` int(11) DEFAULT NULL,
  `pekerjaan_sekarang` varchar(100) DEFAULT NULL,
  `relevansi_kuliah` varchar(100) DEFAULT NULL,
  `perusahaan` varchar(100) DEFAULT NULL,
  `gaji_range` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pra_tracer_study`
--

CREATE TABLE `pra_tracer_study` (
  `id_pra_tracer` int(11) NOT NULL,
  `id_alumni` int(11) NOT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `nama_alumni` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `provinsi` varchar(50) DEFAULT NULL,
  `kota` varchar(50) DEFAULT NULL,
  `pendidikan_terakhir` varchar(50) DEFAULT NULL,
  `rencana_karir` varchar(255) DEFAULT NULL,
  `keahlian_utama` text DEFAULT NULL,
  `sertifikasi` text DEFAULT NULL,
  `status_kontak` enum('aktif','nonaktif') DEFAULT 'aktif',
  `catatan` text DEFAULT NULL,
  `filled_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `provinsi`
--

CREATE TABLE `provinsi` (
  `id_provinsi` int(11) NOT NULL,
  `nama_provinsi` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `provinsi`
--

INSERT INTO `provinsi` (`id_provinsi`, `nama_provinsi`) VALUES
(1, 'Aceh'),
(16, 'Bali'),
(8, 'Bangka Belitung'),
(15, 'Banten'),
(28, 'Gorontalo'),
(10, 'Jakarta'),
(5, 'Jambi'),
(11, 'Jawa Barat'),
(12, 'Jawa Tengah'),
(14, 'Jawa Timur'),
(19, 'Kalimantan Barat'),
(21, 'Kalimantan Selatan'),
(20, 'Kalimantan Tengah'),
(22, 'Kalimantan Timur'),
(23, 'Kalimantan Utara'),
(9, 'Kepulauan Riau'),
(7, 'Lampung'),
(34, 'Luar Negeri'),
(30, 'Maluku'),
(31, 'Maluku Utara'),
(17, 'Nusa Tenggara Barat'),
(18, 'Nusa Tenggara Timur'),
(33, 'Papua'),
(32, 'Papua Barat'),
(4, 'Riau'),
(29, 'Sulawesi Barat'),
(26, 'Sulawesi Selatan'),
(25, 'Sulawesi Tengah'),
(27, 'Sulawesi Tenggara'),
(24, 'Sulawesi Utara'),
(3, 'Sumatera Barat'),
(6, 'Sumatera Selatan'),
(2, 'Sumatera Utara'),
(13, 'Yogyakarta');

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `nama_instansi` varchar(255) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `deskripsi_website` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`id`, `nama_instansi`, `logo`, `deskripsi_website`) VALUES
(1, 'Universitas Sari Mutiara Indonesia', 'LOGO_USM.png', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `survey_pengguna`
--

CREATE TABLE `survey_pengguna` (
  `id_survey` int(11) NOT NULL,
  `nama_instansi` varchar(150) NOT NULL,
  `industri` varchar(100) DEFAULT NULL,
  `alamat_instansi` text DEFAULT NULL,
  `provinsi_instansi` varchar(50) DEFAULT NULL,
  `email_instansi` varchar(100) DEFAULT NULL,
  `no_telp_instansi` varchar(20) DEFAULT NULL,
  `nama_responden` varchar(100) DEFAULT NULL,
  `posisi_responden` varchar(100) DEFAULT NULL,
  `alumni_id` int(11) DEFAULT NULL,
  `nama_alumni` varchar(100) DEFAULT NULL,
  `posisi_alumni` varchar(100) DEFAULT NULL,
  `lama_bekerja_bulan` int(11) DEFAULT NULL,
  `integritas` int(11) DEFAULT 3,
  `etika` int(11) DEFAULT 3,
  `profesionalisme` int(11) DEFAULT 3,
  `komunikasi` int(11) DEFAULT 3,
  `kerja_tim` int(11) DEFAULT 3,
  `kepemimpinan` int(11) DEFAULT 3,
  `teknologi_informasi` int(11) DEFAULT 3,
  `bahasa_asing` int(11) DEFAULT 3,
  `kepuasan_umum` int(11) DEFAULT 3,
  `rekomendasi` enum('Ya','Mungkin','Tidak') DEFAULT 'Ya',
  `kekuatan_alumni` text DEFAULT NULL,
  `kelemahan_alumni` text DEFAULT NULL,
  `saran_perbaikan` text DEFAULT NULL,
  `filled_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `survey_user`
--

CREATE TABLE `survey_user` (
  `id` int(11) NOT NULL,
  `alumni_id` int(11) DEFAULT NULL,
  `kepuasan_kuliah` varchar(100) DEFAULT NULL,
  `saran` text DEFAULT NULL,
  `rekomendasi_kampus` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tracer_study`
--

CREATE TABLE `tracer_study` (
  `id` int(11) NOT NULL,
  `alumni_id` int(11) DEFAULT NULL,
  `status_lulusan` varchar(100) DEFAULT NULL,
  `waktu_tunggu_kerja` varchar(50) DEFAULT NULL,
  `metode_mendapat_kerja` varchar(100) DEFAULT NULL,
  `kesesuaian_bidang` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `alumni`
--
ALTER TABLE `alumni`
  ADD PRIMARY KEY (`id_alumni`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `nim` (`nim`);

--
-- Indeks untuk tabel `lowongan`
--
ALTER TABLE `lowongan`
  ADD PRIMARY KEY (`id_lowongan`);

--
-- Indeks untuk tabel `pra_tracer`
--
ALTER TABLE `pra_tracer`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pra_tracer_study`
--
ALTER TABLE `pra_tracer_study`
  ADD PRIMARY KEY (`id_pra_tracer`),
  ADD KEY `id_alumni` (`id_alumni`);

--
-- Indeks untuk tabel `provinsi`
--
ALTER TABLE `provinsi`
  ADD PRIMARY KEY (`id_provinsi`),
  ADD UNIQUE KEY `nama_provinsi` (`nama_provinsi`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `survey_pengguna`
--
ALTER TABLE `survey_pengguna`
  ADD PRIMARY KEY (`id_survey`),
  ADD KEY `alumni_id` (`alumni_id`);

--
-- Indeks untuk tabel `survey_user`
--
ALTER TABLE `survey_user`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tracer_study`
--
ALTER TABLE `tracer_study`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `alumni`
--
ALTER TABLE `alumni`
  MODIFY `id_alumni` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `lowongan`
--
ALTER TABLE `lowongan`
  MODIFY `id_lowongan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `pra_tracer`
--
ALTER TABLE `pra_tracer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pra_tracer_study`
--
ALTER TABLE `pra_tracer_study`
  MODIFY `id_pra_tracer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `provinsi`
--
ALTER TABLE `provinsi`
  MODIFY `id_provinsi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `survey_pengguna`
--
ALTER TABLE `survey_pengguna`
  MODIFY `id_survey` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `survey_user`
--
ALTER TABLE `survey_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tracer_study`
--
ALTER TABLE `tracer_study`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `pra_tracer_study`
--
ALTER TABLE `pra_tracer_study`
  ADD CONSTRAINT `pra_tracer_study_ibfk_1` FOREIGN KEY (`id_alumni`) REFERENCES `alumni` (`id_alumni`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `survey_pengguna`
--
ALTER TABLE `survey_pengguna`
  ADD CONSTRAINT `survey_pengguna_ibfk_1` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`id_alumni`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
