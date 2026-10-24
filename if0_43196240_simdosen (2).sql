-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql101.infinityfree.com
-- Waktu pembuatan: 24 Okt 2026 pada 08.52
-- Versi server: 11.4.13-MariaDB
-- Versi PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_43196240_simdosen`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `api_tokens`
--

CREATE TABLE `api_tokens` (
  `id` bigint(20) NOT NULL,
  `pengguna_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `api_tokens`
--

INSERT INTO `api_tokens` (`id`, `pengguna_id`, `token_hash`, `expires_at`, `created_at`) VALUES
(1, 1, 'a944449a6e1aa6b0e36cd6e43a9cd427a3e53cda6bebc8d4a8913e5f8ab519fe', '2026-11-18 03:28:17', '2026-10-19 07:28:17'),
(2, 1, 'd1e6073fb5dc6dc4afbfcaa429ed2d87922aa1c32ddc507764dbf9ffd8c35e97', '2026-11-18 03:45:20', '2026-10-19 07:45:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `audit_log`
--

CREATE TABLE `audit_log` (
  `id` bigint(20) NOT NULL,
  `pengguna_id` int(11) DEFAULT NULL,
  `aktivitas` varchar(100) NOT NULL,
  `detail` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `audit_log`
--

INSERT INTO `audit_log` (`id`, `pengguna_id`, `aktivitas`, `detail`, `ip_address`, `created_at`) VALUES
(1, 1, 'Menambahkan data dosen', 'ID dosen: 2; Nama: Rony', '27.124.75.57', '2026-10-19 07:01:53'),
(2, 1, 'Membuat token API', 'ID token: 1', '27.124.75.57', '2026-10-19 07:28:17'),
(3, 1, 'Membuat token API', 'ID token: 2', '27.124.75.57', '2026-10-19 07:45:20'),
(4, 1, 'Menambahkan pengguna', 'ID pengguna: 3, username: netrinur', '36.67.65.211', '2026-10-21 01:28:29'),
(5, 1, 'Mengubah pengguna', 'ID pengguna: 3', '36.67.65.211', '2026-10-21 01:30:56'),
(6, 1, 'Mengubah pengguna', 'ID pengguna: 2', '36.67.65.211', '2026-10-21 01:38:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `dosen`
--

CREATE TABLE `dosen` (
  `id` int(11) NOT NULL,
  `nomor_registrasi` varchar(40) DEFAULT NULL,
  `nuptk` varchar(40) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `nip` varchar(40) DEFAULT NULL,
  `jabatan_fungsional` varchar(100) DEFAULT NULL,
  `kepangkatan` varchar(100) DEFAULT NULL,
  `pendidikan_terakhir` varchar(100) DEFAULT NULL,
  `rumpun_ilmu` varchar(120) DEFAULT NULL,
  `status_serdos` enum('Sudah','Belum','Tidak Berlaku') NOT NULL DEFAULT 'Belum',
  `status_pegawai` enum('PNS','Tetap','Kontrak','Honorer','Lainnya') NOT NULL DEFAULT 'Tetap',
  `ikatan_kerja` enum('Dosen Tetap','Dosen Tidak Tetap','Praktisi','Lainnya') NOT NULL DEFAULT 'Dosen Tetap',
  `status_aktivitas` enum('Aktif','Cuti','Nonaktif') NOT NULL DEFAULT 'Aktif',
  `penempatan` varchar(150) DEFAULT NULL,
  `fakultas` varchar(150) DEFAULT 'Teknik',
  `program_studi` varchar(150) DEFAULT 'Teknik Informatika',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `dosen`
--

INSERT INTO `dosen` (`id`, `nomor_registrasi`, `nuptk`, `nama`, `nip`, `jabatan_fungsional`, `kepangkatan`, `pendidikan_terakhir`, `rumpun_ilmu`, `status_serdos`, `status_pegawai`, `ikatan_kerja`, `status_aktivitas`, `penempatan`, `fakultas`, `program_studi`, `created_at`, `updated_at`) VALUES
(1, '01', NULL, 'Dimas', NULL, 'Staff', NULL, '2026', NULL, 'Sudah', '', 'Dosen Tetap', '', NULL, NULL, 'Teknik Informatika', '2026-10-16 22:38:55', '2026-10-16 22:38:55'),
(2, '02', '', 'Rony', '-', 'Staff', '', 'S3', '', 'Sudah', 'PNS', 'Dosen Tetap', 'Aktif', '', 'Teknik', 'Teknik Informatika', '2026-10-19 07:01:53', '2026-10-19 07:01:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int(11) NOT NULL,
  `nama` varchar(120) NOT NULL,
  `username` varchar(60) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('administrator','operator','pimpinan') NOT NULL DEFAULT 'operator',
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `pengguna`
--

INSERT INTO `pengguna` (`id`, `nama`, `username`, `password_hash`, `role`, `aktif`, `created_at`) VALUES
(1, 'Dimas', 'Admin', '$2y$12$s0YMgrjtJwz.zTIeU/k27u0tZaoQk/T5GKvF3PY5kAod9dRBWA6hy', 'administrator', 1, '2026-10-16 22:29:24'),
(2, 'Rony Chandra', 'rony', '$2y$12$7whjGRY70TUAzaHzC7wksuE3T3W4roGtQpLfqa3OomC8weN2XYrQC', 'operator', 1, '2026-10-16 22:42:01'),
(3, 'Netri', 'netrinur', '$2y$12$eZzwBkE26M1gyKE3e3blzuCMuGhXqthi85hN1QFBeESdmM36L/FYW', 'pimpinan', 1, '2026-10-21 01:28:29');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `pengguna_id` (`pengguna_id`);

--
-- Indeks untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `api_tokens`
--
ALTER TABLE `api_tokens`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
