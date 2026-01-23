-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql:3306
-- Generation Time: Jan 20, 2026 at 01:33 AM
-- Server version: 11.8.3-MariaDB-ubu2404
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `survey_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `criteria`
--

CREATE TABLE `criteria` (
  `criteria_ID` varchar(10) NOT NULL,
  `domain_ID` varchar(10) DEFAULT NULL,
  `criteria_name` varchar(100) NOT NULL,
  `input_id` varchar(10) DEFAULT NULL,
  `input_at` date DEFAULT NULL,
  `updated_id` varchar(10) DEFAULT NULL,
  `updated_at` date DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `criteria`
--

INSERT INTO `criteria` (`criteria_ID`, `domain_ID`, `criteria_name`, `input_id`, `input_at`, `updated_id`, `updated_at`, `status`) VALUES
('AC001', 'AD001', 'Tadbir urus keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AC002', 'AD001', 'Pengurusan Atasan', NULL, NULL, NULL, NULL, 'Active'),
('AC003', 'AD001', 'Polisi dan prosedur Keselamatan Siber', NULL, NULL, '4', '2025-12-23', 'Active'),
('AC004', 'AD002', 'Penilaian Risiko', NULL, NULL, '4', '2025-12-23', 'Active'),
('AC005', 'AD002', 'Rawatan Risiko', NULL, NULL, '4', '2025-12-23', 'Active'),
('AC006', 'AD003', 'Standard dan amalan terbaik keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AC007', 'AD003', 'Pengauditan keselamatan siber ', NULL, NULL, NULL, NULL, 'Active'),
('AC008', 'AD004', 'Pembangunan Kompetensi dan Kesedaran', NULL, NULL, NULL, NULL, 'Active'),
('AC009', 'AD004', 'Pengurusan Peranan dan Tanggungjawab Keselamatan Siber', NULL, NULL, '4', '2026-01-19', 'Active'),
('AC010', 'AD005', 'Inventori aset', NULL, NULL, '4', '2025-12-23', 'Inactive'),
('AC011', 'AD004', 'Klasifikasi maklumat', NULL, NULL, '4', '2026-01-19', 'Active'),
('AC012', 'AD006', 'Penguatkuasaan mekanisme pengesahan identiti', NULL, NULL, NULL, NULL, 'Active'),
('AC013', 'AD006', 'Pengurusan capaian', NULL, NULL, NULL, NULL, 'Active'),
('AC014', 'AD007', 'Kesedaran dan pematuhan', NULL, NULL, '4', '2025-12-23', 'Active'),
('AC015', 'AD007', 'Penilaian keberkesanan pihak ketiga', NULL, NULL, NULL, NULL, 'Active'),
('AC017', 'AD008', 'Kawalan keselamatan sistem dan aplikasi', NULL, NULL, '4', '2025-12-23', 'Active'),
('AC018', 'AD008', 'Operasi keselamatan', NULL, NULL, '4', '2025-12-23', 'Active'),
('AC019', 'AD009', 'Pelan insiden keselamatan siber ', NULL, NULL, NULL, NULL, 'Active'),
('AC020', 'AD009', 'Simulasi pelan insiden keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AC021', 'AD010', 'Prosedur pengurusan ancaman dan kerentanan keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AC022', 'AD010', 'Teknologi bagi pengurusan ancaman dan kerentanan', NULL, NULL, NULL, NULL, 'Active'),
('AC023', 'AD011', 'Pelan kesinambungan perkhidmatan ICT', NULL, NULL, NULL, NULL, 'Active'),
('AC024', 'AD011', 'Simulasi ', NULL, NULL, NULL, NULL, 'Active'),
('AC025', 'AD007', 'Kumpulan pakar dan pakar bidang', '4', '2025-12-23', NULL, NULL, 'Active');

--
-- Triggers `criteria`
--
DELIMITER $$
CREATE TRIGGER `trg_criteria_id` BEFORE INSERT ON `criteria` FOR EACH ROW BEGIN
    DECLARE next_id INT;

    SELECT IFNULL(MAX(CAST(SUBSTRING(criteria_ID, 3) AS UNSIGNED)), 0) + 1
    INTO next_id
    FROM criteria;

    SET NEW.criteria_ID = CONCAT('AC', LPAD(next_id, 3, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `domain`
--

CREATE TABLE `domain` (
  `domain_ID` varchar(10) NOT NULL,
  `domain_name` varchar(100) NOT NULL,
  `input_id` varchar(10) DEFAULT NULL,
  `input_at` date DEFAULT NULL,
  `updated_id` varchar(10) DEFAULT NULL,
  `updated_at` date DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `sec_ID` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `domain`
--

INSERT INTO `domain` (`domain_ID`, `domain_name`, `input_id`, `input_at`, `updated_id`, `updated_at`, `status`, `sec_ID`) VALUES
('AD001', 'Tadbir Urus', NULL, NULL, '4', '2025-11-17', 'Active', '5'),
('AD002', 'Pengurusan Risiko', NULL, NULL, '4', '2025-11-07', 'Active', '6'),
('AD003', 'Pematuhan dan Pengauditan', NULL, NULL, NULL, NULL, 'Active', '9'),
('AD004', 'Keselamatan Sumber Manusia', NULL, NULL, '4', '2026-01-19', 'Active', 'A6'),
('AD005', 'Pengurusan Aset', NULL, NULL, NULL, NULL, 'Active', 'A5'),
('AD006', 'Pengurusan Identiti Dan Capaian', NULL, NULL, NULL, NULL, 'Active', 'A5'),
('AD007', 'Pengurusan Pihak Ketiga', NULL, NULL, NULL, NULL, 'Inactive', 'A5'),
('AD008', 'Pengurusan Keselamatan Sistem Dan Aplikasi', NULL, NULL, NULL, NULL, 'Active', 'A8'),
('AD009', 'Pengurusan Insiden', NULL, NULL, NULL, NULL, 'Active', 'A5'),
('AD010', 'Pengurusan Ancaman Dan Kerentanan', NULL, NULL, NULL, NULL, 'Active', 'A8'),
('AD011', 'Pengurusan Kesinambungan Perkhidmatan ICT', NULL, NULL, NULL, NULL, 'Active', 'A5');

--
-- Triggers `domain`
--
DELIMITER $$
CREATE TRIGGER `trg_domain_ID` BEFORE INSERT ON `domain` FOR EACH ROW BEGIN
	DECLARE next_id INT;
	
	SELECT IFNULL(MAX(CAST(SUBSTRING(domain_ID, 3) AS UNSIGNED)), 0) +1
	INTO next_id
	FROM domain;
	SET NEW.domain_ID = CONCAT('AD', LPAD(next_id, 3, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `element`
--

CREATE TABLE `element` (
  `element_ID` varchar(10) NOT NULL,
  `criteria_ID` varchar(10) DEFAULT NULL,
  `element_name` varchar(200) DEFAULT NULL,
  `input_id` varchar(10) DEFAULT NULL,
  `input_at` date DEFAULT NULL,
  `updated_id` varchar(10) DEFAULT NULL,
  `updated_at` date DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `element`
--

INSERT INTO `element` (`element_ID`, `criteria_ID`, `element_name`, `input_id`, `input_at`, `updated_id`, `updated_at`, `status`) VALUES
('AE001', 'AC001', 'Pembangunan dan pelaksanaan tadbir urus keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE002', 'AC001', 'Tanggungjawab dan terma tadbir urus dihuraikan dengan jelas', NULL, NULL, NULL, NULL, 'Active'),
('AE003', 'AC001', 'Pembangunan strategi keselamatan siber sejajar dengan strategi organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE004', 'AC002', 'Komitmen pengurusan atasan dalam pembangunan serta penyemakan polisi dan program keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE005', 'AC002', 'Komitmen pengurusan atasan dalam penyediaan sumber kewangan dan sumber manusia.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE006', 'AC002', 'Komitmen pengurusan atasan dalam memastikan organisasi, pihak ketiga dan pihak yang berkepentingan organisasi memahami kepentingan keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE007', 'AC003', 'Objektif dasar/polisi dan prosedur dalam penentuan tanggungjawab organisasi, warga, pihak ketiga dan pihak berkepentingan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE008', 'AC003', 'Pembangunan dasar/polisi dan prosedur keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE009', 'AC003', 'Penyebaran dasar/polisi dan prosedur keselamatan siber kepada warga, pihak ketiga dan pihak berkepentingan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE010', 'AC003', 'Penyemakan dan pengemaskinian dasar/polisi dan prosedur secara berkala.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE011', 'AC004', 'Penentuan penilaian risiko keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE012', 'AC004', 'Pelaksanaan penilaian risiko keselamatan siber disemak dan dikemas kini secara berkala.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE013', 'AC005', 'Penyemakan pelaksanaan rawatan risiko keselamatan siber secara berkala.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE014', 'AC005', 'Pemantauan keberkesanan rawatan risiko.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE015', 'AC006', 'Dasar/polisi dan prosedur sejajar dengan keperluan undang-undang, peraturan, standard dan amalan terbaik keselamatan siber organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE019', 'AC008', 'Kesedaran dan latihan keselamatan siber', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE020', 'AC008', 'Pengasingan tugas berdasarkan peranan dan tanggungjawab dalam keselamatan siber', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE021', 'AC008', 'Pengukuran keberkesanan inisiatif program kesedaran dan latihan keselamatan siber', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE022', 'AC009', 'Dokumentasi tanggungjawab keselamatan siber.', NULL, NULL, '4', '2026-01-19', 'Active'),
('AE023', 'AC009', 'Penentuan dan pengurusan peranan keselamatan siber untuk memastikan kecukupan dan redundansi kakitangan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE024', 'AC009', 'Pelaksanaan Program Pengganti (Succesor Program)', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE025', 'AC008', 'Pengurusan inventori aset.', NULL, NULL, '4', '2026-01-19', 'Active'),
('AE026', 'AC011', 'Klasifikasi maklumat mengikut maklumat terperingkat', NULL, NULL, NULL, NULL, 'Active'),
('AE027', 'AC011', 'Perlindungan, sanitasi dan pelupusan maklumat semasa kitar hayat.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE028', 'AC012', 'Pengwujudan dan pengurusan identiti pengesahan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE029', 'AC012', 'Penguatkuasaan mekanisme pengesahan bagi sistem dan aplikasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE030', 'AC013', 'Pemantauan dan penyemakan hak akses pengguna secara berkala.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE031', 'AC013', 'Pemantauan dan penyemakan pengurusan akses data dan maklumat organisasi bagi pihak ketiga secara berkala.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE033', 'AC014', 'Kesedaran pihak ketiga terhadap kepentingan keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE034', 'AC014', 'Pengesahan akuan perjanjian keselamatan siber pihak ketiga.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE035', 'AC015', 'Penilaian keberkesanan pihak ketiga', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE037', 'AC017', 'Penyelesaian keselamatan yang efektif berdasarkan standard dan amalan terbaik.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE038', 'AC017', 'Konfigurasi dan pengurusan aset IT yang selamat.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE039', 'AC017', 'Pemantauan aktiviti dan tingkah laku rangkaian dan infrastruktur sistem secara berterusan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE040', 'AC017', 'Perisian dan perkakasan terkini.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE041', 'AC017', 'Penilaian penggunaan teknologi terkini.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE042', 'AC018', 'Pemantauan dan pengesanan ancaman keselamatan siber melalui Pusat Operasi Keselamatan (SOC : Security Operation Center).', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE043', 'AC018', 'Penggunaan teknologi terkini dalam pelaksanaan dan pemantauan keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE044', 'AC019', 'Pelan tindak balas insiden keselamatan siber', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE045', 'AC019', 'Takrif peranan dan tanggungjawab pasukan tindak balas insiden.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE047', 'AC019', 'Penyiasatan insiden dan amaran keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE048', 'AC019', 'Penyemakan pelan pengurusan insiden secara berkala.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE049', 'AC021', 'Pembangunan dan pelaksanaan ancaman dan kerentanan keselamatan siber secara proaktif.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE050', 'AC022', 'Penggunaan teknologi pengurusan pemantauan ancaman dan kerentanan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('AE051', 'AC023', 'Pembangunan dan pelaksanaan bagi pengurusan kesinambungan perkhidmatan ICT.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE052', 'AC023', 'Penyemakan pelan kesinambungan perkhidmatan ICT.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE053', 'AC024', 'Pelaksanaan simulasi pelan kesinambungan perkhidmatan ICT secara berkala.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE054', 'AC021', 'Penyediaan dan pengawalan versi dokumen prosedur bagi ancaman dan kelemahan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE055', 'AC020', 'Pembangunan prosedur tindak balas dan pemulihan insiden keselamatan siber secara proaktif.', NULL, NULL, '4', '2025-12-23', 'Active'),
('AE056', 'AC001', 'Pemeliharaan privasi dan perlindungan Maklumat Pengenalan Peribadi (Personal Identifiable Information -PII).', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE057', 'AC006', 'Pelaksanaan keselamatan siber organisasi mengikut keperluan undang-undang, peraturan, standard dan amalan terbaik.', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE058', 'AC006', 'Penguatkuasaan dan pengauditan yang disemak dan dikemas kini secara berkala.', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE059', 'AC007', 'Pelaksanaan tindakan susulan audit keselamtan siber.', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE060', 'AC007', 'Pemantauan penyediaan dokumentasi dan bukti pematuhan.', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE061', 'AC009', 'Pemantauan dan penyemakan tanggungjawab keselamtan siber dan keperluan kakitangan secara berkala.', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE062', 'AC010', 'Pemantauan dan penyemakan pengurusan inventori aset disemak dan dikemaskini secara berkala.', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE063', 'AC012', 'Pengwujudan dan pengekalan akses logikal.', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE064', 'AC012', 'Pemeriksaan, pemantauan dan penyemakan aktiviti capaian pengguna.', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE065', 'AC014', 'Pemantauan dan penyemakan pematuhan pihak ketiga yang diperakui secara berkala.', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE066', 'AC015', 'Pemantauan dan penyemakan penilaian keberkesanan pihak ketiga secara berkala.', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE067', 'AC025', 'Penglibatan pakar bidang atau kumpulan pakar dalam organisasi', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE068', 'AC017', 'Penggunaan teknologi yang efektif berdasarkan standard', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE069', 'AC018', 'Penilaian dan pematauan berkala bagi operasi keselamatan siber.', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE070', 'AC020', 'Penyemakan prosedur tindak balas dan pemulihan secara berkala berdasarkan Cyber Drills.', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE071', 'AC022', 'Penilaian penggunaan teknologi pengurusan pemantauan ancaman dan kerentanan.', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE072', 'AC022', 'Pemantauan pengujian keselamatan siber bagi rangkaian, sistem dan aplikasi secara berkala.', '4', '2025-12-23', NULL, NULL, 'Active'),
('AE073', 'AC024', 'Pemantauan dan penyemakan prosedur simulasi kesinambungan pekhidmatan ICT secara berkala.', '4', '2025-12-23', '4', '2025-12-24', 'Active');

--
-- Triggers `element`
--
DELIMITER $$
CREATE TRIGGER `trg_element_ID` BEFORE INSERT ON `element` FOR EACH ROW BEGIN
	DECLARE next_id INT;
	
	SELECT IFNULL(MAX(CAST(SUBSTRING(element_ID, 3) AS UNSIGNED)), 0) +1
	INTO next_id
	FROM element;
	SET NEW.element_ID = CONCAT('AE', LPAD(next_id, 3, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `element_control`
--

CREATE TABLE `element_control` (
  `id` int(11) NOT NULL,
  `element_ID` varchar(10) DEFAULT NULL,
  `sub_con_ID` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `element_control`
--

INSERT INTO `element_control` (`id`, `element_ID`, `sub_con_ID`) VALUES
(1, 'AE001', 'A.5.1'),
(2, 'AE003', 'A.5.1'),
(3, 'AE001', 'A.5.2'),
(4, 'AE002', 'A.5.2'),
(5, 'AE020', 'A.5.3'),
(6, 'AE023', 'A.5.3'),
(7, 'AE002', 'A.5.3'),
(8, 'AE022', 'A.5.1'),
(9, 'AE004', 'A.5.1'),
(10, 'AE007', 'A.5.1'),
(11, 'AE009', 'A.5.1'),
(12, 'AE010', 'A.5.1'),
(13, 'AE011', 'A.5.1'),
(14, 'AE012', 'A.5.1'),
(15, 'AE021', 'A.5.1'),
(16, 'AE007', 'A.5.2'),
(17, 'AE022', 'A.5.2'),
(18, 'AE023', 'A.5.2'),
(19, 'AE024', 'A.5.2'),
(20, 'AE062', 'A.5.2'),
(21, 'AE045', 'A.5.2'),
(22, 'AE061', 'A.5.2'),
(23, 'AE004', 'A.5.4'),
(24, 'AE005', 'A.5.4'),
(25, 'AE006', 'A.5.4'),
(26, 'AE007', 'A.5.4'),
(27, 'AE011', 'A.5.4'),
(28, 'AE012', 'A.5.4'),
(29, 'AE014', 'A.5.4'),
(30, 'AE013', 'A.5.4'),
(31, 'AE015', 'A.5.4'),
(32, 'AE058', 'A.5.4'),
(33, 'AE019', 'A.5.4'),
(34, 'AE022', 'A.5.4'),
(35, 'AE023', 'A.5.4'),
(36, 'AE024', 'A.5.4'),
(37, 'AE025', 'A.5.4'),
(38, 'AE062', 'A.5.4'),
(39, 'AE026', 'A.5.4'),
(40, 'AE034', 'A.5.4'),
(41, 'AE065', 'A.5.4'),
(42, 'AE035', 'A.5.4'),
(43, 'AE066', 'A.5.4'),
(44, 'AE067', 'A.5.4'),
(45, 'AE001', 'A.5.4'),
(46, 'AE039', 'A.5.7'),
(47, 'AE042', 'A.5.7'),
(48, 'AE049', 'A.5.7'),
(49, 'AE050', 'A.5.7'),
(50, 'AE071', 'A.5.7'),
(51, 'AE003', 'A.5.7');

-- --------------------------------------------------------

--
-- Table structure for table `gap_analysis`
--

CREATE TABLE `gap_analysis` (
  `GA_id` int(11) NOT NULL,
  `domain_ID` varchar(10) DEFAULT NULL,
  `criteria_ID` varchar(10) DEFAULT NULL,
  `element_ID` varchar(10) DEFAULT NULL,
  `survey_ID` varchar(50) DEFAULT NULL,
  `user_ID` int(11) DEFAULT NULL,
  `auditor_id` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `severity` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `status` enum('Open','In Progress','Resolved','Closed') DEFAULT 'Open',
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `user_id`, `token`, `expires_at`, `used`, `created_at`) VALUES
(1, 18, '65d33c6b47920b048dde36ca53cd6ec5659b3594986df3b21692c438de175f72', '2026-01-15 10:56:01', 0, '2026-01-15 09:56:01'),
(2, 18, 'f6a4669ed187c95003ddbc55490cc4d6df738bf14205b999ebe129bf0cfb3930', '2026-01-15 16:04:04', 0, '2026-01-15 15:04:04'),
(3, 18, '8d721b168cb409a1ed619863551a4f0686211249526508e960e243bf1104bf2f', '2026-01-15 16:07:20', 0, '2026-01-15 15:07:20'),
(4, 18, '3c8b92dafe22ffa7dae69b221fa46eecc7045bb7d560641294711bf7287cc6aa', '2026-01-15 16:08:59', 0, '2026-01-15 15:08:59'),
(5, 18, '9e1d6304a7041acf6842e2efddac6abe7f1f360e7b08c0d250a1a95a827cf845', '2026-01-15 16:10:18', 0, '2026-01-15 15:10:18'),
(6, 18, '911f3cb2fcba0da0696de706586af57e7c7a0168cbb391c43ec7f57b0e01e847', '2026-01-15 16:12:21', 0, '2026-01-15 15:12:21'),
(7, 18, '4bb271e5cbe53a4bbd5891bd3dcdb87160c16f32b5215422643d6d6f8bb1f148', '2026-01-15 16:14:35', 1, '2026-01-15 15:14:35');

-- --------------------------------------------------------

--
-- Table structure for table `response`
--

CREATE TABLE `response` (
  `response_ID` varchar(10) NOT NULL,
  `element_ID` varchar(10) NOT NULL,
  `survey_ID` varchar(50) DEFAULT NULL,
  `se_ID` varchar(10) DEFAULT NULL,
  `user_ID` int(11) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `input_at` date DEFAULT NULL,
  `updated_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `response`
--

INSERT INTO `response` (`response_ID`, `element_ID`, `survey_ID`, `se_ID`, `user_ID`, `score`, `input_at`, `updated_at`) VALUES
('RS001', 'AE001', 'SV001', 'ASA002', 10, 2, '2026-01-09', '2026-01-09'),
('RS002', 'AE002', 'SV001', 'ASA007', 10, 2, '2026-01-09', '2026-01-09'),
('RS003', 'AE003', 'SV001', 'ASA012', 10, 2, '2026-01-09', '2026-01-09'),
('RS004', 'AE056', 'SV001', 'ASA274', 10, 2, '2026-01-09', '2026-01-09'),
('RS005', 'AE004', 'SV001', 'ASA017', 10, 2, '2026-01-09', '2026-01-09'),
('RS006', 'AE005', 'SV001', 'ASA022', 10, 2, '2026-01-09', '2026-01-09'),
('RS007', 'AE006', 'SV001', 'ASA027', 10, 2, '2026-01-09', '2026-01-09'),
('RS008', 'AE007', 'SV001', 'ASA032', 10, 2, '2026-01-09', '2026-01-09'),
('RS009', 'AE008', 'SV001', 'ASA037', 10, 2, '2026-01-09', '2026-01-09'),
('RS010', 'AE009', 'SV001', 'ASA042', 10, 2, '2026-01-09', '2026-01-09'),
('RS011', 'AE010', 'SV001', 'ASA047', 10, 2, '2026-01-09', '2026-01-09'),
('RS012', 'AE011', 'SV001', 'ASA053', 10, 3, '2026-01-09', '2026-01-09'),
('RS013', 'AE012', 'SV001', 'ASA058', 10, 3, '2026-01-09', '2026-01-09'),
('RS014', 'AE013', 'SV001', 'ASA063', 10, 3, '2026-01-09', '2026-01-09'),
('RS015', 'AE014', 'SV001', 'ASA068', 10, 3, '2026-01-09', '2026-01-09'),
('RS016', 'AE001', 'SV001', 'ASA003', 12, 3, '2026-01-09', '2026-01-09'),
('RS017', 'AE002', 'SV001', 'ASA007', 12, 2, '2026-01-09', '2026-01-09'),
('RS018', 'AE003', 'SV001', 'ASA013', 12, 3, '2026-01-09', '2026-01-09'),
('RS019', 'AE056', 'SV001', 'ASA276', 12, 4, '2026-01-09', '2026-01-09'),
('RS020', 'AE004', 'SV001', 'ASA018', 12, 3, '2026-01-09', '2026-01-09'),
('RS021', 'AE005', 'SV001', 'ASA024', 12, 4, '2026-01-09', '2026-01-09'),
('RS022', 'AE006', 'SV001', 'ASA027', 12, 2, '2026-01-09', '2026-01-09'),
('RS023', 'AE007', 'SV001', 'ASA033', 12, 3, '2026-01-09', '2026-01-09'),
('RS024', 'AE008', 'SV001', 'ASA037', 12, 2, '2026-01-09', '2026-01-09'),
('RS025', 'AE009', 'SV001', 'ASA043', 12, 3, '2026-01-09', '2026-01-09'),
('RS026', 'AE010', 'SV001', 'ASA048', 12, 3, '2026-01-09', '2026-01-09'),
('RS027', 'AE011', 'SV001', 'ASA052', 12, 2, '2026-01-09', '2026-01-09'),
('RS028', 'AE012', 'SV001', 'ASA057', 12, 2, '2026-01-09', '2026-01-09'),
('RS029', 'AE013', 'SV001', 'ASA063', 12, 3, '2026-01-09', '2026-01-09'),
('RS030', 'AE014', 'SV001', 'ASA068', 12, 3, '2026-01-09', '2026-01-09'),
('RS031', 'AE001', 'SV001', 'ASA001', 14, 1, '2026-01-09', '2026-01-09'),
('RS032', 'AE002', 'SV001', 'ASA007', 14, 2, '2026-01-09', '2026-01-09'),
('RS033', 'AE003', 'SV001', 'ASA012', 14, 2, '2026-01-09', '2026-01-09'),
('RS034', 'AE056', 'SV001', 'ASA273', 14, 1, '2026-01-09', '2026-01-09'),
('RS035', 'AE004', 'SV001', 'ASA018', 14, 3, '2026-01-09', '2026-01-09'),
('RS036', 'AE005', 'SV001', 'ASA022', 14, 2, '2026-01-09', '2026-01-09'),
('RS037', 'AE006', 'SV001', 'ASA027', 14, 2, '2026-01-09', '2026-01-09'),
('RS038', 'AE007', 'SV001', 'ASA033', 14, 3, '2026-01-09', '2026-01-09'),
('RS039', 'AE008', 'SV001', 'ASA037', 14, 2, '2026-01-09', '2026-01-09'),
('RS040', 'AE009', 'SV001', 'ASA042', 14, 2, '2026-01-09', '2026-01-09'),
('RS041', 'AE010', 'SV001', 'ASA048', 14, 3, '2026-01-09', '2026-01-09'),
('RS042', 'AE011', 'SV001', 'ASA051', 14, 1, '2026-01-09', '2026-01-09'),
('RS043', 'AE012', 'SV001', 'ASA057', 14, 2, '2026-01-09', '2026-01-09'),
('RS044', 'AE013', 'SV001', 'ASA063', 14, 3, '2026-01-09', '2026-01-09'),
('RS045', 'AE014', 'SV001', 'ASA067', 14, 2, '2026-01-09', '2026-01-09');

--
-- Triggers `response`
--
DELIMITER $$
CREATE TRIGGER `trg_response_id` BEFORE INSERT ON `response` FOR EACH ROW BEGIN
    DECLARE next_id INT;

    SELECT IFNULL(MAX(CAST(SUBSTRING(response_ID, 3) AS UNSIGNED)), 0) + 1
    INTO next_id
    FROM response;

    SET NEW.response_ID = CONCAT('RS', LPAD(next_id, 3, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `result_criteria`
--

CREATE TABLE `result_criteria` (
  `id` int(11) NOT NULL,
  `criteria_ID` varchar(10) DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `result_domain`
--

CREATE TABLE `result_domain` (
  `rd_ID` varchar(10) NOT NULL,
  `domain_ID` varchar(10) NOT NULL,
  `domain_score_level` int(11) DEFAULT NULL,
  `last_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `score` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `role_ID` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_ID`, `role_name`, `created_at`) VALUES
(1, 'admin', '2025-10-29'),
(2, 'user', '2025-10-29');

-- --------------------------------------------------------

--
-- Table structure for table `score`
--

CREATE TABLE `score` (
  `score_ID` varchar(10) NOT NULL,
  `score_level` int(11) DEFAULT NULL,
  `desc_level` varchar(50) DEFAULT NULL,
  `input_id` varchar(10) DEFAULT NULL,
  `input_at` date DEFAULT NULL,
  `updated_id` varchar(10) DEFAULT NULL,
  `updated_at` date DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `score`
--

INSERT INTO `score` (`score_ID`, `score_level`, `desc_level`, `input_id`, `input_at`, `updated_id`, `updated_at`, `status`) VALUES
('AS001', 1, 'Permulaan', NULL, NULL, '4', '2025-12-23', 'Active'),
('AS002', 2, 'Terlaksana', NULL, NULL, '4', '2025-12-23', 'Active'),
('AS003', 3, 'Tertakrif', NULL, NULL, '4', '2025-12-23', 'Active'),
('AS004', 4, 'Terurus', NULL, NULL, '4', '2025-12-23', 'Active'),
('AS005', 5, 'Teroptimum', NULL, NULL, '4', '2025-12-23', 'Active'),
('AS006', 2, 'Done', '4', '2026-01-20', '4', '2026-01-20', 'Inactive');

--
-- Triggers `score`
--
DELIMITER $$
CREATE TRIGGER `trg_score_ID` BEFORE INSERT ON `score` FOR EACH ROW BEGIN
	DECLARE next_id INT;
	
	SELECT IFNULL(MAX(CAST(SUBSTRING(score_ID, 3) AS UNSIGNED)), 0) +1
	INTO next_id
	FROM score;
	SET NEW.score_ID = CONCAT('AS', LPAD(next_id, 3, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `score_element`
--

CREATE TABLE `score_element` (
  `se_ID` varchar(10) NOT NULL,
  `element_ID` varchar(10) DEFAULT NULL,
  `score_ID` varchar(10) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `input_id` varchar(10) DEFAULT NULL,
  `input_at` date DEFAULT NULL,
  `updated_id` varchar(10) DEFAULT NULL,
  `updated_at` date DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `score_element`
--

INSERT INTO `score_element` (`se_ID`, `element_ID`, `score_ID`, `details`, `input_id`, `input_at`, `updated_id`, `updated_at`, `status`) VALUES
('ASA001', 'AE001', 'AS001', '•	Pembangunan dan pelaksanaan tadbir urus keselamatan siber adalah ad hoc; dan\r\n•	Pembangunan dan pelaksanaan tadbir urus keselamatan siber tidak dilaksanakan secara sistematik; dan\r\n•	Tiada dokumen bagi pembangunan dan pelaksanaan tadbir urus keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA002', 'AE001', 'AS002', '•	Tadbir urus keselamatan siber asas ada tetapi tidak dilaksanakan secara sistematik; dan\r\n•	Tadbir urus keselamatan siber asas tidak konsisten; dan\r\n•	Pembangunan dan pelaksanaan tadbir urus keselamatan siber tidak didokumenkan dengan baik.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA003', 'AE001', 'AS003', '•	Telah lengkap Tahap 2; dan\r\n•	Tadbir urus keselamatan siber dibangunkan dan didokumenkan sepenuhnya; dan\r\n•	Pemantauan, semakan dan kemas kini yang minima.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA004', 'AE001', 'AS004', '•	Telah lengkap Tahap 3; dan\r\n•	Tadbir urus keselamatan siber diurus secara sistematik dengan pemantauan, semakan dan kemas kini yang kerap; dan\r\n•	Terdapat mekanisme formal untuk memastikan pematuhan dan keberkesanan merentas organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA005', 'AE001', 'AS005', '•	Telah lengkap Tahap 4; dan\r\n•	Tadbir urus keselamatan siber dioptimumkan secara berterusan melalui penilaian dan penambahbaikan yang berterusan; dan\r\n•	Tadbir urus keselamatan siber terus dipertingkatkan dan diperhalusi berdasarkan maklum balas, metrik prestasi dan  warga dan pihak yang berkepentingan ancaman yang muncul.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA006', 'AE002', 'AS001', '•	Tiada tanggungjawab atau terma tadbir urus yang ditakrifkan dengan jelas; dan \r\n•	Tiada dokumen yang formal disediakan bagi tanggungjawab atau terma tadbir urus; dan\r\n•	Warga organisasi dan pihak berkepentingan tidak mengetahui peranan keselamatan siber mereka.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA007', 'AE002', 'AS002', '•	Sesetengah tanggungjawab dan terma tadbir urus ditakrifkan tetapi tidak lengkap dan tidak digunakan secara konsisten di seluruh organisasi; dan\r\n•	Dokumen tanggungjawab atau terma tadbir urus yang minima; dan\r\n•	Peranan dan tanggungjawab asas tidak formal atau disampaikan sepenuhnya kepada warga.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA008', 'AE002', 'AS003', '•	Telah lengkap Tahap 2; dan\r\n•	Ia diseragamkan dan digunakan secara konsisten di seluruh organisasi; dan\r\n•	Tanggungjawab dan syarat tadbir urus dibangunkan sepenuhnya, didokumenkan dan disampaikan; dan\r\n•	Semua pihak berkepentingan yang berkaitan sedar akan peranan dan tanggungjawab mereka.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA009', 'AE002', 'AS004', '•	Telah lengkap Tahap 3; dan\r\n•	Tanggungjawab dan syarat tadbir urus ditakrifkan, diurus secara aktif dan dipantau; dan\r\n•	Tanggungjawab dan terma tadbir urus ditakrifkan dengan jelas, didokumenkan dan diuruskan melalui proses formal; dan\r\n•	Terdapat proses untuk memastikan pematuhan dan akauntabiliti.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA010', 'AE002', 'AS005', '•	Telah lengkap Tahap 4; dan\r\n•	Tanggungjawab dan tadbir urus terus bertambah baik berdasarkan maklum balas warga dan pihak yang berkepentingan mengikut amalan terbaik semasa;  dan\r\n•	Tanggungjawab dan syarat tadbir urus terus dioptimumkan melalui penilaian dan penambahbaikan berterusan; dan\r\n•	Pendekatan proaktif diperhalusi dalam mengoptimumkan takrifan ini; dan\r\n•	Strategi penyesuaian memastikan ia kekal relevan dan berkesan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA011', 'AE003', 'AS001', '•	Tiada strategi keselamatan siber dibangunkan sejajar dengan strategi organisasi; dan\r\n•	Strategi keselamatan siber tidak wujud atau terputus sepenuhnya daripada matlamat dan objektif organisasi; dan\r\n•	Usaha keselamatan siber adalah ad hoc dan tidak diselaraskan dengan matlamat organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA012', 'AE003', 'AS002', '•	Hanya sebahagian strategi keselamatan siber sejajar dengan strategi organisasi; dan\r\n•	Strategi keselamatan siber tidak secara menyeluruh dan selaras dengan objektif organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA013', 'AE003', 'AS003', '•	Telah lengkap Tahap 2; dan\r\n•	Strategi keselamatan siber dibangunkan selaras dengan strategi organisasi, dengan dokumentasi yang jelas dan proses standard memastikan penjajaran; dan \r\n•	Terdapat pendekatan yang didokumenkan dan diseragamkan yang memastikan strategi keselamatan siber menyokong matlamat dan objektif organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA014', 'AE003', 'AS004', '•	Telah lengkap Tahap 3; dan\r\n•	Strategi keselamatan siber bukan sahaja sejajar dengan strategi organisasi tetapi juga diurus dan dipantau secara aktif untuk memastikan penjajaran berterusan; dan\r\n•	Strategi keselamatan siber diurus secara sistematik dan disemak secara berkala dalam memastikan ia kekal sejajar dengan strategi organisasi; dan\r\n•	Proses formal disediakan bagi penyesuaian perubahan dalam objektif organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA015', 'AE003', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Strategi keselamatan siber dioptimumkan secara berterusan untuk kekal sejajar sepenuhnya dengan strategi organisasi yang sedang berkembang; dan\r\n• Terdapat pendekatan proaktif dalam meningkatkan strategi berdasarkan maklum balas warga, pihak ketiga dan pihak yang berkepentingan, metrik prestasi dan amalan terbaik yang muncul; dan\r\n• Penilaian dan penambahbaikan berterusan memastikan usaha keselamatan siber menyokong matlamat organisasi yang berkembang dengan berkesan', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA016', 'AE004', 'AS001', '• Tiada komitmen formal daripada pengurusan atasan terhadap keselamatan siber; dan\r\n• Polisi dan program keselamatan siber tidak wujud atau wujud secara minimum; dan\r\n• Tindakan keselamatan siber dilakukan secara reaktif tanpa perancangan strategik; dan\r\n• Kakitangan dan pengurusan tidak diberi latihan khusus dalam bidang keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA017', 'AE004', 'AS002', '• Pengurusan atasan mula menunjukkan minat terhadap keselamatan siber, tetapi komitmen mereka tidak sepenuhnya formal; dan\r\n• Program keselamatan siber mula diwujudkan, walaupun masih tidak komprehensif; dan\r\n• Polisi siber dan garis panduan mula disediakan, tetapi belum disemak secara berkala; dan\r\n• Beberapa sumber diperuntukkan untuk program keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA018', 'AE004', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Pengurusan atasan memberi komitmen yang jelas melalui pelaburan dalam keselamatan siber dan penyertaan aktif dalam perancangan strategik; dan\r\n• Polisi keselamatan siber telah diwujudkan dan dikemas kini secara berkala; dan\r\n• Program keselamatan siber menjadi sebahagian daripada budaya organisasi dan melibatkan penyertaan semua peringkat pekerja; dan\r\n• Latihan berkala dijalankan untuk memastikan pemahaman dan pematuhan polisi keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA019', 'AE004', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pengurusan atasan secara aktif mengawasi, mengukur, dan menilai kecekapan program keselamatan siber; dan\r\n• Komitmen jelas melalui pembiayaan mencukupi dan pelaksanaan inisiatif keselamatan siber yang inovatif; dan\r\n• Polisi keselamatan siber ditakrifkan secara kuantitatif dengan ukuran prestasi yang jelas; dan\r\n• Laporan dan audit keselamatan dilakukan secara berkala untuk menilai pencapaian matlamat keselamatan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA020', 'AE004', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Pengurusan atasan menunjukkan kepimpinan yang aktif dan berterusan dalam pembangunan serta inovasi polisi dan program keselamatan siber; dan\r\n• Komitmen pengurusan bukan sahaja kepada pematuhan, tetapi juga kepada inovasi berterusan dalam strategi keselamatan siber untuk menyesuaikan diri dengan ancaman siber yang berubah-ubah; dan\r\n• Penyemakan polisi dan program keselamatan siber dilakukan secara berkala dan dengan strategi masa depan yang proaktif; dan\r\n• Keselamatan siber menjadi agenda utama pengurusan dan budaya organisasi di seluruh peringkat, disokong oleh penggunaan teknologi dan amalan terbaik terkini..', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA021', 'AE005', 'AS001', '• Pengurusan atasan tidak memberi perhatian atau menyediakan peruntukan kewangan yang khusus untuk sokongan keselamatan siber atau projek lain; dan\r\n• Tiada perancangan sistematik bagi sumber manusia atau kewangan untuk inisiatif yang penting; dan\r\n• Sumber yang diperuntukkan adalah terhad dan tidak memenuhi keperluan asas organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA022', 'AE005', 'AS002', '• Pengurusan atasan mula menyediakan sumber kewangan dan manusia, tetapi sokongan ini tidak berstruktur atau mencukupi; dan\r\n• Beberapa bajet diperuntukkan untuk projek tertentu, tetapi ia hanya bersifat reaktif, misalnya selepas insiden atau apabila diperlukan segera; dan\r\n• Terdapat kesedaran mengenai keperluan tambahan, namun peruntukan masih belum dioptimumkan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA023', 'AE005', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Pengurusan atasan secara aktif menyokong penyediaan sumber kewangan dan sumber manusia berdasarkan rancangan yang jelas; dan\r\n• Bajet dan sumber diperuntukkan secara formal untuk menyokong inisiatif keselamatan dan pembangunan organisasi; dan\r\n• Pengurusan memastikan bahawa setiap jabatan atau projek menerima sumber yang sewajarnya, dengan penekanan kepada kecekapan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA024', 'AE005', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pengurusan atasan mengukur dan menilai keperluan sumber kewangan dan sumber manusia secara kuantitatif; dan\r\n• Sokongan sumber disediakan berdasarkan analisis yang teliti dan penyelarasan strategik; dan\r\n• Sumber diperuntukkan dengan berkesan dan diselaraskan dengan keperluan projek jangka panjang organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA025', 'AE005', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Pengurusan atasan menyediakan sokongan sumber kewangan dan manusia yang optimum dengan komitmen tinggi kepada keperluan semasa dan masa depan; dan\r\n• Peruntukan kewangan dan sumber manusia tidak sahaja mencukupi, tetapi juga dioptimumkan untuk mencapai inovasi dan peningkatan berterusan dalam semua bidang; dan\r\n• Sokongan sumber adalah bersepadu dengan strategi organisasi jangka panjang dan disesuaikan dengan perubahan keperluan serta teknologi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA026', 'AE006', 'AS001', '• Pengurusan atasan tidak mempunyai atau kurang memberikan komitmen untuk memastikan organisasi, pihak ketiga dan pihak yang berkepentingan memahami keselamatan siber; dan\r\n• Program kesedaran dan latihan yang minima dan tidak mencukupi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA027', 'AE006', 'AS002', '• Pengurusan atasan menunjukkan sedikit kesedaran tentang keperluan untuk pemahaman keselamatan siber, tetapi komitmen boleh menjadi lebih pelbagai dan konsisten; dan\r\n• Beberapa latihan asas atau usaha kesedaran wujud tetapi perlu distrukturkan dengan lebih baik.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA028', 'AE006', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Komitmen pengurusan atasan terhadap kesedaran keselamatan siber diwujudkan dan didokumenkan; dan\r\n• Terdapat program latihan dan kesedaran yang jelas dan berjadual dengan baik untuk memastikan warga organisasi memahami prinsip dan amalan keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA029', 'AE006', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Komitmen pengurusan atasan terhadap kesedaran keselamatan siber diurus dan dipantau secara aktif; dan\r\n• Pengurusan atasan menguruskan inisiatif kesedaran keselamatan siber secara sistematik; dan\r\n• Penilaian dan kemas kini yang kerap memastikan program latihan berkesan dan warga organisasi dididik secara berterusan tentang amalan keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA030', 'AE006', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Komitmen pengurusan atasan terhadap kesedaran keselamatan siber terus dioptimumkan; dan\r\n• Program latihan dan kesedaran dipertingkatkan secara proaktif berdasarkan maklum balas serta amalan terbaik dan terkini untuk memastikan ia berkesan dengan cabaran keselamatan siber semasa.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA031', 'AE007', 'AS001', '• Dasar dan polisi tidak jelas atau tidak wujud secara formal untuk menetapkan tanggungjawab dalam organisasi; dan\r\n• Tanggungjawab antara organisasi, kakitangan, pihak ketiga, dan pihak berkepentingan tidak ditakrifkan dengan baik; dan\r\n• Kebergantungan tinggi kepada penyelesaian masalah yang bersifat ad-hoc dan reaktif.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA032', 'AE007', 'AS002', '• Tanggungjawab asas bagi organisasi dan warga mula ditetapkan, tetapi masih kekurangan definisi formal untuk pihak ketiga dan pihak berkepentingan; dan\r\n• Dasar dan polisi mula diperkenalkan, tetapi masih tidak konsisten atau terperinci; dan\r\n• Terdapat kesedaran tentang keperluan prosedur yang lebih jelas, namun pelaksanaannya masih bersifat terbatas.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA033', 'AE007', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Dasar dan prosedur yang jelas telah diwujudkan untuk semua pihak termasuk organisasi, warga, pihak ketiga, dan pihak berkepentingan; dan\r\n• Tanggungjawab ditakrifkan dengan baik dalam dokumen polisi, memastikan pemahaman dan pematuhan dari semua pihak terlibat; dan\r\n• Terdapat penyelarasan yang konsisten antara pelbagai jabatan atau pihak dalam organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA034', 'AE007', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Dasar, polisi, dan prosedur yang jelas serta diukur secara kuantitatif untuk memastikan tanggungjawab setiap pihak diuruskan dengan berkesan; dan\r\n• Penilaian berkala dilakukan untuk memastikan bahawa setiap pihak mematuhi tanggungjawab yang telah ditetapkan dalam polisi; dan\r\n• Analisis dan laporan dibuat untuk mengukur prestasi berasaskan pematuhan kepada tanggungjawab yang telah didefinisikan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA035', 'AE007', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Polisi dan prosedur yang jelas dan dioptimumkan di mana tanggungjawab antara organisasi, warga, pihak ketiga, dan pihak berkepentingan bukan sahaja ditetapkan, tetapi sentiasa diperbaharui mengikut perubahan dalam peraturan atau amalan terbaik; dan\r\n• Dasar dan tanggungjawab difahami dan disepakati sepenuhnya oleh semua pihak terlibat, dan pengurusan secara aktif mengawal selia kepatuhan; dan\r\n• Inovasi dalam pelaksanaan tanggungjawab diterapkan, memastikan proses sentiasa efisien dan selari dengan matlamat jangka panjang organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA036', 'AE008', 'AS001', '• Dasar/polisi dan prosedur keselamatan siber tidak menggabungkan kawalan yang disyorkan oleh standard antarabangsa dan amalan terbaik; dan\r\n• Tiada dasar/polisi dan prosedur formal yang ditetapkan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA037', 'AE008', 'AS002', '• Beberapa usaha dibuat untuk menggabungkan kawalan daripada standard antarabangsa dan amalan terbaik, tetapi tidak lengkap, digunakan secara tidak konsisten dan tidak didokumenkan dengan baik; dan\r\n• Dasar/polisi dan prosedur berada di peringkat awal pembangunan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA038', 'AE008', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Dasar/polisi dan prosedur keselamatan siber ditakrifkan dan didokumenkan dengan baik; dan\r\n• Menggabungkan semua kawalan berkaitan yang disyorkan oleh standard antarabangsa dan amalan terbaik; dan\r\n• Dasar/polisi dan prosedur ini dibangunkan dan diselenggara secara sistematik.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA039', 'AE008', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Penggabungan kawalan yang disyorkan oleh standard antarabangsa dan amalan terbaik diurus dan dipantau secara aktif; dan\r\n• Dasar/polisi dan prosedur sentiasa disemak dan dikemas kini untuk memastikan ia kekal komprehensif dan berkesan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA040', 'AE008', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Proses membangunkan dasar/polisi dan prosedur keselamatan siber dioptimumkan secara berterusan; dan\r\n• Terdapat pendekatan proaktif untuk menambah baik dan dasar/polisi diperhalusi berdasarkan maklum balas warga dan pihak yang berkepentingan, metrik prestasi dan amalan terbaik yang muncul; dan\r\n• Kawalan sentiasa disemak dan dipertingkatkan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA041', 'AE009', 'AS001', '• Penyebaran dasar/polisi keselamatan siber sangat terhad, jika ada; dan\r\n• Tiada strategi formal untuk memastikan warga organisasi, pihak ketiga, atau pihak berkepentingan memahami prosedur keselamatan siber; dan\r\n• Sebarang komunikasi yang berlaku adalah tidak formal, tidak didokumentasikan, dan bersifat ad-hoc.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA042', 'AE009', 'AS002', '• Penyebaran dasar/polisi keselamatan siber mula dilakukan, tetapi pelaksanaannya tidak konsisten; dan\r\n• Beberapa warga dan pihak berkepentingan diberikan akses kepada dasar ini, tetapi latihan dan panduan formal masih terhad; dan\r\n• Prosedur keselamatan siber didokumenkan, tetapi penyebaran kepada pihak ketiga masih tidak menyeluruh.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA043', 'AE009', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Polisi dan prosedur keselamatan siber telah ditakrif dan dikomunikasikan secara jelas kepada semua pihak, termasuk warga organisasi, pihak ketiga, dan pihak berkepentingan; dan\r\n• Sesi latihan dan bengkel formal telah diadakan untuk memastikan semua pihak memahami dasar dan prosedur; dan\r\n• Saluran komunikasi yang formal dan sistematik telah diwujudkan untuk menyebarkan maklumat berkaitan keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA044', 'AE009', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Penyebaran dasar/polisi keselamatan siber dilakukan secara kuantitatif dan diawasi untuk memastikan semua pihak memahami dan mematuhi prosedur; dan\r\n• Mekanisme penilaian dan audit digunakan untuk mengukur tahap pemahaman warga dan pihak ketiga terhadap polisi yang disebarkan; dan\r\n• Proses penyebaran dikawal selia dengan analisis prestasi berkala.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA045', 'AE009', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Penyebaran dasar dan prosedur keselamatan siber adalah sangat berstruktur dan berkesan, dengan penggunaan teknologi terkini untuk memastikan semua pihak mendapat akses tepat waktu dan berterusan kepada polisi ini; dan\r\n• Sesi latihan dan komunikasi keselamatan siber sentiasa diperbaharui dan disesuaikan dengan ancaman serta teknologi semasa; dan\r\n• Semua warga, pihak ketiga, dan pihak berkepentingan bukan sahaja memahami, tetapi juga mematuhi dan mengamalkan dasar keselamatan siber sebagai sebahagian daripada budaya organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA046', 'AE010', 'AS001', '• Semakan sama ada tidak dijalankan sama sekali atau berlaku secara proaktif tanpa proses yang ditetapkan; dan\r\n• Tiada mekanisme formal untuk memastikan dasar atau amalan disemak secara berkala atau sebagai tindak balas kepada keperluan semasa.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA047', 'AE010', 'AS002', '• Semakan dijalankan sekali-sekala; dan\r\n• Semakan dijalankan tidak teratur dan tidak berdasarkan jadual;dan\r\n• Tiada jadual rasmi dan semakan berlaku sebagai tindak balas kepada isu tertentu dan bukannya secara proaktif.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA048', 'AE010', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Jadual dan proses rasmi untuk semakan berkala diwujudkan dan didokumenkan; dan\r\n• Semakan dijalankan secara berkala mengikut jadual; dan\r\n• Terdapat prosedur bagi tindak balas kepada keperluan semasa juga.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA049', 'AE010', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Proses semakan diurus dan dipantau secara aktif; dan\r\n• Semakan dijalankan secara sistematik serta berkala.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA050', 'AE010', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Proses semakan dioptimumkan secara berterusan; dan\r\n• Penambahbaikan berterusan dibuat berdasarkan maklum balas warga dan pihak yang berkepentingan mengikut amalan terbaik; dan \r\n• Penambahbaikan dilakukan  secara dinamik mengikut keperluan dan keadaan ancaman siber yang berubah-ubah.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA051', 'AE011', 'AS001', '• Tiada pendekatan sistematik atau formal untuk menilai risiko keselamatan siber; dan\r\n• Penilaian risiko dijalankan secara ad-hoc, hanya selepas insiden berlaku atau apabila diperlukan; dan\r\n• Organisasi tidak mempunyai strategi keselamatan yang jelas yang diselaraskan dengan matlamat dan tujuan organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA052', 'AE011', 'AS002', '• Keperluan kepentingan Terdapat kesedaran terhadap keperluan untuk penilaian risiko keselamatan siber, dan beberapa langkah telah dilaksanakan; dan\r\n• Penilaian risiko mula dilaksanakan, tetapi tidak secara konsisten di seluruh organisasi atau tidak sepenuhnya disesuaikan dengan matlamat strategik; dan\r\n• Organisasi mula menggunakan alat asas atau kerangka umum untuk menilai risiko, tetapi ia masih tidak berkait rapat dengan objektif organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA053', 'AE011', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Penilaian risiko keselamatan siber telah dijalankan secara formal dan didokumenkan, dengan dasar dan prosedur yang jelas; dan\r\n• Penilaian risiko diselaraskan dengan objektif organisasi  dan dilaksanakan di seluruh organisasi, melibatkan semua jabatan yang berkaitan; dan\r\n• Terdapat penggunaan kerangka penilaian risiko yang lebih mantap seperti ISO 27001 atau NIST, dan risiko dinilai berdasarkan impaknya terhadap tujuan organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA054', 'AE011', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Proses penilaian risiko keselamatan siber diurus dan diukur secara kuantitatif, dengan analisis mendalam mengenai potensi ancaman dan kelemahan berdasarkan matlamat strategik organisasi; dan\r\n• Penilaian risiko dilakukan secara berkala dan hasilnya dianalisis untuk memastikan bahawa mitigasi risiko adalah selari dengan strategi organisasi; dan\r\n• Keputusan dibuat berdasarkan metrik yang jelas dan pengurusan risiko disokong oleh data dan analitik yang terperinci.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA055', 'AE011', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Penilaian risiko keselamatan siber adalah bersepadu sepenuhnya dengan objektif strategik dan matlamat jangka panjang organisasi; dan\r\n• Organisasi sentiasa memperbaiki proses penilaian risiko berdasarkan maklum balas dan perubahan dalam persekitaran ancaman siber; dan\r\n• Strategi mitigasi risiko adalah proaktif dan inovatif, menggunakan teknologi canggih serta pendekatan analisis ramalan untuk menjangka ancaman yang akan datang dan menyesuaikan langkah pencegahan mengikut keperluan organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA056', 'AE012', 'AS001', '• Tiada keperluan mengikut keutamaan keselamatan siber berdasarkan penilaian risiko; dan\r\n• Keutamaan permohonan keperluan dilakukan secara ad hoc tidak konsisten; dan\r\n• Langkah keselamatan siber dilaksanakan secara rawak atau berdasarkan kebimbangan; dan\r\n• Kurang kesedaran tentang keperluan mengikut keutamaan keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA057', 'AE012', 'AS002', '• Sedikit kesedaran tentang keperluan mengikut keutamaan keselamatan siber berdasarkan risiko; dan\r\n• Proses dilakukan tidak formal; dan\r\n•Tindakan adalah reaktif dan bukannya proaktif, serta bergantung pada kebimbangan individu.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA058', 'AE012', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Organisasi membuat keperluan mengikut keutamaan berdasarkan penilaian risiko secara formal; dan\r\n• Prosedur keperluan mengikut keutamaan keselamatan siber diwujudkan dan didokumenkan; dan\r\n• Dokumentasi yang jelas dan pelaksanaan yang konsisten; dan\r\n• Permohonan yang konsisten dan bersistematik dalam membuat keutamaan bagi proses penyediaan keperluan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA059', 'AE012', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pemantauan dan semakan berkala terhadap proses keperluan mengikut keutamaan; dan\r\n• Organisasi mempunyai proses pengurusan pengawasan dalam membuat keberkesanan pemantauan bagi keperluan mengikut keutamaan; dan\r\n• Penggunaan metrik dan KPI untuk mengukur keberkesanan; dan\r\n• Pematuhan berterusan kepada proses dengan pengawasan dan pelarasan berdasarkan data prestasi; dan\r\n• Proses ini diselarikan dalam strategi pengurusan risiko organisasi yang lebih menyeluruh.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA060', 'AE012', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Penambahbaikan berterusan proses penyediaan keperluan melalui inovasi dan maklum balas warga dan pihak yang berkepentingan; dan\r\n• Pelarasan proses penyediaan keperluan mengikut keutamaan dalam membuat keputusan strategik; dan\r\n• Penyesuaian proaktif kepada ancaman dan perubahan yang muncul dalam landskap keselamatan siber; dan\r\n• Penggunaan kaedah dan metodologi terkini dalam meningkatkan proses keutamaan keputusan bagi penyediaan keperluan keselamatan siber.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA061', 'AE013', 'AS001', '• Tiada proses atau garis panduan formal disediakan; dan\r\n• Aktiviti penyemakan adalah secara ad hoc;\r\n• Tiada dokumentasi atau laporan rasmi; dan\r\n• Kesedaran terhad di kalangan warga organisasi dan pihak berkepentingan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA062', 'AE013', 'AS002', '• Proses asas untuk memantau pelan rawatan risiko adalah tidak seragam atau secara konsisten; dan\r\n• Langkah awal untuk melaporkan dan mendokumentasi penemuan; dan\r\n• Pihak yang berkaitan dimaklumkan atas dasar mengetahui; dan\r\n• Penglibatan aktif semua pihak yang berkaitan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA063', 'AE013', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Proses dan prosedur formal untuk memantau keberkesanan rawatan risiko diwujudkan dan didokumenkan; dan\r\n• Prosedur semakan dan penyemakan yang standard; dan\r\n• Dokumentasi dan pelaporan tetap; dan\r\n• Peranan dan tanggungjawab ditetapkan bagi senua pihak yang berkaitan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA064', 'AE013', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Proses penyemakan diurus dengan baik dan disepadukan ke dalam rangka kerja pengurusan risiko keseluruhan organisasi; dan\r\n• Pemakaian prosedur penyemakan yang konsisten; dan\r\n• Semakan tetap dan kemas kini kepada pelan rawatan risiko berdasarkan penemuan; dan \r\n• Penglibatan aktif semua pihak yang berkaitan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA065', 'AE013', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Proses penyemakan terus diperbaiki dan dioptimumkan untuk keberkesanan maksimum; dan\r\n• Penggunaan alat dan instrumen canggih bagi analisis dan pemantauan; dan\r\n• Maklum balas berterusan untuk menambah baik pelan rawatan risiko; dan\r\n• Pengenalpastian proaktif potensi penambahbaikan dan inovasi dalam amalan pengurusan risiko; dan \r\n• Pelaporan dan komunikasi yang komprehensif merentasi semua peringkat dalam organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA066', 'AE014', 'AS001', '• Pemantauan  tidak dijalankan;\r\n• Pemantauan  berlaku secara sesekali tanpa sebarang proses yang ditetapkan; dan\r\n• Tiada mekanisme untuk memastikan pemantauan  berdasarkan keperluan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA067', 'AE014', 'AS002', '• Pemantauan  dijalankan sekali-sekala tetapi tidak teratur dan tidak berdasarkan jadual rasmi; dan\r\n• Proses pemantauan  adalah reaktif dan bukannya proaktif, berdasarkan kepada tindak balas isu khusus dan bukannya sebagai sebahagian daripada rancangan berstruktur.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA068', 'AE014', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Jadual dan proses rasmi untuk pemantauan  diwujudkan dan didokumenkan; dan\r\n• Pemantauan  dijalankan mengikut jadual ini; dan\r\n• Terdapat prosedur untuk tindak balas kepada keperluan semasa.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA069', 'AE014', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Proses pemantauan  diurus dan dipantau secara aktif; dan\r\n• Semakan dijalankan secara sistematik secara berkala dan sebagai tindak balas kepada keperluan semasa, dengan penemuan digunakan untuk membuat pelarasan tepat pada masanya.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA070', 'AE014', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Proses Pemantauan  dioptimumkan secara berterusan; dan\r\n• Terdapat pendekatan proaktif bagi menambah baik proses pemantauan  berdasarkan maklum balas warga dan pihak yang berkepentingan, metrik prestasi dan amalan terbaik; dan \r\n• Penambahbaikan dibuat berdasarkan maklum balas warga dan pihak yang berkepentingan dan amalan terbaik; dan\r\n• Penambahbaikan dilakukan  secara dinamik mengikut keperluan dan keadaan ancaman siber yang berubah-ubah.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA071', 'AE015', 'AS001', '• Tiada dasar atau prosedur formal yang ditetapkan untuk mematuhi undang-undang, peraturan, atau standard keselamatan siber; dan\r\n• Organisasi tidak sedar atau memahami sepenuhnya keperluan undang-undang dan peraturan yang berkaitan dengan keselamatan siber; dan\r\n• Amalan keselamatan siber berlaku secara ad-hoc, tanpa rujukan kepada amalan terbaik industri atau keperluan perundangan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA072', 'AE015', 'AS002', '• Organisasi mula memahami keperluan undang-undang peraturan, dan standard keselamatan siber; dan\r\n• Beberapa dasar dan prosedur keselamatan telah diperkenalkan untuk mematuhi keperluan minimum, tetapi tidak sepenuhnya disesuaikan dengan amalan terbaik; dan\r\n• Terdapat usaha untuk memenuhi kepatuhan secara asas, tetapi implementasi tidak konsisten di seluruh organisasi.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA073', 'AE015', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Organisasi mempunyai dasar dan prosedur keselamatan siber yang jelas dan didokumenkan, sejajar dengan keperluan undang-undang, peraturan, dan standard yang terpakai seperti ISO 27001, NIST, atau RAKKSA; dan\r\n• Terdapat penekanan pada pematuhan sepenuhnya terhadap undang-undang dan peraturan yang relevan, dengan polisi yang ditakrifkan dengan baik untuk memastikan keselamatan siber dipelihara; dan\r\n• Proses audit dalaman dan pemeriksaan berkala mula diperkenalkan untuk memastikan pematuhan berterusan.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA074', 'AE015', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Dasar dan prosedur keselamatan siber bukan sahaja mematuhi undang-undang dan peraturan tetapi juga diukur secara kuantitatif untuk menilai keberkesanan pematuhan; dan\r\n• Organisasi melaksanakan amalan terbaik dan standard keselamatan siber antarabangsa, dengan penggunaan metrik dan pengukuran yang jelas untuk menilai kepatuhan dan keberkesanan dasar; dan\r\n• Penilaian risiko dilakukan secara berkala untuk memastikan organisasi terus sejajar dengan peraturan keselamatan siber yang berubah-ubah.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA075', 'AE015', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Organisasi sentiasa memperbaharui dan mengoptimumkan dasar dan prosedur keselamatan siber agar selaras dengan perubahan undang-undang, peraturan, dan amalan terbaik dalam industri; dan\r\n• Dasar keselamatan siber organisasi adalah fleksibel dan proaktif, memastikan pematuhan bukan sahaja kepada undang-undang semasa tetapi juga dapat menjangka perubahan dalam peraturan masa hadapan; dan\r\n• Keselamatan siber menjadi sebahagian daripada budaya organisasi dengan komitmen sepenuhnya daripada pengurusan dan pekerja untuk memenuhi atau melampaui standard  perundangan dan industri.', NULL, NULL, '4', '2025-12-23', 'Active'),
('ASA091', 'AE019', 'AS001', '• Kesedaran dan latihan keselamatan siber tidak ada atau dilaksanakan secara minimum dan tidak formal; dan\r\n• Tiada program latihan keselamatan siber yang tersusun. Kesedaran di kalangan kakitangan tentang keselamatan siber sangat rendah; dan\r\n• Tiada usaha khusus untuk mendidik atau melibatkan kakitangan dalam memahami risiko keselamatan siber yang dihadapi oleh organisasi.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA092', 'AE019', 'AS002', '• Program latihan keselamatan siber mula dilaksanakan, tetapi ia masih bersifat asas dan tidak sistematik; dan\r\n• Latihan disediakan secara sekali sekala, melalui modul dalam talian atau bengkel asas, tetapi tidak disesuaikan dengan keperluan khusus kakitangan; dan\r\n• Kesedaran tentang keselamatan siber di kalangan kakitangan mula meningkat, tetapi tidak semua kakitangan terlibat secara aktif.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA093', 'AE019', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Program kesedaran dan latihan keselamatan siber telah ditakrifkan dengan jelas. Kandungan latihan disesuaikan mengikut peranan dan tanggungjawab kakitangan; dan\r\n• Latihan diadakan secara berkala dan melibatkan topik penting seperti pengurusan kata laluan, phishing, dan ancaman siber; dan\r\n• Kesedaran keselamatan siber mula menjadi sebahagian daripada budaya organisasi, dan kakitangan mulai memahami pentingnya keselamatan siber dalam kerja harian mereka.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA094', 'AE019', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Program kesedaran dan latihan  keselamatan siber dilaksanakan secara sistematik dan dipantau untuk memastikan keberkesanannya; dan\r\n• Latihan diberikan kepada semua peringkat organisasi secara berkala dengan pemantauan dan pelaporan terhadap penyertaan serta keberkesanan latihan; dan\r\n• Kakitangan terlatih untuk mengenal pasti dan bertindak balas terhadap ancaman siber dengan betul, dan mereka memahami dasar serta prosedur keselamatan siber organisasi.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA095', 'AE019', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Kesedaran dan latihan siber dioptimumkan dengan pendekatan yang berterusan dan disesuaikan untuk semua kakitangan, menggunakan teknologi moden seperti simulasi serangan dan kajian kes yang interaktif; dan\r\n• Program latihan sentiasa dikemas kini mengikut ancaman terkini dan disesuaikan dengan perubahan dalam peraturan atau standard keselamatan; dan\r\n• Kesedaran keselamatan siber menjadi sebahagian penting daripada strategi keselamatan keseluruhan organisasi. Kakitangan bukan sahaja mematuhi dasar, tetapi juga memainkan peranan aktif dalam mengurangkan risiko siber.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA096', 'AE020', 'AS001', '• Pengasingan tugas tidak dilaksanakan atau dilaksanakan secara minimum; dan\r\n• Kakitangan sering mempunyai akses tanpa sekatan kepada sistem dan data sensitif tanpa mengambil kira peranan atau tanggungjawab mereka; dan\r\n• Tiada dasar formal untuk memisahkan tugas-tugas kritikal, menjadikan organisasi terdedah kepada risiko keselamatan dalaman seperti penyalahgunaan kuasa atau akses yang tidak sah.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA097', 'AE020', 'AS002', '• Pengasingan tugas mula diperkenalkan, tetapi masih belum lengkap atau konsisten di seluruh organisasi; dan\r\n• Beberapa peranan kritikal dalam keselamatan siber sudah dipisahkan, tetapi pemisahan ini tidak selalu dilaksanakan mengikut amalan terbaik; dan\r\n• Terdapat dasar awal untuk membatasi akses kepada sistem tertentu, tetapi belum ada mekanisme yang jelas untuk menguatkuasakan atau memantau pemisahan ini.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA098', 'AE020', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Pengasingan tugas telah ditakrifkan dengan jelas dan didokumentasikan dalam dasar keselamatan siber; dan\r\n• Setiap peranan dan tanggungjawab dalam organisasi mempunyai akses dan kuasa yang sesuai dengan fungsi mereka, mengurangkan risiko konflik kepentingan atau akses yang tidak sah; dan\r\n• Tugas-tugas kritikal, seperti pengesahan transaksi atau pengurusan konfigurasi sistem, diasingkan dengan baik untuk mengelakkan individu yang sama melakukan tugas yang boleh menjejaskan keselamatan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA099', 'AE020', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pengasingan tugas dilaksanakan dengan baik dan dipantau secara berterusan melalui sistem pengurusan keselamatan; dan\r\n• Prosedur dan alat teknologi digunakan untuk memastikan bahawa akses dan kawalan selaras dengan peranan individu, dan sebarang konflik atau pelanggaran pengasingan tugas dapat dikesan serta ditangani dengan segera; dan\r\n• Audit berkala dilakukan untuk menilai keberkesanan pengasingan tugas dan memastikan pematuhan kepada dasar.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA100', 'AE020', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Pengasingan tugas mencapai tahap optimum di mana ia diintegrasikan sepenuhnya dalam strategi keselamatan siber organisasi; dan\r\n• Teknologi automasi dan pemantauan digunakan untuk mengurus akses berdasarkan peranan, dengan pengesanan konflik dan risiko secara real-time; dan\r\n• Pengasingan tugas dioptimumkan melalui analisis risiko berterusan dan penambahbaikan, memastikan bahawa setiap tugas dijalankan oleh individu yang betul dengan tahap akses yang sewajarnya, mengurangkan risiko dalaman dan mempertingkatkan keselamatan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA101', 'AE021', 'AS001', '• Tiada proses formal untuk mengukur keberkesanan program kesedaran dan latihan keselamatan siber; dan\r\n• Latihan keselamatan siber dijalankan, tetapi tiada usaha untuk menilai impak atau keberkesanannya; dan\r\n• Organisasi bergantung pada penyertaan semata-mata tanpa menilai sama ada latihan tersebut benar-benar meningkatkan kesedaran atau pengetahuan kakitangan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA102', 'AE021', 'AS002', '• Pengukuran keberkesanan mula dilaksanakan, tetapi masih terhad kepada metrik asas seperti jumlah penyertaan atau penyelesaian kursus; dan\r\n• Tiada mekanisme yang kukuh untuk mengukur perubahan dalam tingkah laku kakitangan atau peningkatan pengetahuan berkaitan keselamatan siber; dan\r\n• Penilaian keberkesanan bersifat reaktif, hanya dijalankan apabila berlaku insiden keselamatan atau kelemahan yang dikenal pasti dalam audit.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA103', 'AE021', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Proses pengukuran keberkesanan program kesedaran dan latihan keselamatan siber telah ditakrifkan dengan jelas; dan\r\n• Pelbagai metrik digunakan untuk menilai keberkesanan program seperti penilaian pra dan pasca latihan, ujian pengetahuan, dan kadar kejayaan dalam mengesan ancaman seperti ujian phishing; dan\r\n• Data mengenai keberkesanan program dikumpulkan secara berkala dan dilaporkan kepada pihak pengurusan, dengan fokus pada peningkatan berterusan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA104', 'AE021', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pengukuran keberkesanan program kesedaran keselamatan siber adalah menyeluruh dan dioptimumkan, menggunakan teknologi seperti analitik data dan automasi untuk menilai tingkah laku kakitangan secara real-time; dan\r\n• Program latihan dan kesedaran keselamatan siber sentiasa diperbaiki berdasarkan data keberkesanan yang diukur, memastikan ia responsif terhadap ancaman siber terkini; dan\r\n• Organisasi mengukur impak jangka panjang terhadap budaya keselamatan siber, memastikan peningkatan kesedaran bukan sahaja diukur melalui prestasi semasa tetapi juga melalui pengurangan insiden keselamatan yang ketara.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA105', 'AE021', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Pengukuran keberkesanan program kesedaran keselamatan siber adalah menyeluruh dan dioptimumkan, menggunakan teknologi seperti analitik data dan automasi untuk menilai tingkah laku kakitangan secara real-time; dan\r\n• Program latihan dan kesedaran keselamatan siber sentiasa diperbaiki berdasarkan data keberkesanan yang diukur, memastikan ia responsif terhadap ancaman siber terkini; dan\r\n• Organisasi mengukur impak jangka panjang terhadap budaya keselamatan siber, memastikan peningkatan kesedaran bukan sahaja diukur melalui prestasi semasa tetapi juga melalui pengurangan insiden keselamatan yang ketara.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA106', 'AE022', 'AS001', '• Tiada dokumentasi formal mengenai tanggungjawab keselamatan siber; dan\r\n• Kakitangan tidak jelas tentang peranan mereka dalam memastikan keselamatan siber organisasi; dan\r\n• Tanggungjawab keselamatan siber hanya diketahui secara lisan atau tidak ditetapkan secara jelas.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA107', 'AE022', 'AS002', '• Dokumentasi tanggungjawab keselamatan siber mula diwujudkan, tetapi hanya untuk beberapa peranan kritikal; dan\r\n• Tanggungjawab masih tidak lengkap atau tidak menyeluruh, dengan peranan keselamatan siber yang tidak terperinci untuk semua kakitangan; dan\r\n• Peranan dan tanggungjawab hanya ditetapkan untuk memenuhi keperluan pematuhan minimum tanpa penyelarasan menyeluruh dalam organisasi.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA108', 'AE022', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Tanggungjawab keselamatan siber telah ditakrifkan dengan jelas dan didokumentasikan untuk semua peranan yang relevan dalam organisasi; dan\r\n• Setiap kakitangan mempunyai peranan keselamatan siber yang khusus, dengan tanggungjawab yang sepadan mengikut tugas dan kedudukan mereka dalam organisasi; dan\r\n• Dokumentasi disediakan dalam bentuk dasar, garis panduan, dan prosedur yang jelas serta dikongsi dengan semua peringkat kakitangan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA109', 'AE022', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Dokumentasi tanggungjawab keselamatan siber diurus secara berkala dan dikemas kini apabila terdapat perubahan dalam struktur organisasi, teknologi, atau ancaman siber; dan\r\n• Proses penyemakan dan pengesahan tanggungjawab dilakukan secara berkala untuk memastikan setiap peranan dipenuhi dengan betul; dan\r\n• Tanggungjawab keselamatan siber diintegrasikan sepenuhnya dalam pengurusan risiko dan strategi keselamatan organisasi.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA110', 'AE022', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Dokumentasi tanggungjawab keselamatan siber dioptimumkan dan menjadi sebahagian daripada budaya organisasi; dan\r\n• Setiap individu dalam organisasi memahami dan melaksanakan tanggungjawab keselamatan siber mereka, dan peranan tersebut disesuaikan berdasarkan perubahan ancaman dan keperluan pematuhan; dan\r\n• Dokumentasi disokong oleh teknologi automasi untuk mengesan, mengurus, dan mengesahkan pematuhan terhadap tanggungjawab yang telah ditetapkan secara real-time.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA111', 'AE023', 'AS001', '• Penentuan dan pengurusan tanggungjawab keselamatan siber tidak dilakukan atau dilakukan secara minimum dan tidak formal; dan\r\n• Tiada proses untuk memastikan tanggungjawab keselamatan siber diagihkan dengan jelas atau untuk memastikan kecukupan dalam peranan keselamatan siber; dan\r\n• Redundansi tanggungjawab kakitangan tidak diambil kira, menjadikan organisasi bergantung kepada individu tertentu tanpa pelan sandaran sekiranya berlaku ketidakhadiran atau kekosongan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA112', 'AE023', 'AS002', '• Penentuan tanggungjawab keselamatan siber mula dilakukan tetapi masih tidak menyeluruh. Sesetengah peranan penting ditetapkan, namun masih ada kelemahan dalam mengurus peranan kritikal; dan\r\n• Usaha untuk memastikan kecukupan tanggungjawab dilakukan, tetapi tidak ada strategi jelas untuk menangani keperluan redundansi; dan\r\n• Pemantauan tanggungjawab keselamatan kakitangan adalah terhad, dengan sedikit tumpuan pada bagaimana tanggungjawab diagihkan apabila kakitangan kritikal tiada.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA113', 'AE023', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Penentuan tanggungjawab keselamatan siber telah ditakrifkan dengan jelas untuk semua peranan yang relevan, termasuk kecukupan dalam setiap peranan keselamatan; dan\r\n• Redundansi tanggungjawab telah dirancang, dengan pelan kontingensi yang jelas bagi memastikan setiap peranan keselamatan kritikal mempunyai individu sandaran; dan\r\n• Tanggungjawab keselamatan siber didokumenkan dengan baik, dan terdapat proses formal untuk memastikan bahawa jika kakitangan kritikal tiada, tanggungjawab mereka dapat diambil alih oleh kakitangan lain yang terlatih.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA114', 'AE023', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pengurusan tanggungjawab keselamatan siber dilakukan secara berkala, dengan pemantauan aktif terhadap kecukupan dan redundansi setiap tanggungjawab; dan\r\n• Setiap tanggungjawab keselamatan siber dalam organisasi dipastikan mencukupi, dan pelan sandaran atau redundansi diurus secara sistematik untuk peranan kritikal; dan\r\n• Latihan diberikan kepada kakitangan sandaran untuk memastikan mereka dapat mengambil alih tanggungjawab keselamatan apabila perlu, memastikan kesinambungan operasi tanpa risiko keselamatan yang tinggi.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA115', 'AE023', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Penentuan dan pengurusan tanggungjawab keselamatan siber mencapai tahap optimum, di mana tanggungjawab bukan sahaja diagihkan dengan baik tetapi juga dioptimumkan berdasarkan keperluan organisasi dan ancaman keselamatan terkini; dan\r\n• Redundansi tanggungjawab diurus secara berterusan dan dipantau dengan teknologi untuk memastikan setiap peranan kritikal sentiasa mempunyai pelan sandaran yang aktif dan berkesan; dan\r\n• Organisasi memastikan bahawa kecukupan dan redundansi tanggungjawab keselamatan siber diselaraskan dengan strategi keseluruhan keselamatan, dengan audit dan penilaian berkala bagi memastikan kelangsungan operasi yang optimum tanpa sebarang gangguan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA116', 'AE024', 'AS001', '• Program Penggantian belum ada atau tidak diformalisasikan; dan\r\n• Tiada perancangan atau usaha untuk mengenal pasti individu yang sesuai bagi menggantikan kakitangan penting dalam keselamatan siber atau peranan kritikal lain; dan\r\n• Kakitangan meninggalkan organisasi atau jabatan tanpa pelan kesinambungan yang jelas, menyebabkan kelemahan dalam operasi keselamatan dan kepimpinan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA117', 'AE024', 'AS002', '• Program Penggantian mula diperkenalkan, tetapi hanya untuk beberapa peranan kritikal; dan\r\n• Organisasi mula mengenal pasti bakat dalaman untuk menggantikan kakitangan penting, tetapi proses ini belum menyeluruh atau berstruktur; dan\r\n• Tumpuan hanya diberikan kepada beberapa peranan kepimpinan atau keselamatan siber utama, tanpa pelan yang jelas untuk peranan keselamatan siber peringkat menengah atau rendah', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA118', 'AE024', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Program Penggantian telah ditakrifkan dengan jelas dan melibatkan penilaian bakat serta perancangan penggantian bagi peranan keselamatan siber yang penting; dan\r\n• Proses pemilihan pengganti dibuat berdasarkan kriteria yang jelas, seperti prestasi, kemahiran teknikal, dan potensi kepimpinan; dan\r\n• Program ini merangkumi pembangunan dan latihan bagi individu yang dikenalpasti sebagai calon pengganti, dengan tumpuan pada peningkatan kemahiran dan pengetahuan keselamatan siber.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA119', 'AE024', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pelaksanaan Program Penggantian dijalankan secara sistematik dengan pemantauan berterusan dan penilaian kemajuan calon pengganti; dan\r\n• Terdapat pelan latihan dan pembangunan yang terstruktur bagi setiap calon pengganti, yang direka untuk memastikan mereka bersedia mengambil alih peranan kritikal apabila perlu; dan\r\n• Program penggantian dilihat sebagai komponen penting dalam strategi keselamatan organisasi, memastikan kesinambungan kepimpinan dan keselamatan siber dalam menghadapi perubahan atau ketidakhadiran kakitangan utama.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA120', 'AE024', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Program Penggantian dioptimumkan sepenuhnya, dengan pendekatan yang proaktif untuk mengenal pasti, melatih, dan mengurus calon pengganti bagi semua peranan penting, termasuk keselamatan siber; dan\r\n• Program ini diintegrasikan dengan strategi pengurusan bakat dan perancangan jangka panjang organisasi, menggunakan analitik data dan penilaian prestasi untuk mengenal pasti calon pengganti terbaik; dan\r\n• Proses penggantian adalah dinamik dan disesuaikan dengan keperluan operasi, memastikan bahawa organisasi sentiasa bersedia untuk menangani perubahan kepimpinan atau keselamatan tanpa gangguan kepada operasi dan tahap keselamatan.', NULL, NULL, '4', '2025-12-24', 'Active');
INSERT INTO `score_element` (`se_ID`, `element_ID`, `score_ID`, `details`, `input_id`, `input_at`, `updated_id`, `updated_at`, `status`) VALUES
('ASA121', 'AE025', 'AS001', '• Tiada proses pengurusan inventori aset formal disediakan;\r\n• Kurang kesedaran atau pengakuan tentang kepentingan pengurusan inventori aset; \r\n• Aset, termasuk data, sistem dan aplikasi, tidak dapat dijejaki atau didokumenkan; dan\r\n• Beberapa kesedaran tentang kepentingan pengurusan inventori aset, tetapi usaha adalah tidak formal dan adalah secara ad hoc.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA122', 'AE025', 'AS002', '• Inventori aset wujud tetapi tidak lengkap, tidak konsisten dan tidak dikemas kini secara sistematik; dan\r\n• Data, sistem dan aplikasi dijejaki tanpa proses formal atau mengikut standardan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA123', 'AE025', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Proses pengurusan inventori aset yang dibangunkan  dan didokumenkan; dan\r\n• Inventori komprehensif yang merangkumi semua data, sistem dan aplikasi; dan\r\n• Kemas kini dan penyelenggaraan inventori aset secara berkala; dan\r\n• Peranan dan tanggungjawab yang jelas dalam menguruskan inventori aset.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA124', 'AE025', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pemantauan dan semakan berkala terhadap proses pengurusan inventori aset; dan\r\n• Penggunaan metrik dan petunjuk prestasi utama (KPI) bagi mengukur ketepatan dan keberkesanan inventori; dan\r\n• Pematuhan berterusan kepada proses pengurusan inventori dengan pengawasan dan pelarasan pengurusan berdasarkan data prestasi; dan\r\n• Penyepaduan pengurusan inventori aset ke dalam proses dan strategi organisasi yang lebih luas.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA125', 'AE025', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Penambahbaikan berterusan proses pengurusan inventori aset melalui inovasi dan maklum balas warga dan pihak yang berkepentingan; dan\r\n• Integrasi pengurusan inventori aset diselaraskan dengan perancangan strategik dan hasil jangka panjang; dan\r\n• Penyesuaian proaktif kepada teknologi baru muncul dan amalan terbaik industri; dan\r\n• Penggunaan metodologi dan teknologi terkini bagi meningkatkan proses pengurusan inventori.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA126', 'AE026', 'AS001', '• Tiada sistem pengelasan maklumat disediakan; \r\n• Hanya jenis maklumat tertentu diklasifikasikan dan pengelasan dilakukan secara ad-hoc; dan\r\n• Maklumat tidak dikelaskan, dan tiada garis panduan atau proses yang ditetapkan untuk pengelasan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA127', 'AE026', 'AS002', '• Klasifikasi maklumat wujud tetapi tidak lengkap, tidak konsisten dan tidak dikemas kini secara sistematik; dan\r\n• Beberapa usaha dilakukan bagi mengklasifikasikan maklumat, tetapi ini adalah tidak formal, tidak konsisten dan tidak mempunyai proses berstruktur.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA128', 'AE026', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Proses yang ditakrifkan dengan baik dan didokumenkan bagi klasifikasi maklumat telah disediakan; dan\r\n• Garis panduan dan prosedur diwujudkan, dan maklumat dikelaskan secara konsisten mengikut standardan ini.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA129', 'AE026', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Proses pengelasan maklumat diurus dan dipantau secara aktif; dan\r\n• Terdapat prosedur yang sistematik dalam memastikan semua maklumat dikelaskan dengan betul; dan\r\n• Pematuhan dan garis panduan bagi klasifikasi maklumat disemak secara berkala, dan penambahbaikan dibuat mengikut keperluan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA130', 'AE026', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Proses pengelasan maklumat dioptimumkan secara berterusan; dan\r\n• Pendekatan proaktif bagi meningkatkan sistem klasifikasi berdasarkan maklum balas warga dan pihak yang berkepentingan, metrik prestasi dan amalan terbaik; dan\r\n• Penggunaan metodologi dan teknologi terkini dalam meningkatkan proses klasifikasi maklumat.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA131', 'AE027', 'AS001', '• Tiada prosedur formal untuk perlindungan, sanitasi, atau pelupusan maklumat; dan\r\n• Maklumat tidak dikendalikan secara sistematik mengikut keperluan kitar hayat maklumat; dan\r\n• Proses pelupusan maklumat berlaku secara ad-hoc atau tidak dipantau dengan betul, meningkatkan risiko kebocoran maklumat.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA132', 'AE027', 'AS002', '• Terdapat langkah asas untuk melindungi maklumat semasa kitar hayatnya, tetapi prosedur sanitasi dan pelupusan maklumat masih tidak jelas atau konsisten; dan\r\n• Beberapa prosedur pelupusan telah diperkenalkan, namun pelaksanaannya tidak menyeluruh atau dipatuhi di semua jabatan; dan\r\n• Sanitasi maklumat dilakukan, tetapi prosesnya tidak diaudit secara berkala.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA133', 'AE027', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Dasar dan prosedur perlindungan, sanitasi, dan pelupusan maklumat telah dirangka dan didokumenkan, merangkumi keseluruhan kitar hayat maklumat; dan\r\n• Terdapat kaedah yang jelas untuk melindungi maklumat sepanjang fasa kitar hayatnya, termasuk langkah-langkah sanitasi sebelum pelupusan; dan\r\n• Maklumat sensitif dilupuskan secara sistematik mengikut prosedur yang ditetapkan, memastikan pematuhan kepada peraturan dan standard keselamatan maklumat.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA134', 'AE027', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Perlindungan, sanitasi, dan pelupusan maklumat dikawal dan diukur secara kuantitatif untuk memastikan keberkesanan prosedur sepanjang kitar hayat maklumat; dan\r\n• Semua maklumat diklasifikasikan mengikut tahap keperluan keselamatan, dan prosedur sanitasi serta pelupusan diurus secara sistematik berdasarkan jenis maklumat; dan\r\n• Proses pemantauan dilakukan untuk memastikan maklumat yang telah tamat tempoh dilupuskan dengan selamat dan tepat masa.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA135', 'AE027', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Proses perlindungan, sanitasi, dan pelupusan maklumat dioptimumkan sepenuhnya, dengan penekanan pada inovasi dan pematuhan kepada standard keselamatan maklumat yang terkini; dan\r\n• Sanitasi dan pelupusan maklumat dilakukan secara proaktif, menggunakan teknologi terkini seperti pemadaman selamat (secure wipe) dan kaedah enkripsi untuk memastikan maklumat yang tamat tempoh tidak boleh diakses semula; dan\r\n• Audit berkala dan pemantauan berterusan memastikan amalan terbaik diterapkan sepanjang kitar hayat maklumat, dan perlindungan maklumat menjadi sebahagian daripada budaya keselamatan organisasi.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA136', 'AE028', 'AS001', '• Keperluan untuk mewujudkan identiti dan mengurus pengesahan diiktiraf, tetapi proses atau garis panduan formal masih perlu dilaksanakan; dan\r\n• Kaedah asas atau ad hoc pengurusan identiti dan pengesahan; dan\r\n• Dokumentasi minimum atau proses formal; dan\r\n• Kesedaran terhad tentang amalan terbaik dalam pengurusan identiti dan pengesahan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA137', 'AE028', 'AS002', '• Telah lengkap Tahap 2; dan\r\n• Proses asas untuk mewujudkan identiti dan mengurus pengesahan telah disediakan tetapi tidak seragam atau tidak digunakan secara konsisten; dan\r\n• Usaha awal untuk melaksanakan mekanisme pengesahan (cth., nama pengguna dan kata laluan); dan\r\n• Terdapat beberapa dokumentasi prosedur pengurusan identiti; dan\r\n• Aplikasi amalan pengesahan yang tidak konsisten pada seluruh organisasi.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA138', 'AE028', 'AS003', '• Proses dan prosedur formal untuk mewujudkan identiti dan mengurus pengesahan diwujudkan dan didokumenkan;\r\n• Prosedur standard  untuk pengesahan identiti dan pengesahan;\r\n• Dokumentasi komprehensif pengurusan identiti dan amalan pengesahan; dan\r\n• Pelaksanaan pengesahan pelbagai faktor (Multi Factor Aunthentication-MFA) untuk sistem kritikal.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA139', 'AE028', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Proses untuk mewujudkan identiti dan mengurus pengesahan diurus dengan baik dan disepadukan ke dalam rangka kerja keselamatan organisasi; dan\r\n• Aplikasi konsisten prosedur pengurusan identiti dan pengesahan di seluruh organisasi; dan\r\n• Semakan tetap dan kemas kini amalan pengesahan untuk menangani ancaman baharu; dan\r\n• Penggunaan penyelesaian identiti dan pengurusan akses (Identity Access Management-IAM) terpusat; dan\r\n• Pemantauan dan pengurusan aktif acara dan insiden pengesahan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA140', 'AE028', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Proses untuk mewujudkan identiti dan mengurus pengesahan terus dipertingkatkan dan dioptimumkan untuk keselamatan maksimum dan kemudahan pengguna; dan\r\n• Penggunaan teknologi pengesahan lanjutan (cth., biometrik, analisis tingkah laku); dan\r\n• Proses maklum balas berterusan untuk meningkatkan identiti dan amalan pengesahan; dan\r\n• Pengenalpastian proaktif dan penggunaan kaedah pengesahan yang baru muncul; dan\r\n• Dokumentasi yang komprehensif dan mudah dilayari; dan\r\n• Penjajaran strategik dengan matlamat organisasi dan amalan terbaik industri; dan\r\n• Penyepaduan pengurusan identiti dan pengesahan dengan keselamatan siber dan strategi organisasi  yang lebih luas.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA141', 'AE029', 'AS001', '• Tiada mekanisme pengesahan formal yang diterapkan, atau sistem pengesahan yang sangat asas digunakan; dan\r\n• Pengesahan pengguna dilakukan menggunakan kata laluan yang lemah tanpa standard keselamatan yang jelas; dan\r\n• Aplikasi dan sistem tidak mematuhi keperluan minimum untuk keselamatan pengesahan, meningkatkan risiko akses tanpa izin.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA142', 'AE029', 'AS002', '• Mekanisme pengesahan asas seperti kata laluan telah dilaksanakan, namun masih terdapat kelemahan dalam keselamatan, seperti tiada keperluan untuk kata laluan yang kompleks; dan\r\n• Pengesahan dua faktor (Two Factor Aunthentication - 2FA) mula diperkenalkan, tetapi penggunaannya tidak meluas dan hanya diaplikasikan pada sistem kritikal; dan\r\n• Mekanisme pengesahan wujud tetapi tidak dikendalikan atau dipantau secara aktif.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA143', 'AE029', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Sistem dan aplikasi mempunyai mekanisme pengesahan yang jelas dan mengikut standard  keselamatan yang diiktiraf; dan\r\n• Pengesahan dua faktor (2FA) atau pelbagai faktor (Multi Factor Aunthentication- MFA) digunakan untuk aplikasi yang sensitif dan kritikal, serta menjadi sebahagian daripada polisi keselamatan organisasi; dan\r\n• Dasar pengurusan kata laluan yang kuat telah diterapkan, termasuk keperluan untuk kata laluan kompleks, penukaran kata laluan secara berkala, dan penyimpanan kata laluan yang selamat.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA144', 'AE029', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Mekanisme pengesahan dipantau dan diukur secara kuantitatif untuk menilai keberkesanan, dengan pemantauan berterusan terhadap akses pengguna; dan\r\n• Pengesahan (Multi factor Authentication- MFA) digunakan secara meluas di seluruh organisasi, termasuk pada semua sistem dan aplikasi yang penting; dan\r\n• Terdapat analisis berkala terhadap log pengesahan untuk mengenal pasti dan mencegah percubaan akses tidak sah, dengan penggunaan teknologi analitik keselamatan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA145', 'AE029', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Pengesahan bagi sistem dan aplikasi dioptimumkan sepenuhnya, menggunakan teknologi terkini seperti pengesahan biometrik, token keselamatan fizikal, atau pengesahan berasaskan risiko; dan\r\n• Proses pengesahan berintegrasi dengan sistem kecerdasan buatan (Artificial Intelligence- AI) untuk menilai risiko akses secara automatik dan menyesuaikan tahap pengesahan yang diperlukan; dan\r\n• Organisasi terus menilai dan memperbaiki mekanisme pengesahan berdasarkan ancaman keselamatan siber yang berubah-ubah dan teknologi baru yang tersedia.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA146', 'AE030', 'AS001', '• Tiada proses formal atau prosedur yang jelas untuk memantau dan menyemak hak akses pengguna; dan\r\n• Akses pengguna ke sistem atau data jarang atau tidak pernah disemak secara berkala; dan\r\n• Risiko akses yang berlebihan atau akses yang tidak diperlukan dibiarkan tanpa pengawasan, meningkatkan potensi risiko keselamatan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA147', 'AE030', 'AS002', '• Proses pemantauan hak akses pengguna mula dilaksanakan, tetapi ia tidak dilakukan secara konsisten di seluruh organisasi; dan\r\n• Penyemakan hak akses dilakukan pada beberapa sistem kritikal sahaja, sementara sistem lain tidak diberikan perhatian yang sama; dan\r\n• Terdapat kesedaran tentang keperluan menyemak akses secara berkala, tetapi tiada garis panduan formal atau dokumentasi yang lengkap.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA148', 'AE030', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Prosedur formal untuk pemantauan dan penyemakan hak akses pengguna telah diwujudkan dan didokumenkan; dan\r\n• Penyemakan hak akses pengguna dilakukan secara berkala dan merangkumi semua sistem dan aplikasi penting.\r\n• Hasil penyemakan didokumentasikan dan tindakan diambil untuk membatalkan atau menyelaraskan hak akses yang tidak lagi diperlukan, dengan proses audit dalaman yang menyokong.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA149', 'AE030', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pemantauan dan penyemakan hak akses pengguna dikawal dan diukur secara kuantitatif, menggunakan metrik dan data untuk menilai keberkesanan proses; dan\r\n• Sistem pengurusan akses automatik digunakan untuk memantau akses secara real-time dan menyemak hak akses secara berkala mengikut jadual yang telah ditetapkan; dan\r\n• Audit akses dilakukan pada selang masa tertentu dengan penekanan pada pengesanan akses yang tidak perlu atau melampaui keperluan, serta pematuhan kepada polisi keselamatan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA150', 'AE030', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Proses pemantauan dan penyemakan hak akses pengguna dioptimumkan sepenuhnya dengan automasi dan integrasi teknologi kecerdasan buatan (AI) untuk mengenal pasti risiko akses yang tidak sah atau mencurigakan secara proaktif; dan\r\n• Akses pengguna disemak secara berkala berdasarkan faktor risiko yang dinamik, dan mekanisme pencegahan dibina untuk menghalang akses yang tidak sah atau melebihi keperluan secara automatik; dan\r\n• Penyemakan hak akses dilakukan secara berterusan, dengan proses pemantauan akses yang selaras dengan perubahan peranan pengguna, serta pengurusan identiti dan akses yang terkemuka dalam industri.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA151', 'AE031', 'AS001', '• Tiada prosedur formal untuk memantau atau menyemak akses pihak ketiga kepada data dan maklumat organisasi; dan\r\n• Akses pihak ketiga diberikan tanpa penilaian risiko yang tepat, dan tiada penyeliaan selepas akses diberikan; dan\r\n• Potensi risiko keselamatan yang tinggi kerana tiada kawalan terhadap akses pihak ketiga selepas ia diluluskan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA152', 'AE031', 'AS002', '• Proses asas untuk memberikan dan memantau akses pihak ketiga telah diwujudkan, tetapi hanya meliputi sebahagian daripada data atau sistem penting; dan\r\n• Penyemakan hak akses pihak ketiga dilakukan secara tidak konsisten, dan kadangkala akses berterusan tanpa justifikasi jelas; dan\r\n• Terdapat usaha untuk mengenal pasti pihak ketiga dengan akses berlebihan, tetapi tindakan proaktif untuk menyekat atau menyemak semula akses adalah terhad.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA153', 'AE031', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Polisi dan prosedur yang jelas untuk pemantauan dan penyemakan akses pihak ketiga telah ditetapkan, termasuk proses dokumentasi dan kawalan; dan\r\n• Akses pihak ketiga disemak secara berkala berdasarkan risiko dan keperluan organisasi , dengan penyelarasan yang lebih baik antara jabatan teknologi maklumat dan pengurusan risiko; dan\r\n• Tindakan diambil untuk membatalkan atau menyemak semula akses pihak ketiga yang tidak lagi diperlukan atau yang tidak mematuhi syarat perjanjian.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA154', 'AE031', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Proses pemantauan dan penyemakan akses pihak ketiga dikawal dengan metrik yang jelas, dan pengukuran dilakukan untuk menilai keberkesanan langkah keselamatan yang diambil; dan\r\n• Log akses pihak ketiga dipantau secara berkala, dan penyemakan dilakukan berdasarkan analisis risiko yang lebih mendalam; dan\r\n• Pengurusan akses pihak ketiga melibatkan penggunaan alat automatik untuk mengesan akses luar biasa atau aktiviti yang mencurigakan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA155', 'AE031', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Pemantauan dan penyemakan akses pihak ketiga adalah automatik sepenuhnya, dengan penggunaan teknologi seperti AI dan pembelajaran mesin untuk menganalisis aktiviti akses secara real-time; dan\r\n• Akses pihak ketiga diurus dengan teliti menggunakan strategi berasaskan risiko yang disesuaikan dengan keperluan organisasi dan ancaman keselamatan semasa; dan\r\n• Audit secara berterusan dilakukan untuk memastikan pematuhan pihak ketiga terhadap semua dasar keselamatan, dengan tindakan segera diambil apabila terdapat pelanggaran atau risiko yang dikenal pasti.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA161', 'AE033', 'AS001', '• Tiada program berstruktur atau usaha untuk meningkatkan kesedaran pihak ketiga tentang dasar dan amalan keselamatan; dan\r\n• Tidak menyampaikan kepentingan keselamatan dan pematuhan kepada pihak ketiga; dan\r\n• Sebarang usaha kesedaran adalah tidak bersistematik dan tidak digunakan secara konsisten.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA162', 'AE033', 'AS002', '• Beberapa usaha untuk meningkatkan kesedaran pihak ketiga adalah tidak formal dan bukan sebahagian daripada program berstruktur; dan\r\n• Pihak ketiga dimaklumkan tentang beberapa dasar keselamatan, tetapi ini dilakukan secara tidak konsisten; dan\r\n• Usaha kesedaran adalah terhad dan hanya mencapai sebilangan kecil pihak ketiga.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA163', 'AE033', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Organisasi telah membangunkan dokumen berkaitan proses dalam meningkatkan kesedaran pihak ketiga tentang dasar, polisi dan amalan keselamatan;\r\n• Strategi komunikasi yang jelas dan sistematik disediakan untuk memaklumkan pihak ketiga tentang dasar keselamatan yang berkaitan; dan\r\n• Pihak ketiga menerima latihan dan pendidikan tetap mengenai dasar keselamatan dan keperluan pematuhan; dan\r\n• Terdapat prosedur standard untuk memastikan kesedaran pihak ketiga sebagai sebahagian daripada perjanjian kontrak.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA164', 'AE033', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Organisasi sentiasa memantau keberkesanan usaha kesedaran pihak ketiganya dan mengumpul maklum balas untuk penambahbaikan; dan\r\n• Penggunaan metrik dan penunjuk prestasi utama (Key Performance Indicator-KPI) untuk mengukur kesan program kesedaran pihak ketiga;\r\n• Pemantauan pengurusan yang berterusan memastikan program kesedaran pihak ketiga berkesan dan sejajar dengan matlamat organisasi; dan\r\n• Program kesedaran pihak ketiga disepadukan ke dalam dasar organisasi dan rangka kerja pematuhan yang lebih luas.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA165', 'AE033', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Organisasi terus menambah baik program kesedaran pihak ketiga melalui inovasi, maklum balas dan penyesuaian kepada ancaman baharu; dan\r\n• Program kesedaran pihak ketiga diselaraskan dengan perancangan strategik dan hasil jangka panjang; dan\r\n• Organisasi menggunakan kaedah dan teknologi terkini untuk meningkatkan keberkesanan usaha kesedaran pihak ketiganya; dan\r\n• Organisasi secara proaktif menyesuaikan program kesedaran kepada keperluan keselamatan yang muncul dan amalan terbaik industri.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA166', 'AE034', 'AS001', '• Tiada mekanisme atau usaha disediakan untuk memastikan pematuhan akuan pihak ketiga terhadap perjanjian yang diperakui; dan\r\n• Pematuhan tidak dipantau mahupun dikuatkuasakan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA167', 'AE034', 'AS002', '• Beberapa usaha ad-hoc dibuat untuk memastikan pematuhan pihak ketiga, tetapi tidak konsisten dan tiada proses formal; dan\r\n• Perjanjian keselamatan siber kadangkala diperiksa, tetapi tidak dikuatkuasakan secara sistematik.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA168', 'AE034', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Terdapat proses yang ditakrifkan dan didokumenkan dengan baik untuk memastikan pematuhan pihak ketiga terhadap perjanjian yang diperakui; dan\r\n• Pematuhan dilaksanakan mengikut proses secara berkala dan pihak ketiga secara amnya mengetahui keperluan pematuhan.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA169', 'AE034', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Proses untuk memastikan pematuhan pihak ketiga diurus dan dipantau secara aktif; dan\r\n• Terdapat semakan pematuhan yang sistematik, dan sebarang ketidakpatuhan akan ditangani dengan segera; dan  \r\n• Proses ini disemak dan diperbaiki secara berkala.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA170', 'AE034', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Memastikan pematuhan pihak ketiga dioptimumkan secara berterusan; dan\r\n• Terdapat pendekatan proaktif untuk mempertingkatkan proses pematuhan berdasarkan maklum balas, metrik prestasi dan amalan terbaik; dan\r\n• Pemeriksaan pematuhan sangat berkesan dan menyesuaikan diri secara dinamik kepada cabaran dan perjanjian baharu.', NULL, NULL, '4', '2025-12-24', 'Active'),
('ASA273', 'AE056', 'AS001', '•	Organisasi berada pada tahap asas dalam melaksanakan langkah-langkah untuk melindungi PII; dan\r\n•	Tiada dasar atau prosedur rasmi yang ditetapkan, dan perlindungan PII dilakukan secara ad-hoc atau reaktif; dan\r\n•	Kesedaran tentang undang-undang seperti PDPA 2010 adalah rendah.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA274', 'AE056', 'AS002', '• Terdapat langkah-langkah asas yang mula dilaksanakan untuk melindungi PII, namun belum sepenuhnya formal; dan\r\n• Beberapa prosedur atau garis panduan wujud, tetapi pelaksanaannya tidak konsisten; dan\r\n• Warga mula didedahkan kepada latihan asas berkaitan pematuhan privasi dan PII.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA275', 'AE056', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Organisasi mempunyai dasar-dasar dan prosedur yang formal dan didokumentasikan untuk pemeliharaan privasi dan perlindungan PII; dan\r\n• Pematuhan terhadap undang-undang privasi seperti PDPA dipantau secara berkala; dan\r\n• Terdapat pemantauan risiko untuk pelanggaran PII, dan pelaporan pelanggaran dilaksanakan mengikut peraturan yang terpakai.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA276', 'AE056', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Privasi dan perlindungan PII diurus dengan pendekatan sistematik dan diukur secara kuantitatif; dan\r\n• Pengurusan privasi menjadi sebahagian daripada strategi keseluruhan organisasi, dengan analisis risiko dan penilaian kesan dilakukan secara berkala; dan\r\n• Mekanisme untuk mengendalikan insiden pelanggaran privasi diselaraskan dan diuji secara berkala.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA277', 'AE056', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Perlindungan PII dioptimumkan dengan menggunakan teknologi terkini seperti enkripsi data dan pemantauan berterusan; dan\r\n• Pemeliharaan privasi adalah budaya organisasi yang kuat, dengan latihan dan audit berkala untuk memastikan pematuhan yang berterusan; dan\r\n• Organisasi mematuhi dan sering melampaui keperluan undang-undang serta kontrak, sambil berinovasi untuk meningkatkan keselamatan data.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA278', 'AE057', 'AS001', '• Organisasi tidak mempunyai pelan formal untuk mematuhi undang-undang, peraturan, dan standard keselamatan siber.\r\n• Langkah-langkah keselamatan siber hanya dilaksanakan secara reaktif apabila berlaku insiden atau ancaman yang teruk.\r\n• Tiada rujukan atau kepatuhan yang konsisten kepada undang-undang dan standard  seperti ISO/IEC 27001, GDPR, atau peraturan tempatan.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA279', 'AE057', 'AS002', '• Organisasi mula melaksanakan beberapa prosedur keselamatan siber berdasarkan undang-undang dan peraturan, namun tidak menyeluruh atau konsisten.\r\n• Pematuhan terhadap standard  seperti ISO 27001 atau amalan terbaik industri telah mula diterapkan tetapi belum sepenuhnya diurus secara formal.\r\n• Tiada audit berkala untuk memastikan pematuhan yang berterusan.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA280', 'AE057', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Proses dan prosedur keselamatan siber yang jelas telah dirumuskan untuk mematuhi semua undang-undang, peraturan, dan standard  yang relevan.\r\n• Pelaksanaan keselamatan siber dijalankan secara konsisten merentasi semua jabatan dalam organisasi dan melibatkan pihak ketiga yang berkaitan.\r\n• Audit dalaman dan luaran dijalankan secara berkala untuk memastikan pematuhan.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA281', 'AE057', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pelaksanaan keselamatan siber dipantau dengan menggunakan metrik kuantitatif untuk menilai keberkesanan pelaksanaan mengikut undang-undang dan standard yang diperlukan.\r\n• Organisasi mengkaji hasil audit dan laporan untuk membuat penambahbaikan berterusan terhadap pematuhan peraturan dan undang-undang.\r\n• Pematuhan standard  seperti ISO 27001, PCI-DSS, atau NIST dipastikan dengan konsisten melalui analisis risiko dan langkah kawalan yang sistematik.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA282', 'AE057', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Keselamatan siber dilaksanakan secara proaktif dengan menggunakan teknologi terkini untuk memastikan pematuhan automatik terhadap undang-undang dan peraturan yang berubah-ubah.\r\n• Organisasi melaksanakan audit berterusan, ujian penetrasi, dan pemantauan keselamatan secara masa nyata untuk memastikan pelaksanaan keselamatan yang optimum.\r\n• Pematuhan undang-undang dan peraturan sentiasa ditingkatkan berdasarkan ancaman dan risiko baru yang dikenal pasti, serta perkembangan dalam undang-undang keselamatan siber global.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA283', 'AE058', 'AS001', '• Penguatkuasaan dan pengauditan belum diwujudkan atau dilakukan secara sporadik.\r\n• Tiada pelaksanaan yang formal atau proses penyemakan semula pematuhan undang-undang dan peraturan.\r\n• Proses tidak mendokumentasikan amalan terbaik dan standard keselamatan yang diperlukan.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA284', 'AE058', 'AS002', '• Penguatkuasaan dan pengauditan telah dilaksanakan, namun tiada jadual atau sistem yang tetap.\r\n• Penyemakan terhadap pematuhan undang-undang dan standard  dilakukan hanya apabila diperlukan, contohnya apabila ada perubahan besar.\r\n• Organisasi mula mengenal pasti standard keselamatan tetapi belum ada integrasi penuh ke dalam operasi berkala.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA285', 'AE058', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Penguatkuasaan dan pengauditan dilakukan secara berkala mengikut prosedur yang jelas dan terdokumentasi.\r\n• Organisasi mempunyai proses penyemakan semula yang berstruktur untuk memastikan pematuhan terhadap undang-undang, standard , dan amalan terbaik.\r\n• Pengemaskinian dilakukan mengikut jadual yang ditetapkan untuk memastikan pematuhan berterusan.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA286', 'AE058', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Penguatkuasaan dan pengauditan diuruskan secara kuantitatif dengan metrik untuk menilai keberkesanannya.\r\n• Proses penyemakan semula undang-undang, standard , dan amalan terbaik dijalankan secara berkala, dengan hasil audit digunakan untuk memperbaiki sistem.\r\n• Organisasi memastikan setiap perubahan undang-undang dan peraturan diselaraskan dengan segera.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA287', 'AE058', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Penguatkuasaan dan pengauditan dikemas kini secara dinamik menggunakan automasi dan teknologi terkini.\r\n• Proses penyemakan dan pengemaskinian dilakukan secara proaktif, memastikan keperluan undang-undang dan amalan terbaik sentiasa diikuti.\r\n• Audit dijalankan secara berkala dengan pemantauan masa nyata untuk memastikan pematuhan berterusan tanpa gangguan operasi.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA288', 'AE059', 'AS001', '• Tindakan susulan audit dijalankan secara ad hoc atau apabila terdapat keperluan mendesak; dan\r\n• Tiada prosedur formal untuk pelaksanaan tindakan pembetulan selepas audit; dan\r\n• Pemenuhan keperluan pematuhan masih tidak konsisten, dan organisasi hanya bertindak balas kepada isu atau ancaman apabila dikesan.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA289', 'AE059', 'AS002', '• Tindakan susulan audit mula dilaksanakan tetapi masih tidak lengkap atau sepenuhnya sistematik; dan\r\n• Terdapat usaha untuk mematuhi cadangan audit dan keperluan pematuhan, tetapi tindakan tidak dilaksanakan dengan segera atau sepenuhnya; dan\r\n• Pelaksanaan tindakan pembetulan masih bersifat reaktif, dengan pemantauan yang minimum terhadap keberkesanannya.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA290', 'AE059', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Proses tindakan susulan audit keselamatan siber dan pematuhan telah ditakrifkan dengan jelas; dan\r\n• Semua cadangan audit dilaksanakan dengan garis masa yang ditetapkan, dan ada pelan tindakan yang sistematik untuk menyelesaikan isu keselamatan; dan\r\n• Keperluan pematuhan diurus dengan baik, dan organisasi mempunyai mekanisme untuk memantau dan melaporkan kepatuhan terhadap standard dan peraturan.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA291', 'AE059', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pelaksanaan tindakan susulan audit dijalankan secara terurus, dan dipantau secara berkala melalui proses yang formal dan berstruktur; dan\r\n• Tindakan pembetulan dilaksanakan secara menyeluruh, dengan sistem pemantauan prestasi bagi memastikan keberkesanan langkah pembetulan; dan\r\n• Pemenuhan keperluan pematuhan sentiasa dipantau, dengan dokumentasi lengkap yang menyokong setiap tindakan dan peningkatan yang diambil.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA292', 'AE059', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Tindakan susulan audit dan pemenuhan keperluan pematuhan dilaksanakan secara proaktif, dengan amalan terbaik yang dioptimumkan untuk mengurangkan risiko keselamatan siber; dan\r\n• Penggunaan teknologi automasi dan analitik untuk memantau tindakan susulan audit secara real-time; dan\r\n• Proses tindakan pembetulan dan pematuhan bukan sahaja dilaksanakan, tetapi secara berterusan dikaji dan diperbaiki untuk menyesuaikan diri dengan ancaman keselamatan terkini serta perubahan dalam undang-undang dan peraturan.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA293', 'AE060', 'AS001', '• Tiada proses formal untuk memantau penyediaan dokumentasi pematuhan; dan\r\n• Pemantauan adalah secara ad hoc dan tidak konsisten; dan\r\n• Kurang kesedaran atau pengakuan tentang kepentingan memantau proses ini; dan\r\n• Dokumentasi pematuhan disediakan tanpa sebarang pengawasan atau pemantauan.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA294', 'AE060', 'AS002', '• Proses dokumentasi asas ada tetapi kurang menyeluruh atau kemas kini secara berkala;\r\n• Proses dokumentasi yang terhad atau tidak teratur; dan\r\n• Beberapa keperluan untuk memantau penyediaan dokumentasi pematuhan, tetapi pendekatannya tidak formal; dan\r\n• Organisasi menyedari kepentingan pemantauan tetapi tidak mempunyai prosedur yang ditetapkan atau pendekatan berstruktur.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA295', 'AE060', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Prosedur yang ditetapkan dan didokumenkan bagi memantau penyediaan dokumentasi pematuhan; dan\r\n• Proses konsisten yang jelas telah disediakan; dan\r\n• Aktiviti pemantauan dijalankan mengikut jadual atau kriteria yang ditetapkan.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA296', 'AE060', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pemantauan dan semakan berkala terhadap proses penyediaan dokumentasi pematuhan; dan\r\n• Penggunaan metrik atau petunjuk prestasi utama (KPI) untuk mengukur keberkesanan dan kecekapan proses; dan\r\n• Pelarasan dan penambahbaikan dibuat berdasarkan hasil pemantauan.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA297', 'AE060', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Penambahbaikan berterusan proses pemantauan melalui inovasi dan maklum balas warga dan pihak yang berkepentingan; dan\r\n• Aktiviti pemantauan diselaraskan dalam objektif dan proses organisasi yang lebih menyeluruh; dan\r\n• Jangkaan proaktif terhadap keperluan pematuhan dan keperluan masa hadapan.', '4', '2025-12-23', NULL, NULL, 'Active'),
('ASA298', 'AE061', 'AS001', '• Pemantauan dan penyemakan tanggungjawab keselamatan siber tidak wujud atau dilakukan secara ad hoc; dan\r\n• Tiada proses formal untuk menilai keperluan kakitangan berkaitan keselamatan siber atau menyemak sama ada tanggungjawab mereka masih relevan dan dipenuhi; dan\r\n• Keperluan keselamatan siber hanya dikaji semula apabila berlaku insiden keselamatan atau audit luaran.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA299', 'AE061', 'AS002', '• Pemantauan dan penyemakan mula dijalankan, tetapi hanya terhad kepada jabatan atau peranan tertentu; dan\r\n• Terdapat beberapa usaha untuk memastikan tanggungjawab keselamatan siber disemak secara berkala, tetapi ia masih belum sepenuhnya sistematik; dan\r\n• Keperluan kakitangan berkaitan keselamatan siber dinilai secara sporadik, dan tiada prosedur jelas untuk memperbaharui tanggungjawab atau memperbaiki kelemahan yang dikenalpasti.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA300', 'AE061', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Pemantauan dan penyemakan tanggungjawab keselamatan siber telah ditakrifkan dengan jelas dan dilaksanakan secara berkala; dan\r\n• Proses penyemakan keperluan kakitangan dijalankan secara teratur untuk memastikan mereka mempunyai latihan, pengetahuan, dan tanggungjawab yang sesuai dengan perubahan ancaman dan teknologi; dan\r\n• Tanggungjawab keselamatan siber dikemas kini mengikut perubahan dalam organisasi, dan prosedur untuk menyemak dan melaporkan status pemenuhan tanggungjawab dilaksanakan dengan jelas.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA301', 'AE061', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pemantauan dan penyemakan tanggungjawab keselamatan siber dijalankan secara sistematik dan berstruktur; dan\r\n• Setiap tanggungjawab keselamatan siber dipantau melalui proses formal, dan keperluan kakitangan dinilai secara berkala berdasarkan hasil audit, risiko terkini, dan prestasi keselamatan; dan\r\n• Keputusan daripada penyemakan tanggungjawab dan keperluan kakitangan digunakan untuk memperbaiki program latihan dan menetapkan tanggungjawab baru yang lebih relevan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA302', 'AE061', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Pemantauan dan penyemakan dilakukan secara berterusan dengan bantuan teknologi automasi dan analitik untuk menilai pematuhan terhadap tanggungjawab keselamatan siber dalam masa nyata; dan\r\n• Proses penyemakan bukan sahaja menilai tanggungjawab sedia ada tetapi juga proaktif dalam mengenal pasti dan menangani keperluan masa depan kakitangan berdasarkan ancaman baru, teknologi, dan perubahan dalam peraturan; dan\r\n• Tanggungjawab keselamatan siber dan keperluan kakitangan secara berkala dikaji semula sebagai sebahagian daripada strategi keselamatan menyeluruh, dengan maklum balas daripada semua peringkat organisasi digunakan untuk penambahbaikan berterusan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA303', 'AE062', 'AS001', '• Pemantauan dan penyemakan jarang dilakukan atau hanya apabila berlaku masalah teknikal atau keperluan auditan; dan\r\n• Tiada proses tetap untuk mengemas kini inventori, menyebabkan data aset yang tidak tepat atau ketinggalan zaman.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA304', 'AE062', 'AS002', '• Pemantauan dan penyemakan dilakukan secara berkala tetapi terhad kepada sebahagian aset atau tidak menyeluruh; dan\r\n• Tanggungjawab pengurusan inventori telah diberikan, namun proses untuk mengemas kini inventori masih kurang teratur atau tidak konsisten; dan\r\n• Inventori tidak dikaitkan sepenuhnya dengan keperluan keselamatan siber atau risiko organisasi  yang lebih luas.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA305', 'AE062', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Pemantauan dan penyemakan inventori aset dijalankan secara berkala dan mengikut prosedur yang jelas; dan\r\n• Proses mengemas kini inventori dilakukan secara teratur apabila terdapat perubahan dalam aset atau semasa audit dalaman; dan\r\n• Data inventori digunakan untuk menilai risiko keselamatan siber dan memastikan kawalan keselamatan yang sewajarnya diterapkan pada setiap aset.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA306', 'AE062', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pengurusan inventori aset dikawal secara sistematik dengan penyemakan dan pengemaskinian berkala yang jelas dan terjadual; dan\r\n• Pemantauan inventori melibatkan teknologi automasi atau alat pengurusan aset, yang memudahkan pengesanan dan kemas kini secara real-time atau pada selang waktu tertentu; dan\r\n• Aset dihubungkan dengan risiko keselamatan siber yang berkaitan, dan penyemakan dilakukan bagi memastikan setiap aset dilindungi dengan kawalan keselamatan yang tepat.\r\n• Inventori diperbaharui berdasarkan analisis risiko dan keperluan peraturan, serta disepadukan dengan keperluan organisasi  dan operasi.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA307', 'AE062', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Pengurusan inventori aset dioptimumkan sepenuhnya, dengan pemantauan dan penyemakan dilakukan secara automatik dan berterusan; dan\r\n• Sistem pengurusan aset menggunakan teknologi terkini seperti AI atau pembelajaran mesin untuk mengenal pasti perubahan dan mengemas kini inventori dengan serta-merta; dan\r\n• Inventori aset diintegrasikan dengan strategi keseluruhan keselamatan siber dan pengurusan risiko, membolehkan organisasi bertindak balas dengan cepat terhadap ancaman yang berpotensi; dan\r\n• Penyemakan inventori dilakukan secara proaktif, memastikan semua aset yang berkaitan dengan data, sistem, dan aplikasi sentiasa dalam keadaan terkawal dan dilindungi mengikut keperluan peraturan serta amalan terbaik.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA308', 'AE063', 'AS001', '• Tiada proses formal atau sistematik untuk pengwujudan dan pengekalan akses logikal; dan\r\n• Akses kepada sistem dan data diberikan secara ad-hoc tanpa penilaian risiko yang jelas atau pengesahan kebenaran; dan\r\n• Tiada prosedur khusus untuk mengurus dan menghapuskan akses apabila pengguna meninggalkan organisasi atau apabila akses tidak lagi diperlukan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA309', 'AE063', 'AS002', '• Prosedur asas untuk pengwujudan akses logikal telah dilaksanakan, tetapi terdapat kekurangan dalam pengawasan dan pengurusan akses; dan\r\n• Akses diberikan berdasarkan peranan pengguna, tetapi polisi pengurusan akses tidak diikuti sepenuhnya atau tidak dikemas kini secara berkala.\r\n• Proses pengekalan akses mula diperkenalkan, tetapi tidak ada mekanisme automatik untuk menyemak atau mengaudit akses yang berterusan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA310', 'AE063', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Polisi dan prosedur yang jelas telah diwujudkan untuk pengwujudan dan pengekalan akses logikal, berdasarkan prinsip akses minimum (least privilege) ; dan\r\n• Proses pengesahan akses berdasarkan peranan, dengan semakan berkala dilakukan untuk memastikan hanya pengguna yang dibenarkan mempunyai akses; dan\r\n• Terdapat dokumentasi yang lengkap untuk proses pengurusan akses dan penghapusan akses apabila tidak diperlukan lagi, termasuk semakan akses semasa perubahan peranan atau pemergian pengguna.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA311', 'AE063', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Akses logikal dikawal secara kuantitatif dengan metrik dan pengukuran untuk menilai keberkesanan pengurusan akses; dan\r\n• Sistem pengurusan akses automatik digunakan untuk mengawal dan mengesan perubahan akses pengguna, termasuk pengesahan semula akses pada selang masa yang ditetapkan; dan\r\n• Log akses disemak secara berkala untuk memastikan integriti dan pematuhan kepada polisi keselamatan siber.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA312', 'AE063', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Pengwujudan dan pengekalan akses logikal dioptimumkan dengan penggunaan teknologi automasi dan kecerdasan buatan (AI) untuk menyesuaikan akses berdasarkan keperluan risiko semasa; dan\r\n• Akses logikal dipantau secara real-time, dengan amaran automatik untuk sebarang aktiviti mencurigakan atau akses tidak sah; dan\r\n• Organisasi secara aktif menyesuaikan dasar pengurusan akses untuk menyokong persekitaran organisasi  yang berubah, dan akses logikal diperkemas selaras dengan amalan terbaik keselamatan siber global.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA313', 'AE064', 'AS001', '• Tiada proses formal untuk memeriksa, memantau, atau menyemak aktiviti capaian pengguna; dan\r\n• Akses pengguna kepada sistem tidak dipantau secara konsisten, menyebabkan risiko kebocoran maklumat atau penyalahgunaan akses; dan\r\n• Organisasi bergantung pada tindakan reaktif sahaja, hanya mengesan masalah selepas insiden keselamatan berlaku.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA314', 'AE064', 'AS002', '• Pemeriksaan dan pemantauan aktiviti capaian pengguna telah dimulakan, tetapi hanya dijalankan pada sistem atau aplikasi tertentu, terutamanya yang dianggap kritikal; dan\r\n• Terdapat log aktiviti pengguna yang dihasilkan, tetapi ia tidak disemak secara berkala dan tiada tindakan segera diambil berdasarkan aktiviti yang mencurigakan; dan\r\n• Prosedur penyemakan tidak dijalankan secara konsisten di seluruh organisasi.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA315', 'AE064', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Proses pemeriksaan, pemantauan, dan penyemakan aktiviti capaian pengguna telah ditetapkan secara formal dengan dokumentasi lengkap; dan\r\n• Pemantauan dijalankan secara berkala pada semua sistem penting, dan log aktiviti pengguna diperiksa untuk mengesan sebarang anomali atau aktiviti yang tidak dibenarkan; dan\r\n• Pengurusan dan audit secara dalaman dijalankan untuk memastikan tiada pelanggaran atau penyalahgunaan akses.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA316', 'AE064', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pemantauan aktiviti capaian pengguna dilakukan secara kuantitatif, dengan metrik dan data yang dikumpulkan untuk menilai keberkesanan sistem pemantauan; dan\r\n• Log aktiviti pengguna disemak secara automatik dengan sistem pengesanan anomali yang mampu memberikan amaran jika berlaku aktiviti luar biasa atau risiko keselamatan; dan\r\n• Audit berjadual dilakukan dengan kerap dan hasil audit digunakan untuk memperbaiki dasar keselamatan akses.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA317', 'AE064', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Pemantauan dan penyemakan aktiviti capaian pengguna adalah automatik sepenuhnya, dengan penggunaan teknologi canggih seperti kecerdasan buatan (AI) dan pembelajaran mesin (Machine Learning- ML) untuk mengenal pasti tingkah laku mencurigakan dalam masa nyata; dan\r\n• Sistem pemantauan aktif mampu mengenal pasti dan mencegah akses tidak sah sebelum kerosakan berlaku, dengan pembetulan automatik atau penutupan akses yang mencurigakan; dan\r\n• Penyemakan dan audit dijalankan secara berterusan, dengan proses keselamatan yang dioptimumkan untuk menyesuaikan dengan perubahan ancaman keselamatan siber dan persekitaran teknologi yang berkembang.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA318', 'AE065', 'AS001', '• Tiada proses formal untuk memastikan pihak ketiga mematuhi perjanjian keselamatan maklumat; dan\r\n• Pemantauan dan penyemakan pematuhan pihak ketiga tidak dilakukan atau hanya berlaku dalam situasi tertentu tanpa garis panduan yang jelas; dan\r\n• Organisasi bergantung pada tanggungjawab pihak ketiga tanpa kawalan dalaman untuk memastikan pematuhan yang berterusan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA319', 'AE065', 'AS002', '• Organisasi mula melaksanakan proses asas untuk memantau pematuhan pihak ketiga terhadap perjanjian keselamatan maklumat; dan\r\n• Penyemakan dilakukan secara berkala, tetapi ia terhad kepada dokumen perjanjian tanpa pemantauan aktif terhadap amalan keselamatan pihak ketiga; dan\r\n• Terdapat penambahbaikan dalam pemantauan, tetapi prosedur ini masih tidak konsisten dan bergantung pada situasi risiko.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA320', 'AE065', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Polisi dan prosedur yang jelas telah ditetapkan untuk memantau dan menyemak pematuhan pihak ketiga terhadap perjanjian keselamatan maklumat; dan\r\n• Penyemakan pematuhan pihak ketiga dilakukan secara berkala dengan audit dalaman atau eksternal yang memeriksa dokumentasi dan amalan keselamatan; dan\r\n• Organisasi memantau bahawa pihak ketiga mematuhi syarat-syarat keselamatan maklumat yang dipersetujui dalam kontrak dan perjanjian perkhidmatan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA321', 'AE065', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Proses pemantauan dan penyemakan pematuhan pihak ketiga dikawal secara kuantitatif dengan penggunaan metrik yang jelas untuk menilai tahap pematuhan; dan\r\n• Organisasi menggunakan alat teknologi untuk memantau pematuhan secara automatik dan mengesan sebarang penyelewengan atau ketidakpatuhan; dan\r\n• Penyemakan dilakukan berdasarkan risiko yang telah dinilai, dan audit berkala dijalankan dengan laporan terperinci untuk memastikan bahawa pihak ketiga terus mematuhi perjanjian.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA322', 'AE065', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Pemantauan dan penyemakan pematuhan pihak ketiga terhadap perjanjian keselamatan maklumat dijalankan secara proaktif dan automatik dengan teknologi canggih seperti AI untuk menilai risiko dalam masa nyata; dan\r\n• Proses pemantauan adalah berterusan, dengan tindak balas segera terhadap sebarang ketidakpatuhan yang dikesan melalui pemantauan yang berasaskan risiko; dan\r\n• Pihak ketiga diaudit secara berkala menggunakan standard  keselamatan maklumat yang diiktiraf di peringkat global, dan pematuhan mereka disahkan melalui proses audit dalaman dan eksternal.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA323', 'AE035', 'AS001', '• Tiada proses formal untuk menilai keberkesanan pihak ketiga dalam menyediakan perkhidmatan atau memenuhi keperluan keselamatan maklumat; dan\r\n• Penilaian keberkesanan dilakukan secara tidak berkala, atau hanya apabila berlaku masalah atau insiden; dan\r\n• Pihak ketiga dipilih dan diuruskan tanpa mekanisme penilaian yang jelas untuk menilai prestasi keseluruhan atau risiko yang berkaitan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA324', 'AE035', 'AS002', '• Terdapat usaha asas untuk menilai keberkesanan pihak ketiga, tetapi penilaian ini dilakukan secara tidak konsisten dan terhad kepada aspek tertentu; dan\r\n• Penilaian prestasi  terhad kepada pematuhan kontrak tanpa penilaian menyeluruh terhadap kesesuaian amalan keselamatan atau perkhidmatan yang disediakan; dan\r\n• Penilaian dilakukan berdasarkan kejadian atau apabila diperlukan sahaja, dengan tumpuan pada aspek operasi.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA325', 'AE035', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Organisasi telah menubuhkan dan mendokumentasikan proses untuk memastikan pematuhan pihak ketiga terhadap perjanjian yang diperakui; dan\r\n• Prosedur sistematik disediakan untuk memantau dan menguatkuasakan pematuhan; dan\r\n• Perjanjian yang diperakui termasuk keperluan pematuhan yang jelas dan ditakrifkan; dan\r\n• Kakitangan  yang bertanggungjawab untuk pengawasan pematuhan dilatih dan mengetahui prosedur ini.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA326', 'AE035', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Organisasi mengaudit pihak ketiga untuk memastikan pematuhan dengan perjanjian yang diperakui; dan\r\n• Penggunaan metrik dan petunjuk prestasi utama (KPI) untuk mengukur tahap pematuhan; dan\r\n• Pemantauan pengurusan yang berterusan memastikan pematuhan pihak ketiga dikekalkan; dan\r\n• Pemantauan pematuhan diintegrasikan dengan pengurusan risiko organisasi dan rangka kerja pematuhan yang lebih luas.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA327', 'AE035', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Organisasi terus meningkatkan proses pematuhan pihak ketiga melalui inovasi dan maklum balas; dan\r\n• Proses pematuhan pihak ketiga diselaraskan dengan perancangan strategik dan hasil jangka panjang; dan\r\n• Organisasi menggunakan kaedah terkini untuk meningkatkan pemantauan pematuhan; dan\r\n• Organisasi secara proaktif menyesuaikan proses pematuhannya kepada risiko baru muncul dan amalan terbaik industri.', '4', '2025-12-24', NULL, NULL, 'Active');
INSERT INTO `score_element` (`se_ID`, `element_ID`, `score_ID`, `details`, `input_id`, `input_at`, `updated_id`, `updated_at`, `status`) VALUES
('ASA328', 'AE066', 'AS001', '• Tiada proses atau struktur formal untuk memantau atau menyemak penilaian keberkesanan pihak ketiga; dan\r\n• Penilaian dilakukan hanya apabila terdapat masalah atau insiden yang berkaitan dengan perkhidmatan pihak ketiga, tanpa pemantauan proaktif; dan\r\n• Organisasi kurang menyedari kepentingan pemantauan secara berkala terhadap keberkesanan pihak ketiga.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA329', 'AE066', 'AS002', '• Terdapat usaha untuk melaksanakan pemantauan dan penyemakan keberkesanan pihak ketiga, tetapi ia dilakukan secara tidak berkala atau tidak menyeluruh; dan\r\n• Penilaian hanya dilakukan dalam keadaan tertentu atau pada waktu yang tidak tetap, menyebabkan kurangnya konsistensi dalam mengesan risiko atau masalah; dan\r\n• Tindakan susulan dari penilaian tidak didokumentasikan atau dipantau secara sistematik.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA330', 'AE066', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Proses formal untuk pemantauan dan penyemakan keberkesanan pihak ketiga telah didokumentasikan dan dijalankan secara berkala; dan\r\n• Penilaian keberkesanan pihak ketiga dilakukan mengikut jadual yang jelas, dengan prosedur dan metrik prestasi yang spesifik; dan\r\n• Proses ini merangkumi pemeriksaan terhadap pematuhan perjanjian, prestasi keselamatan, dan pengurusan risiko pihak ketiga, dengan tindakan pembetulan diambil berdasarkan hasil penilaian.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA331', 'AE066', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pemantauan dan penyemakan keberkesanan pihak ketiga dilakukan berdasarkan analisis kuantitatif, menggunakan data dan metrik prestasi yang diukur secara objektif; dan\r\n• Penilaian dilakukan secara berkala dengan sokongan teknologi untuk mengesan penyelewengan atau masalah dalam masa nyata; dan\r\n• Keberkesanan pihak ketiga dinilai berdasarkan risiko yang dinamik, dengan audit dan laporan prestasi yang digunakan untuk memperbaiki atau menguruskan hubungan dengan pihak ketiga.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA332', 'AE066', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Pemantauan dan penyemakan keberkesanan pihak ketiga sepenuhnya automatik, dengan penggunaan teknologi AI dan pembelajaran mesin untuk menilai prestasi pihak ketiga secara berterusan; dan\r\n• Organisasi mengadaptasi proses penilaian mengikut perubahan dalam keperluan organisasi , risiko keselamatan, dan perubahan dalam landskap pihak ketiga; dan\r\n• Penilaian keberkesanan bukan sahaja melibatkan pematuhan terhadap perjanjian, tetapi juga menggalakkan peningkatan berterusan dalam prestasi pihak ketiga melalui kolaborasi dan perkongsian maklumat.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA333', 'AE067', 'AS001', '• Organisasi tidak mempunyai akses tetap kepada pakar bidang atau kumpulan pakar untuk memberi khidmat rundingan; dan\r\n• Penglibatan pakar hanya dilakukan berdasarkan keperluan mendesak atau apabila timbul masalah tertentu; dan\r\n• Keputusan biasanya dibuat tanpa rujukan atau nasihat daripada pakar bidang yang berpengalaman.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA334', 'AE067', 'AS002', '• Organisasi mula melibatkan pakar bidang atau kumpulan pakar dalam beberapa aspek operasi, tetapi penglibatan mereka adalah terhad kepada bidang tertentu sahaja; dan\r\n• Khidmat rundingan diberikan berdasarkan permintaan, tanpa ada proses formal untuk mendapatkan maklum balas secara berkala; dan\r\n• Pakar bidang hanya dirujuk untuk projek atau isu yang dianggap kritikal, dan bukan sebagai sebahagian daripada strategi menyeluruh.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA335', 'AE067', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Terdapat proses yang ditetapkan untuk melibatkan pakar bidang atau kumpulan pakar dalam keputusan strategik dan operasi; dan\r\n• Maklumat dan khidmat runding daripada pakar digunakan secara aktif untuk menyokong perancangan, pemantauan, dan peningkatan kualiti perkhidmatan atau produk; dan\r\n• Pakar berkongsi kepakaran mereka secara berkala, membantu dalam menilai risiko, peluang, dan trend terkini yang relevan dengan organisasi .', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA336', 'AE067', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Penglibatan pakar bidang atau kumpulan pakar adalah berstruktur dan diukur dengan metrik tertentu untuk menilai keberkesanan sumbangan mereka; dan\r\n• Pakar bidang memberikan maklumat yang penting untuk meningkatkan daya saing dan mengurangkan risiko operasi; dan\r\n• Khidmat rundingan ini diselaraskan dengan matlamat strategik organisasi, memastikan pengurusan risiko dan peluang dilakukan secara efisien.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA337', 'AE067', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Pakar bidang atau kumpulan pakar terlibat secara aktif dan berterusan dalam semua proses pengurusan dan pengambilan keputusan, menggunakan teknologi canggih untuk meningkatkan penyampaian maklumat dan khidmat runding; dan\r\n• Organisasi mengintegrasikan kepakaran pakar ke dalam strategi menyeluruh, memastikan penyelarasan dengan perubahan teknologi, undang-undang, dan keperluan organisasi  global; dan\r\n• Sumbangan pakar dinilai secara berterusan dan digabungkan dengan analitik data untuk memastikan keputusan yang diambil adalah berdasarkan fakta dan maklumat yang terkini serta tepat.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA338', 'AE038', 'AS001', '• Tiada proses formal atau dokumentasi untuk mengurus konfigurasi dan aset IT; dan\r\n• Konfigurasi aset IT dilakukan secara ad-hoc, tanpa perancangan atau panduan keselamatan yang jelas; dan\r\n• Risiko keselamatan yang berkaitan dengan aset IT tidak dikenalpasti atau diurus secara berkesan, menyebabkan potensi ancaman yang tinggi terhadap sistem dan data.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA339', 'AE038', 'AS002', '• Proses asas untuk konfigurasi dan pengurusan aset IT telah diwujudkan, tetapi masih kurang teratur dan bergantung kepada tindakan manual; dan\r\n• Pengurusan aset IT dijalankan, tetapi prosedur keselamatan tidak diikuti secara konsisten di seluruh organisasi; dan\r\n• Terdapat usaha untuk memastikan konfigurasi yang selamat, tetapi pelaksanaan dan pengurusan perubahan aset adalah tidak konsisten atau terhad kepada sistem kritikal sahaja.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA340', 'AE038', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Proses dan prosedur formal telah diwujudkan untuk mengurus konfigurasi dan aset IT dengan fokus pada keselamatan; dan\r\n• Semua aset IT didaftarkan dan dikonfigurasikan dengan dasar keselamatan yang ditetapkan, termasuk kawalan akses dan pengesahan; dan\r\n• Proses ini melibatkan pengurusan perubahan konfigurasi aset IT dengan dokumentasi dan pengesahan untuk meminimakan risiko pelanggaran keselamatan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA341', 'AE038', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Konfigurasi dan pengurusan aset IT dijalankan dengan sokongan metrik dan analisis risiko yang jelas, membolehkan organisasi mengukur dan memantau keberkesanan proses pengurusan keselamatan; dan\r\n• Pengurusan perubahan dan kawalan konfigurasi dijalankan secara berterusan, dengan pengesahan keselamatan secara berkala; dan\r\n• Alat automasi digunakan untuk mengurus dan memantau aset IT, termasuk pelaksanaan patch keselamatan dan pengesanan konfigurasi yang tidak selamat.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA342', 'AE038', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Proses pengurusan aset IT sepenuhnya automatik, dengan penggunaan teknologi seperti AI dan pembelajaran mesin untuk mengesan ancaman dan memastikan konfigurasi yang selamat; dan\r\n• Pengurusan konfigurasi aset IT dilakukan secara proaktif, dengan analisis risiko real-time untuk mengenal pasti dan menangani kelemahan keselamatan dengan cepat.\r\n• Organisasi sentiasa menilai dan mengemas kini konfigurasi aset IT berdasarkan perubahan dalam landskap ancaman siber dan keperluan organisasi.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA343', 'AE037', 'AS001', '• Tiada proses formal untuk mengenal pasti dan  memilih penyelesaian keselamatan siber yang sesuai; dan\r\n• Pemilihan penyelesaian keselamatan siber dilakukan secara ad-hoc atau reaktif, hanya apabila terdapat insiden keselamatan; dan\r\n• Organisasi bergantung kepada penyelesaian keselamatan siber  yang asas, tanpa rujukan kepada standard  dan amalan terbaik dalam industri.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA344', 'AE037', 'AS002', '• Terdapat proses asas untuk mengenal pasti penyelesaian keselamatan siber, tetapi ia masih tidak sepenuhnya teratur atau komprehensif; dan\r\n• Organisasi mula menyedari kepentingan standard  keselamatan siber seperti ISO atau NIST, namun pelaksanaan standard  ini tidak menyeluruh; dan\r\n• Pemilihan penyelesaian keselamatan siber  dilakukan berdasarkan keperluan tertentu, namun tidak merangkumi semua komponen infrastruktur, sistem, dan aplikasi.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA345', 'AE037', 'AS003', '• Proses dan telah lengkap Tahap 2; dan\r\n• Proses formal untuk mengenal pasti penyelesaian keselamatan siber  telah didokumentasikan dan diterapkan di seluruh organisasi; dan\r\n• Penyelesaian keselamatan siber  dipilih berdasarkan standard  yang ditetapkan, serta penilaian risiko yang menyeluruh terhadap ancaman terhadap infrastruktur dan sistem; dan\r\n• Penyelesaian yang dipilih selaras dengan amalan terbaik industri, dan proses pemilihan melibatkan kajian terhadap keberkesanan teknologi terkini.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA346', 'AE037', 'AS004', '• Proses dan telah lengkap Tahap 3; dan\r\n• Proses pemilihan penyelesaian keselamatan siber dilakukan secara kuantitatif, di mana metrik digunakan untuk mengukur keberkesanan terhadap ancaman yang dikenalpasti; dan\r\n• Sistem automasi digunakan untuk menilai prestasi penyelesaian keselamatan siber yang sedia ada, serta untuk mengenal pasti penyelesaian baru yang lebih efektif berdasarkan standard  industri; dan\r\n• Organisasi secara berkala menyemak dan mengemas kini penyelesaian keselamatan siber  bagi memastikan ia masih relevan dengan perubahan dalam landskap ancaman.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA347', 'AE037', 'AS005', '• Proses dan telah lengkap Tahap 4; dan\r\n• Pemilihan penyelesaian keselamatan siber dilakukan secara proaktif, dengan penggunaan teknologi seperti AI dan pembelajaran mesin untuk mengenal pasti ancaman baru dan mencadangkan penyelesaian keselamatan yang paling sesuai; dan\r\n• Organisasi mengamalkan pendekatan berasaskan risiko diintegrasikan dengan strategi keselamatan siber dan organisasi   yang berterusan dengan merujuk kepada standard terkini; dan\r\n• Penyelesaian keselamatan siber yang digunakan sentiasa dioptimumkan, dengan proses yang automatik dan penilaian berterusan terhadap keberkesanan penyelesaian yang dipilih.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA348', 'AE040', 'AS001', '• Tiada prosedur formal untuk mengemas kini perisian dan perkakasan; dan\r\n• Kemas kini dilakukan secara ad-hoc atau hanya apabila berlaku masalah teknikal atau ancaman keselamatan; dan\r\n• Organisasi bergantung kepada pengguna akhir atau pentadbir untuk melakukan kemas kini secara manual, yang menyebabkan kelewatan dalam pemakaian patch atau kemas kini penting.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA349', 'AE040', 'AS002', '• Kemas kini perisian dan perkakasan dilakukan secara berkala tetapi tidak menyeluruh atau tepat pada masanya; dan\r\n• Proses kemas kini adalah manual dan dilakukan berdasarkan keperluan yang dikenalpasti, tetapi tanpa jadual kemas kini yang jelas; dan\r\n• Organisasi mula memperkenalkan dasar dan prosedur asas untuk mengurus kemas kini keselamatan, tetapi pelaksanaan tidak konsisten di seluruh infrastruktur IT.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA350', 'AE040', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Proses formal untuk memastikan perisian dan perkakasan sentiasa dikemas kini telah diwujudkan, termasuk jadual kemas kini yang berkala; dan\r\n• Alat dan sistem automasi diperkenalkan untuk membantu mengenal pasti kemas kini dan memudahkan pemasangan patch keselamatan; dan\r\n• Kemas kini dilakukan mengikut prosedur yang ditetapkan, dan semua peranti serta aplikasi penting disertakan dalam program pengurusan kemas kini.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA351', 'AE040', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Kemas kini perisian dan perkakasan diuruskan secara kuantitatif, dengan metrik yang jelas untuk mengukur keberkesanan dan ketepatan masa kemas kini; dan\r\n• Sistem pemantauan digunakan untuk mengenal pasti kelemahan atau perisian yang tidak dikemas kini dan menyediakan amaran automatik; dan\r\n• Kemas kini dilakukan berdasarkan keutamaan risiko, dengan tumpuan pada perlindungan keselamatan siber dan memastikan semua perisian kritikal sentiasa terkini.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA352', 'AE040', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Organisasi terus kemas kini perisian dan perkakasan dilakukan secara automatik dan berterusan dengan penggunaan teknologi AI dan pembelajaran mesin untuk mengesan kelemahan serta mengurus proses kemas kini secara proaktif; dan\r\n• Semua peranti, perisian, dan sistem di organisasi dikemas kini secara real-time, tanpa campur tangan manual, untuk mengurangkan risiko keselamatan; dan\r\n• Organisasi menggunakan pendekatan zero-day patching untuk memastikan bahawa sebarang kelemahan keselamatan segera ditangani sebaik sahaja ia dikenalpasti.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA353', 'AE068', 'AS001', '• Tiada penggunaan teknologi yang terancang dan tiada standard yang digunakan sebagai rujukan; dan\r\n• Sistem dan aplikasi dibangunkan atau diperoleh tanpa mengikut standard  atau amalan terbaik dalam industri; dan\r\n• Tiada proses formal untuk memastikan teknologi yang digunakan relevan dengan keperluan semasa.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA354', 'AE068', 'AS002', '• Penggunaan teknologi mula diuruskan secara lebih sistematik, tetapi masih terhad kepada projek atau keperluan tertentu; dan\r\n• Organisasi mula menyedari pentingnya penggunaan teknologi terkini tetapi masih bergantung kepada standard lama atau secara manual; dan\r\n• Terdapat usaha untuk memperkenalkan standard, tetapi pelaksanaan tidak konsisten di seluruh organisasi.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA355', 'AE068', 'AS003', '• Proses dan telah lengkap Tahap 2; dan\r\n• Prosedur untuk penggunaan teknologi berdasarkan standard  terkini telah didokumentasikan dan dilaksanakan di seluruh organisasi; dan\r\n• Semua sistem dan aplikasi baru dibangunkan atau diperoleh dengan mematuhi standard  industri yang diiktiraf, seperti ISO atau NIST; dan\r\n• Teknologi yang digunakan diselaraskan dengan amalan terbaik dalam industri dan proses penilaian berkala dilakukan untuk memastikan standard  dikekalkan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA356', 'AE068', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Penggunaan teknologi disokong oleh analisis kuantitatif, di mana pematuhan terhadap standard  terkini diukur dan dipantau secara berkala; dan\r\n• Sistem pemantauan automatik digunakan untuk mengesan sebarang penyimpangan daripada standard  yang ditetapkan; dan\r\n• Organisasi menggunakan alat dan teknologi yang dioptimumkan untuk memastikan bahawa semua aplikasi dan sistem memenuhi standard terkini dan keperluan keselamatan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA357', 'AE068', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Penggunaan teknologi bersepadu sepenuhnya dengan amalan terbaik dan standard  terkini, dengan automasi yang membolehkan pemantauan dan pengemaskinian secara berterusan; dan\r\n• Sistem dan aplikasi sentiasa ditingkatkan untuk mematuhi perubahan standard  teknologi dan keperluan peraturan; dan\r\n• Organisasi menerapkan teknologi terkini, seperti AI, blockchain, atau cloud computing, selaras dengan standard  antarabangsa terkini, untuk meningkatkan prestasi, keselamatan, dan kecekapan operasi.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA358', 'AE041', 'AS001', '• Tiada proses formal untuk menilai dan mengkaji penggunaan teknologi terkini dalam organisasi; dan\r\n• Organisasi menggunakan teknologi berdasarkan keperluan segera tanpa mempertimbangkan keberkesanan atau relevansi teknologi terkini; \r\n• Kajian terhadap teknologi baru dilakukan secara ad hoc;\r\n• Kajian hanya dilakukan apabila berlaku masalah yang memerlukan penyelesaian segera.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA359', 'AE041', 'AS002', '• Proses untuk mengkaji teknologi baru mula diperkenalkan, tetapi masih dalam peringkat awal dan terhad kepada beberapa bahagian atau jabatan; dan\r\n• Kajian dilakukan berdasarkan keperluan projek tertentu;\r\n• Kajian dilakukan apabila terdapat tekanan dari pihak pengurusan bagi meningkatkan kecekapan operasi; dan\r\n• Penilaian teknologi yang terkini tidak konsisten atau sistematik.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA360', 'AE041', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Terdapat proses yang jelas dan formal untuk mengkaji dan menilai penggunaan teknologi terkini di seluruh organisasi; dan\r\n• Penilaian dilakukan secara berkala dan melibatkan kajian menyeluruh terhadap tren teknologi semasa, termasuk potensi risiko dan faedah; dan\r\n• Organisasi mempunyai jawatankuasa yang didedikasikan untuk mengenal pasti dan menilai teknologi baru sebelum membuat keputusan bajet.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA361', 'AE041', 'AS004', '• Proses dan Telah lengkap Tahap 3; dan\r\n• Proses kajian dan penilaian teknologi terkini dilakukan secara kuantitatif, menggunakan metrik untuk mengukur impak teknologi terhadap produktiviti, keselamatan, dan keuntungan; dan\r\n• Kajian teknologi didorong oleh analisis data, dan keputusan dibuat berdasarkan bukti yang jelas; dan\r\n• Penggunaan teknologi baru diukur dari segi prestasi dan penyelarasan dengan objektif strategik organisasi.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA362', 'AE041', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Organisasi mengamalkan pendekatan proaktif dalam mengkaji dan menilai teknologi terkini, menggunakan alat canggih seperti AI dan pembelajaran mesin untuk meramalkan tren teknologi masa depan; dan\r\n• Proses ini bersepadu sepenuhnya dengan strategi organisasi , dan keputusan untuk melabur dalam teknologi baru didorong oleh analisis kos manfaat yang komprehensif; dan\r\n• Organisasi sentiasa berada di barisan hadapan dalam menerima-guna teknologi terkini untuk memacu inovasi dan kelebuhan daya saing, memastikan setiap penggunaan teknologi baru memberikan nilai tambah yang optimum.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA363', 'AE039', 'AS001', '• Tiada proses pemantauan yang berterusan terhadap aktiviti dan tingkah laku rangkaian serta infrastruktur sistem; dan\r\n• Pemantauan hanya dilakukan secara ad-hoc atau apabila berlaku insiden keselamatan; dan\r\n• Terdapat sedikit atau tiada alat teknologi yang digunakan untuk memantau rangkaian secara proaktif, menyebabkan kelemahan dalam mengenal pasti ancaman siber.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA364', 'AE039', 'AS002', '• Proses pemantauan asas telah dilaksanakan, tetapi masih terhad kepada aktiviti kritikal tertentu atau bergantung kepada tindakan manual; dan\r\n• Penggunaan alat pemantauan mula diperkenalkan untuk mengesan aktiviti yang mencurigakan, tetapi liputan tidak menyeluruh di seluruh rangkaian dan infrastruktur; dan\r\n• Pemantauan dilakukan secara berkala, tetapi respons terhadap insiden keselamatan lambat atau tidak konsisten.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA365', 'AE039', 'AS003', '• Telah lengkap Tahap 2; dan\r\n• Proses formal untuk pemantauan rangkaian dan infrastruktur sistem secara berterusan telah diwujudkan; dan\r\n• Alat pemantauan yang lebih komprehensif digunakan untuk memantau aktiviti rangkaian, mengenal pasti anomali, dan mengesan percubaan serangan; dan\r\n• Pemantauan dijalankan secara real-time, dengan sistem yang dapat memberi amaran tentang sebarang aktiviti yang mencurigakan atau penyimpangan daripada tingkah laku biasa.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA366', 'AE039', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Pemantauan rangkaian dan sistem dilakukan secara kuantitatif dengan analisis tingkah laku yang menggunakan data historikal untuk mengenal pasti corak ancaman yang lebih halus; dan\r\n• Proses pemantauan menggunakan teknologi automasi dan alat pengurusan insiden yang dapat memberikan respons yang cepat terhadap ancaman yang dikesan; dan\r\n• Penggunaan metrik keselamatan dan pemantauan berasaskan risiko membantu dalam membuat keputusan yang lebih tepat dan meningkatkan keberkesanan pertahanan siber.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA367', 'AE039', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• Pemantauan aktiviti dan tingkah laku rangkaian serta sistem dijalankan secara automatik dan berterusan dengan bantuan teknologi AI dan Machine learning untuk mengesan ancaman dalam masa nyata; dan\r\n• Sistem pemantauan sentiasa ditingkatkan dan dikonfigurasi untuk mengenal pasti ancaman siber baru dan tingkah laku rangkaian yang tidak normal sebelum ia dapat menyebabkan kerosakan; dan\r\n• Proses pemantauan adalah proaktif, dengan pendekatan \"threat hunting\" yang mengesan ancaman yang belum diketahui berdasarkan analisis tingkah laku dan anomali sistem.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA368', 'AE043', 'AS001', '• Tiada penggunaan teknologi terkini yang signifikan dalam pelaksanaan dan pemantauan keselamatan siber; dan\r\n• Organisasi bergantung kepada teknologi keselamatan siber yang ketinggalan zaman atau tidak menggunakan teknologi automatik sama sekali; dan\r\n• Pemantauan keselamatan dilakukan secara manual dan terhad kepada langkah-langkah asas, seperti kata laluan dan antivirus yang standard.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA369', 'AE043', 'AS002', '• Teknologi keselamatan siber mula diperkenalkan, seperti firewall, antivirus, dan alat pemantauan rangkaian asas; dan\r\n• Walaupun beberapa proses pemantauan telah diotomatisasikan, penggunaan teknologi masih terhad kepada fungsi yang terasing, tanpa penyepaduan sepenuhnya dalam keseluruhan ekosistem keselamatan; dan\r\n• Organisasi menyedari keperluan untuk meningkatkan teknologi keselamatan siber, tetapi pelaksanaannya masih terhad.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA370', 'AE043', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Penggunaan teknologi terkini diterapkan secara formal dalam pelaksanaan dan pemantauan keselamatan siber, dengan penyepaduan sistem yang lebih baik; dan\r\n• Alat seperti SIEM (Security Information and Event Management) dan IDS/IPS (Intrusion Detection/Prevention Systems) telah diperkenalkan untuk pemantauan ancaman yang lebih efisien; dan\r\n• Organisasi mengikut standard  keselamatan terkini seperti ISO 27001 atau NIST dan memastikan penggunaan teknologi selaras dengan amalan terbaik.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA371', 'AE043', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Teknologi terkini digunakan secara menyeluruh dan pemantauan keselamatan dilakukan secara automatik dengan alat yang mengesan ancaman dalam masa nyata; dan\r\n• Penggunaan teknologi seperti AI, pembelajaran mesin, dan analisis tingkah laku diterapkan untuk mengesan anomali atau ancaman yang lebih kompleks; dan\r\n• Organisasi mampu mengukur keberkesanan penggunaan teknologi keselamatan siber melalui metrik prestasi yang jelas, dan proses penambahbaikan diterapkan berdasarkan data yang dikumpulkan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA372', 'AE043', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Organisasi menggunakan teknologi terkini secara proaktif, dengan pemantauan keselamatan yang berterusan melalui penggunaan alat automatik yang sangat canggih; dan\r\n• Alat seperti blockchain, kecerdasan buatan (Artificial Intellignece- AI), dan pembelajaran mesin diintegrasikan sepenuhnya dalam rangkaian keselamatan siber untuk ramalan, pengesanan awal, dan respons automatik terhadap ancaman; dan\r\n• Organisasi sentiasa mengikuti perkembangan terkini dalam teknologi keselamatan dan menerapkan pendekatan bersepadu serta holistik untuk meminimakan risiko keselamatan siber.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA373', 'AE042', 'AS001', '• Tiada SOC yang khusus untuk pemantauan dan pengesanan ancaman siber; dan\r\n• Pemantauan keselamatan dijalankan secara ad-hoc atau berdasarkan keperluan tertentu sahaja, tanpa menggunakan alat automatik atau berterusan; dan\r\n• Ancaman siber sering kali dikesan hanya selepas insiden berlaku, menyebabkan organisasi lambat bertindak balas terhadap ancaman.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA374', 'AE042', 'AS002', '• Organisasi mula mempunyai kapasiti asas SOC, tetapi ia masih terhad kepada pengesanan ancaman asas melalui alat manual atau teknologi sederhana; dan\r\n• SOC mula menggunakan beberapa alat pemantauan automatik, tetapi belum menyeluruh untuk semua aspek rangkaian, sistem, dan aplikasi; dan\r\n• Ancaman keselamatan dikesan lebih awal berbanding sebelumnya, namun proses pemantauan dan respons ancaman masih bergantung pada intervensi manusia.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA375', 'AE042', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• SOC telah diwujudkan dengan prosedur operasi standard yang jelas, membolehkan pemantauan dan pengesanan ancaman siber dilakukan secara konsisten; dan\r\n• Alat dan teknologi pemantauan automatik digunakan secara meluas untuk mengesan ancaman dan kejadian keselamatan dalam masa nyata; dan\r\n• SOC mula menerapkan standard  industri seperti NIST (National Institute Standard of Technology), ISO (Internasional Standard Organisation), atau CIS (Control Internet Security) bagi mematuhi amalan terbaik dalam pengurusan keselamatan siber.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA376', 'AE042', 'AS004', '• Telah lengkap Tahap 3; dan\r\n• Proses pemantauan ancaman siber dilakukan secara kuantitatif dengan penggunaan metrik untuk mengukur keberkesanan sistem pengesanan; dan\r\n• SOC menggabungkan penggunaan alat canggih seperti SIEM (Security Information and Event Management) yang membolehkan pengesanan ancaman lebih mendalam dan analisis tingkah laku anomali; dan\r\n• Respons terhadap ancaman lebih pantas, dan SOC beroperasi secara bersepadu dengan pelan pemulihan bencana dan pengurusan risiko keselamatan siber.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA377', 'AE042', 'AS005', '• Telah lengkap Tahap 4; dan\r\n• SOC berfungsi pada tahap optimum dengan penggunaan teknologi automatik canggih, seperti AI dan pembelajaran mesin, untuk proaktif dalam pengesanan dan pengurusan ancaman siber; dan\r\n• Pemantauan dilakukan secara berterusan dan dalam masa nyata, dengan pengesanan ancaman yang lebih awal dan respons automatik untuk mitigasi segera; dan\r\n• SOC mengamalkan pendekatan proaktif seperti \"threat hunting\" dan analisis tingkah laku yang dapat meramalkan dan menghalang serangan sebelum ia berlaku, sejajar dengan standard  keselamatan terkini dan keperluan peraturan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA378', 'AE069', 'AS001', '• Tiada proses formal untuk penilaian atau pemantauan keselamatan siber; dan\r\n• Penilaian operasi keselamatan siberdilakukan secara ad-hoc atau hanya selepas berlaku insiden keselamatan; dan\r\n• Tiada sistem pemantauan berterusan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA379', 'AE069', 'AS002', '• Penilaian keselamatan siber mula dilaksanakan secara berkala, tetapi masih bersifat reaktif; dan\r\n• Pemantauan terhadap operasi keselamatan dilakukan menggunakan alat asas seperti log sistem dan audit dalaman yang tidak berterusan; dan\r\n• Pelaksanaan penilaian dan pemantauan berkala bagi operasi keselamatan siber belum menyeluruh atau teratur.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA380', 'AE069', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Penilaian dan pemantauan keselamatan siber dilaksanakan secara sistematik mengikut jadual yang tetap; dan\r\n• Organisasi mengamalkan standard  keselamatan siber yang diiktiraf, seperti ISO 27001, dan proses pemantauan dijalankan mengikut prosedur yang telah ditetapkan; dan\r\n• Penggunaan teknologi pemantauan secara automatik untuk mengesan anomali atau ancaman yang berpotensi diperkenalkan, dan penilaian prestasi keselamatan dilakukan secara lebih mendalam.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA381', 'AE069', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Proses penilaian dan pemantauan keselamatan dijalankan menggunakan metrik yang jelas untuk menilai keberkesanan operasi keselamatan siber; dan\r\n• Organisasi menggunakan alat automatik seperti SIEM (Security Information and Event Management) untuk pemantauan berterusan, dan ancaman dianalisis dalam masa nyata; dan\r\n• Penilaian keberkesanan pemantauan dijalankan dengan menggunakan data yang dikumpulkan dari sistem untuk memastikan peningkatan berterusan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA382', 'AE069', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Organisasi mempunyai proses penilaian dan pemantauan keselamatan siber yang dioptimumkan sepenuhnya dengan penggunaan teknologi canggih seperti AI dan pembelajaran mesin; dan\r\n• Pemantauan dijalankan secara proaktif, dengan pengesanan ancaman yang cepat dan automatik serta sistem tindak balas pantas terhadap sebarang insiden keselamatan; dan\r\n• Penilaian berkala dijalankan untuk memastikan pematuhan dengan standard  keselamatan siber terkini dan amalan terbaik global, serta untuk mengesan keperluan peningkatan secara berterusan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA383', 'AE044', 'AS001', '• Tiada pelan tindak balas insiden yang formal atau terdokumentasi. Respons terhadap insiden keselamatan siber dilakukan secara ad-hoc; dan\r\n• Apabila berlaku insiden keselamatan, tindakan diambil secara spontan, tanpa arahan atau prosedur yang jelas; dan\r\n• Tiada penilaian atau latihan khusus yang berkaitan dengan pengurusan insiden keselamatan siber.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA384', 'AE044', 'AS002', '• Pelan tindak balas insiden mula diwujudkan, tetapi pelaksanaannya masih terhad dan tidak diterapkan secara konsisten; dan\r\n• Prosedur asas untuk mengenal pasti, melaporkan, dan menangani insiden keselamatan telah tersedia, namun tindak balas masih bergantung kepada individu tertentu; dan\r\n• Pelan ini tidak diuji secara berkala, dan kakitangan kurang latihan dalam melaksanakan pelan tersebut.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA385', 'AE044', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Pelan tindak balas insiden yang terdokumentasi sepenuhnya diterapkan dengan jelas, mengandungi peranan dan tanggungjawab setiap individu atau jabatan dalam menangani insiden keselamatan; dan\r\n• Organisasi mengadakan latihan berkala untuk menguji keberkesanan pelan tindak balas insiden; dan\r\n• Pelan ini merangkumi langkah-langkah untuk mengenal pasti, mengesahkan, menanggapi, dan memulihkan operasi selepas insiden keselamatan berlaku.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA386', 'AE044', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Proses tindak balas insiden dijalankan secara terukur dengan penggunaan metrik untuk menilai keberkesanan tindak balas terhadap insiden keselamatan siber; dan\r\n• Latihan simulasi insiden diadakan secara berkala untuk menguji pelan tindak balas insiden dan memastikan kakitangan bersedia menghadapi ancaman sebenar; dan\r\n• Tindakan pembaikan selepas insiden dikenalpasti dan diterapkan untuk mengelakkan insiden serupa daripada berulang.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA387', 'AE044', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Pelan tindak balas insiden berfungsi secara optimum, dengan proses automasi tindak balas terhadap insiden keselamatan menggunakan teknologi terkini seperti AI dan pembelajaran mesin; dan\r\n• Respons terhadap insiden keselamatan siber berlaku dengan segera dan selaras dengan standard  keselamatan global, seperti NIST atau ISO 27035; dan\r\n• Pelan ini dikaji dan dikemas kini secara berterusan berdasarkan pengalaman insiden terdahulu dan perkembangan terkini dalam teknologi keselamatan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA388', 'AE045', 'AS001', '• Tiada takrifan jelas mengenai peranan dan tanggungjawab dalam pasukan tindak balas insiden; dan\r\n• Tindak balas terhadap insiden siber dilakukan secara ad-hoc dengan kakitangan bertindak tanpa peranan atau tugas yang jelas; dan\r\n• Tiada struktur organisasi untuk mengurus tindak balas insiden, menyebabkan kekeliruan ketika menghadapi insiden keselamatan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA389', 'AE045', 'AS002', '• Peranan dan tanggungjawab mula ditakrifkan dalam pasukan tindak balas insiden, tetapi dokumen tidak lengkap atau tidak konsisten; dan\r\n• Ahli pasukan mengetahui peranan mereka, tetapi belum ada latihan berstruktur untuk memastikan pemahaman menyeluruh; dan\r\n• Proses ini mula diterapkan, namun pelaksanaannya belum menyeluruh atau sepenuhnya diikuti semasa insiden sebenar.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA390', 'AE045', 'AS003', '•	Telah lengkap Tahap 2; dan \r\n•	Peranan dan tanggungjawab ahli pasukan tindak balas insiden ditakrifkan dengan jelas dalam dokumentasi rasmi; dan\r\n•	Terdapat struktur organisasi yang jelas yang menyenaraikan setiap peranan ahli pasukan serta tanggungjawab yang perlu dilaksanakan semasa insiden berlaku; dan\r\n•	Latihan dan simulasi dilakukan secara berkala untuk memastikan semua ahli memahami dan boleh melaksanakan tugas mereka dengan baik.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA391', 'AE045', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Peranan dan tanggungjawab pasukan tindak balas insiden bukan sahaja ditakrifkan, tetapi juga dinilai secara berkala berdasarkan metrik prestasi; dan\r\n• Proses penilaian ini digunakan untuk mengenal pasti kelemahan dan penambahbaikan yang boleh dilakukan untuk mengoptimumkan keberkesanan pasukan tindak balas.\r\n• Struktur organisasi pasukan disemak dan dikemas kini secara berkala berdasarkan pengalaman daripada insiden-insiden terdahulu.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA392', 'AE045', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Peranan dan tanggungjawab dalam pasukan tindak balas insiden sentiasa dioptimumkan dan disesuaikan berdasarkan perubahan landskap ancaman keselamatan siber; dan\r\n• Penggunaan teknologi canggih seperti AI dan automasi untuk menyokong tindak balas insiden serta mempercepat proses pengesanan dan mitigasi ancaman; dan\r\n• Pelan tindak balas insiden dikaji dan diubah suai secara berterusan, dengan input daripada pengalaman terdahulu dan ujian simulasi untuk memastikan peranan dan tanggungjawab ahli pasukan selaras dengan keperluan semasa.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA393', 'AE047', 'AS001', '• Tiada proses formal untuk menyiasat insiden keselamatan atau amaran yang diterima; dan\r\n• Insiden keselamatan ditangani secara reaktif, tanpa analisis mendalam tentang punca atau langkah pembaikan jangka panjang\r\n• Tiada dokumentasi mengenai insiden atau pelajaran yang diambil daripada insiden tersebut, menyebabkan kelemahan berterusan dalam sistem.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA394', 'AE047', 'AS002', '• Proses penyiasatan insiden mula dijalankan, tetapi masih pada peringkat asas dan tidak mendalam; dan\r\n• Amaran keselamatan mula diambil perhatian, namun pelan tindak balas dan pembaikan masih bersifat ad-hoc; dan\r\n• Pelan pembaikan disediakan berdasarkan keperluan semasa, namun tidak ada proses formal untuk memastikan pelan ini diikuti atau dikemas kini.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA395', 'AE047', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Proses penyiasatan insiden dan amaran keselamatan yang formal telah ditakrifkan dan didokumentasikan; dan\r\n• Setiap insiden yang berlaku dianalisis untuk mengenal pasti punca utama, dan pelan pembaikan khusus disediakan untuk mengelakkan kejadian berulangv\r\n• Sistem laporan dan pengurusan insiden diwujudkan untuk memastikan setiap insiden dan amaran dikendalikan mengikut prosedur yang jelas.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA396', 'AE047', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Proses penyiasatan insiden dijalankan dengan penggunaan metrik untuk mengukur keberkesanan respons dan pelan pembaikan; dan\r\n• Setiap insiden keselamatan dan amaran diikuti dengan laporan terperinci yang dianalisis dan digunakan untuk penambahbaikan sistem keselamatan; dan\r\n• Pelan pembaikan yang disediakan berasaskan data dan keputusan penyiasatan, dan kemajuan pelaksanaan pelan ini dipantau dengan teliti.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA397', 'AE047', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Penyiasatan insiden dan amaran keselamatan dijalankan secara proaktif dengan penggunaan teknologi canggih seperti AI dan analitik data; dan\r\n• Terdapat pelan pembaikan yang komprehensif dan dioptimumkan berdasarkan analisis insiden terdahulu, dan proses pembaikan dilaksanakan secara automatik atau separa automatik; dan\r\n• Proses ini sentiasa diperbaiki berdasarkan penilaian berterusan, dengan ujian simulasi dijalankan untuk memastikan keberkesanan pelan pembaikan serta pengurangan insiden serupa di masa hadapan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA398', 'AE048', 'AS001', '• Tiada pelan formal untuk penyemakan berkala pengurusan insiden; dan\r\n• Penyemakan dilakukan secara ad-hoc, hanya selepas insiden besar berlaku atau ketika diminta oleh pihak tertentu; dan\r\n• Tiada dokumentasi atau rekod yang sistematik mengenai penyemakan yang dilakukan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA399', 'AE048', 'AS002', '• Pelan penyemakan pengurusan insiden mula diterapkan, tetapi tidak dijalankan secara berkala dan konsisten.\r\n• Proses penyemakan hanya berlaku apabila terdapat keperluan mendesak, tanpa jadual yang tetap; dan\r\n• Tindakan yang diambil selepas penyemakan masih tidak berstruktur, dan tiada penambahbaikan berterusan dilakukan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA400', 'AE048', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Penyemakan pelan pengurusan insiden dijalankan secara berkala dan formal, berdasarkan jadual yang telah ditetapkan; dan\r\n• Pelan ini didokumentasikan dengan jelas dan setiap aspek pengurusan insiden diteliti secara teliti untuk memastikan pelan yang ada masih relevan dan berkesan; dan\r\n• Tindakan susulan diambil berdasarkan hasil penyemakan untuk mengemas kini dan memperbaiki pelan pengurusan insiden.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA401', 'AE048', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Penyemakan pelan pengurusan insiden dilakukan menggunakan metrik yang jelas untuk mengukur keberkesanan pelan yang sedia ada; dan\r\n• Jadual penyemakan disusun berdasarkan penilaian risiko dan pengalaman insiden terdahulu, memastikan pelan sentiasa ditambah baik; dan\r\n• Hasil penyemakan didokumentasikan dan digunakan untuk memperbaiki pelan sedia ada dengan perubahan atau penambahbaikan yang spesifik.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA402', 'AE048', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Penyemakan pelan pengurusan insiden bukan sahaja dijalankan secara berkala, tetapi juga menggunakan teknologi automasi dan analitik untuk mempercepat dan memperbaiki proses penyemakan; dan\r\n• Penyemakan dilakukan secara proaktif, dengan penilaian berterusan terhadap pelan pengurusan insiden bagi memastikan keberkesanan maksimum dan penyesuaian kepada ancaman terkini; dan\r\n• Proses penyemakan adalah sebahagian daripada usaha berterusan untuk meningkatkan ketahanan siber organisasi, dengan penyemakan dilakukan secara dinamik berdasarkan keperluan dan perkembangan teknologi.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA403', 'AE055', 'AS001', '• Tiada prosedur formal untuk tindak balas atau pemulihan daripada insiden keselamatan siber; dan\r\n• Tindak balas terhadap insiden dilakukan secara reaktif, tanpa perancangan atau garis panduan yang jelas; dan\r\n• Langkah pemulihan tidak dijalankan dengan konsisten, dan tiada panduan terperinci untuk proses pemulihan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA404', 'AE055', 'AS002', '• Prosedur asas tindak balas dan pemulihan mula diterapkan, tetapi masih tidak sepenuhnya didokumentasikan atau diselaraskan; dan\r\n• Terdapat garis panduan umum untuk tindak balas terhadap insiden keselamatan, tetapi prosedur pemulihan belum lengkap; dan\r\n• Organisasi mula menyedari keperluan untuk memperbaiki prosedur, namun pelaksanaan dan pemulihan masih dilakukan secara minimum.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA405', 'AE055', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Prosedur tindak balas dan pemulihan daripada insiden keselamatan siber telah ditakrifkan dengan jelas dan didokumentasikan; dan\r\n• Setiap langkah dalam tindak balas insiden diaturkan secara  proaktif dan formal, termasuk proses pemulihan yang jelas; dan\r\n• Latihan dan simulasi dijalankan untuk menguji keberkesanan prosedur tindak balas dan pemulihan, memastikan semua anggota pasukan mengetahui peranan mereka.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA406', 'AE055', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Prosedur tindak balas dan pemulihan diuruskan secara proaktif dan dinilai menggunakan metrik untuk mengukur keberkesanan; dan\r\n• Proses tindak balas dan pemulihan diaudit secara berkala untuk mengenal pasti kelemahan dan kawasan untuk penambahbaikan; dan\r\n• Organisasi menggunakan hasil daripada audit dan insiden terdahulu untuk terus memperkemaskan prosedur.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA407', 'AE055', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Prosedur tindak balas dan pemulihan dioptimumkan dengan menggunakan teknologi canggih seperti AI dan automasi untuk meningkatkan kecekapan; dan\r\n• Tindak balas terhadap insiden dijalankan secara proaktif, dengan pelan pemulihan yang fleksibel berdasarkan pengalaman dan analisis insiden terdahulu; dan\r\n• Prosedur ini sentiasa disemak dan diubah suai untuk memastikan respons yang cepat dan berkesan terhadap ancaman keselamatan yang berkembang.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA408', 'AE070', 'AS001', '• Tiada penyemakan berkala dilakukan terhadap prosedur tindak balas dan pemulihan; dan\r\n• Cyber Drills (latihan siber) sama ada tidak dijalankan atau hanya dilakukan secara minimum, tanpa impak kepada semakan prosedur; dan\r\n• Organisasi bertindak reaktif tanpa adanya proses yang jelas untuk mengemas kini prosedur berdasarkan hasil Cyber Drills.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA409', 'AE070', 'AS002', '• Cyber Drills mula dijalankan, tetapi penyemakan prosedur tindak balas dan pemulihan masih tidak berlaku secara konsisten atau berjadual; dan\r\n• Sebahagian hasil Cyber Drills digunakan untuk memperbaiki prosedur, tetapi tindakan ini dilakukan secara sekali sekala; dan\r\n• Prosedur tindak balas dan pemulihan mula dikemas kini, tetapi pelaksanaannya masih dalam peringkat awal tanpa jadual penyemakan yang tetap.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA410', 'AE070', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Prosedur penyemakan berkala telah ditetapkan, dengan latihan Cyber Drills dijalankan mengikut jadual untuk menilai keberkesanan tindak balas dan pemulihan; dan\r\n• Hasil daripada latihan Cyber Drills digunakan secara formal untuk mengemas kini dan memperbaiki prosedur sedia ada; dan\r\n• Proses penyemakan ini didokumentasikan dengan jelas, dan pelan tindak balas serta pemulihan diperbaiki secara konsisten berdasarkan hasil latihan tersebut.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA411', 'AE070', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Penyemakan prosedur dilakukan secara berkala dengan metrik prestasi yang ditetapkan untuk menilai keberkesanan Cyber Drills dan kesesuaian tindak balas serta pemulihan; dan\r\n• Analisis mendalam daripada latihan siber digunakan untuk menilai dan memperbaiki kelemahan dalam prosedur tindak balas; dan\r\n• Prosedur pemulihan dikemas kini berdasarkan data yang diperoleh daripada latihan, dengan tindakan susulan untuk memastikan penambahbaikan dilaksanakan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA412', 'AE070', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Penyemakan berkala prosedur tindak balas dan pemulihan dioptimumkan dengan menggunakan hasil daripada Cyber Drills terkini dan simulasi automatik; dan\r\n• Latihan siber dijalankan secara berterusan, dan hasilnya digunakan secara proaktif untuk memperbaiki prosedur dengan segera; dan\r\n• Prosedur ini sentiasa disemak dan dikemas kini berdasarkan perubahan landskap ancaman siber, memastikan tindak balas dan pemulihan sentiasa relevan dan efisien.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA413', 'AE049', 'AS001', '• Tiada usaha proaktif untuk mengenal pasti ancaman dan kerentanan keselamatan siber; dan\r\n• Organisasi hanya bertindak balas terhadap ancaman dan kerentanan apabila insiden keselamatan siber berlaku; dan\r\n• Tiada pemantauan atau pengesanan awal yang dilakukan, dan kesedaran terhadap ancaman adalah terhad.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA414', 'AE049', 'AS002', '• Pengenalpastian ancaman dan kerentanan mula diterapkan tetapi hanya berlaku secara asas dan tidak sepenuhnya konsisten; dan\r\n• Organisasi mula menggunakan alat asas untuk mengenal pasti kelemahan dalam sistem, namun tindakan yang diambil masih bersifat reaktif; dan\r\n• Walaupun ancaman diambil kira, usaha proaktif untuk mengatasi atau mencegahnya masih terhad.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA415', 'AE049', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Proses proaktif untuk mengenal pasti dan menilai ancaman serta kerentanan keselamatan siber telah ditakrifkan dan didokumentasikan; dan\r\n• Organisasi mula melaksanakan pemantauan berterusan terhadap ancaman dan kerentanan dengan menggunakan alat dan teknologi yang lebih canggih; dan\r\n• Pelan tindakan dan langkah pencegahan telah ditakrifkan dengan jelas untuk mengurangkan pendedahan kepada ancaman siber.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA416', 'AE049', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Ancaman dan kerentanan keselamatan siber dipantau secara berkala menggunakan metrik yang jelas dan analisis data untuk mengukur keberkesanan usaha pencegahan; dan\r\n• Usaha proaktif dilakukan secara berterusan untuk mengenal pasti kelemahan baru dan meminimumkan risiko melalui pelaksanaan langkah pencegahan yang teratur; dan\r\n• Analisis risiko dilakukan secara menyeluruh, dengan pelaporan berkala dan kemas kini prosedur berasaskan hasil pemantauan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA417', 'AE049', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Pembangunan dan pelaksanaan pengurusan ancaman dan kerentanan adalah proaktif dan dioptimumkan dengan penggunaan AI, analitik prediktif, dan automasi untuk mengesan ancaman baru; dan\r\n• Tindakan pencegahan dilaksanakan secara automatik untuk menutup kerentanan sebelum ancaman dapat dieksploitasi; dan\r\n• Organisasi sentiasa mengemas kini alat dan prosedur berdasarkan kajian terhadap landskap ancaman semasa, serta melibatkan maklum balas daripada pakar-pakar industri dan analisis data yang terkini.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA418', 'AE054', 'AS001', '• Tiada proses formal untuk penyediaan dan pengawalan versi dokumen yang berkaitan dengan prosedur ancaman dan kelemahan; dan\r\n• Dokumen disimpan secara tidak teratur;\r\n• Dokumen tidak dikemas kini secara berkala; dan\r\n• Tiada sistem untuk menjejaki versi dokumen terdahulu atau untuk memastikan semua pihak menggunakan versi terkini.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA419', 'AE054', 'AS002', '• Pelaksanaan penyediaan dan pengawalan versi dokumen masih tidak sepenuhnya konsisten; dan\r\n• Dokumen prosedur ancaman dan kelemahan yang dikemas kini secara berkala ada kekurangan dalam pengesanan versi terdahulu; dan\r\n• Pengawalan yang kurang terhadap akses kepada dokumen.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA420', 'AE054', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Proses pengawalan versi dokumen telah ditakrifkan dengan jelas dan didokumentasikan; dan\r\n• Mekanisme formal untuk mengemas kini dokumen prosedur ancaman dan kelemahan\r\n• Mekanisme formal untuk  mengesan perubahan dan versi dokumen prosedur yang direkod; dan\r\n• Pengesahan dan kawalan akses kepada versi terkini dokumen dilaksanakan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA421', 'AE054', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Proses penyediaan dan pengawalan versi dokumen dipantau dan diuruskan secara berkala menggunakan metrik yang jelas; dan\r\n• Setiap versi dokumen disemak dan diluluskan sebelum disebarkan, dengan prosedur kawalan yang memastikan hanya versi terkini digunakan dalam operasi; dan\r\n• Sistem pengurusan dokumen yang lebih maju digunakan untuk memastikan kawalan akses dan integriti dokumen sepanjang kitaran hayatnya.', '4', '2025-12-24', NULL, NULL, 'Active');
INSERT INTO `score_element` (`se_ID`, `element_ID`, `score_ID`, `details`, `input_id`, `input_at`, `updated_id`, `updated_at`, `status`) VALUES
('ASA422', 'AE054', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Pengawalan versi dokumen adalah automatik dan sepenuhnya dioptimumkan menggunakan alat teknologi seperti sistem Enterprise Content Management (ECM) atau Document Management System (DMS); dan\r\n• Perubahan pada dokumen dibuat dengan pantas dan dikawal ketat, dengan pengesanan versi secara automatik yang menyimpan sejarah perubahan, memudahkan audit dan kajian semula; dan\r\n• Proses ini selari dengan amalan terbaik dan standard  antarabangsa, memastikan dokumentasi ancaman dan kelemahan adalah tepat, terkini, dan mudah diakses oleh pihak yang berkepentingan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA423', 'AE050', 'AS001', '• Tiada penggunaan teknologi formal untuk pemantauan ancaman dan kerentanan; dan\r\n• Organisasi bergantung kepada kaedah manual atau minimum untuk mengenal pasti ancaman siber, tanpa penggunaan teknologi moden atau standard  terkini; dan\r\n• Tindak balas adalah reaktif dan tidak berdasarkan kepada pemantauan berterusan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA424', 'AE050', 'AS002', '• Penggunaan teknologi asas untuk pemantauan ancaman dan kerentanan telah dimulakan, tetapi masih bersifat terhad dan kurang konsisten; dan\r\n• Teknologi tidak selaras sepenuhnya dengan standard  terkini, dan pengesanan ancaman masih dilakukan pada skala kecil; dan\r\n• Pemantauan masih dilakukan secara berkala tetapi tidak berterusan, dengan respons kepada ancaman yang masih lambat.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA425', 'AE050', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Proses pemantauan ancaman dan kerentanan telah ditakrifkan dan didokumentasikan dengan jelas; dan\r\n• Teknologi yang digunakan selari dengan standard  keselamatan terkini, seperti NIST, ISO 27001, atau CIS Controls; dan\r\n• Pemantauan ancaman siber dilakukan secara lebih aktif dan berterusan menggunakan teknologi yang boleh mengesan ancaman dalam masa nyata; dan\r\n• Proses-proses ini ditakrifkan dan digunakan secara meluas dalam organisasi.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA426', 'AE050', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Teknologi pemantauan ancaman dan kerentanan diurus secara berkesan menggunakan alat yang diiktiraf berdasarkan standard  keselamatan siber terkini; dan\r\n• Data dari pemantauan digunakan untuk mengukur keberkesanan tindak balas terhadap ancaman dan untuk memperbaiki kelemahan dalam sistem keselamatan; dan\r\n• Pemantauan ancaman adalah berterusan dan berskala global, menggunakan teknologi yang lebih maju seperti SIEM (Security Information and Event Management) dan AI/ML untuk mengesan ancaman yang kompleks.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA427', 'AE050', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Organisasi menggunakan teknologi pemantauan ancaman dan kerentanan terkini yang dioptimumkan untuk automasi dan analitik canggih, seperti Threat Intelligence Platforms dan Behavioral Analytics; dan\r\n• Pemantauan dilakukan secara masa nyata dengan integrasi sepenuhnya kepada sistem keselamatan dan operasi organisasi, menjadikan tindak balas kepada ancaman lebih cepat dan proaktif; dan\r\n• Teknologi yang digunakan terus dikemas kini dan selari dengan standard  keselamatan terkini, memastikan organisasi sentiasa bersedia menghadapi ancaman baru dalam dunia siber yang semakin dinamik.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA428', 'AE071', 'AS001', '• Tiada proses formal untuk mengkaji atau menilai penggunaan teknologi pemantauan ancaman dan kerentanan; dan\r\n• Penggunaan teknologi sedia ada adalah berdasarkan keperluan segera dan tidak melibatkan kajian yang berstruktur atau berdasarkan standard  industri; dan\r\n• Organisasi tidak mengamalkan penilaian berkala terhadap teknologi yang digunakan, menyebabkan potensi peninggalan teknologi usang.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA429', 'AE071', 'AS002', '• Kajian terhadap penggunaan teknologi pemantauan ancaman dan kerentanan dilakukan secara ad-hoc tetapi tidak terancang secara formal; dan\r\n• Penilaian teknologi dilakukan sesekali, namun masih belum ada proses rasmi yang diikuti secara konsisten; dan\r\n• Walaupun kajian berlaku, ia tidak berdasarkan standard  industri terkini atau tidak menyeluruh.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA430', 'AE071', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Proses mengkaji dan menilai teknologi pemantauan ancaman telah ditakrifkan dengan jelas dan didokumentasikan; dan\r\n• Organisasi mempunyai jadual penilaian berkala untuk menilai keberkesanan dan kesesuaian teknologi terhadap standard  terkini; dan\r\n• Kajian ini melibatkan perbandingan teknologi dengan standard  keselamatan yang diiktiraf seperti NIST, ISO, dan standard  industri yang berkaitan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA431', 'AE071', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Proses penilaian teknologi dijalankan secara berkala menggunakan metrik kuantitatif untuk mengukur keberkesanan dan prestasi teknologi; dan\r\n• Kajian terhadap teknologi dilakukan berasaskan data, dan laporan penilaian dihasilkan untuk mengesan potensi penambahbaikan atau keperluan untuk menaik taraf teknologi; dan\r\n• Organisasi juga mengikut perkembangan teknologi terkini dan amalan terbaik dalam industri keselamatan siber.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA432', 'AE071', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Proses kajian dan penilaian teknologi adalah berterusan dan dioptimumkan melalui penggunaan alat analitik canggih dan AI untuk menilai keberkesanan teknologi pemantauan ancaman dan kerentanan; dan\r\n• Organisasi bukan sahaja melakukan kajian terhadap teknologi yang digunakan, tetapi juga membuat penilaian proaktif terhadap teknologi baru yang muncul untuk memastikan mereka berada di hadapan dalam perlindungan siber; dan\r\n• Teknologi sentiasa diselaraskan dengan perkembangan standard  keselamatan terkini, dan keputusan untuk menerapkan teknologi baru dibuat berdasarkan analisis risiko dan kos-faedah yang komprehensif.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA433', 'AE072', 'AS001', '• Tiada proses formal untuk pemantauan atau pengujian keselamatan siber terhadap rangkaian, sistem, dan aplikasi; dan\r\n• Pengujian dilakukan secara ad-hoc atau apabila insiden berlaku, tanpa pendekatan sistematik; dan\r\n• Tiada jadual atau struktur bagi pelaksanaan pengujian keselamatan secara berkala.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA434', 'AE072', 'AS002', '• Pengujian keselamatan siber telah mula dilaksanakan tetapi dilakukan secara minimum dan tidak konsisten; dan\r\n• Pengujian dilakukan hanya pada sistem atau aplikasi tertentu, dan masih tiada proses terpusat untuk memantau semua komponen rangkaian; dan\r\n• Jadual pengujian keselamatan wujud tetapi tidak dipatuhi sepenuhnya.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA435', 'AE072', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Proses pengujian keselamatan siber telah ditakrifkan dengan jelas dan dijalankan secara berkala mengikut jadual yang teratur; dan\r\n• Pengujian menyeluruh dilakukan terhadap semua rangkaian, sistem, dan aplikasi yang kritikal, dengan pemantauan berterusan terhadap hasil pengujian; dan\r\n• Proses ini mengikuti amalan terbaik dan standard  keselamatan siber yang relevan, seperti NIST, ISO 27001, dan OWASP.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA436', 'AE072', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Pengujian keselamatan siber dijalankan secara berkala dan dipantau menggunakan metrik dan indikator prestasi yang jelas; dan\r\n• Keberkesanan pengujian diukur, dan hasilnya dianalisis untuk mengenal pasti kelemahan dan peluang penambahbaikan; dan\r\n• Organisasi telah melaksanakan automasi untuk beberapa komponen pengujian, seperti vulnerability scanning, penetration testing, dan patch management.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA437', 'AE072', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Proses pengujian keselamatan siber dipantau dan diurus secara automatik menggunakan teknologi canggih seperti AI/ML untuk pengesanan ancaman secara masa nyata; dan\r\n• Pengujian keselamatan adalah berterusan dan diintegrasikan ke dalam DevSecOps, memastikan sistem, rangkaian, dan aplikasi diuji pada setiap peringkat pembangunan dan operasi; dan\r\n• Pengujian dilakukan berdasarkan pendekatan risk-based testing untuk memastikan bahawa semua risiko dinilai dan mitigasi dilakukan dengan segera, mengikut standard  terkini yang terus berkembang.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA438', 'AE051', 'AS001', '• Tiada proses atau rancangan formal untuk memastikan kesinambungan perkhidmatan ICT; dan\r\n• Perkhidmatan ICT terdedah kepada gangguan atau kegagalan tanpa pelan tindak balas yang jelas; dan\r\n• Tiada dokumentasi atau prosedur untuk menangani insiden yang menjejaskan perkhidmatan ICT.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA439', 'AE051', 'AS002', '• Pembangunan dan pelaksanaan pengurusan kesinambungan perkhidmatan ICT telah dimulakan, tetapi masih terhad kepada beberapa komponen; dan\r\n• Proses-proses ini dilaksanakan secara minimum tanpa kerangka yang menyeluruh; dan\r\n• Pelan tindak balas wujud tetapi tidak diuji secara konsisten, dan keupayaan pemulihan belum dioptimumkan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA440', 'AE051', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Pengurusan kesinambungan perkhidmatan ICT telah ditakrifkan dengan jelas dan terdokumentasi; dan\r\n• Pelan pemulihan bencana, pemulihan data, dan tindak balas terhadap gangguan ICT dijalankan secara konsisten; dan\r\n• Organisasi mempunyai panduan yang ditetapkan untuk menangani insiden dan memastikan perkhidmatan ICT dapat pulih dengan cepat berdasarkan amalan terbaik dalam industri.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA441', 'AE051', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Pengurusan kesinambungan perkhidmatan ICT dipantau secara berkala, dan pelan disesuaikan berdasarkan data prestasi dan insiden yang lepas; dan\r\n• Ujian berkala dijalankan bagi memastikan keberkesanan pelan pemulihan dan tindak balas terhadap gangguan; dan\r\n• Metrik yang kuantitatif digunakan untuk menilai keupayaan pemulihan dan kesinambungan perkhidmatan ICT, dan langkah-langkah penambahbaikan diterapkan secara berterusan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA442', 'AE051', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Pengurusan kesinambungan perkhidmatan ICT dilaksanakan secara automatik dengan proses dan teknologi yang optimum; dan\r\n• Organisasi menggunakan automasi dan teknologi canggih seperti disaster recovery as a service (DRaaS) untuk memastikan kesinambungan perkhidmatan ICT tanpa gangguan; dan\r\n• Proses kesinambungan dipantau secara berterusan, dan kajian penambahbaikan dilakukan secara proaktif untuk memastikan perkhidmatan ICT sentiasa berjalan dengan optimum, walaupun menghadapi cabaran besar.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA443', 'AE052', 'AS001', '• Tiada proses formal untuk menyemak pelan kesinambungan perkhidmatan ICT; dan\r\n• Penyemakan pelan dilakukan secara ad-hoc atau hanya apabila terdapat keperluan segera, seperti selepas berlaku insiden atau gangguan; dan\r\n• Dokumentasi dan penilaian keberkesanan pelan jarang dilakukan, jika ada.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA444', 'AE052', 'AS002', '• Penyemakan pelan telah mula dilaksanakan, namun ia masih bersifat reaktif, dilakukan selepas insiden atau berdasarkan keperluan minimum; dan\r\n• Penyemakan dilakukan pada sebahagian komponen perkhidmatan ICT, tetapi tiada jadual berkala atau pelaksanaan menyeluruh; dan\r\n• Kelemahan dan kekuatan pelan jarang dinilai secara sistematik.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA445', 'AE052', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Proses penyemakan pelan kesinambungan perkhidmatan ICT ditakrifkan dengan jelas dan dijalankan secara berkala; dan\r\n• Penyemakan ini melibatkan semua komponen utama perkhidmatan ICT dan memastikan pelan dikemaskini mengikut perubahan persekitaran teknologi dan peraturan; dan\r\n• Pelan yang disemak mengambil kira perubahan risiko, keperluan undang-undang, dan perkembangan teknologi.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA446', 'AE052', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Penyemakan pelan dilakukan secara berkala dengan menggunakan metrik kuantitatif untuk menilai keberkesanan pelan kesinambungan; dan\r\n• Organisasi memantau prestasi dan hasil penyemakan, serta memperbaiki kelemahan yang dikesan; dan\r\n• Pelan yang disemak diukur terhadap standard  industri seperti ISO 22301 dan amalan terbaik keselamatan siber.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA447', 'AE052', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Penyemakan pelan dilakukan secara berterusan dan disokong oleh teknologi automasi yang memastikan pemantauan dan penilaian secara masa nyata; dan\r\n• Organisasi menggunakan analitik canggih dan pendekatan proaktif untuk mengesan kelemahan serta memperbaiki pelan kesinambungan dengan segera; dan\r\n• Penyemakan pelan bukan sahaja memenuhi standard  terkini, tetapi sentiasa dioptimumkan untuk menghadapi ancaman baru dan memastikan kesediaan penuh dalam semua senario.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA448', 'AE053', 'AS001', '• Tiada simulasi yang dijalankan atau hanya simulasi asas dilakukan secara ad-hoc; dan\r\n• Tiada pelan yang formal dan sistematik untuk menguji keberkesanan pelan kesinambungan perkhidmatan ICT; dan\r\n• Hanya bergantung pada tindak balas kecemasan semasa insiden berlaku tanpa sebarang simulasi yang terancang.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA449', 'AE053', 'AS002', '• Simulasi pelan kesinambungan perkhidmatan ICT dijalankan, namun masih dilakukan secara terhad dan tidak melibatkan keseluruhan perkhidmatan ICT; dan\r\n• Proses simulasi dilaksanakan mengikut keperluan atau selepas berlaku insiden, namun belum ada jadual berkala yang ketat; dan\r\n• Penyemakan hasil simulasi tidak dijalankan secara konsisten untuk memperbaiki pelan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA450', 'AE053', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Simulasi pelan kesinambungan perkhidmatan ICT dilakukan mengikut jadual yang tetap, dengan panduan dan prosedur yang jelas; dan\r\n• Semua komponen kritikal perkhidmatan ICT diuji secara berkala untuk memastikan kesiapsiagaan organisasi dalam menghadapi gangguan; dan\r\n• Hasil simulasi dianalisis, dan pelan diperbaiki berdasarkan penemuan dari simulasi tersebut.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA451', 'AE053', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Simulasi dijalankan secara sistematik dengan metrik untuk menilai keberkesanan pelan kesinambungan perkhidmatan ICT; dan\r\n• Organisasi menggunakan hasil simulasi untuk mengukur keupayaan pemulihan dan membuat penambahbaikan berdasarkan data yang dikumpulkan; dan\r\n• Simulasi dikaitkan dengan pengurusan risiko, dan pelan diperbaharui secara berkala berdasarkan perubahan dalam risiko organisasi  dan teknologi.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA452', 'AE053', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Simulasi pelan kesinambungan dijalankan secara proaktif dan berterusan, menggunakan automasi dan teknologi canggih; dan\r\n• Organisasi sentiasa memantau keberkesanan pelan melalui simulasi berkala, dan pelan ini disesuaikan secara dinamik untuk menangani ancaman baru; dan\r\n• Ujian simulasi menyeluruh dilakukan secara masa nyata, memastikan pelan kesinambungan perkhidmatan ICT sentiasa berada pada tahap optimum dan mampu menangani sebarang insiden yang berpotensi mengganggu perkhidmatan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA453', 'AE073', 'AS001', '• Tiada prosedur formal atau simulasi yang jelas untuk menguji kesinambungan perkhidmatan ICT; dan\r\n• Organisasi bergantung kepada langkah tindak balas kecemasan yang belum diuji secara sistematik; dan\r\n• Prosedur simulasi tidak diwujudkan atau hanya dijalankan apabila berlaku insiden sebenar.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA454', 'AE073', 'AS002', '• Simulasi bagi kesinambungan perkhidmatan ICT telah mula dilaksanakan, namun masih secara tidak konsisten; dan\r\n• Proses simulasi hanya melibatkan sebahagian perkhidmatan ICT, dan tidak melibatkan semua komponen kritikal; dan\r\n• Penyemakan dilakukan secara minimum, dan bergantung kepada keperluan segera atau selepas insiden berlaku.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA455', 'AE073', 'AS003', '• Telah lengkap Tahap 2; dan \r\n• Prosedur simulasi kesinambungan perkhidmatan ICT ditakrifkan dengan jelas dan dilaksanakan secara berkala mengikut jadual yang tetap; dan\r\n• Penyemakan prosedur simulasi dijalankan melibatkan semua bahagian penting perkhidmatan ICT, dengan memastikan kesediaan sistem dan kakitangan diuji secara menyeluruh; dan\r\n• Hasil simulasi dianalisis untuk mengenal pasti kelemahan dan peluang untuk penambahbaikan.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA456', 'AE073', 'AS004', '• Telah lengkap Tahap 3; dan \r\n• Simulasi kesinambungan perkhidmatan ICT dijalankan secara berkala dengan menggunakan metrik untuk menilai keberkesanan prosedur; dan\r\n• Organisasi memantau keputusan simulasi dan membuat perubahan berdasarkan penilaian prestasi yang berterusan; dan\r\n• Penyemakan berkala terhadap prosedur simulasi memastikan pelan tindak balas yang disesuaikan dengan perubahan risiko dan teknologi.', '4', '2025-12-24', NULL, NULL, 'Active'),
('ASA457', 'AE073', 'AS005', '• Telah lengkap Tahap 4; dan \r\n• Penyemakan dan simulasi kesinambungan perkhidmatan ICT dilakukan secara automatik dan proaktif, dengan penggunaan teknologi terkini seperti AI atau machine learning untuk menganalisis risiko dan meramalkan kegagalan perkhidmatan; dan\r\n• Simulasi diintegrasikan ke dalam proses operasi harian organisasi dan disesuaikan dengan ancaman baru serta perubahan teknologi; dan\r\n• Penyemakan simulasi dilakukan secara masa nyata untuk memastikan organisasi sentiasa bersedia menghadapi sebarang kemungkinan gangguan.', '4', '2025-12-24', NULL, NULL, 'Active');

--
-- Triggers `score_element`
--
DELIMITER $$
CREATE TRIGGER `trg_sa_ID` BEFORE INSERT ON `score_element` FOR EACH ROW BEGIN
    DECLARE next_id INT;
    
    -- FIX: Changed 'score_assesment' to 'score_element'
    SELECT IFNULL(MAX(CAST(SUBSTRING(se_ID, 4) AS UNSIGNED)), 0) + 1
    INTO next_id
    FROM score_element; 
    
    SET NEW.se_ID = CONCAT('ASA', LPAD(next_id, 3, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `sec_ID` varchar(10) NOT NULL,
  `type` varchar(15) NOT NULL,
  `sec_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`sec_ID`, `type`, `sec_name`) VALUES
('10', 'Requirement', 'Improvement'),
('4', 'Requirement', 'Context of the Organisation'),
('5', 'Requirement', 'Leadership'),
('6', 'Requirement', 'Planning'),
('7', 'Requirement', 'Support'),
('8', 'Requirement', 'Operation'),
('9', 'Requirement', 'Performance Evaluation'),
('A5', 'Control', 'Organizational'),
('A6', 'Control', 'People'),
('A7', 'Control', 'Physical'),
('A8', 'Control', 'Technological');

-- --------------------------------------------------------

--
-- Table structure for table `sub_con`
--

CREATE TABLE `sub_con` (
  `sec_ID` varchar(10) NOT NULL,
  `sub_con_ID` varchar(10) NOT NULL,
  `sub_con_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `sub_con`
--

INSERT INTO `sub_con` (`sec_ID`, `sub_con_ID`, `sub_con_name`) VALUES
('A5', 'A.5.1', 'Policies for information security'),
('A5', 'A.5.10', 'Acceptable use of information and other associated assets'),
('A5', 'A.5.11', 'Return of assets'),
('A5', 'A.5.12', 'Classification of information'),
('A5', 'A.5.13', 'Labelling of information'),
('A5', 'A.5.14', 'Information transfer'),
('A5', 'A.5.15', 'Access control'),
('A5', 'A.5.16', 'Identity management'),
('A5', 'A.5.17', 'Authentication information'),
('A5', 'A.5.18', 'Access rights'),
('A5', 'A.5.19', 'Information security in supplier relationships'),
('A5', 'A.5.2', 'Information security roles and responsibilities'),
('A5', 'A.5.20', 'Addressing information security within supplier agreements'),
('A5', 'A.5.21', 'Managing information security in the information \r\nand communication technology (ICT) supply-chain'),
('A5', 'A.5.22', 'Monitoring, review and change management of supplier services'),
('A5', 'A.5.23', 'Information security for use of cloud services'),
('A5', 'A.5.24', 'Information security incident management planning and preparation'),
('A5', 'A.5.25', 'Assessment and decision on information security events'),
('A5', 'A.5.26', 'Response to information security incidents'),
('A5', 'A.5.27', 'Learning from information security incidents'),
('A5', 'A.5.28', 'Collection of evidence'),
('A5', 'A.5.29', 'Information security during disruption'),
('A5', 'A.5.3', 'Segregation of duties'),
('A5', 'A.5.30', 'ICT readiness for business continuity'),
('A5', 'A.5.31', 'Legal, statutory, regulatory and contractual requirements'),
('A5', 'A.5.32', 'Intellectual property rights'),
('A5', 'A.5.33', 'Protection of records'),
('A5', 'A.5.34', 'Privacy and protection of personal identifiable information (PII)'),
('A5', 'A.5.35', 'Independent review of information security'),
('A5', 'A.5.36', 'Compliance with policies, rules and standards for information security'),
('A5', 'A.5.37', 'Documented operating procedures'),
('A5', 'A.5.4', 'Management responsibilities'),
('A5', 'A.5.5', 'Contact with authorities'),
('A5', 'A.5.6', 'Contact with special interest groups'),
('A5', 'A.5.7', 'Threat intelligence'),
('A5', 'A.5.8', 'Information security in project management'),
('A5', 'A.5.9', 'Inventory of information and other associated assets'),
('A6', 'A.6.1', 'Screening'),
('A6', 'A.6.2', 'Terms and conditions of employment'),
('A6', 'A.6.3', 'Information security awareness, education and training'),
('A6', 'A.6.4', 'Disciplinary process'),
('A6', 'A.6.5', 'Responsibilities after termination or change of employment'),
('A6', 'A.6.6', 'Confidentiality or non-disclosure agreements'),
('A6', 'A.6.7', 'Remote working'),
('A6', 'A.6.8', 'Information security event reporting'),
('A6', 'A.6.9', 'test control 9'),
('A7', 'A.7.1', 'Physical security perimeters'),
('A7', 'A.7.10', 'Storage media'),
('A7', 'A.7.11', 'Supporting utilities'),
('A7', 'A.7.12', 'Cabling security'),
('A7', 'A.7.13', 'Equipment maintenance'),
('A7', 'A.7.14', 'Secure disposal or re-use of equipment'),
('A7', 'A.7.2', 'Physical entry'),
('A7', 'A.7.3', 'Securing offices, rooms and facilities'),
('A7', 'A.7.4', 'Physical security monitoring'),
('A7', 'A.7.5', 'Protecting against physical and environmental threats'),
('A7', 'A.7.6', 'Working in secure areas'),
('A7', 'A.7.7', 'Clear desk and clear screen'),
('A7', 'A.7.8', 'Equipment siting and protection'),
('A7', 'A.7.9', 'Security of assets off-premises'),
('A8', 'A.8.1', 'User end point devices'),
('A8', 'A.8.10', 'Information deletion'),
('A8', 'A.8.11', 'Data masking'),
('A8', 'A.8.12', 'Data leakage prevention'),
('A8', 'A.8.13', 'Information backup'),
('A8', 'A.8.14', 'Redundancy of information processing facilities'),
('A8', 'A.8.15', 'Logging'),
('A8', 'A.8.16', 'Monitoring activities'),
('A8', 'A.8.17', 'Clock synchronization'),
('A8', 'A.8.18', 'Use of privileged utility programs'),
('A8', 'A.8.19', 'Installation of software on operational systems'),
('A8', 'A.8.2', 'Privileged access rights'),
('A8', 'A.8.20', 'Networks security'),
('A8', 'A.8.21', 'Security of network services'),
('A8', 'A.8.22', 'Segregation of networks'),
('A8', 'A.8.23', 'Web filtering'),
('A8', 'A.8.24', 'Use of cryptography'),
('A8', 'A.8.25', 'Secure development life cycle'),
('A8', 'A.8.26', 'Application security requirements'),
('A8', 'A.8.27', 'Secure system architecture and engineering principles'),
('A8', 'A.8.28', 'Secure coding'),
('A8', 'A.8.29', 'Security testing in development and acceptance'),
('A8', 'A.8.3', 'Information access restriction'),
('A8', 'A.8.30', 'Outsourced development'),
('A8', 'A.8.31', 'Separation of development, test and production environments'),
('A8', 'A.8.32', 'Change management'),
('A8', 'A.8.33', 'Test information'),
('A8', 'A.8.34', 'Protection of information systems during audit testing'),
('A8', 'A.8.4', 'Access to source code'),
('A8', 'A.8.5', 'Secure authentication'),
('A8', 'A.8.6', 'Capacity management'),
('A8', 'A.8.7', 'Protection against malware'),
('A8', 'A.8.8', 'Management of technical vulnerabilities'),
('A8', 'A.8.9', 'Configuration management');

--
-- Triggers `sub_con`
--
DELIMITER $$
CREATE TRIGGER `trg_generate_control_id` BEFORE INSERT ON `sub_con` FOR EACH ROW BEGIN
    DECLARE next_num INT;
    DECLARE prefix VARCHAR(20);

    -- FIX: If Section ID is like 'A6', turn it into 'A.6'
    IF NEW.sec_ID REGEXP '^A[0-9]+$' THEN
        SET prefix = CONCAT('A.', SUBSTRING(NEW.sec_ID, 2));
    ELSE
        SET prefix = NEW.sec_ID;
    END IF;

    -- Find the highest number for this section
    SELECT IFNULL(MAX(CAST(SUBSTRING_INDEX(sub_con_ID, '.', -1) AS UNSIGNED)), 0) + 1
    INTO next_num
    FROM sub_con
    WHERE sub_con_ID LIKE CONCAT(prefix, '.%');

    -- Generate the final ID (e.g., A.6.1)
    SET NEW.sub_con_ID = CONCAT(prefix, '.', next_num);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `sub_req`
--

CREATE TABLE `sub_req` (
  `sec_ID` varchar(10) NOT NULL,
  `sub_req_ID` varchar(10) NOT NULL,
  `sub_req_name` varchar(500) NOT NULL,
  `criteria_ID` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `sub_req`
--

INSERT INTO `sub_req` (`sec_ID`, `sub_req_ID`, `sub_req_name`, `criteria_ID`) VALUES
('10', '10.1 (a)', 'Continual improvement - Improve the suitability, adequacy and effectiveness of ISMS', 'AC007'),
('10', '10.2 (a)', 'Nonconformity and corrective action - React to nonconformity by correcting it and dealing with consequences', 'AC007'),
('10', '10.2 (b)', 'Nonconformity and corrective action - Evaluate need for action by reviewing nonconformity, finding causes, and checking for similar cases', 'AC007'),
('10', '10.2 (c)', 'Nonconformity and corrective action - Implement any action needed', 'AC007'),
('10', '10.2 (d)', 'Nonconformity and corrective action - Review the effectiveness of any corrective action taken', 'AC007'),
('10', '10.2 (e)', 'Nonconformity and corrective action - Make changes to the ISMS (if necessary)', 'AC007'),
('10', '10.2 (f)', 'Nonconformity and corrective action - Document evidence of nonconformities and actions taken', 'AC007'),
('10', '10.2 (g)', 'Nonconformity and corrective action - Document the result of corrective action', 'AC007'),
('4', '4.1', 'Organisational context - Determine the organization\'s ISMS objectives and any issues that might affect its effectiveness', 'AC001'),
('4', '4.2 (a)', 'Interested parties - Identify interested parties', 'AC001'),
('4', '4.2 (b)', 'Interested parties - Determine their information security-relevant requirements and obligations', 'AC001'),
('4', '4.2 (c)', 'Interested parties - Determine the requirments that will be addressed through ISMS.', 'AC001'),
('4', '4.3 (a)', 'ISMS scope - Determine the external and internal issues', 'AC001'),
('4', '4.3 (b)', 'ISMS scope - Determine the requirments', 'AC001'),
('4', '4.3 (c)', 'ISMS scope - Determine interfaces and dependencies between perfomed by the organization or by other organization', 'AC001'),
('4', '4.4', ' ISMS - Establish, implement, maintain and continually improve an ISMS according to the standard', 'AC001'),
('5', '5.1 (a)', 'Leadership & commitment- Ensure ISMS objectives are established', 'AC002'),
('5', '5.1 (b)', 'Leadership & commitment- Ensure integrate ISMS requirements into organizational processes', 'AC002'),
('5', '5.1 (c)', 'Leadership & commitment- Ensure resource for the ISMS are available', 'AC002'),
('5', '5.1 (d)', 'Leadership & commitment- Communicate the importance of effective ISMS and compliance with its requirements', 'AC002'),
('5', '5.1 (e)', 'Leadership & commitment- Ensure the ISMS achieves its outcome', 'AC002'),
('5', '5.1 (f)', 'Leadership & commitment- Direct and support person to ensure ISMS effectiveness', 'AC002'),
('5', '5.1 (g)', 'Leadership & commitment- Promote continual improvement', 'AC002'),
('5', '5.1 (h)', 'Leadership & commitment- Support management roles in demonstrating leadership with their responsibilities', 'AC002'),
('5', '5.2 (a)', 'Policy - Establish the information security policy', 'AC003'),
('5', '5.2 (b)', 'Policy - Include IS objectives or a framework for setting', 'AC003'),
('5', '5.2 (c)', 'Policy - Commits to satisfy applicable IS requirements', 'AC003'),
('5', '5.2 (d)', 'Policy - Commits to continual improvement of the ISMS', 'AC003'),
('5', '5.2 (e)', 'Policy - Be available as documented information', 'AC003'),
('5', '5.2 (f)', 'Policy - Communicate within organization', 'AC003'),
('5', '5.2 (g)', 'Policy - Be available to interested parties', 'AC003'),
('5', '5.3 (a)', 'Organizational roles, responsibilities & authorities - Ensure the ISMS conforms to document requirements', 'AC009'),
('5', '5.3 (b)', 'Organizational roles, responsibilities & authorities - Report on the performance ISMS to top management', 'AC009'),
('6', '6.1', 'Actions to address risks & opportunities', 'AC019'),
('6', '6.1.1 (a)', 'General - Ensure the ISMS achieve the outcome(s)', 'AC001'),
('6', '6.1.1 (b)', 'General - Prevent or reduce undesired effects', 'AC018'),
('6', '6.1.1 (c)', 'General - Achieve continual improvement', 'AC018'),
('6', '6.1.1 (d)', 'General - Organizational shall plan actions to address the risks and opportunities', 'AC018'),
('6', '6.1.1 (e)', 'General - How to integrate and implement the actions into ISMS processes and evaluate its effectiveness', 'AC018'),
('6', '6.1.2 (a)', 'Information security risk assessment - Establish and maintains IS risk criteria', 'AC004'),
('6', '6.1.2 (b)', 'Information security risk assessment - Ensure IS risk assessments give consistent, valid, and comparable result', 'AC004'),
('6', '6.1.2 (c)', 'Information security risk assessment - Identify the IS risks', 'AC004'),
('6', '6.1.2 (d)', 'Information security risk assessment - Analyses the IS risks', 'AC004'),
('6', '6.1.2 (e)', 'Information security risk assessment - Evaluate the IS risks', 'AC004'),
('6', '6.1.3 (a)', 'Information security risk treatment - Select appropriate IS risk treatment options', 'AC005'),
('6', '6.1.3 (b)', 'Information security risk treatment - Determine all controls to be implemented', 'AC005'),
('6', '6.1.3 (c)', 'Information security risk treatment - Compare determined control with Annex A ', 'AC005'),
('6', '6.1.3 (d)', 'Information security risk treatment - Produce statement of applicability that contains necessary control, justification for inclusion, and the necessary controls are implemented or not', 'AC005'),
('6', '6.1.3 (e)', 'Information security risk treatment - Formulate an IS risk treatment plan', 'AC005'),
('6', '6.1.3 (f)', 'Information security risk treatment - Obtain risk owners\' approval of the treatment plan and acceptance of residual risks', 'AC005'),
('6', '6.2 (a)', 'Information security objectives & plans - Consistency with IS policy', 'AC006'),
('6', '6.2 (b)', 'Information security objectives & plans - Measurable', 'AC006'),
('6', '6.2 (c)', 'Information security objectives & plans - Consider IS requirements and results from risk assessment and treatment', 'AC006'),
('6', '6.2 (d)', 'Information security objectives & plans - Monitor', 'AC006'),
('6', '6.2 (e)', 'Information security objectives & plans - Communicate', 'AC003'),
('6', '6.2 (f)', 'Information security objectives & plans - Updated', 'AC006'),
('6', '6.2 (g)', 'Information security objectives & plans - Available as document', 'AC006'),
('6', '6.2 (h)', 'Information security objectives & plans - Define what will be done', 'AC006'),
('6', '6.2 (i)', 'Information security objectives & plans - Define required resources', 'AC006'),
('6', '6.2 (j)', 'Information security objectives & plans - Define the person who take responsibility', 'AC009'),
('6', '6.2 (k)', 'Information security objectives & plans - Define when it will be completed', 'AC006'),
('6', '6.2 (l)', 'Information security objectives & plans - Define how the result will be eavluated', 'AC006'),
('6', '6.3', 'Planning of changes - Determine the need for changes to the ISMS', 'AC006'),
('7', '7.1', 'Resources - Determine and provide the resources needed for the establishment, implementation, maintenance and continual improvement of the ISMS', 'AC002'),
('7', '7.2 (a)', 'Competence - Determine competence of persons affecting IS performance', 'AC014'),
('7', '7.2 (b)', 'Competence - Ensure persons are competent through education, training, or experience', 'AC014'),
('7', '7.2 (c)', 'Competence - Take actions to gain needed competence and evaluate effectiveness', 'AC014'),
('7', '7.2 (d)', 'Competence - Retain in document as evidence', 'AC014'),
('7', '7.3 (a)', 'Awareness - Information security policy', 'AC014'),
('7', '7.3 (b)', 'Awareness - Contribution to the effectiveness of the ISMS', 'AC008'),
('7', '7.3 (c)', 'Awareness - Implications of not comforming with ISMS requirements', 'AC008'),
('7', '7.4 (a)', 'Communication - What to communicate', 'AC003'),
('7', '7.4 (b)', 'Communication - When to communicate', 'AC003'),
('7', '7.4 (c)', 'Communication - with whom to communicate', 'AC003'),
('7', '7.4 (d)', 'Communication - How to communicate', 'AC003'),
('7', '7.5', 'Documented information', 'AC002'),
('7', '7.5.1 (a)', 'General - Require documented information', 'AC002'),
('7', '7.5.1 (b)', 'General - Determined by the organization for the effectiveness of ISMS', 'AC002'),
('7', '7.5.2 (a)', 'Creating and updating - Identification and description', 'AC002'),
('7', '7.5.2 (b)', 'Creating and updating - Format and media', 'AC002'),
('7', '7.5.2 (c)', 'Creating and updating - Review and approval for suitability and adequacy', 'AC002'),
('7', '7.5.3 (a)', 'Control of documented information - Document shall available and suitable of use when needed', 'AC002'),
('7', '7.5.3 (b)', 'Control of documented information - Document shall be protected', 'AC002'),
('7', '7.5.3 (c)', 'Control of documented information - The organization shall address distribution, access, retrieval and use', 'AC002'),
('7', '7.5.3 (d)', 'Control of documented information - The organization shall address control of changes address storage and preservation including legibility', 'AC002'),
('7', '7.5.3 (e)', 'Control of documented information - The organization shall address control of changes', 'AC002'),
('7', '7.5.3 (f)', 'Control of documented information - The organization shall address retention and disposition', 'AC002'),
('8', '8.1', 'Operational planning and control - Plan, implement, control & document ISMS processes to manage risks', 'AC018'),
('8', '8.2', 'Information security risk assessment - Perform information security risk assessments regularly or when significant changes occur', 'AC018'),
('8', '8.3', 'Information security risk treatment - Implement the IS risk treatment plan and retain documented of the result', 'AC018'),
('9', '9.1 (a)', 'Monitoring, measurement, analysis and evaluation - Determine what needs to be monitored and measured', 'AC018'),
('9', '9.1 (b)', 'Monitoring, measurement, analysis and evaluation - Determine the methods for monitoring, measurement, analysis, and evaluation', 'AC018'),
('9', '9.1 (c)', 'Monitoring, measurement, analysis and evaluation - Determine when the monitoring and measuring shall be performed', 'AC018'),
('9', '9.1 (d)', 'Monitoring, measurement, analysis and evaluation - Determine who shall monitor and measure', 'AC009'),
('9', '9.1 (e)', 'Monitoring, measurement, analysis and evaluation - Determine when the result shall be analysed and evaluated', 'AC018'),
('9', '9.1 (f)', 'Monitoring, measurement, analysis and evaluation - Determine who shall analyse and evalaute the result', 'AC009'),
('9', '9.2', 'Internal audit', 'AC007'),
('9', '9.2.1 (a)', 'General - Conforms to the organization\'s ISMS requirements and the document', 'AC007'),
('9', '9.2.1 (b)', 'General - Identify the ISMS is effectively implemented and maintained', 'AC007'),
('9', '9.2.2', 'Internal audit progamme - Plan, establish, implement and maintain ad auidt programme(s)', 'AC007'),
('9', '9.2.2 (a)', 'Internal audit progamme - Define the audit criteria and scope for each audit', 'AC007'),
('9', '9.2.2 (b)', 'Internal audit progamme - Select auditors and conduct audits', 'AC007'),
('9', '9.2.2 (c)', 'Internal audit progamme - Report the result of audit to relevant management', 'AC007'),
('9', '9.3', 'Management review', 'AC007'),
('9', '9.3.1', 'General - Review the organization\'s ISMS at planned intervals', 'AC007'),
('9', '9.3.2 (a)', 'Management review inputs - Considerate of the status of action from previous management reviews', 'AC007'),
('9', '9.3.2 (b)', 'Management review inputs - Considerate changes in external and internal issue in ISMS', 'AC007'),
('9', '9.3.2 (c)', 'Management review inputs - Considerate of change in needs and expectation of interested parties', 'AC007'),
('9', '9.3.2 (d)', 'Management review inputs - Feedback on ISMS performance in nonconformities and corrective actions, monitoring and measurements results, audit result, and fulfilment of IS obejctives', 'AC007'),
('9', '9.3.2 (e)', 'Management review inputs - Feedback from interested parties', 'AC007'),
('9', '9.3.2 (f)', 'Management review inputs - Result of risk assessment and status of risk treatment plan', 'AC007'),
('9', '9.3.2 (g)', 'Management review inputs - Considerate opportunities for continual improvement', 'AC007'),
('9', '9.3.3', 'Management review result - Management review result must include improvement decisions, ISMS changes, and be documented as evidence', 'AC007');

-- --------------------------------------------------------

--
-- Table structure for table `survey`
--

CREATE TABLE `survey` (
  `survey_ID` varchar(50) NOT NULL,
  `survey_name` varchar(30) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `survey_description` varchar(500) DEFAULT NULL,
  `created_by` varchar(10) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_id` varchar(10) DEFAULT NULL,
  `updated_by` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `survey`
--

INSERT INTO `survey` (`survey_ID`, `survey_name`, `department`, `start_date`, `end_date`, `status`, `survey_description`, `created_by`, `created_at`, `updated_id`, `updated_by`) VALUES
('SV001', 'Survey 1', 'fskm', '2026-01-05 09:58:00', '2026-01-30 21:58:00', 'Active', 'descriptiondescriptiondescriptiondescriptiondescription', '4', '2026-01-05 09:59:25', '4', '2026-01-15');

-- --------------------------------------------------------

--
-- Table structure for table `survey_domain`
--

CREATE TABLE `survey_domain` (
  `survey_domain_id` int(11) NOT NULL,
  `survey_id` varchar(50) DEFAULT NULL,
  `domain_id` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `survey_domain`
--

INSERT INTO `survey_domain` (`survey_domain_id`, `survey_id`, `domain_id`) VALUES
(225, 'SV001', 'AD005'),
(226, 'SV001', 'AD002'),
(227, 'SV001', 'AD001');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_ID` int(11) NOT NULL,
  `primary_email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_sub_id` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `email_verified` enum('Verified','Not Verified') NOT NULL DEFAULT 'Not Verified',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_organization` varchar(100) DEFAULT NULL,
  `user_position` varchar(100) DEFAULT NULL,
  `user_phone_company` varchar(20) DEFAULT NULL,
  `user_handphone_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_ID`, `primary_email`, `password`, `google_sub_id`, `full_name`, `department`, `status`, `email_verified`, `last_login`, `created_at`, `updated_at`, `user_organization`, `user_position`, `user_phone_company`, `user_handphone_no`) VALUES
(4, 'admin@uitm.edu.my', '$2y$10$UTKIVYNidiMD1AFK7jT79.PSK5vnDObgjMRM/QXfOJK8AWwqC5zBa', NULL, 'System Administrator', NULL, 'Active', 'Verified', '2026-01-20 08:59:03', '2025-10-29 06:29:06', '2026-01-20 08:59:03', NULL, NULL, NULL, NULL),
(10, 'ali@gmail.com', '$2y$10$F2iHe.T65eU3qFrACN8BwOpEnwvVlG/JfzM68mHSKXu03bR0rfR6u', NULL, 'ali bin abuu', 'FSKM', 'Active', '', '2026-01-15 15:52:54', '2025-11-12 01:36:41', '2026-01-15 15:52:54', 'UiTM 10', 'Manager', '', '+60182396060'),
(12, '2023864212@student.uitm.edu.my', '$2y$10$DEMeUh70OgYal7oSzY5hdOOgHGej7c4vTzfDj3LE5NdobA2AUMIL2', '110380624589280730990', 'IYLIA MAISARAH MOHD KHAIROL', '', 'Active', 'Verified', '2026-01-16 09:34:46', '2025-11-21 08:46:20', '2026-01-16 09:34:46', '', '', '', '+60182396090'),
(13, 'abu.bakar@student.uitm.edu.my', '$2y$10$7RXFWqkQbMZLo6AGAm03AO7Fke9oj2fhoeMZ8HyugWY20SfI/Hj5C', NULL, 'Abu Bakar', 'FSR', 'Active', 'Verified', '2026-01-15 15:53:55', '2025-12-10 08:18:52', '2026-01-15 15:53:55', 'UiTM', 'Student', NULL, '+60123456789'),
(14, 'siti.aminah@uitm.edu.my', '$2y$10$zh/p1t6Fj234oORkAfVk6O7J/QbHo6P0RmmXgL0BQypHTU4m.zSjC', NULL, 'Siti Aminah', 'FSKM', 'Active', 'Verified', '2026-01-15 09:47:13', '2025-12-10 08:18:52', '2026-01-15 09:47:13', 'UiTM', 'Lecturer', '0355442000', '+60134567890'),
(15, 'chong.wei@uitm.edu.my', '$2y$10$YHuTyDyNfSpuc6fW4njiM.aoR7G0idYfagakcgVTfPSIHfTZOBKHm', NULL, 'Chong Wei', 'Business Management', 'Active', 'Verified', '2025-12-12 11:01:37', '2025-12-10 08:18:52', '2025-12-12 11:01:37', 'UiTM', 'Senior Lecturer', '0355443000', '+60145678901'),
(16, 'devi.muthu@uitm.edu.my', '$2y$10$RHAXuIP9g8oslfXkvRbAfegIym351W3i/vNmkyhRZGgHmeIYx0MJe', NULL, 'Devi Muthu', 'Academy of Language Studies', 'Active', 'Verified', NULL, '2025-12-10 08:18:52', '2025-12-10 08:25:32', 'UiTM', 'Coordinator', NULL, '+60156789012'),
(17, 'salsabilashamsul896@gmail.com', '$2y$10$kvv2rkjbzbDsCjMYud.on.m.uw7.RipWNFFA8Ca4FNDbI9J/NXv0y', NULL, 'Salsabila Shamsul', 'FSKM', 'Active', '', '2025-12-15 13:07:48', '2025-12-15 13:07:35', '2025-12-15 13:07:48', 'UiTM', 'Student', '', '+60182037159'),
(18, 'iyliamaisarah050205@gmail.com', '$2y$10$XC0n4fLwHEMY9fqPI8PJ2eesbqbUPbt60nKF3uKX8e9sh/stkk4e2', NULL, 'iylia maisarah', '', 'Active', '', '2026-01-15 15:23:27', '2026-01-15 09:55:45', '2026-01-15 15:23:27', '', '', '', '+60182396090');

-- --------------------------------------------------------

--
-- Table structure for table `user_analysis`
--

CREATE TABLE `user_analysis` (
  `id` int(11) NOT NULL,
  `user_ID` int(11) DEFAULT NULL,
  `GA_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_role`
--

CREATE TABLE `user_role` (
  `user_role_ID` int(11) NOT NULL,
  `user_ID` int(11) NOT NULL,
  `role_ID` int(11) NOT NULL,
  `assigned_at` datetime DEFAULT current_timestamp(),
  `assigned_by` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_role`
--

INSERT INTO `user_role` (`user_role_ID`, `user_ID`, `role_ID`, `assigned_at`, `assigned_by`) VALUES
(2, 4, 1, '2025-10-29 07:56:49', NULL),
(3, 10, 2, '2025-12-03 10:57:42', 'System'),
(4, 12, 2, '2026-01-15 09:09:37', 'Google'),
(5, 13, 2, '2025-12-10 08:18:52', 'System'),
(6, 15, 2, '2025-12-10 08:18:52', 'System'),
(7, 16, 2, '2025-12-10 08:18:52', 'System'),
(8, 14, 2, '2025-12-10 08:18:52', 'System'),
(9, 17, 2, '2025-12-15 13:07:35', 'System'),
(10, 18, 2, '2026-01-15 09:55:45', 'System');

-- --------------------------------------------------------

--
-- Table structure for table `user_survey`
--

CREATE TABLE `user_survey` (
  `user_survey_ID` varchar(10) NOT NULL,
  `survey_ID` varchar(50) DEFAULT NULL,
  `user_ID` int(11) DEFAULT NULL,
  `status` enum('Pending','In progress','Completed','Expired') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `user_survey`
--

INSERT INTO `user_survey` (`user_survey_ID`, `survey_ID`, `user_ID`, `status`) VALUES
('US00001', 'SV001', 13, 'In progress'),
('US00002', 'SV001', 10, 'Completed'),
('US00003', 'SV001', 15, 'Pending'),
('US00004', 'SV001', 16, 'Pending'),
('US00005', 'SV001', 12, 'Completed'),
('US00006', 'SV001', 17, 'Pending'),
('US00007', 'SV001', 14, 'Completed'),
('US00008', 'SV001', 4, 'Pending');

--
-- Triggers `user_survey`
--
DELIMITER $$
CREATE TRIGGER `trg_generate_user_survey_id` BEFORE INSERT ON `user_survey` FOR EACH ROW BEGIN
    DECLARE last_id VARCHAR(10);
    DECLARE next_num INT DEFAULT 1;

    -- 1. Find the highest current ID (e.g., 'US00005')
    SELECT MAX(user_survey_ID) INTO last_id FROM user_survey;

    -- 2. If an ID exists, extract the number and add 1
    IF last_id IS NOT NULL THEN
        -- SUBSTRING(last_id, 3) removes the 'US' prefix
        SET next_num = CAST(SUBSTRING(last_id, 3) AS UNSIGNED) + 1;
    END IF;

    -- 3. Set the new ID (e.g., 'US' + '00006')
    -- LPAD ensures it always has enough zeros to look consistent
    SET NEW.user_survey_ID = CONCAT('US', LPAD(next_num, 5, '0'));
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `criteria`
--
ALTER TABLE `criteria`
  ADD PRIMARY KEY (`criteria_ID`),
  ADD KEY `domain_ID` (`domain_ID`);

--
-- Indexes for table `domain`
--
ALTER TABLE `domain`
  ADD PRIMARY KEY (`domain_ID`),
  ADD KEY `idx_domain_section` (`sec_ID`);

--
-- Indexes for table `element`
--
ALTER TABLE `element`
  ADD PRIMARY KEY (`element_ID`),
  ADD KEY `criteria_ID` (`criteria_ID`);

--
-- Indexes for table `element_control`
--
ALTER TABLE `element_control`
  ADD PRIMARY KEY (`id`),
  ADD KEY `element_ID` (`element_ID`),
  ADD KEY `sub_con_ID` (`sub_con_ID`);

--
-- Indexes for table `gap_analysis`
--
ALTER TABLE `gap_analysis`
  ADD PRIMARY KEY (`GA_id`),
  ADD KEY `domain_ID` (`domain_ID`),
  ADD KEY `criteria_ID` (`criteria_ID`),
  ADD KEY `element_ID` (`element_ID`),
  ADD KEY `user_ID` (`user_ID`),
  ADD KEY `fk_gap_auditor` (`auditor_id`),
  ADD KEY `fk_gap_survey` (`survey_ID`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `token` (`token`);

--
-- Indexes for table `response`
--
ALTER TABLE `response`
  ADD PRIMARY KEY (`response_ID`),
  ADD KEY `sa_ID` (`se_ID`),
  ADD KEY `response_user_fk` (`user_ID`),
  ADD KEY `element_ID_fk` (`element_ID`),
  ADD KEY `idx_response_survey` (`survey_ID`);

--
-- Indexes for table `result_criteria`
--
ALTER TABLE `result_criteria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `criteria_ID` (`criteria_ID`);

--
-- Indexes for table `result_domain`
--
ALTER TABLE `result_domain`
  ADD PRIMARY KEY (`rd_ID`),
  ADD KEY `domain_id_fk` (`domain_ID`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_ID`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `score`
--
ALTER TABLE `score`
  ADD PRIMARY KEY (`score_ID`);

--
-- Indexes for table `score_element`
--
ALTER TABLE `score_element`
  ADD PRIMARY KEY (`se_ID`),
  ADD KEY `element_ID` (`element_ID`),
  ADD KEY `score_ID` (`score_ID`);

--
-- Indexes for table `section`
--
ALTER TABLE `section`
  ADD PRIMARY KEY (`sec_ID`);

--
-- Indexes for table `sub_con`
--
ALTER TABLE `sub_con`
  ADD PRIMARY KEY (`sub_con_ID`),
  ADD KEY `sec_ID` (`sec_ID`);

--
-- Indexes for table `sub_req`
--
ALTER TABLE `sub_req`
  ADD PRIMARY KEY (`sub_req_ID`),
  ADD KEY `sec_ID` (`sec_ID`),
  ADD KEY `fk_subreq_criteria` (`criteria_ID`);

--
-- Indexes for table `survey`
--
ALTER TABLE `survey`
  ADD PRIMARY KEY (`survey_ID`);

--
-- Indexes for table `survey_domain`
--
ALTER TABLE `survey_domain`
  ADD PRIMARY KEY (`survey_domain_id`),
  ADD KEY `survey_id` (`survey_id`),
  ADD KEY `domain_id` (`domain_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_ID`),
  ADD UNIQUE KEY `primary_email` (`primary_email`),
  ADD UNIQUE KEY `google_sub_id` (`google_sub_id`),
  ADD KEY `idx_user_email` (`primary_email`),
  ADD KEY `idx_user_google_sub` (`google_sub_id`),
  ADD KEY `idx_user_active` (`status`);

--
-- Indexes for table `user_analysis`
--
ALTER TABLE `user_analysis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_ID` (`user_ID`),
  ADD KEY `GA_id` (`GA_id`);

--
-- Indexes for table `user_role`
--
ALTER TABLE `user_role`
  ADD PRIMARY KEY (`user_role_ID`),
  ADD UNIQUE KEY `user_role_unique` (`user_ID`,`role_ID`),
  ADD KEY `user_ID` (`user_ID`),
  ADD KEY `role_ID` (`role_ID`);

--
-- Indexes for table `user_survey`
--
ALTER TABLE `user_survey`
  ADD PRIMARY KEY (`user_survey_ID`) USING BTREE,
  ADD KEY `user_id` (`survey_ID`),
  ADD KEY `fk_auditee_to_users` (`user_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `element_control`
--
ALTER TABLE `element_control`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `survey_domain`
--
ALTER TABLE `survey_domain`
  MODIFY `survey_domain_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=228;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_role`
--
ALTER TABLE `user_role`
  MODIFY `user_role_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `criteria`
--
ALTER TABLE `criteria`
  ADD CONSTRAINT `criteria_ibfk_1` FOREIGN KEY (`domain_ID`) REFERENCES `domain` (`domain_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `domain`
--
ALTER TABLE `domain`
  ADD CONSTRAINT `fk_domain_section` FOREIGN KEY (`sec_ID`) REFERENCES `section` (`sec_ID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `element`
--
ALTER TABLE `element`
  ADD CONSTRAINT `element_ibfk_1` FOREIGN KEY (`criteria_ID`) REFERENCES `criteria` (`criteria_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `element_control`
--
ALTER TABLE `element_control`
  ADD CONSTRAINT `element_control_ibfk_1` FOREIGN KEY (`element_ID`) REFERENCES `element` (`element_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `element_control_ibfk_2` FOREIGN KEY (`sub_con_ID`) REFERENCES `sub_con` (`sub_con_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gap_analysis`
--
ALTER TABLE `gap_analysis`
  ADD CONSTRAINT `fk_gap_auditor` FOREIGN KEY (`auditor_id`) REFERENCES `user` (`user_ID`),
  ADD CONSTRAINT `fk_gap_survey` FOREIGN KEY (`survey_ID`) REFERENCES `survey` (`survey_ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `gap_analysis_ibfk_1` FOREIGN KEY (`domain_ID`) REFERENCES `domain` (`domain_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `gap_analysis_ibfk_2` FOREIGN KEY (`criteria_ID`) REFERENCES `criteria` (`criteria_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `gap_analysis_ibfk_3` FOREIGN KEY (`element_ID`) REFERENCES `element` (`element_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `gap_analysis_ibfk_4` FOREIGN KEY (`user_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE;

--
-- Constraints for table `response`
--
ALTER TABLE `response`
  ADD CONSTRAINT `element_ID_fk` FOREIGN KEY (`element_ID`) REFERENCES `element` (`element_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_response_to_survey` FOREIGN KEY (`survey_ID`) REFERENCES `survey` (`survey_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `response_ibfk_1` FOREIGN KEY (`se_ID`) REFERENCES `score_element` (`se_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_ID_fk` FOREIGN KEY (`user_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `result_criteria`
--
ALTER TABLE `result_criteria`
  ADD CONSTRAINT `result_criteria_ibfk_1` FOREIGN KEY (`criteria_ID`) REFERENCES `criteria` (`criteria_ID`);

--
-- Constraints for table `result_domain`
--
ALTER TABLE `result_domain`
  ADD CONSTRAINT `domain_id_fk` FOREIGN KEY (`domain_ID`) REFERENCES `domain` (`domain_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `score_element`
--
ALTER TABLE `score_element`
  ADD CONSTRAINT `score_element_ibfk_1` FOREIGN KEY (`element_ID`) REFERENCES `element` (`element_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `score_element_ibfk_2` FOREIGN KEY (`score_ID`) REFERENCES `score` (`score_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sub_con`
--
ALTER TABLE `sub_con`
  ADD CONSTRAINT `sub_con_ibfk_1` FOREIGN KEY (`sec_ID`) REFERENCES `section` (`sec_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sub_req`
--
ALTER TABLE `sub_req`
  ADD CONSTRAINT `fk_subreq_criteria` FOREIGN KEY (`criteria_ID`) REFERENCES `criteria` (`criteria_ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sub_req_ibfk_1` FOREIGN KEY (`sec_ID`) REFERENCES `section` (`sec_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `survey_domain`
--
ALTER TABLE `survey_domain`
  ADD CONSTRAINT `survey_domain_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `survey` (`survey_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `survey_domain_ibfk_2` FOREIGN KEY (`domain_id`) REFERENCES `domain` (`domain_ID`);

--
-- Constraints for table `user_analysis`
--
ALTER TABLE `user_analysis`
  ADD CONSTRAINT `user_analysis_ibfk_1` FOREIGN KEY (`user_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_analysis_ibfk_2` FOREIGN KEY (`GA_id`) REFERENCES `gap_analysis` (`GA_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_role`
--
ALTER TABLE `user_role`
  ADD CONSTRAINT `user_role_ibfk_1` FOREIGN KEY (`user_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_role_ibfk_2` FOREIGN KEY (`role_ID`) REFERENCES `role` (`role_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_survey`
--
ALTER TABLE `user_survey`
  ADD CONSTRAINT `fk_auditee_to_users` FOREIGN KEY (`user_ID`) REFERENCES `user` (`user_ID`),
  ADD CONSTRAINT `user_auditee_auditee_FK` FOREIGN KEY (`survey_ID`) REFERENCES `survey` (`survey_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;