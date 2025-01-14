-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql102.infinityfree.com
-- Waktu pembuatan: 13 Jan 2025 pada 04.13
-- Versi server: 10.6.19-MariaDB
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
-- Database: `if0_38027526_db_apprim`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `gedung`
--

CREATE TABLE `gedung` (
  `id_gedung` int(2) NOT NULL,
  `nama_gedung` varchar(50) NOT NULL,
  `deskripsi` text NOT NULL,
  `foto_gedung` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `gedung`
--

INSERT INTO `gedung` (`id_gedung`, `nama_gedung`, `deskripsi`, `foto_gedung`) VALUES
(1, 'Gedung Utama', 'Sebagai Perguruan Tinggi jalur vokasional yang berorientasi pada penguatan kemampuan praktek dan keterampilan mahasiswanya, proses pendidikan Polibatam didukung infrastruktur gedung yang sangat memadai. Gedung bebas asap rokok tersebut, berdiri kokoh diatas lahan seluas 12,5 Ha di pusat kota Batam, Batam Center. Gedung Utama tersebut merupakan pusat aktivitas manajemen, dosen dan proses kegiatan akademik dilakukan. Fasilitas digedung ini meliputi 20 ruang kelas, 29 laboratorium, perpustakaan, ruang administrasi, dan ruang layanan informasi serta berbagai sarana umum seperti masjid, kantin, dan auditorium yang cukup luas dengan daya tampung sekitar 1000 orang.', 'gedung-utama.jpg'),
(2, 'Gedung TA', 'Tower A (TA) atau disebut gedung Mohamad Nasir, diresmikan oleh Mentri Riset, Teknologi dan Pendidikan Tinggi Republik Indonesia, Prof. H. Mohamad Nasir, Ph.D.Ak. Batam 28 Agustus 2017. Tower ini berisikan 12 lantai dengan fasilitas 3 lift, toilet dan mushollah tiap lantai. Tower ini di lengkapi dengan HYDRANT dan kotak P3K serta tower ini juga dilengkapi dengan tangga darurat dengan indikasi pintu berwarna merah.', 'tower-a.jpg'),
(3, 'Gedung Techno', 'Peresmian Gedung Technopreneur Center Politeknik Negeri Batam yang  dilakukan oleh Direktur Jenderal Pendidikan Vokasi Kementerian  Pendidikan, Kebudayaan, Riset, dan Teknologi, Dr. Wikan Sakarinto pada  Senin, 21 Maret 2022. Gedung ini merupakan pusat kegiatan dari  Project/problem/product Based Learning (PBL) yang merupakan salah satu  model pembelajaran yang digaungkan oleh seluruh pendidikan vokasi yang  ada di Indonesia.', 'tekno.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `libur_nasional`
--

CREATE TABLE `libur_nasional` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `deskripsi` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `libur_nasional`
--

INSERT INTO `libur_nasional` (`id`, `tanggal`, `deskripsi`) VALUES
(1, '2025-01-01', 'Tahun Baru Masehi'),
(2, '0000-00-00', 'Tahun Baru Imlek 2576 Kongzili'),
(3, '2025-03-29', 'Hari Suci Nyepi Tahun Baru Saka 1947'),
(4, '2025-04-18', 'Wafat Isa Almasih'),
(5, '2025-04-22', 'Hari Raya Idul Fitri 1446H (Hari Pertama)'),
(6, '2025-04-23', 'Hari Raya Idul Fitri 1446H (Hari Kedua)'),
(7, '2025-05-01', 'Hari Buruh Internasional'),
(8, '2025-05-29', 'Kenaikan Isa Almasih'),
(9, '2025-06-01', 'Hari Lahir Pancasila'),
(10, '2025-06-07', 'Hari Raya Idul Adha 1446H'),
(11, '2025-07-27', 'Tahun Baru Islam 1447H'),
(12, '2025-08-17', 'Hari Kemerdekaan Republik Indonesia'),
(13, '2025-10-25', 'Maulid Nabi Muhammad SAW'),
(14, '2025-12-25', 'Hari Raya Natal');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` int(11) NOT NULL,
  `id_peminjaman` int(10) NOT NULL,
  `nik` varchar(30) NOT NULL,
  `status_peminjaman` enum('Berhasil','Dibatalkan','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`id_notifikasi`, `id_peminjaman`, `nik`, `status_peminjaman`) VALUES
(1, 4, '4342401085', 'Berhasil'),
(2, 5, '4342401080', 'Berhasil'),
(3, 6, '4342401088', 'Berhasil'),
(6, 9, '4342401088', 'Berhasil'),
(7, 10, '4342401085', 'Berhasil'),
(8, 11, '4342401085', 'Berhasil'),
(27, 20, '4342401085', 'Dibatalkan'),
(29, 22, '4342401085', 'Dibatalkan'),
(31, 24, '4342401085', 'Dibatalkan'),
(32, 25, '4342401080', 'Dibatalkan'),
(34, 27, '4342401080', 'Dibatalkan'),
(36, 29, '4342401080', 'Dibatalkan'),
(38, 31, '4342401080', 'Dibatalkan'),
(39, 32, '4342401085', 'Berhasil'),
(41, 34, '4342401080', 'Dibatalkan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_peminjaman` int(10) NOT NULL,
  `nik` varchar(30) NOT NULL,
  `kode_ruangan` varchar(50) NOT NULL,
  `tanggal_pemakaian` date NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL,
  `tanggal_peminjaman` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `keperluan` varchar(255) NOT NULL,
  `status_peminjaman` enum('Berhasil','Dibatalkan','','') NOT NULL,
  `penilaian` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peminjaman`
--

INSERT INTO `peminjaman` (`id_peminjaman`, `nik`, `kode_ruangan`, `tanggal_pemakaian`, `waktu_mulai`, `waktu_selesai`, `tanggal_peminjaman`, `keperluan`, `status_peminjaman`, `penilaian`) VALUES
(2, '4342401080', 'TA.101', '2025-01-08', '08:00:00', '09:00:00', '2025-01-13 07:23:33', 'pbl', 'Berhasil', 2),
(3, '4342401085', 'TA.101', '2025-01-07', '08:00:00', '09:00:00', '2025-01-06 19:57:27', '10', 'Berhasil', 0),
(4, '4342401085', 'TA.101', '2025-01-07', '09:00:00', '10:00:00', '2025-01-06 20:06:11', '10', 'Berhasil', 0),
(5, '4342401080', 'TA.101', '2025-01-09', '08:00:00', '09:00:00', '2025-01-13 07:20:59', 'p', 'Berhasil', 7),
(6, '4342401088', 'TA.94A', '2025-09-01', '08:00:00', '12:00:00', '2025-01-09 05:25:14', 'Project PBL', 'Berhasil', 0),
(9, '4342401088', 'TP205 - CWS Batu Ampar', '2025-10-01', '08:00:00', '09:00:00', '2025-01-09 05:53:44', 'Project PBL', 'Berhasil', 0),
(10, '4342401085', 'TA.94A', '2025-01-09', '08:00:00', '09:00:00', '2025-01-09 06:03:58', 'pbl', 'Berhasil', 0),
(11, '4342401085', 'TP203 - CWS Batam Center', '2025-01-09', '08:00:00', '09:00:00', '2025-01-09 06:25:28', 'pbl', 'Berhasil', 0),
(13, '4342401066', 'TA.101', '2025-01-08', '13:00:00', '14:00:00', '2025-01-09 06:33:11', 'Project pbl', 'Berhasil', 9),
(14, '4342401067', 'TA.101', '2025-01-08', '14:00:00', '17:00:00', '2025-01-09 07:14:32', 'Pertemuan Manpro PBL', 'Berhasil', 9),
(20, '4342401085', 'TA.101', '2027-01-08', '08:00:00', '09:00:00', '2025-01-09 12:29:52', 'pbl', 'Dibatalkan', 0),
(22, '4342401085', 'TA.94A', '2025-01-14', '08:00:00', '09:00:00', '2025-01-10 04:37:12', 'pbl', 'Dibatalkan', 0),
(24, '4342401085', 'TA.94A', '2025-01-27', '08:00:00', '09:00:00', '2025-01-10 04:42:09', 'pbl', 'Dibatalkan', 0),
(25, '4342401080', 'TA.101', '2025-01-31', '08:00:00', '09:00:00', '2025-01-09 07:40:36', 'p', 'Dibatalkan', 0),
(27, '4342401080', 'TA.101', '2025-01-17', '08:00:00', '09:00:00', '2025-01-10 06:38:42', 'pbl', 'Dibatalkan', 0),
(29, '4342401080', 'TA.101', '2025-01-14', '08:00:00', '09:00:00', '2025-01-12 00:38:11', 'pbl', 'Dibatalkan', 0),
(31, '4342401080', 'TA.101', '2025-01-16', '08:00:00', '09:00:00', '2025-01-13 22:26:46', 'pbl', 'Dibatalkan', 0),
(32, '4342401085', 'TA.101', '2025-01-20', '08:00:00', '09:00:00', '2025-01-13 22:28:37', '10', 'Berhasil', 0),
(34, '4342401080', 'TA 13.4', '2025-01-15', '08:00:00', '09:00:00', '2025-01-13 22:31:55', 'pppp', 'Dibatalkan', 0),
(35, '4342401068', 'TA.101', '2025-01-15', '08:00:00', '09:00:00', '2025-01-13 22:44:12', 'pvl', 'Berhasil', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `nik` varchar(30) NOT NULL,
  `nama_lengkap` varchar(50) NOT NULL,
  `kata_sandi` varchar(255) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `peran` enum('Mahasiswa','Dosen','PIC Ruangan','Admin') NOT NULL,
  `foto_pengguna` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengguna`
--

INSERT INTO `pengguna` (`nik`, `nama_lengkap`, `kata_sandi`, `email`, `peran`, `foto_pengguna`) VALUES
('102020', 'Hilda Widyastuti, S.T., M.T. ', '$2y$10$LbtYCfEZiprM52UrjnTUNuh0uYcjnh3YzEgRg73Vx97KikdOJGfR6', '', 'PIC Ruangan', ''),
('113105', 'Supardianto, S.ST., M.Eng', '$2y$10$auOmAfx2GmL1vD.YGiVM/OEp64cofezhOQcvmIdMa7IGh55riZd.y', NULL, 'PIC Ruangan', 'foto_default.jpg'),
('121246', 'Siskha Handayani, S.Si., M.Si', '$2y$10$8Zn6knlwOTb72ElbvNC8nOn7I50zaLrBkWEjq99q/s1CanBKuhMPa', NULL, 'Dosen', 'foto_default.jpg'),
('122275', 'Ahmadi Irmansyah Lubis, S.kom., M.kom.', '$2y$10$FNLHZ8g9UKs0ukThwd7pEe84Jv.dcomcuaiiUV1cTNq8wLthLds42', NULL, 'Dosen', 'foto_default.jpg'),
('122277', 'Noper Ardi, S.Pd., M.Eng', '$2y$10$BaTpIIDYAXqVSrF9G1DbMeri8jC6TGIZTaUniQkHjiEibgG7a.3dC', NULL, 'Dosen', 'foto_default.jpg'),
('122279', 'Alena Uperiati, S.T, M.Cs', '$2y$10$WcjNp2TCxbcy3fStnVVti.vyiJ5QlKupMxsiAVFst22fZS7Q3CRwe', 'alena@gmail.com', 'PIC Ruangan', 'foto_default.jpg'),
('215211', 'Banu Failasuf, S.Tr', '$2y$10$oPsDNUU.CXdDRfnkEXOtw.IOdNJxqJrTp9F6tNIHnuUGqrJKZdH3K', 'banu@gmail.com', 'PIC Ruangan', ''),
('222331', 'Gilang Bagus Ramadhan, A.Md.Kom', '$2y$10$0dLmi/R2cwlJ70kDufGfPufOadckaVnuB03OhxUJZU2NTOLayI1sm', '', 'PIC Ruangan', 'foto_default.jpg'),
('222332', 'Iqbal Afif, A.Md.Kom', '$2y$10$gYx6eYT457N9s28mrjXS8ekPOFC86cZLR/tKBMvNnwnIw1OUEKd0u', NULL, 'PIC Ruangan', 'foto_default.jpg'),
('4342401061', 'Fajar Mirza Hanif', '$2y$10$sd.Ccca.KgBk.MRnSJ/uluW42m5k/7VLsZRBQAWxxUPis/23TqKIa', '', 'Mahasiswa', ''),
('4342401062', 'Putri', '$2y$10$VvjeFeWKHLYoWuaZavLs5eTKcZsPdITjRkxoBhjYIKwxJqYR.a2N.', '', 'Mahasiswa', ''),
('4342401063', 'Afifah Luthfi Fathonah', '$2y$10$L681sTAGhdhIyHGQZw1SK.B1io.hU6EkfYvHIQxkVU9jmMsH62Ibm', '', 'Mahasiswa', ''),
('4342401064', 'Navita Damayati Syarif', '$2y$10$WkytNzHjd5dD5uhIxOJus.T3/jDOI./leYedO6vsc3422fFdkiZHK', '', 'Mahasiswa', ''),
('4342401065', 'Muhammad Fadhil Osman', '$2y$10$HnA7OZIZ2N1STaYvmCaIbOLcNE7Tfxv9GO5rbP1HUPvywPTVgEFRi', '', 'Mahasiswa', ''),
('4342401066', 'Thalita Aurelia Marsim', '$2y$10$KHgRv0.SqCh1kZx4OkyBqevIwj6ldcJVg7zGiqo4Tc8.fladsu4Pe', 'lita@gmail.com', 'Mahasiswa', 'pengguna_4342401066_677f6e89ed6e5_a4adc32531.jpg'),
('4342401067', 'Muhammad Thariq Syafruddin', '$2y$10$cHlVqDsU6MAD.Cb4KBZwBe1i3a1zHgvWUHMvLujUze7M/IIxlOaA.', 'thariq@gmail.com', 'Mahasiswa', 'pengguna_4342401067_677f77b2c76cf_bc51f43130.jpg'),
('4342401068', 'Steven Kumala', '$2y$10$tL1yXCQtmJFuocmIeewzn..PS0L0Qg03lWlNWeGBCieB8fEZ0I1gS', '', 'Mahasiswa', ''),
('4342401069', 'Hafiz Atama Romadhoni', '$2y$10$FmOYMIPYCmZqCwKCTRaozOR7hDcpO5Sx.pDVNn1z2lYZ/djZ7YCri', 'hafiz@gmail.com', 'Mahasiswa', ''),
('4342401070', 'Muhamad Ariffadhlullah', '$2y$10$ZPy2cSRzeG.fzaV8ntQuHungdgOF6UHHAAfgcVX4H4OFgE4jvIc.C', '', 'Mahasiswa', ''),
('4342401071', 'Ibra Marioka', '$2y$10$2T7kPfrkTqwKavHNZx4T/OdoA3EU0mX0pEY4FVVwoHTcx/LlnRPuO', '', 'Mahasiswa', ''),
('4342401072', 'Diva Satria', '$2y$10$lB.e7NRZ8.nGHpjdQ1.szuXH5ofzgznmAFn6cZtv1GXdPkJ/Dq7De', '', 'Mahasiswa', ''),
('4342401073', 'Fahri Andrean Saputra', '$2y$10$tjB5Y8/T9ixEeivZ1Q2iqeH2on.RkeoI5VKSGcFO1UssYHjDDTU/W', '', 'Mahasiswa', ''),
('4342401074', 'Surya Nur Aini', '$2y$10$33r2eyaZDoTu7OPSixDzXe/wJHN7.e1.rqjtJ/cP9WRs8LFGgh9QK', '', 'Mahasiswa', ''),
('4342401075', 'Arshafin Alfisyahrin', '$2y$10$55KRJS4ce.vJ15..fGOLiOnAZF7Z1rmCQeaxVYyC8VwjH7WFaqkAS', '', 'Mahasiswa', ''),
('4342401076', 'Muhammad Addin', '$2y$10$vhhziWEI5tig.c5K6vGAAeTu.bwMlDICqa7jmw5QVeIHtjSva2Awy', '', 'Mahasiswa', ''),
('4342401077', 'Jerimy Steven Robert Monangin', '$2y$10$JBt5hs0vpI8oL80tMAFUQepUcskqeuBorpS7tB8Wd9hQw6YkA0AnW', '', 'Mahasiswa', ''),
('4342401078', 'Muhammad Ali Asrory', '$2y$10$qnswauv1tXSt9zFTWO5M5uE0oNlhLNfKG.T8iqP9me9eeKNohKR8C', '', 'Mahasiswa', ''),
('4342401079', 'Muhammad Faldy Rizaldi', '$2y$10$pZxI9fK7WKR/1DdweSz5UO2ne26aK0Vh4aE.y.lmoQOgbIJ0j869u', '', 'Mahasiswa', ''),
('4342401080', 'Adhyca Hafeez Wibowo', '$2y$10$trJ619T9FZ8dv2AZgftN3eobvtTzVdKUi/5cc4TTJLYtn59Z9gXBO', 'adhykarya@gmail.com', 'Admin', 'pengguna_4342401080_67780925dcf2f_d2d32f0a7d.gif'),
('4342401081', 'Lathifah Nasywa Kesumaputri', '$2y$10$MiWVujE7uZoscvDZhvQ/o.i/uoo6p.abZAmhzFj9aSQr6vPX5RlW.', '', 'Mahasiswa', ''),
('4342401082', 'Agnes Natalia Silalahi', '$2y$10$B3BZN3p..aRo.jlTz3dWc.Vs6pLTmNbS12ipHaGFpq/aHtVNIOfKy', '', 'Mahasiswa', ''),
('4342401083', 'Nayla Nur Nabila', '$2y$10$QFZy7bmwjEvy1f3P34.RW.u/6KfLj2mONz8ZXlOsi3Fi26kmOpmjy', '', 'Admin', ''),
('4342401084', 'Hermansa', '$2y$10$rY0OK8IPYkBcS5jHP6sB..S6ABcFjngrtsH4MlGfa1j7NTUnFFd.y', '', 'Admin', ''),
('4342401085', 'Berkat Tua Siallagan', '$2y$10$xyHp7J5aIkQlTuNDoHviROWVNnuiPV3Y3Qo0u59SFPILZU0nDHESi', 'berkat@gmail.com', 'Admin', 'pengguna_4342401085_677f9dc1021c2_c00d4f1e2b.gif'),
('4342401086', 'Ananda Meliana Sembiring', '$2y$10$IdySnwh503QfUf/ot3G2Je3d9oEfihLJq9WhOkO/ctmasEYrtlIce', '', 'Mahasiswa', ''),
('4342401087', 'Suci Aqilah Nst', '$2y$10$/taAkwlyIZ.QPlXQ4hE4bOrH0M0zi2PazKtWoCaqp9bvFIKfuiNCa', 'saqilahnst@gmail.com', 'Admin', 'pengguna_4342401087_677d06b5ed6c8_910f7e4783.jpg'),
('4342401088', 'Ray Refaldo', '$2y$10$DdKJWyk3pLq6naGdt6S8k.oB0EckO3fZ/.vSHtycIugIVEdhY/xfm', 'rayreyfaldo@gmail.com', 'Admin', 'pengguna_4342401088_677e82c958c8b_b444e46b55.jpg'),
('4342401089', 'Bagus Harimukti', '$2y$10$.myoRitLXiFZOCT.SZE3U.MTo6XwJOLy/71GETtFUd8EdDrPVx6yW', '', 'Mahasiswa', ''),
('4342401090', 'Hady Wiranata', '$2y$10$WWyZEuLxDz4.p6ac.Xdcnuwtf69PAkMIsRlwb2gvVg0V3Ey.NktWS', '', 'Mahasiswa', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penilaian`
--

CREATE TABLE `penilaian` (
  `id_penilaian` int(11) NOT NULL,
  `id_peminjaman` int(11) NOT NULL,
  `nilai_penilaian` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penilaian`
--

INSERT INTO `penilaian` (`id_penilaian`, `id_peminjaman`, `nilai_penilaian`) VALUES
(10, 13, 9),
(11, 14, 9),
(12, 5, 7),
(13, 2, 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat`
--

CREATE TABLE `riwayat` (
  `id_riwayat` int(11) NOT NULL,
  `nik` varchar(30) NOT NULL,
  `id_peminjaman` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `riwayat`
--

INSERT INTO `riwayat` (`id_riwayat`, `nik`, `id_peminjaman`) VALUES
(2, '4342401080', 2),
(3, '4342401085', 3),
(4, '4342401085', 4),
(5, '4342401080', 5),
(6, '4342401088', 6),
(9, '4342401088', 9),
(10, '4342401085', 10),
(11, '4342401085', 11),
(13, '4342401066', 13),
(14, '4342401067', 14),
(21, '4342401085', 32),
(23, '4342401068', 35);

-- --------------------------------------------------------

--
-- Struktur dari tabel `ruangan`
--

CREATE TABLE `ruangan` (
  `kode_ruangan` varchar(50) NOT NULL,
  `id_gedung` int(2) NOT NULL,
  `nik_pic` varchar(30) DEFAULT NULL,
  `jenis_ruang` varchar(30) NOT NULL,
  `kapasitas` int(3) NOT NULL,
  `fasilitas` text NOT NULL,
  `lokasi` varchar(20) NOT NULL,
  `status_ruangan` varchar(15) NOT NULL,
  `penilaian` int(11) DEFAULT NULL,
  `foto_ruang` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ruangan`
--

INSERT INTO `ruangan` (`kode_ruangan`, `id_gedung`, `nik_pic`, `jenis_ruang`, `kapasitas`, `fasilitas`, `lokasi`, `status_ruangan`, `penilaian`, `foto_ruang`) VALUES
('TA.101', 2, '222331', 'Ruang Rapat', 20, 'TV', 'Lantai 10', 'tertutup', 0, 'ruang_676e780f3d6b7.jpg'),
('TA.22A', 2, '102020', 'Ruang Rapat', 30, 'TV, Injector, Mic, Speaker', 'Lantai 2', 'tertutup', 0, 'ruang_6772c66c160b2.jpg'),
('TA.61', 2, '222331', 'Ruang Rapat', 20, 'TV, Injector, Mic, Speaker', 'Lantai 6', 'tertutup', 0, 'ruang_6772c67ccb854.jpg'),
('TA.81', 2, '', 'Ruang Rapat', 15, 'TV, Injector, Mic, Speaker', 'Lantai 8', 'tertutup', 0, 'ruang_6755393a50d1d1.18448191.jpg'),
('TA.91', 2, '', 'Ruang Rapat', 20, 'TV, Injector, Mic, Speaker', 'Lantai 9', 'terbuka', 0, 'ruang_6755395f7b59d0.13355618.jpg'),
('TA.92A', 2, '', 'Ruang Rapat', 25, 'TV, Injector, Mic, Speaker', 'Lantai 9', 'terbuka ', 0, 'ruang_67683252009415.24797525.jpg'),
('TA.94A', 2, '', 'Ruang Rapat', 30, 'TV, Injector, Mic, Speaker', 'Lantai 9', 'terbuka', 0, 'ruang_6772c82800aea.jpg'),
('TP102 - CWS Nagoya', 3, '', 'Ruang Diskusi', 10, 'meja,kursi,papan tulis,tv,monitor', 'Lantai 1', 'terbuka', 0, 'ruang_6772d70ab57d9.jpg'),
('TP103 - CWS sei jodoh', 3, '', 'Ruang Diskusi', 10, 'meja,kursi,tv', 'Lantai 1', 'terbuka', 0, 'ruang_6772d78e04fc8.jpg'),
('TP104 - CWS tiban', 3, '', 'Ruang Diskusi', 10, 'meja,kursi,tv', 'Lantai 1', 'terbuka', 0, 'ruang_6772d79fdbb4b.jpg'),
('TP105 - CWS baloi', 3, '', 'Ruang Diskusi', 10, 'meja,kursi,tv', 'Lantai 1', 'terbuka', 0, 'ruang_6772d7b61033c.jpg'),
('TP106 - CWS Kabil', 3, '', 'Ruang Diskusi', 10, 'meja,kursi,papan tulis,tv', 'Lantai 1', 'terbuka', 0, 'ruang_6772d7c17de32.jpg'),
('TP108 - CWS nongsa', 3, NULL, 'Ruang Diskusi', 8, 'meja,kursi,papan tulis,tv', 'Lantai 1', 'terbuka', 0, 'ruang_6772d85a747b78.73052367.jpg'),
('Tp108 - CWS sei panas', 3, '', 'Ruang Diskusi', 20, 'meja,kursi,papan tulis,tv', 'Lantai 1', 'terbuka', 0, 'ruang_6772d7d03c2c3.jpg'),
('TP111- CWS sagulung', 3, '', 'Ruang Diskusi', 20, 'meja,kursi,tv', 'Lantai 1', 'terbuka', 0, 'ruang_6772d7ded73d8.jpg'),
('TP201 - CWS muka kuning', 3, NULL, 'Ruang Diskusi', 40, 'meja,kursi,papan tulis,tv', 'Lantai 2', 'terbuka', 0, 'ruang_6772d8a53e1c42.56257893.jpg'),
('TP202 - CWS Sekupang', 3, NULL, 'Ruang Diskusi', 30, 'meja,kursi,papan tulis,tv', 'Lantai 2', 'terbuka', 0, 'ruang_6772d90e359f64.98607179.jpg'),
('TP203 - CWS Batam Center', 3, NULL, 'Ruang Diskusi', 32, 'meja,kursi,papan tulis,tv', 'Lantai 2', 'terbuka', 0, 'ruang_6772d875547516.62294387.jpg'),
('TP205 - CWS Batu Ampar', 3, NULL, 'Ruang Diskusi', 50, 'meja,kursi,papan tulis,tv,speaker', 'Lantai 2', 'terbuka', 0, 'ruang_6772d8d53699c6.27907548.jpg');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `gedung`
--
ALTER TABLE `gedung`
  ADD PRIMARY KEY (`id_gedung`);

--
-- Indeks untuk tabel `libur_nasional`
--
ALTER TABLE `libur_nasional`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`),
  ADD UNIQUE KEY `id_pemesanan` (`id_peminjaman`),
  ADD KEY `nik` (`nik`),
  ADD KEY `id_peminjaman` (`id_peminjaman`),
  ADD KEY `id_peminjaman_2` (`id_peminjaman`),
  ADD KEY `nik_2` (`nik`),
  ADD KEY `id_peminjaman_3` (`id_peminjaman`),
  ADD KEY `nik_3` (`nik`),
  ADD KEY `id_notifikasi` (`id_notifikasi`),
  ADD KEY `nik_4` (`nik`);

--
-- Indeks untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `pemesanan_ibfk_2` (`kode_ruangan`),
  ADD KEY `nik` (`nik`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`nik`);

--
-- Indeks untuk tabel `penilaian`
--
ALTER TABLE `penilaian`
  ADD PRIMARY KEY (`id_penilaian`),
  ADD UNIQUE KEY `unique_peminjaman_rating` (`id_peminjaman`);

--
-- Indeks untuk tabel `riwayat`
--
ALTER TABLE `riwayat`
  ADD PRIMARY KEY (`id_riwayat`),
  ADD KEY `id_pemesanan` (`id_peminjaman`),
  ADD KEY `nik` (`nik`);

--
-- Indeks untuk tabel `ruangan`
--
ALTER TABLE `ruangan`
  ADD PRIMARY KEY (`kode_ruangan`),
  ADD KEY `id_gedung` (`id_gedung`),
  ADD KEY `nik` (`nik_pic`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `gedung`
--
ALTER TABLE `gedung`
  MODIFY `id_gedung` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `libur_nasional`
--
ALTER TABLE `libur_nasional`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_peminjaman` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `penilaian`
--
ALTER TABLE `penilaian`
  MODIFY `id_penilaian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `riwayat`
--
ALTER TABLE `riwayat`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjaman` (`id_peminjaman`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`nik`) REFERENCES `pengguna` (`nik`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penilaian`
--
ALTER TABLE `penilaian`
  ADD CONSTRAINT `fk_peminjaman_rating` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjaman` (`id_peminjaman`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `riwayat`
--
ALTER TABLE `riwayat`
  ADD CONSTRAINT `riwayat_ibfk_1` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjaman` (`id_peminjaman`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_ibfk_2` FOREIGN KEY (`nik`) REFERENCES `pengguna` (`nik`);

--
-- Ketidakleluasaan untuk tabel `ruangan`
--
ALTER TABLE `ruangan`
  ADD CONSTRAINT `ruangan_ibfk1` FOREIGN KEY (`id_gedung`) REFERENCES `gedung` (`id_gedung`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
