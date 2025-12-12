-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql:3306
-- Generation Time: Nov 28, 2025 at 12:40 AM
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
('AC003', 'AD001', 'Polisi/prosedur Keselamatan Siber', NULL, NULL, NULL, NULL, 'Active'),
('AC004', 'AD002', 'Pelan Penilaian Risiko', NULL, NULL, NULL, NULL, 'Active'),
('AC005', 'AD002', 'Pelan Rawatan Risiko', NULL, NULL, NULL, NULL, 'Active'),
('AC006', 'AD003', 'Standard dan amalan terbaik keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AC007', 'AD003', 'Pengauditan keselamatan siber ', NULL, NULL, NULL, NULL, 'Active'),
('AC008', 'AD004', 'Pembangunan Kompetensi dan Kesedaran', NULL, NULL, NULL, NULL, 'Active'),
('AC009', 'AD004', 'Kakitangan ICT: Latihan kompetensi, kesedaran, kepatuhan ', NULL, NULL, NULL, NULL, 'Active'),
('AC010', 'AD005', 'Pengurusan Inventori aset', NULL, NULL, NULL, NULL, 'Active'),
('AC011', 'AD005', 'Klasifikasi maklumat ', NULL, NULL, NULL, NULL, 'Active'),
('AC012', 'AD006', 'Penguatkuasaan mekanisme pengesahan identiti', NULL, NULL, NULL, NULL, 'Active'),
('AC013', 'AD006', 'Pengurusan capaian', NULL, NULL, NULL, NULL, 'Active'),
('AC014', 'AD007', 'Kesedaran, kepatuhan', NULL, NULL, NULL, NULL, 'Active'),
('AC015', 'AD007', 'Penilaian keberkesanan pihak ketiga', NULL, NULL, NULL, NULL, 'Active'),
('AC016', 'AD007', 'Keperluan kumpulan pakar dan pakar bidang', NULL, NULL, NULL, NULL, 'Active'),
('AC017', 'AD008', 'Kawalan keselamatan infrastruktur rangkaian dan sistem ', NULL, NULL, NULL, NULL, 'Active'),
('AC018', 'AD008', 'Kesediaan pusat operasi keselamatan (SOC)', NULL, NULL, NULL, NULL, 'Active'),
('AC019', 'AD009', 'Pelan insiden keselamatan siber ', NULL, NULL, NULL, NULL, 'Active'),
('AC020', 'AD009', 'Simulasi pelan insiden keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AC021', 'AD010', 'Prosedur pengurusan ancaman dan kerentanan keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AC022', 'AD010', 'Teknologi bagi pengurusan ancaman dan kerentanan', NULL, NULL, NULL, NULL, 'Active'),
('AC023', 'AD011', 'Pelan kesinambungan perkhidmatan ICT', NULL, NULL, NULL, NULL, 'Active'),
('AC024', 'AD011', 'Simulasi ', NULL, NULL, NULL, NULL, 'Active'),
('AC025', 'AD012', 'test test criteria edit 21', '4', '2025-11-10', '4', '2025-11-13', 'Active'),
('AC026', 'AD012', 'testing 222', '4', '2025-11-10', NULL, NULL, 'Active'),
('AC027', 'AD012', 'aaaa', '4', '2025-11-10', NULL, '2025-11-13', 'Active'),
('AC028', 'AD013', 'criteria testing 3333', '4', '2025-11-13', '4', '2025-11-14', 'Active'),
('AC029', 'AD013', 'criteria testing 1233322', '4', '2025-11-14', NULL, NULL, 'Active'),
('AC030', 'AD013', 'lalalallala', '4', '2025-11-14', NULL, NULL, 'Active'),
('AC031', 'AD013', 'amamamamma', '4', '2025-11-14', NULL, NULL, 'Active'),
('AC032', 'AD014', 'criteria domain 1', '4', '2025-11-26', NULL, NULL, 'Active');

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
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `domain`
--

INSERT INTO `domain` (`domain_ID`, `domain_name`, `input_id`, `input_at`, `updated_id`, `updated_at`, `status`) VALUES
('AD001', 'Tadbir Urus', NULL, NULL, '4', '2025-11-17', 'Inactive'),
('AD002', 'Pengurusan Risiko', NULL, NULL, '4', '2025-11-07', 'Active'),
('AD003', 'Pematuhan dan Pengauditan', NULL, NULL, NULL, NULL, 'Active'),
('AD004', 'Keselamatan Sumber Manusia', NULL, NULL, NULL, NULL, 'Active'),
('AD005', 'Pengurusan Aset', NULL, NULL, NULL, NULL, 'Active'),
('AD006', 'Pengurusan Identiti Dan Capaian', NULL, NULL, NULL, NULL, 'Active'),
('AD007', 'Pengurusan Pihak Ketiga', NULL, NULL, NULL, NULL, 'Active'),
('AD008', 'Pengurusan Keselamatan Sistem Dan Aplikasi', NULL, NULL, NULL, NULL, 'Active'),
('AD009', 'Pengurusan Insiden', NULL, NULL, NULL, NULL, 'Active'),
('AD010', 'Pengurusan Ancaman Dan Kerentanan', NULL, NULL, NULL, NULL, 'Active'),
('AD011', 'Pengurusan Kesinambungan Perkhidmatan ICT', NULL, NULL, NULL, NULL, 'Active'),
('AD012', 'test domain 2', '4', '2025-11-07', '4', '2025-11-07', 'Active'),
('AD013', 'testing 10 220', '4', '2025-11-13', '4', '2025-11-14', 'Active'),
('AD014', 'domain test 12223334445555555', '4', '2025-11-26', '4', '2025-11-27', 'Active');

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
('AE001', 'AC001', 'Tadbir urus keselamatan siber dibangunkan dan dilaksanakan', NULL, NULL, NULL, NULL, 'Active'),
('AE002', 'AC001', 'Tanggungjawab dan terma tadbir urus dihuraikan dengan jelas', NULL, NULL, NULL, NULL, 'Active'),
('AE003', 'AC001', 'Strategi keselamatan siber dibangunkan sejajar dengan strategi universiti', NULL, NULL, NULL, '2025-11-13', 'Active'),
('AE004', 'AC002', 'Komitmen pengurusan mengkaji dasar dan inisiatif keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AE005', 'AC002', 'Komitmen pengurusan atasan dalam memberi sokongan sumber kewangan dan sumber lain (jika perlu)', NULL, NULL, NULL, NULL, 'Active'),
('AE006', 'AC002', 'Komitmen pengurusan atasan dalam memasti warga universiti faham mengenai keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AE007', 'AC003', 'Jelas dalam objektif polisi/prosedur serta tanggungjawab yang oleh warga universiti, pihak ketiga dan pihak berkepentingan', NULL, NULL, NULL, NULL, 'Active'),
('AE008', 'AC003', 'Pembangunan polisi dan prosedur keselamatan siber  merangkumi semua kawalan yang dicadang oleh piawaian antarabangsa dan amalan terbaik', NULL, NULL, NULL, NULL, 'Active'),
('AE009', 'AC003', 'Dihebahkan kepada warga universiti, pihak ketiga dan pihak berkepentingan', NULL, NULL, NULL, NULL, 'Active'),
('AE010', 'AC003', 'Dikaji/disemak secara berkala atau mengikut keperluan semasa', NULL, NULL, NULL, NULL, 'Active'),
('AE011', 'AC004', 'Keperluan menjalankan penilaian risiko secara berkala', NULL, NULL, NULL, NULL, 'Active'),
('AE012', 'AC004', 'Keperluan keutamaan peruntukan terhadap keselamatan siber berdasarkan penilaian risiko', NULL, NULL, NULL, NULL, 'Active'),
('AE013', 'AC005', 'Keperluan menjalankan pelan rawatan risiko secara berkala', NULL, NULL, NULL, NULL, 'Active'),
('AE014', 'AC005', 'Keperluan pemantauan dan penyemakan keberkesanan pelan rawatan risiko', NULL, NULL, NULL, NULL, 'Active'),
('AE015', 'AC006', 'Keperluan polisi dan prosedur sejajar dengan keperluan standard dan amalan terbaik', NULL, NULL, NULL, NULL, 'Active'),
('AE016', 'AC007', 'Keperluan menjalankan audit dan penilaian secara berkala', NULL, NULL, NULL, NULL, 'Active'),
('AE017', 'AC007', 'Pelaksanaan penemuan dan cadangan daripada audit susulan audit keselamatan maklumat/siber', NULL, NULL, NULL, NULL, 'Active'),
('AE018', 'AC007', 'Pemantauan proses penyediaan dokumentasi dan bukti pematuhan', NULL, NULL, NULL, NULL, 'Active'),
('AE019', 'AC008', 'Kesedaran terhadap kepentingan keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AE020', 'AC008', 'Kepentingan pengasingan tugas berdasarkan peranan dan tanggungjawab dalam keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AE021', 'AC008', 'Keperluan pengukuran keberkesanan inisitif program kesedaran dan program latihan keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AE022', 'AC009', 'Motivasi kakitangan universiti', NULL, NULL, NULL, NULL, 'Active'),
('AE023', 'AC009', 'Kemahiran dan kepakaran kakitangan ICT', NULL, NULL, NULL, NULL, 'Active'),
('AE024', 'AC009', 'Keperluan Program Pengganti (Succesor Program)', NULL, NULL, NULL, NULL, 'Active'),
('AE025', 'AC010', 'Pengurusan inventori aset dan termasuk data, sistem dan aplikasi', NULL, NULL, NULL, NULL, 'Active'),
('AE026', 'AC011', 'Klasifikasi maklumat mengikut maklumat terperingkat', NULL, NULL, NULL, NULL, 'Active'),
('AE027', 'AC011', 'Perlindungan, sanitasi dan pelupusan maklumat semasa kitar hayat maklumat', NULL, NULL, NULL, NULL, 'Active'),
('AE028', 'AC012', 'Keperluan pengurusan kawalan had capaian', NULL, NULL, NULL, NULL, 'Active'),
('AE029', 'AC012', 'Keperluan penguatkuasaan mekanisma pengesahan', NULL, NULL, NULL, NULL, 'Active'),
('AE030', 'AC013', 'Keperluan penyemakan hak capaian pengguna', NULL, NULL, NULL, NULL, 'Active'),
('AE031', 'AC013', 'Keperluan pemeriksaan dan pemantauan aktiviti capaian pengguna', NULL, NULL, NULL, NULL, 'Active'),
('AE032', 'AC013', 'Pengurusan dan pemantauan capaian pihak ketiga ke sistem maklumat dan data', NULL, NULL, NULL, NULL, 'Active'),
('AE033', 'AC014', 'Kesedaran pihak ketiga', NULL, NULL, NULL, NULL, 'Active'),
('AE034', 'AC014', 'Kepatuhan pihak ketiga terhadap perjanjian ditandatangani', NULL, NULL, NULL, NULL, 'Active'),
('AE035', 'AC015', 'Keperluan penilaian keberkesanan pihak ketiga', NULL, NULL, NULL, NULL, 'Active'),
('AE036', 'AC016', 'Keperluan kumpulan pakar atau pakar bidang.', NULL, NULL, NULL, NULL, 'Active'),
('AE037', 'AC017', 'Kawalan keselamatan yang dilaksanakan', NULL, NULL, NULL, NULL, 'Active'),
('AE038', 'AC017', 'Memastikan konfigurasi dan pengurusan aset IT yang selamat', NULL, NULL, NULL, NULL, 'Active'),
('AE039', 'AC017', 'Proses untuk memantau secara berterusan ', NULL, NULL, NULL, NULL, 'Active'),
('AE040', 'AC017', 'Perisian dan perkakasan dikemaskini dan dikemas kini secara berkala ', NULL, NULL, NULL, NULL, 'Active'),
('AE041', 'AC017', 'Keperluan mengkaji dan menilai penggunakan teknologi terkini', NULL, NULL, NULL, NULL, 'Active'),
('AE042', 'AC018', 'Keupayaan SOC untuk memantau dan mengesan ancaman keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AE043', 'AC018', 'Perisian dan perkakasan terkini dalam pelaksanaan dan pemantauan keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AE044', 'AC019', 'Pelan tindak balas insiden untuk menangani insiden keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AE045', 'AC019', 'Peranan dan tanggungjawab ditakrifkan dengan jelas dalam pasukan tindak balas kecemasan', NULL, NULL, NULL, NULL, 'Active'),
('AE046', 'AC019', 'Keperluan pelan insiden keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AE047', 'AC019', 'Insiden keselamatan dan amaran disiasat serta tambah baik pelan mengikut keperluan', NULL, NULL, NULL, NULL, 'Active'),
('AE048', 'AC019', 'Keperluan penyemakan pelan pengurusan insiden', NULL, NULL, NULL, NULL, 'Active'),
('AE049', 'AC021', 'Keperluan pengurusan pembangunan dan pelaksanaan ancaman dan kerentanan keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AE050', 'AC022', 'Keperluan teknologi terkini bagi pengurusan ancaman dan kerentanan keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AE051', 'AC023', 'Keperluan pelan kesinambungan perkhidmatan ICT', NULL, NULL, NULL, NULL, 'Active'),
('AE052', 'AC023', 'Keperluan penyemakan pelan kesinambungan perkhidmatan ICT', NULL, NULL, NULL, NULL, 'Active'),
('AE053', 'AC024', 'Keperluan simulasi terhadap pelan kesinambungan perkhidmatan ICT universiti secara berkala', NULL, NULL, NULL, NULL, 'Active'),
('AE054', 'AC021', 'Keperluan pengurusan ancaman dan kerentanan keselamatan siber secara berkala', NULL, NULL, NULL, NULL, 'Active'),
('AE055', 'AC020', 'Tindak balas dan proses pemulihan daripada insiden keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('AE056', 'AC028', 'element 1101269875555', '4', '2025-11-14', '4', '2025-11-14', 'Active'),
('AE057', 'AC028', 'element 555', '4', '2025-11-14', NULL, NULL, 'Active'),
('AE058', 'AC028', 'element 2025', '4', '2025-11-14', NULL, '2025-11-14', 'Active'),
('AE059', 'AC025', 'element 1', '4', '2025-11-25', NULL, '2025-11-26', 'Active'),
('AE060', 'AC026', 'element 2', '4', '2025-11-25', NULL, NULL, 'Active'),
('AE061', 'AC027', 'element', '4', '2025-11-25', NULL, NULL, 'Active'),
('AE062', 'AC032', 'element criteria 1', '4', '2025-11-26', NULL, NULL, 'Active');

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

-- --------------------------------------------------------

--
-- Table structure for table `response`
--

CREATE TABLE `response` (
  `response_ID` varchar(10) NOT NULL,
  `element_ID` varchar(10) NOT NULL,
  `se_ID` varchar(10) DEFAULT NULL,
  `user_ID` int(11) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `input_at` date DEFAULT NULL,
  `updated_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

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
-- Table structure for table `result_domain`
--

CREATE TABLE `result_domain` (
  `rd_ID` varchar(10) NOT NULL,
  `domain_ID` varchar(10) NOT NULL,
  `domain_score_level` int(11) DEFAULT NULL,
  `num_of_response` int(11) DEFAULT NULL,
  `last_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
('AS001', 1, 'Tidak lengkap', NULL, NULL, NULL, NULL, 'Active'),
('AS002', 2, 'Permulaan', NULL, NULL, NULL, NULL, 'Active'),
('AS003', 3, 'Ditakrif', NULL, NULL, NULL, NULL, 'Active'),
('AS004', 4, 'Diurus', NULL, NULL, NULL, NULL, 'Active'),
('AS005', 5, 'Dioptimum', NULL, NULL, NULL, NULL, 'Active'),
('AS006', 1, 'Ad-Hoc', NULL, NULL, NULL, NULL, 'Active'),
('AS007', 2, 'Dilaksana', NULL, NULL, NULL, NULL, 'Active'),
('AS008', 3, 'Ditakrif', NULL, NULL, NULL, NULL, 'Active'),
('AS009', 4, 'Diurus', NULL, NULL, NULL, NULL, 'Active'),
('AS010', 5, 'Dioptimum', NULL, NULL, NULL, NULL, 'Active');

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
  `details` varchar(500) DEFAULT NULL,
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
('ASA001', 'AE001', 'AS001', 'Tiada struktur tadbir urus  keselamatan siber yang formal.', NULL, NULL, NULL, NULL, 'Active'),
('ASA002', 'AE001', 'AS002', 'Struktur tadbir urus  asas telah wujud tetapi memerlukan lebih formalisasi atau skop yang komprehensif.', NULL, NULL, NULL, NULL, 'Active'),
('ASA003', 'AE001', 'AS003', 'Struktur tadbir urus  keselamatan siber yang jelas ditakrifkan dan didokumentasikan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA004', 'AE001', 'AS004', 'Struktur tadbir urus  diurus secara aktif, dikaji semula, dan disesuaikan dengan keadaan yang berubah.', NULL, NULL, NULL, NULL, 'Active'),
('ASA005', 'AE001', 'AS005', 'Struktur tadbir urus  dipertingkatkan secara berterusan, selari dengan amalan terbaik dan bertindak secara proaktif.', NULL, NULL, NULL, NULL, 'Active'),
('ASA006', 'AE002', 'AS001', 'Peranan dan tanggungjawab yang berkaitan dengan keselamatan siber tidak jelas.', NULL, NULL, NULL, NULL, 'Active'),
('ASA007', 'AE002', 'AS002', '•	Peranan dan tanggungjawab asas telah dikenal pasti.\r\n•	Peranan dan tanggungjawab asas tidak formal atau disampaikan sepenuhnya kepada warga.', NULL, NULL, NULL, NULL, 'Active'),
('ASA008', 'AE002', 'AS003', 'Peranan dan tanggungjawab mengenai keselamatan siber yang jelas, didokumentasikan, dan disampaikan kepada seluruh universiti.', NULL, NULL, NULL, NULL, 'Active'),
('ASA009', 'AE002', 'AS004', 'Kajian semula peranan dan tanggungjawab keselamatan siber dan dikemas kini secara berkala.', NULL, NULL, NULL, NULL, 'Active'),
('ASA010', 'AE002', 'AS005', '•	Peningkatan berterusan dalam menentukan peranan dan tanggungjawab.\r\n•	 Selari dengan perubahan universiti dan keperluan keselamatan siber.', NULL, NULL, NULL, NULL, 'Active'),
('ASA011', 'AE003', 'AS001', 'Tiada strategi keselamatan siber yang formal atau selari dengan objektif universiti.', NULL, NULL, NULL, NULL, 'Active'),
('ASA012', 'AE003', 'AS002', 'Strategi asas wujud tetapi kurang selari sepenuhnya dengan objektif universiti atau skop yang komprehensif.', NULL, NULL, NULL, NULL, 'Active'),
('ASA013', 'AE003', 'AS003', 'Strategi keselamatan siber yang tersedia selari dengan objektif universiti.', NULL, NULL, NULL, NULL, 'Active'),
('ASA014', 'AE003', 'AS004', 'Strategi tersebut dikaji semula dan dikemaskini secara berkala untuk memastikan keselarasan berterusan dengan objektif universiti dan landskap keselamatan siber yang sentiasa berkembang.', NULL, NULL, NULL, NULL, 'Active'),
('ASA015', 'AE003', 'AS005', 'Strategi diselaraskan, diuruskan, dan disesuaikan secara proaktif, dengan mekanisme maklum balas dan peningkatan berterusan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA016', 'AE004', 'AS001', 'Tiada kajian formal dilakukan oleh pengurusan atasan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA017', 'AE004', 'AS002', 'Kajian sesekali, tetapi tidak secara berkala atau sistematik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA018', 'AE004', 'AS003', 'Kajian dasar dan inisiatif keselamatan siber adalah secara berkala dan berjadual.', NULL, NULL, NULL, NULL, 'Active'),
('ASA019', 'AE004', 'AS004', 'Kajian pengurusan yang komprehensif dan sistematik, termasuk memberikan maklum balas dan langkah penyesuaian berdasarkan hasil kajian.', NULL, NULL, NULL, NULL, 'Active'),
('ASA020', 'AE004', 'AS005', 'Proses kajian adalah secara dinamik dan berterusan dengan mengintegrasikan metrik dan real data time untuk memberi maklumat bagi membuat keputusan dan penyesuaian.', NULL, NULL, NULL, NULL, 'Active'),
('ASA021', 'AE005', 'AS001', 'Tiada atau kurang peruntukan bagi keselamatan siber.', NULL, NULL, NULL, NULL, 'Active'),
('ASA022', 'AE005', 'AS002', 'Sebahagian peruntukan kewangan dan sumber disediakan tetapi tidak secara konsisten.', NULL, NULL, NULL, NULL, 'Active'),
('ASA023', 'AE005', 'AS003', 'Terdapat proses formal untuk memperuntukkan sumber berdasarkan keperluan keselamatan siber.', NULL, NULL, NULL, NULL, 'Active'),
('ASA024', 'AE005', 'AS004', 'Sumber disediakan secara konsisten, dan terdapat pendekatan proaktif untuk memastikan keperluan keselamatan siber dipenuhi.', NULL, NULL, NULL, NULL, 'Active'),
('ASA025', 'AE005', 'AS005', 'Sumber disediakan secara konsisten dan terdapat pendekatan proaktif dalam memastikan keperluan keselamatan siber dipenuhi.', NULL, NULL, NULL, NULL, 'Active'),
('ASA026', 'AE006', 'AS001', '•	Usaha kesedaran tidak dilaksanakan secara sistematik.\r\n•	Kurang mendapat sokongan dari pengurusan atasan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA027', 'AE006', 'AS002', 'Beberapa usaha bagi program kesedaran telah dilaksanakan, tetapi ia tidak menyeluruh atau konsisten.', NULL, NULL, NULL, NULL, 'Active'),
('ASA028', 'AE006', 'AS003', 'Dasar, prosedur dan program latihan yang ditetapkan, disokong oleh pengurusan tertinggi.', NULL, NULL, NULL, NULL, 'Active'),
('ASA029', 'AE006', 'AS004', '•	Pengurusan atasan secara aktif mengurus inisiatif kesedaran keselamatan siber.\r\n•	Memastikan ia dilaksanakan dengan berkesan dan disepadukan ke dalam budaya organisasi.', NULL, NULL, NULL, NULL, 'Active'),
('ASA030', 'AE006', 'AS005', '•	Pengurusan atasan secara aktif mengurus inisiatif kesedaran keselamatan siber.\r\n•	Memastikan ia dilaksanakan dengan berkesan dan disepadukan ke dalam budaya organisasi.', NULL, NULL, NULL, NULL, 'Active'),
('ASA031', 'AE007', 'AS001', 'Warga universiti, pihak ketiga dan pihak berkepentingan  tidak jelas tentang objektif polisi/prosedur serta tanggungjawab.', NULL, NULL, NULL, NULL, 'Active'),
('ASA032', 'AE007', 'AS002', 'Ketelusan mengenai objektif polisi/prosedur serta tanggungjawab warga universiti, pihak ketiga dan pihak berkepentingan tidak disampaikan dengan jelas.', NULL, NULL, NULL, NULL, 'Active'),
('ASA033', 'AE007', 'AS003', 'Objektif polisi/prosedur serta tanggungjawab oleh warga universiti, pihak ketiga dan pihak berkepentingan jelas digariskan dalam dasar/polisi.', NULL, NULL, NULL, NULL, 'Active'),
('ASA034', 'AE007', 'AS004', 'Polisi/prosedur serta tanggungjawab disampaikan dengan baik, dan terdapat mekanisme untuk dikuatkuasa.', NULL, NULL, NULL, NULL, 'Active'),
('ASA035', 'AE007', 'AS005', 'Ketelusan diutamakan, dan semua pihak berkepentingan memahami sepenuhnya peranan dan tanggungjawab mereka.', NULL, NULL, NULL, NULL, 'Active'),
('ASA036', 'AE008', 'AS001', 'Polisi dan prosedur keselamatan siber tidak dibangunkan secara komprehensif', NULL, NULL, NULL, NULL, 'Active'),
('ASA037', 'AE008', 'AS002', 'Polisi dan prosedur keselamatan siber termasuk beberapa kawalan tetapi tidak meliputi semua amalan terbaik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA038', 'AE008', 'AS003', 'Polisi dan prosedur keselamatan siber diselaraskan dengan piawaian antarabangsa dan amalan terbaik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA039', 'AE008', 'AS004', 'Polisi dan prosedur keselamatan siber sentiasa dikemas kini untuk menggabungkan piawaian baharu dan amalan terbaik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA040', 'AE008', 'AS005', 'Polisi/prosedur keselamatan siber diperhalusi secara berterusan untuk kekal mendahului ancaman yang muncul dan perubahan dalam amalan terbaik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA041', 'AE009', 'AS001', 'Polisi dan prosedur keselamatan siber tidak disebarkan dengan berkesan, dan kesedaran adalah rendah.', NULL, NULL, NULL, NULL, 'Active'),
('ASA042', 'AE009', 'AS002', 'Polisi dan prosedur keselamatan siber disebarkan, tetapi usaha kesedaran tidak konsisten.', NULL, NULL, NULL, NULL, 'Active'),
('ASA043', 'AE009', 'AS003', 'Polisi dan prosedur keselamatan siber asar dimaklumkan secara aktif kepada semua pihak yang berkaitan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA044', 'AE009', 'AS004', 'Terdapat kempen secara berkala dalam memastikan kesedaran dan pemahaman polisi dan prosedur di peringkat universiti dan pihak ketiga.', NULL, NULL, NULL, NULL, 'Active'),
('ASA045', 'AE009', 'AS005', 'Polisi/prosedur keselamatan siber telah menjadi budaya, dan semua pihak berkepentingan sedar sepenuhnya akan tanggungjawab mereka.', NULL, NULL, NULL, NULL, 'Active'),
('ASA046', 'AE010', 'AS001', 'Kajian atau semakan tidak wujud.', NULL, NULL, NULL, NULL, 'Active'),
('ASA047', 'AE010', 'AS002', 'Semakan dijalankan sekali-sekala tetapi tidak sistematik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA048', 'AE010', 'AS003', 'Terdapat jadual rasmi untuk semakan dasar.', NULL, NULL, NULL, NULL, 'Active'),
('ASA049', 'AE010', 'AS004', 'Semakan dijalankan secara berkala dan disesuaikan dengan keperluan semasa.', NULL, NULL, NULL, NULL, 'Active'),
('ASA050', 'AE010', 'AS005', 'Semakan dijalankan secara proaktif, dengan tumpuan pada peningkatan berterusan', NULL, NULL, NULL, NULL, 'Active'),
('ASA051', 'AE011', 'AS001', 'Penilaian risiko tidak dijalankan atau bersistematik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA052', 'AE011', 'AS002', 'Beberapa penilaian risiko dijalankan tetapi hanya merangkumi beberapa bahagian atau tidak sepenuhnya secara sistematik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA053', 'AE011', 'AS003', 'Proses formal dan ditakrifkan untuk penilaian risiko berkala wujud, tetapi perlu diintegrasikan sepenuhnya atau lebih komprehensif.', NULL, NULL, NULL, NULL, 'Active'),
('ASA054', 'AE011', 'AS004', '•	Penilaian risiko yang komprehensif dan berkala dijalankan. \r\n•	Diurus secara aktif yang merupakan sebahagian daripada strategi pengurusan risiko universiti.', NULL, NULL, NULL, NULL, 'Active'),
('ASA055', 'AE011', 'AS005', 'Pengoptimuman dan peningkatan berterusan proses penilaian risiko dengan mengintegrasi kaedah yang lebih canggih bagi menyesuaikan diri dengan ancaman baru.', NULL, NULL, NULL, NULL, 'Active'),
('ASA056', 'AE012', 'AS001', 'Tiada peruntukan atau sedikit peruntukan berdasarkan penilaian risiko.', NULL, NULL, NULL, NULL, 'Active'),
('ASA057', 'AE012', 'AS002', 'Beberapa penjajaran peruntukan berdasarkan risiko, tetapi tidak sistematik atau berdasarkan pemahaman risiko yang komprehensif.', NULL, NULL, NULL, NULL, 'Active'),
('ASA058', 'AE012', 'AS003', 'Proses formal untuk memberi keutamaan kepada peruntukan keselamatan siber berdasarkan penilaian risiko.', NULL, NULL, NULL, NULL, 'Active'),
('ASA059', 'AE012', 'AS004', 'Proses yang sistematik dan diurus dengan baik untuk memberi keutamaan kepada semua peruntukan keselamatan siber berdasarkan penilaian risiko yang komprehensif.', NULL, NULL, NULL, NULL, 'Active'),
('ASA060', 'AE012', 'AS005', 'Pengoptimuman berterusan penjajaran pelaburan, menggunakan kaedah yang lebih canggih dan berterusan dengan maklumat dan senario risiko yang baru.', NULL, NULL, NULL, NULL, 'Active'),
('ASA061', 'AE013', 'AS001', 'Pelan rawatan risiko yang tidak formal; tindak balas risiko adalah reaktif dan tidak bersistematik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA062', 'AE013', 'AS002', 'Pelan rawatan risiko asas wujud, tetapi perlu lebih komprehensif dan praktikal.', NULL, NULL, NULL, NULL, 'Active'),
('ASA063', 'AE013', 'AS003', 'Pelan rawatan risiko yang jelas ditakrifkan untuk semua risiko penting tidak diuji sepenuhnya atau diintegrasikan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA064', 'AE013', 'AS004', 'Pelan rawatan risiko yang dikaji semula dan diurus secara berkala, diintegrasikan ke dalam keseluruhan strategi pengurusan risiko.', NULL, NULL, NULL, NULL, 'Active'),
('ASA065', 'AE013', 'AS005', 'Proses rawatan risiko yang telah dikaji dan diperbaiki secara berterusan, dengan  adapatasi proses tersebut dengan landskap risiko yang sering berubah.', NULL, NULL, NULL, NULL, 'Active'),
('ASA066', 'AE014', 'AS001', 'Langkah-langkah pemantauan dan kajian keberkesanan pengurangan risiko adalah minima.', NULL, NULL, NULL, NULL, 'Active'),
('ASA067', 'AE014', 'AS002', 'Beberapa proses pemantauan dan kajian dilaksanakan tetapi kurang sistematik dan praktikal.', NULL, NULL, NULL, NULL, 'Active'),
('ASA068', 'AE014', 'AS003', 'Proses ditakrifkan untuk memantau dan mengkaji langkah-langkah pengurangan risiko dengan diintegrasikan sepenuhnya atau komprehensif.', NULL, NULL, NULL, NULL, 'Active'),
('ASA069', 'AE014', 'AS004', '•	Langkah-langkah pengurangan risiko adalah secara menyeluruh dan diintegrasikan ke dalam strategi pengurusan risiko.\r\n•	Pemantauan dan kajian risiko yang berkala, sistematik, dan menyeluruh.', NULL, NULL, NULL, NULL, 'Active'),
('ASA070', 'AE014', 'AS005', 'Peningkatan berterusan dan pengoptimuman proses pemantauan, dengan memasukkan maklum balas dan adaptasi dengan perubahan landskap risiko yang sering berubah.', NULL, NULL, NULL, NULL, 'Active'),
('ASA071', 'AE015', 'AS001', 'Polisi dan prosedur tidak sejajar dengan standard dan amalan terbaik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA072', 'AE015', 'AS002', '•	Sesetengah penjajaran wujud, tetapi polisi dan prosedur tidak meliputi semua standard dan amalan terbaik yang diperlukan.\r\n•	Pelaksanaan yang tidak konsisten.', NULL, NULL, NULL, NULL, 'Active'),
('ASA073', 'AE015', 'AS003', '•	Polisi dan prosedur jelas sejajar dengan standard dan amalan terbaik yang berkaitan.\r\n•	Terdapat pendekatan yang sistematik untuk memastikan pematuhan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA074', 'AE015', 'AS004', 'Penjajaran dengan standard dan amalan terbaik dikekalkan secara aktif, kemas kini dan pelarasan tetap kepada polisi dan prosedur secara berkala.', NULL, NULL, NULL, NULL, 'Active'),
('ASA075', 'AE015', 'AS005', 'Polisi dan prosedur sejajar dengan standard semasa dan amalan terbaik dan berterusan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA076', 'AE016', 'AS001', 'Audit dan penilaian yang jarang dilaksanakan atau tidak wujud.', NULL, NULL, NULL, NULL, 'Active'),
('ASA077', 'AE016', 'AS002', 'Audit sesekali dijalankan, tetapi tidak teratur atau kurang menyeluruh.', NULL, NULL, NULL, NULL, 'Active'),
('ASA078', 'AE016', 'AS003', 'Audit dan penilaian yang dijadualkan secara berkala dengan pendekatan yang berstruktur tetapi ada ruang untuk penambahbaikan dalam skop.', NULL, NULL, NULL, NULL, 'Active'),
('ASA079', 'AE016', 'AS004', 'Audit dan penilaian berkala yang menyeluruh dan berkesan, dengan pengubahsuaian berdasarkan standard dan peraturan yang berkembang.', NULL, NULL, NULL, NULL, 'Active'),
('ASA080', 'AE016', 'AS005', 'Penambahbaikan berterusan dalam proses audit, dengan amalan terbaik dan pendekatan inovatif untuk memastikan penilaian pematuhan yang menyeluruh dan cekap.', NULL, NULL, NULL, NULL, 'Active'),
('ASA081', 'AE017', 'AS001', 'Tindak balas yang tidak konsisten atau reaktif terhadap dapatan audit.', NULL, NULL, NULL, NULL, 'Active'),
('ASA082', 'AE017', 'AS002', 'Tindak balas asas terhadap dapatan audit tetapi kurang cepat atau secara menyeluruh.', NULL, NULL, NULL, NULL, 'Active'),
('ASA083', 'AE017', 'AS003', 'Pendekatan sistematik untuk menangani dapatan audit tetapi kurang berkesan dalam pelaksanaan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA084', 'AE017', 'AS004', 'Tindak balas yang berkesan dan tepat pada masanya terhadap dapatan audit, dengan kajian berkala dan penambahbaikan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA085', 'AE017', 'AS005', 'Pendekatan proaktif dan menyeluruh untuk menangani dan meramalkan hasil dapatan audit, memberi tumpuan kepada penambahbaikan berterusan dan amalan terbaik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA086', 'AE018', 'AS001', 'Proses dokumentasi yang terhad atau tidak teratur.', NULL, NULL, NULL, NULL, 'Active'),
('ASA087', 'AE018', 'AS002', 'Proses dokumentasi asas ada tetapi kurang menyeluruh atau kemas kini secara berkala.', NULL, NULL, NULL, NULL, 'Active'),
('ASA088', 'AE018', 'AS003', 'Proses dokumentasi berstruktur tetapi memerlukan peningkatan dalam aksesibiliti atau kelengkapannya.', NULL, NULL, NULL, NULL, 'Active'),
('ASA089', 'AE018', 'AS004', 'Proses dokumentasi yang dikendalikan dengan baik dan komprehensif, dengan kemas kini dan ulasan berkala.', NULL, NULL, NULL, NULL, 'Active'),
('ASA090', 'AE018', 'AS005', 'Strategi dokumentasi yang canggih, termasuk automasi dan integrasi dengan sistem lain, memastikan ketepatan dan kecekapan yang tinggi.', NULL, NULL, NULL, NULL, 'Active'),
('ASA091', 'AE019', 'AS001', '•	Kesedaran tentang kepentingan keselamatan siber tidak wujud atau rendah dikalangan pihak berkepentingan.\r\n•	Terdapat sedikit penekanan terhadap kepentingan kesedaran keselamatan siber.', NULL, NULL, NULL, NULL, 'Active'),
('ASA092', 'AE019', 'AS002', '•	Terdapat beberapa kesedaran tentang kepentingan keselamatan siber, tetapi ia tidak konsisten di peringkat universiti.\r\n•	Usaha untuk menggalakkan kesedaran adalah secara ad hoc.', NULL, NULL, NULL, NULL, 'Active'),
('ASA093', 'AE019', 'AS003', '•	Kepentingan keselamatan siber dikomunikasikan dengan jelas dan difahami oleh pihak berkepentingan.\r\n•	Terdapat program kesedaran rasmi yang disediakan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA094', 'AE019', 'AS004', 'Kesedaran tentang kepentingan keselamatan siber secara aktif dipromosikan dan diperkukuh melalui komunikasi berterusan, latihan dan inisiatif kesedaran.', NULL, NULL, NULL, NULL, 'Active'),
('ASA095', 'AE019', 'AS005', '•	Keselamatan siber diiktiraf sebagai keutamaan utama.\r\n•	Terdapat budaya kesedaran dalam keselamatan siber yang kukuh di seluruh universiti.', NULL, NULL, NULL, NULL, 'Active'),
('ASA096', 'AE020', 'AS001', '•	Pengasingan tugas dalam keselamatan siber tidak difahami atau dilaksanakan dengan baik.\r\n•	Terdapat sedikit kesedaran tentang kepentingannya.', NULL, NULL, NULL, NULL, 'Active'),
('ASA097', 'AE020', 'AS002', 'Beberapa pengasingan tugas wujud, tetapi ia tidak diformalkan atau digunakan secara konsisten di seluruh universiti.', NULL, NULL, NULL, NULL, 'Active'),
('ASA098', 'AE020', 'AS003', '•	Peranan dan tanggungjawab dalam keselamatan siber ditakrifkan dengan jelas.\r\n•	Terdapat pendekatan berstruktur untuk pengasingan tugas.', NULL, NULL, NULL, NULL, 'Active'),
('ASA099', 'AE020', 'AS004', 'Pengasingan tugas dikuatkuasakan dan dipantau secara aktif untuk mengelakkan konflik kepentingan dan memastikan akauntabiliti.', NULL, NULL, NULL, NULL, 'Active'),
('ASA100', 'AE020', 'AS005', 'Pengasingan tugas dioptimumkan untuk meminimumkan risiko dan memaksimumkan kecekapan dalam operasi keselamatan siber, dengan pengasingan tanggungjawab yang jelas dan mekanisme pengawasan yang berkesan disediakan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA101', 'AE021', 'AS001', 'Keberkesanan program kesedaran dan latihan tidak diukur atau dinilai.', NULL, NULL, NULL, NULL, 'Active'),
('ASA102', 'AE021', 'AS002', 'Terdapat beberapa percubaan untuk mengukur keberkesanan, tetapi ia tidak formal.', NULL, NULL, NULL, NULL, 'Active'),
('ASA103', 'AE021', 'AS003', 'Penilaian yang lebih berstruktur terhadap keberkesanan latihan kurang mendalam atau konsisten.', NULL, NULL, NULL, NULL, 'Active'),
('ASA104', 'AE021', 'AS004', 'Keberkesanan program dinilai secara berkala, dan maklum balas digunakan untuk menambah baik dan memperhalusi inisiatif.', NULL, NULL, NULL, NULL, 'Active'),
('ASA105', 'AE021', 'AS005', 'Peningkatan berterusan pengukuran keberkesanan, menggunakan kaedah yang canggih seperti simulasi, ujian berkala, dan analisis maklum balas.', NULL, NULL, NULL, NULL, 'Active'),
('ASA106', 'AE022', 'AS001', 'Motivasi kakitangan terhadap keselamatan siber tidak wujud atau rendah.', NULL, NULL, NULL, NULL, 'Active'),
('ASA107', 'AE022', 'AS002', '•	Sedikit kesedaran dan motivasi dalam kalangan kakitangan.\r\n•	Kesedaran tidak konsisten di peringkat organisasi.', NULL, NULL, NULL, NULL, 'Active'),
('ASA108', 'AE022', 'AS003', 'Motivasi ke arah keselamatan siber digalakkan dan disokong secara aktif melalui program kesedaran, insentif atau skim pengiktirafan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA109', 'AE022', 'AS004', 'Terdapat budaya kesedaran keselamatan siber dan motivasi dikekalkan secara konsisten melalui latihan berterusan, komunikasi dan pengiktirafan pencapaian.', NULL, NULL, NULL, NULL, 'Active'),
('ASA110', 'AE022', 'AS005', 'Keselamatan Siber telah tertanam dalam budaya organisasi, dan kakitangan sangat bermotivasi dan proaktif dalam pendekatan mereka terhadap keselamatan siber.', NULL, NULL, NULL, NULL, 'Active'),
('ASA111', 'AE023', 'AS001', '•	Kekurangan kemahiran dan kepakaran keselamatan siber yang diperlukan.\r\n•	Terdapat sedikit peruntukan bagi program latihan atau pembangunan kakitangan ICT.', NULL, NULL, NULL, NULL, 'Active'),
('ASA112', 'AE023', 'AS002', 'Sesetengah kakitangan ICT mempunyai kemahiran asas keselamatan siber, tetapi terdapat jurang dalam kepakaran atau peluang latihan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA113', 'AE023', 'AS003', 'Terdapat program latihan berstruktur disediakan untuk membangunkan kemahiran keselamatan siber di kalangan kakitangan ICT, dan amalan pengambilan pekerja mengutamakan kepakaran keselamatan siber.', NULL, NULL, NULL, NULL, 'Active'),
('ASA114', 'AE023', 'AS004', '•	Kakitangan ICT menerima latihan dan peluang pembangunan kendiri. \r\n•	Terdapat laluan kerjaya yang jelas bagi profesional keselamatan siber dalam organisasi.', NULL, NULL, NULL, NULL, 'Active'),
('ASA115', 'AE023', 'AS005', '•	Kakitangan ICT memiliki kemahiran dan kepakaran keselamatan siber terkini.\r\n•	Terdapat budaya pembelajaran dan inovasi berterusan dalam keselamatan siber.', NULL, NULL, NULL, NULL, 'Active'),
('ASA116', 'AE024', 'AS001', '•	Tiada program penggantian rasmi disediakan untuk bahagian/unit keselamatan siber.\r\n•	Keperluan kakitangan ditangani secara reaktif.', NULL, NULL, NULL, NULL, 'Active'),
('ASA117', 'AE024', 'AS002', 'Keperluan penggantian dikenal pasti, tetapi tidak ada proses rasmi untuk perancangan penggantian atau pembangunan bakat.', NULL, NULL, NULL, NULL, 'Active'),
('ASA118', 'AE024', 'AS003', 'Terdapat program penggantian berstruktur, termasuk perancangan penggantian, pembangunan bakat dan strategi pengambilan untuk memastikan kesinambungan keupayaan keselamatan siber.', NULL, NULL, NULL, NULL, 'Active'),
('ASA119', 'AE024', 'AS004', 'Keperluan program penggantian disemak dan dikemas kini secara berkala berdasarkan keperluan organisasi dan perubahan dalam landskap keselamatan siber.', NULL, NULL, NULL, NULL, 'Active'),
('ASA120', 'AE024', 'AS005', 'Program penggantian adalah sangat cekap dan menyesuaikan diri, memastikan keperluan sumber keselamatan siber mempunyai bakat yang sesuai dalam menangani ancaman dan cabaran yang muncul.', NULL, NULL, NULL, NULL, 'Active'),
('ASA121', 'AE025', 'AS001', 'Tiada inventori aset maklumat atau formal.', NULL, NULL, NULL, NULL, 'Active'),
('ASA122', 'AE025', 'AS002', 'Inventori asas wujud tetapi perlu lebih komprehensif atau dikemaskini secara berkala.', NULL, NULL, NULL, NULL, 'Active'),
('ASA123', 'AE025', 'AS003', 'Proses dan prosedur yang ditakrifkan dengan baik dalam mengekalkan sistem inventori lengkap aset maklumat, tetapi hanya merangkumi beberapa aset atau tidak sepenuhnya bersepadu.', NULL, NULL, NULL, NULL, 'Active'),
('ASA124', 'AE025', 'AS004', 'Inventori yang dikemaskini dan dikelola secara berkala merangkumi semua aset maklumat, yang bersepadu bagi proses IT dan keselamatan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA125', 'AE025', 'AS005', 'Peningkatan berterusan dan pengoptimuman proses inventori aset, menyesuaikan diri dengan teknologi baru dan perubahan universiti.', NULL, NULL, NULL, NULL, 'Active'),
('ASA126', 'AE026', 'AS001', 'Tiada pengelasan maklumat atau pelaksanaan pengelasan yang kurang serta tidak konsisten.', NULL, NULL, NULL, NULL, 'Active'),
('ASA127', 'AE026', 'AS002', 'Pengelasan asas beberapa maklumat, tetapi perlu lebih komprehensif atau dilaksanakan secara konsisten.', NULL, NULL, NULL, NULL, 'Active'),
('ASA128', 'AE026', 'AS003', 'Proses formal untuk mengklasifikasi semua malumat aset mengikut tahap klasifikasi tetapi perlu lebih mendalam atau bersepadu dengan kaedah lain.', NULL, NULL, NULL, NULL, 'Active'),
('ASA129', 'AE026', 'AS004', 'Pengelasan yang komprehensif dan dilaksanakan secara konsisten, yang bersepadu ke dalam proses keselamatan dan pengurusan risiko yang lebih luas.', NULL, NULL, NULL, NULL, 'Active'),
('ASA130', 'AE026', 'AS005', 'Proses pengelasan yang dikaji semula dan diperbaiki secara berterusan, menyesuaikan diri dengan operasi perkhidmatan dan perubahan landskap ancaman.', NULL, NULL, NULL, NULL, 'Active'),
('ASA131', 'AE027', 'AS001', '•	Langkah-langkah yang minimum atau ad-hoc untuk perlindungan dan pembuangan maklumat.\r\n•	Proses tidak sepenuhnya praktikal atau bersistematik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA132', 'AE027', 'AS002', 'Beberapa langkah perlindungan dan proses pembuangan sedia ada, tetapi perlu lebih komprehensif dan praktikal.', NULL, NULL, NULL, NULL, 'Active'),
('ASA133', 'AE027', 'AS003', 'Proses yang jelas ditakrifkan bagi perlindungan, sanitasi dan pelupusan maklumat.', NULL, NULL, NULL, NULL, 'Active'),
('ASA134', 'AE027', 'AS004', '•	Proses perlindungan kitar hayat yang baik dikelola.\r\n•	Proses perlindungan, sanitasi dan pelupusan yang diintegrasikan ke dalam strategi keselamatan maklumat secara keseluruhan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA135', 'AE027', 'AS005', 'Peningkatan berterusan dan pengoptimuman perlindungan aset dan proses sanitasi dan pelupusan, serta kebolehan adapatasi dengan teknologi baru dan ancaman.', NULL, NULL, NULL, NULL, 'Active'),
('ASA136', 'AE028', 'AS001', 'Kawalan akses pengguna tidak diurus dengan baik, dengan pengawasan yang terhad dan penguatkuasaan yang tidak konsisten.', NULL, NULL, NULL, NULL, 'Active'),
('ASA137', 'AE028', 'AS002', 'Sesetengah kawalan akses pengguna telah disediakan, tetapi tidak menyeluruh atau dikuatkuasakan secara konsisten.', NULL, NULL, NULL, NULL, 'Active'),
('ASA138', 'AE028', 'AS003', 'Polisi dan prosedur yang ditakrifkan untuk kawalan akses wujud, tetapi mungkin tidak diintegrasikan sepenuhnya atau dilaksanakan secara konsisten.', NULL, NULL, NULL, NULL, 'Active'),
('ASA139', 'AE028', 'AS004', 'Kawalan akses yang komprehensif dan dikelola dengan baik di semua sistem, dengan kajian semula dan kemaskini secara berkala.', NULL, NULL, NULL, NULL, 'Active'),
('ASA140', 'AE028', 'AS005', 'Peningkatan berterusan dan pengoptimuman proses kawalan akses, menyesuaikan diri dengan teknologi baru dan ancaman.', NULL, NULL, NULL, NULL, 'Active'),
('ASA141', 'AE029', 'AS001', 'Mekanisme pengesahan lemah atau tidak konsisten dikuatkuasakan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA142', 'AE029', 'AS002', 'Pelaksanaan asas kaedah pengesahan yang lebih kukuh, tetapi tidak komprehensif atau dikuatkuasakan untuk semua sumber sensitif.', NULL, NULL, NULL, NULL, 'Active'),
('ASA143', 'AE029', 'AS003', 'Polisi dan prosedur yang ditakrifkan untuk pengesahan yang kukuh sedia ada, tetapi tidak diintegrasikan sepenuhnya dengan semua sistem atau dikuatkuasakan secara konsisten.', NULL, NULL, NULL, NULL, 'Active'),
('ASA144', 'AE029', 'AS004', 'Kaedah pengesahan yang kuat seperti pengesahan multi-faktor dilaksanakan sepenuhnya dan dikelola di semua sumber sensitif.', NULL, NULL, NULL, NULL, 'Active'),
('ASA145', 'AE029', 'AS005', 'Menilai secara berkala dan mengambil teknologi dan kaedah pengesahan terkini, memastikan keselamatan yang optimum.', NULL, NULL, NULL, NULL, 'Active'),
('ASA146', 'AE030', 'AS001', 'Hak akses pengguna tidak disemak atau jarang sama sekali.', NULL, NULL, NULL, NULL, 'Active'),
('ASA147', 'AE030', 'AS002', 'Hak akses disemak secara berkala, tetapi proses manual dan terdedah kepada ralat atau kesilapan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA148', 'AE030', 'AS003', 'Terdapat prosedur untuk semakan akses pengguna, termasuk mempunyai sistem automatik provisioning dan de-provisioning.', NULL, NULL, NULL, NULL, 'Active'),
('ASA149', 'AE030', 'AS004', 'Hak akses pengguna kerap disemak dan diselaraskan berdasarkan perubahan peranan atau tanggungjawab.', NULL, NULL, NULL, NULL, 'Active'),
('ASA150', 'AE030', 'AS005', '•	Peningkatan berterusan dan penyempurnaan proses pengurusan akses.\r\n•	Pemantauan berterusan untuk akses tanpa kebenaran.', NULL, NULL, NULL, NULL, 'Active'),
('ASA151', 'AE031', 'AS001', 'Pemantauan dan pemeriksaan aktiviti akses pengguna yang tiada atau terhad.', NULL, NULL, NULL, NULL, 'Active'),
('ASA152', 'AE031', 'AS002', '•	Pemantauan dan pengauditan asas disediakan.\r\n•	Tidak menyeluruh atau digunakan dengan berkesan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA153', 'AE031', 'AS003', '•	Proses formal untuk pemantauan dan pengauditan wujud.\r\n•	Kekurangan atau penyepaduan penuh dengan proses keselamatan lain.', NULL, NULL, NULL, NULL, 'Active'),
('ASA154', 'AE031', 'AS004', 'Pemantauan dan pengauditan akses pengguna yang komprehensif dan berkesan, disepadukan ke dalam rangka kerja keselamatan dan pematuhan keseluruhan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA155', 'AE031', 'AS005', 'Peningkatan berterusan dan pengoptimuman proses pemantauan dan pengauditan, menggunakan alat dan kaedah terkini.', NULL, NULL, NULL, NULL, 'Active'),
('ASA156', 'AE032', 'AS001', 'Akses pihak ketiga tidak diurus atau dipantau dengan berkesan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA157', 'AE032', 'AS002', 'Prosedur pemantauan asas, tetapi tidak konsisten atau terhad dalam skop.', NULL, NULL, NULL, NULL, 'Active'),
('ASA158', 'AE032', 'AS003', 'Terdapat dasar dan prosedur rasmi untuk mengurus akses pihak ketiga, termasuk penilaian risiko pihak ketiga dan keperluan kontrak.', NULL, NULL, NULL, NULL, 'Active'),
('ASA159', 'AE032', 'AS004', 'Akses pihak ketiga dipantau dan diurus secara aktif, dengan audit dan semakan pematuhan yang kerap.', NULL, NULL, NULL, NULL, 'Active'),
('ASA160', 'AE032', 'AS005', 'Pengurusan akses pihak ketiga dioptimumkan untuk pengurangan risiko, dengan pemantauan berterusan dan kawalan automatik untuk akses pihak ketiga.', NULL, NULL, NULL, NULL, 'Active'),
('ASA161', 'AE033', 'AS001', '•	Tiada proses atau usaha yang ditetapkan bagi mendidik atau menyampaikan keperluan keselamatan siber kepada mereka.\r\n•	Pertimbangan keselamatan siber mungkin kurang atau tidak diendahkan sepenuhnya.', NULL, NULL, NULL, NULL, 'Active'),
('ASA162', 'AE033', 'AS002', '•	Pihak universiti menyedari kepentingan kesedaran keselamatan siber pihak ketiga dan telah memulakan beberapa usaha untuk mendidik atau menyampaikan keperluan keselamatan siber kepada mereka.\r\n•	Pihak ketiga mempunyai pengetahuan minimum tentang keperluan keselamatan siber\r\n•	Usaha ini kurang konsisten merentas semua pihak ketiga.', NULL, NULL, NULL, NULL, 'Active'),
('ASA163', 'AE033', 'AS003', 'Kaedah rasmi wujud untuk memaklumkan pihak ketiga tentang kepentingan keselamatan siber. Ini boleh melibatkan penyediaan dokumen, piawaian atau bahan latihan yang memperincikan jangkaan dan keperluan keselamatan siber', NULL, NULL, NULL, NULL, 'Active'),
('ASA164', 'AE033', 'AS004', '•	Universiti secara agresif menyelia pengetahuan keselamatan siber pihak ketiga dengan berkomunikasi dan menguatkuasakan keperluan keselamatan siber secara konsisten.\r\n•	Sistem ditubuhkan untuk mengawasi pematuhan pihak ketiga terhadap piawaian keselamatan siber, dan maklum balas diberikan untuk meningkatkan pemahaman dan pematuhan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA165', 'AE033', 'AS005', '•	Universiti secara konsisten menyasarkan untuk meningkatkan aktiviti kesedaran keselamatan siber pihak ketiga.\r\n•	Bahan komunikasi dan latihan disesuaikan untuk menangani permintaan unik dan risiko pelbagai perhubungan pihak ketiga.\r\n•	Prosedur penambahbaikan berterusan diwujudkan untuk menjamin keberkesanan kempen kesedaran.', NULL, NULL, NULL, NULL, 'Active'),
('ASA166', 'AE034', 'AS001', 'Penilaian jarang atau tidak ada keperluan keselamatan siber dalam kontrak.', NULL, NULL, NULL, NULL, 'Active'),
('ASA167', 'AE034', 'AS002', 'Perjanjian keselamatan maklumat dengan pihak ketiga diselia dan dikuatkuasakan, walaupun secara tidak konsisten', NULL, NULL, NULL, NULL, 'Active'),
('ASA168', 'AE034', 'AS003', '•	Penilaian berkala dengan klausa dan keperluan keselamatan siber yang ditakrifkan.\r\n•	Perjanjian yang jelas, bertulis dan konsisten mengawal semua perkongsian pihak ketiga.\r\n•	Pemeriksaan pematuhan tetap dan mekanisme ketidakpatuhan telah disediakan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA169', 'AE034', 'AS004', '•	Pematuhan perjanjian keselamatan maklumat dikendalikan dan disemak secara aktif semasa dikendalikan.\r\n•	Terdapat proses untuk menjejak, melaporkan dan mengurus isu dan pelanggaran pematuhan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA170', 'AE034', 'AS005', 'Amalan terbaik dalam keperluan keselamatan siber kontrak, dengan fleksibiliti bagi mengurangkan ancaman dan meminimakan risiko pihak ketiga.', NULL, NULL, NULL, NULL, 'Active'),
('ASA171', 'AE035', 'AS006', 'Tiada penilaian formal; bergantung kepada penilaian secara ad-hoc atau tidak formal.', NULL, NULL, NULL, NULL, 'Active'),
('ASA172', 'AE035', 'AS007', 'Penilaian asas dijalankan tetapi mungkin kurang mendalam atau konsisten.', NULL, NULL, NULL, NULL, 'Active'),
('ASA173', 'AE035', 'AS008', 'Penilaian asas dijalankan tetapi mungkin kurang mendalam atau konsisten.', NULL, NULL, NULL, NULL, 'Active'),
('ASA174', 'AE035', 'AS009', 'Pemantauan berterusan dan proaktif terhadap amalan keselamatan siber pihak ketiga, dengan penilaian semula berkala.', NULL, NULL, NULL, NULL, 'Active'),
('ASA175', 'AE035', 'AS010', 'Proses penilaian yang canggih, termasuk alat pemantauan secara sistematik dan integrasi sistem pengurusan pihak ketiga.', NULL, NULL, NULL, NULL, 'Active'),
('ASA176', 'AE036', 'AS006', 'Tiada proses rasmi bagi penglibatan kumpulan pakar atau pakar bidang.', NULL, NULL, NULL, NULL, 'Active'),
('ASA177', 'AE036', 'AS007', '•	Usaha untuk penglibatan kumpulan pakar atau pakar bidang tidak konsisten.\r\n•	Tidak mempunyai proses yang formal.', NULL, NULL, NULL, NULL, 'Active'),
('ASA178', 'AE036', 'AS008', '•	Proses penglibatan kumpulan pakar atau pakar lapangan itu tidak digunakan secara konsisten atau didokumenkan dengan baik.\r\n•	Penglibatan pakar berlaku mengikut keperluan, tetapi terdapat ruang untuk penambahbaikan dari segi konsistensi dan keberkesanan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA179', 'AE036', 'AS009', '•	Proses yang jelas dalam penglibatan kumpulan pakar atau pakar bidang, dan bimbingan mereka disepadukan ke dalam proses membuat keputusan keselamatan siber.\r\n•	Penglibatan dengan pakar adalah tetap dan sistematik, dengan saluran yang mantap untuk perkongsian maklumat dan perkhidmatan perundingan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA180', 'AE036', 'AS010', '•	Proses melibatkan pakar dioptimumkan untuk kecekapan dan keberkesanan, dengan mekanisme disediakan bagi mendapatkan maklum balas. \r\n•	Peningkatan kualiti perkhidmatan perundingan yang disediakan secara berterusan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA181', 'AE037', 'AS006', 'Kawalan keselamatan formal tidak ada atau minima; bergantung kepada tetapan asas atau ‘default setting’.', NULL, NULL, NULL, NULL, 'Active'),
('ASA182', 'AE037', 'AS007', 'Kawalan keselamatan asas di tempat tetapi tidak menyeluruh atau sepenuhnya bersepadu.', NULL, NULL, NULL, NULL, 'Active'),
('ASA183', 'AE037', 'AS008', 'Satu set kawalan keselamatan yang ditakrifkan dengan baik dilaksanakan, merangkumi aspek keselamatan rangkaian dan sistem yang kritikal.', NULL, NULL, NULL, NULL, 'Active'),
('ASA184', 'AE037', 'AS009', 'Kawalan keselamatan yang dikaji semula dan dikemas kini secara berkala dengan pengurusan dan pengawasan aktif.', NULL, NULL, NULL, NULL, 'Active'),
('ASA185', 'AE037', 'AS010', 'Kawalan keselamatan yang canggih dan adaptif, menggabungkan amalan keselamatan dan teknologi terkini.', NULL, NULL, NULL, NULL, 'Active'),
('ASA186', 'AE038', 'AS006', 'Prosedur khusus yang tidak ada atau minima untuk konfigurasi selamat; bergantung kepada tetapan asas atau (default setting).', NULL, NULL, NULL, NULL, 'Active'),
('ASA187', 'AE038', 'AS007', 'Beberapa langkah untuk konfigurasi selamat digunakan, tetapi mungkin kurang mengikut ketelitian atau', NULL, NULL, NULL, NULL, 'Active'),
('ASA188', 'AE038', 'AS008', 'Prosedur mengikut piawaian untuk konfigurasi selamat dan pengurusan aset IT.', NULL, NULL, NULL, NULL, 'Active'),
('ASA189', 'AE038', 'AS009', 'Kajian semula dan kemas kini berkala terhadap konfigurasi keselamatan, dengan pengawasan pengurusan yang kukuh.', NULL, NULL, NULL, NULL, 'Active'),
('ASA190', 'AE038', 'AS010', 'Pengurusan konfigurasi dinamik dan automatik, menyesuaikan diri dengan landskap keselamatan yang berubah.', NULL, NULL, NULL, NULL, 'Active'),
('ASA191', 'AE039', 'AS006', 'Langkah keselamatan infrastruktur rangkaian dan sistem dipantau tanpa proses formal.', NULL, NULL, NULL, NULL, 'Active'),
('ASA192', 'AE039', 'AS007', 'Langkah keselamatan dan infrastruktu infrastruktur rangkaian dan sistem dipantau, tetapi tidak secara mendalam.', NULL, NULL, NULL, NULL, 'Active'),
('ASA193', 'AE039', 'AS008', 'Proses yang berstruktur untuk pemantauan berterusan tetapi mungkin tidak merangkumi semua kawalan keselamatan infrastruktur rangkaian dan sistem kritikal.', NULL, NULL, NULL, NULL, 'Active'),
('ASA194', 'AE039', 'AS009', 'Pemantauan berterusan yang menyeluruh dan berkesan semua kawalan keselamatan infrastruktur rangkaian dan sistem.', NULL, NULL, NULL, NULL, 'Active'),
('ASA195', 'AE039', 'AS010', 'Pemantauan berterusan yang proaktif dan canggih, dengan penambahbaikan dan penyesuaian berterusan kepada ancaman dan teknologi baru.', NULL, NULL, NULL, NULL, 'Active'),
('ASA196', 'AE040', 'AS006', 'Kemas kini perisian dan perkakasan jarang atau diabaikan, menyebabkan sistem terdedah kepada kelemahan keselamatan yang diketahui.', NULL, NULL, NULL, NULL, 'Active'),
('ASA197', 'AE040', 'AS007', '•	Terdapat beberapa usaha untuk menggunakan kemas kini, tetapi ia tidak teratur atau reaktif.\r\n•	Terdapat kelewatan dalam mengatasi kelemahan kritikal.', NULL, NULL, NULL, NULL, 'Active'),
('ASA198', 'AE040', 'AS008', 'Proses kemaskini dan kemas kini yang berkala dan sistematik, mengikut jadual yang ditakrifkan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA199', 'AE040', 'AS009', 'Pemantauan proaktif dan berterusan terhadap kelemahan dengan kemas kini dan kemaskini yang tepat pada masanya.', NULL, NULL, NULL, NULL, 'Active'),
('ASA200', 'AE040', 'AS010', 'Pengurusan kemaskini yang canggih, secara automatik, memastikan tindak balas masa nyata terhadap kelemahan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA201', 'AE041', 'AS006', 'Tiada proses formal; penggunaan teknologi baru adalah reaktif.', NULL, NULL, NULL, NULL, 'Active'),
('ASA202', 'AE041', 'AS007', 'Beberapa penilaian teknologi baru mungkin tidak teratur atau kurang pendekatan strategik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA203', 'AE041', 'AS008', 'Satu proses yang ditakrifkan untuk menilai dan mempertimbangkan penggunaan teknologi baru.', NULL, NULL, NULL, NULL, 'Active'),
('ASA204', 'AE041', 'AS009', 'Penilaian berkala dan strategik mengenai teknologi yang sedang berkembang.', NULL, NULL, NULL, NULL, 'Active'),
('ASA205', 'AE041', 'AS010', 'Pendekatan proaktif dan canggih dalam memanfaatkan teknologi yang sedang berkembang, proses integrasi dengan lancar untuk meningkatkan kedudukan keselamatan siber.', NULL, NULL, NULL, NULL, 'Active'),
('ASA206', 'AE042', 'AS006', 'Tiada keupayaan SOC formal, pemantauan ancaman yang reaktif atau tidak wujud.', NULL, NULL, NULL, NULL, 'Active'),
('ASA207', 'AE042', 'AS007', 'Fungsi SOC asas ada tetapi terhad dalam skop atau keberkesanan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA208', 'AE042', 'AS008', 'SOC yang ditakrifkan dengan proses pemantauan dan pengesanan yang berstruktur mempunyai had dalam liputan atau pengesanan ancaman yang canggih.', NULL, NULL, NULL, NULL, 'Active'),
('ASA209', 'AE042', 'AS009', 'SOC yang dikendalikan dengan baik dan menyediakan pemantauan menyeluruh serta pengesanan ancaman dengan penambahbaikan berterusan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA210', 'AE042', 'AS010', 'Keupayaan SOC bertaraf dunia dengan penyesuaian berterusan kepada ancaman yang muncul dan integrasi teknologi dan amalan yang canggih.', NULL, NULL, NULL, NULL, 'Active'),
('ASA211', 'AE043', 'AS006', 'Perisian dan perkakasan keselamatan maklumat tidak wujud atau sudah oudated. Peruntukan minimum dalam menaik taraf atau memperoleh teknologi baharu.', NULL, NULL, NULL, NULL, 'Active'),
('ASA212', 'AE043', 'AS007', 'Beberapa alatan dan perkakasan keselamatan maklumat asas telah disediakan, tetapi ia tidak dikemas kini dengan kemajuan terkini dalam teknologi keselamatan siber.', NULL, NULL, NULL, NULL, 'Active'),
('ASA213', 'AE043', 'AS008', 'Proses rasmi untuk memilih dan melaksanakan perisian dan perkakasan keselamatan maklumat terkini, berdasarkan amalan terbaik industri dan penilaian risiko.', NULL, NULL, NULL, NULL, 'Active'),
('ASA214', 'AE043', 'AS009', '•	Perisian dan perkakasan keselamatan maklumat dipantau dan diselenggara secara aktif. \r\n•	Kemas kini dan peningkatan yang kerap untuk memastikan ia kekal berkesan terhadap ancaman yang muncul.', NULL, NULL, NULL, NULL, 'Active'),
('ASA215', 'AE043', 'AS010', 'Penggunaan teknologi keselamatan maklumat termaju, termasuk sistem pengesanan ancaman lanjutan, analitik dipacu AI dan mekanisme tindak balas automatik, untuk mempertahankan secara proaktif daripada ancaman siber.', NULL, NULL, NULL, NULL, 'Active'),
('ASA216', 'AE044', 'AS006', 'Tiada rancangan tindak balas insiden formal dan tindak balas.', NULL, NULL, NULL, NULL, 'Active'),
('ASA217', 'AE044', 'AS007', 'Rancangan tindak balas kejadian asas wujud tetapi tidak lengkap atau dikemaskini secara berkala.', NULL, NULL, NULL, NULL, 'Active'),
('ASA218', 'AE044', 'AS008', 'Satu pelan tindak balas kejadian yang ditakrifkan telah disediakan, walaupun tidak diuji sepenuhnya atau sepenuhnya diintegrasikan bagi seluruh universiti.', NULL, NULL, NULL, NULL, 'Active'),
('ASA219', 'AE044', 'AS009', 'Rancangan tindak balas kejadian yang dikendalikan dengan baik, diuji secara berkala, dan dikemaskini yang merangkumi semua aspek penting.', NULL, NULL, NULL, NULL, 'Active'),
('ASA220', 'AE044', 'AS010', 'Peningkatan berterusan dan pengoptimuman rancangan tindak balas insiden. ', NULL, NULL, NULL, NULL, 'Active'),
('ASA221', 'AE045', 'AS006', 'Peranan dan tanggungjawab tidak ditakrifkan dengan jelas, menyebabkan kekeliruan semasa insiden.', NULL, NULL, NULL, NULL, 'Active'),
('ASA222', 'AE045', 'AS007', 'Peranan asas ditakrifkan tetapi kurang jelas atau lengkap.', NULL, NULL, NULL, NULL, 'Active'),
('ASA223', 'AE045', 'AS008', 'Peranan dan tanggungjawab yang jelas dan diperincikan tetapi tidak sepenuhnya disampaikan atau difahami oleh seluruh pasukan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA224', 'AE045', 'AS009', 'Peranan dan tanggungjawab yang ditakrifkan dengan jelas dan difahami, dengan latihan dan kemaskini yang berkala.', NULL, NULL, NULL, NULL, 'Active'),
('ASA225', 'AE045', 'AS010', 'Penilaian berterusan dan penyempurnaan peranan dan tanggungjawab untuk menyesuaikan diri dengan landskap keselamatan siber yang berkembang.', NULL, NULL, NULL, NULL, 'Active'),
('ASA226', 'AE055', 'AS006', 'Masa tindak balas dan pemulihan insiden yang lambat atau tidak konsisten.', NULL, NULL, NULL, NULL, 'Active'),
('ASA227', 'AE055', 'AS007', 'Mekanisme tindak balas insiden yang asas, tetapi kurang cepat atau cekap.', NULL, NULL, NULL, NULL, 'Active'),
('ASA228', 'AE055', 'AS008', 'Protokol insiden tindak balas dan pemulihan yang ditetapkan, tetapi keberkesanannya mungkin berbeza.', NULL, NULL, NULL, NULL, 'Active'),
('ASA229', 'AE055', 'AS009', 'Tindak balas dan pemulihan dari insiden secara tepat dan berkesan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA230', 'AE055', 'AS010', 'Peningkatan masa insiden tindak balas dan pemulihan berterusan yang dioptimumkan, dengan menggunakan alat dan teknik yang canggih.', NULL, NULL, NULL, NULL, 'Active'),
('ASA231', 'AE046', 'AS006', 'Pengurusan insiden yang tidak konsisten atau ditambah baik, melalui proses pengekalan, pemusnahan dan usaha pemulihan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA232', 'AE046', 'AS007', 'Pengurusan asas insiden tetapi kurang menyeluruh atau cekap.', NULL, NULL, NULL, NULL, 'Active'),
('ASA233', 'AE046', 'AS008', 'Prosedur yang ditakrifkan untuk pengurusan insiden, tetapi keberkesanan berbeza.', NULL, NULL, NULL, NULL, 'Active'),
('ASA234', 'AE046', 'AS009', 'Pengendalian insiden yang berkesan dan terurus, termasuk pengekalan, pemusnahan dan usaha pemulihan tepat pada masanya.', NULL, NULL, NULL, NULL, 'Active'),
('ASA235', 'AE046', 'AS010', 'Penyempurnaan dan peningkatan berterusan dalam proses pengurusan insiden, menggabungkan amalan terbaik dan pembelajaran dari insiden lampau.', NULL, NULL, NULL, NULL, 'Active'),
('ASA236', 'AE047', 'AS006', 'Tindak balas insiden yang perlahan atau tidak konsisten terhadap insiden dan amaran.', NULL, NULL, NULL, NULL, 'Active'),
('ASA237', 'AE047', 'AS007', 'Prosedur penyiasatan dan peningkatan asas insiden ada, tetapi lambat atau tidak cekap.', NULL, NULL, NULL, NULL, 'Active'),
('ASA238', 'AE047', 'AS008', 'Proses yang berstruktur untuk menyiasat dan meningkatkan insiden tetapi kurang cepat atau menyeluruh.', NULL, NULL, NULL, NULL, 'Active'),
('ASA239', 'AE047', 'AS009', 'Penyiasatan dan peningkatan insiden yang cekap dan berkesan, dengan kajian berkala untuk penambahbaikan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA240', 'AE047', 'AS010', 'Penambahbaikan dan pengoptimuman berterusan dalam pengendalian insiden, dengan proses penyiasatan dan peningkatan yang cepat dan sangat berkesan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA241', 'AE048', 'AS006', 'Tiada proses formal untuk mengkaji dan belajar dari insiden.', NULL, NULL, NULL, NULL, 'Active'),
('ASA242', 'AE048', 'AS007', 'Proses ulasan asas telah diletakkan, tetapi tidak teliti atau bersistematik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA243', 'AE048', 'AS008', 'Satu proses yang ditakrifkan untuk pembelajaran dan kajian insiden tetapi kurang mendalam.', NULL, NULL, NULL, NULL, 'Active'),
('ASA244', 'AE048', 'AS009', 'Pembelajaran yang berkesan mengenai insiden secara menyeluruh diintegrasikan secara berkala ke dalam perancangan masa depan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA245', 'AE048', 'AS010', 'Peningkatan berterusan dalam keupayaan tindak balas insiden, mengintegrasikan amalan dan teknologi terkini, serta pembelajaran dari setiap insiden.', NULL, NULL, NULL, NULL, 'Active'),
('ASA246', 'AE049', 'AS006', 'Tiada keperluan pengurusan rasmi bagi ancaman dan kelemahan keselamatan siber.', NULL, NULL, NULL, NULL, 'Active'),
('ASA247', 'AE049', 'AS007', 'Beberapa keperluan pengurusan yang disediakan, tetapi ia tidak diformalkan atau digunakan secara konsisten di seluruh universiti.', NULL, NULL, NULL, NULL, 'Active'),
('ASA248', 'AE049', 'AS008', 'Proses yang jelas dan formal untuk mengurus ancaman dan kelemahan keselamatan siber, termasuk penilaian risiko, pelan tindak balas insiden dan prosedur pemulihan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA249', 'AE049', 'AS009', '•	Keperluan pengurusan untuk ancaman dan kelemahan keselamatan siber dikuatkuasakan dan dipantau secara aktif. \r\n•	Semakan dan kemas kini yang kerap untuk memastikan keberkesanan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA250', 'AE049', 'AS010', '•	Keperluan pengurusan terus diperbaiki dan dioptimumkan berdasarkan  ancaman yang muncul. \r\n•	Proses adalah sangat cekap dan disepadukan ke dalam keseluruhan strategi keselamatan siber.', NULL, NULL, NULL, NULL, 'Active'),
('ASA251', 'AE054', 'AS006', '•	Pengurusan ancaman dan keretanan tidak wujud atau sedikit. \r\n•	Sedikit kesedaran atau keutamaan terhadap aktiviti ini.', NULL, NULL, NULL, NULL, 'Active'),
('ASA252', 'AE054', 'AS007', 'Pengiktirafan tentang keperluan untuk pengurusan ancaman dan kerentanan, tetapi ia tidak dijalankan secara teratur atau sistematik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA253', 'AE054', 'AS008', 'Proses rasmi untuk ancaman keselamatan siber dan penilaian kerentanan biasa, termasuk imbasan dan penilaian berjadual.', NULL, NULL, NULL, NULL, 'Active'),
('ASA254', 'AE054', 'AS009', '•	Pengurusan ancaman dan kelemahan dijalankan secara tetap mengikut jadual yang ditetapkan.\r\n•	Penemuan ditangani dengan segera dan dipulihkan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA255', 'AE054', 'AS010', '•	Proses pengurusan ancaman dan kerentanan sentiasa diperhalusi dan dioptimumkan.\r\n•	Terdapat pemantauan masa nyata dan tindak balas proaktif terhadap ancaman yang muncul.', NULL, NULL, NULL, NULL, 'Active'),
('ASA256', 'AE050', 'AS006', 'Pihak universiti tidak mempunyai kesedaran atau pelaburan dalam teknologi keselamatan siber terkini untuk pengurusan ancaman dan kerentanan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA257', 'AE050', 'AS007', 'Beberapa teknologi keselamatan siber asas telah disediakan, tetapi tidak dikemas kini dengan kemajuan terkini.', NULL, NULL, NULL, NULL, 'Active'),
('ASA258', 'AE050', 'AS008', 'Keperluan dan rancangan untuk menerima pakai teknologi keselamatan siber terkini untuk pengurusan ancaman dan kelemahan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA259', 'AE050', 'AS009', 'Universiti secara aktif menilai dan mengguna pakai teknologi keselamatan siber terkini, termasuk sistem pengesanan ancaman lanjutan dan alat pengimbasan kerentanan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA260', 'AE050', 'AS010', 'Universiti ini memanfaatkan teknologi dan alatan keselamatan siber termaju untuk mengurus ancaman dan kelemahan dengan berkesan, dengan tumpuan pada automasi dan penyepaduan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA261', 'AE051', 'AS006', 'Pengurusan kesinambungan perkhidmatan ICT tidak diformalkan dan tiada keperluan khusus disediakan. ', NULL, NULL, NULL, NULL, 'Active'),
('ASA262', 'AE051', 'AS007', 'Sedikit kesedaran tentang kepentingan kesinambungan perkhidmatan ICT, tetapi keperluan formal adalah  kurang atau tidak konsisten.', NULL, NULL, NULL, NULL, 'Active'),
('ASA263', 'AE051', 'AS008', 'Keperluan pengurusan kesinambungan perkhidmatan ICT yang jelas dan formal, termasuk dasar, prosedur dan peranan/ tanggungjawab.', NULL, NULL, NULL, NULL, 'Active'),
('ASA264', 'AE051', 'AS009', '•	Keperluan pengurusan kesinambungan perkhidmatan ICT dikuatkuasakan dan dipantau secara aktif. \r\n•	Terdapat penilaian dan kemas kini yang kerap bagi memastikan penjajaran dengan keperluan universiti.', NULL, NULL, NULL, NULL, 'Active');
INSERT INTO `score_element` (`se_ID`, `element_ID`, `score_ID`, `details`, `input_id`, `input_at`, `updated_id`, `updated_at`, `status`) VALUES
('ASA265', 'AE051', 'AS010', '•	Proses pengurusan kesinambungan perkhidmatan ICT terus diperhalusi dan dioptimumkan berdasarkan pengajaran dan risiko yang muncul. \r\n•	Ia disepadukan ke dalam keseluruhan strategi pengurusan risiko.', NULL, NULL, NULL, NULL, 'Active'),
('ASA266', 'AE052', 'AS006', '•	Pelan tidak disemak atau dikemas kini secara kerap. \r\n•	Tiada proses formal disediakan untuk semakan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA267', 'AE052', 'AS007', 'Pengiktirafan tentang keperluan untuk semakan pelan, tetapi ia tidak dijalankan secara teratur atau sistematik.', NULL, NULL, NULL, NULL, 'Active'),
('ASA268', 'AE052', 'AS008', 'Keperluan rasmi untuk semakan berkala dan kemas kini pelan, termasuk selang semakan yang ditetapkan dan pihak yang bertanggungjawab.', NULL, NULL, NULL, NULL, 'Active'),
('ASA269', 'AE052', 'AS009', '•	Pelan disemak secara tetap mengikut jadual yang ditetapkan.\r\n•	Penemuan daripada ulasan ditangani dengan segera dan digabungkan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA270', 'AE052', 'AS010', '•	Proses semakan pelan terus diperbaiki dan dioptimumkan.\r\n•	Terdapat pemantauan masa nyata dan pelarasan proaktif terhadap keadaan yang berubah-ubah.', NULL, NULL, NULL, NULL, 'Active'),
('ASA271', 'AE052', 'AS006', 'Simulasi pelan tidak dijalankan dan terdapat sedikit kesedaran tentang kepentingannya.', NULL, NULL, NULL, NULL, 'Active'),
('ASA272', 'AE052', 'AS007', 'Beberapa simulasi pelan dijalankan, tetapi ia jarang berlaku atau tidak formal.', NULL, NULL, NULL, NULL, 'Active'),
('ASA273', 'AE052', 'AS008', 'Keperluan rasmi untuk latihan simulasi berkala bagi pelan, termasuk objektif simulasi yang ditetapkan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA274', 'AE052', 'AS009', '•	Simulasi pelan dijalankan secara tetap mengikut jadual yang ditetapkan. \r\n•	Dapatan daripada simulasi digunakan untuk meningkatkan keberkesanan rancangan.', NULL, NULL, NULL, NULL, 'Active'),
('ASA275', 'AE052', 'AS010', '•	Proses simulasi pelan diperhalusi dan dioptimumkan secara berterusan.\r\n•	Pelan tersebut disepadukan ke dalam keseluruhan pelan latihan dan kesediaan simulasi.', NULL, NULL, NULL, NULL, 'Active'),
('ASA276', 'AE059', 'AS001', 'level 1 score test', '4', '2025-11-26', '4', '2025-11-26', 'Active'),
('ASA277', 'AE059', 'AS002', 'aaaa', '4', '2025-11-26', NULL, NULL, 'Active'),
('ASA278', 'AE062', 'AS001', 'Mekanisme pengesahan lemah atau tidak konsisten dikuatkuasakan.', '4', '2025-11-26', '4', '2025-11-26', 'Active'),
('ASA279', 'AE062', 'AS002', 'Pelaksanaan asas kaedah pengesahan yang lebih kukuh, tetapi tidak komprehensif atau dikuatkuasakan untuk semua sumber sensitif.', '4', '2025-11-26', '4', '2025-11-26', 'Active'),
('ASA280', 'AE062', 'AS003', 'Polisi dan prosedur yang ditakrifkan untuk pengesahan yang kukuh sedia ada, tetapi tidak diintegrasikan sepenuhnya dengan semua sistem atau dikuatkuasakan secara konsisten.', '4', '2025-11-26', NULL, NULL, 'Active'),
('ASA281', 'AE062', 'AS004', 'Kaedah pengesahan yang kuat seperti pengesahan multi-faktor dilaksanakan sepenuhnya dan dikelola di semua sumber sensitif.', '4', '2025-11-26', NULL, NULL, 'Active');

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

-- --------------------------------------------------------

--
-- Table structure for table `sub_req`
--

CREATE TABLE `sub_req` (
  `sec_ID` varchar(10) NOT NULL,
  `sub_req_ID` varchar(10) NOT NULL,
  `sub_req_name` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `sub_req`
--

INSERT INTO `sub_req` (`sec_ID`, `sub_req_ID`, `sub_req_name`) VALUES
('10', '10.1 (a)', 'Continual improvement - Improve the suitability, adequacy and effectiveness of ISMS'),
('10', '10.2 (a)', 'Nonconformity and corrective action - React to nonconformity by correcting it and dealing with consequences'),
('10', '10.2 (b)', 'Nonconformity and corrective action - Evaluate need for action by reviewing nonconformity, finding causes, and checking for similar cases'),
('10', '10.2 (c)', 'Nonconformity and corrective action - Implement any action needed'),
('10', '10.2 (d)', 'Nonconformity and corrective action - Review the effectiveness of any corrective action taken'),
('10', '10.2 (e)', 'Nonconformity and corrective action - Make changes to the ISMS (if necessary)'),
('10', '10.2 (f)', 'Nonconformity and corrective action - Document evidence of nonconformities and actions taken'),
('10', '10.2 (g)', 'Nonconformity and corrective action - Document the result of corrective action'),
('4', '4.1', 'Organisational context - Determine the organization\'s ISMS objectives and any issues that might affect its effectiveness'),
('4', '4.2 (a)', 'Interested parties - Identify interested parties'),
('4', '4.2 (b)', 'Interested parties - Determine their information security-relevant requirements and obligations'),
('4', '4.2 (c)', 'Interested parties - Determine the requirments that will be addressed through ISMS.'),
('4', '4.3 (a)', 'ISMS scope - Determine the external and internal issues'),
('4', '4.3 (b)', 'ISMS scope - Determine the requirments'),
('4', '4.3 (c)', 'ISMS scope - Determine interfaces and dependencies between perfomed by the organization or by other organization'),
('4', '4.4', ' ISMS - Establish, implement, maintain and continually improve an ISMS according to the standard'),
('5', '5.1 (a)', 'Leadership & commitment- Ensure ISMS objectives are established'),
('5', '5.1 (b)', 'Leadership & commitment- Ensure integrate ISMS requirements into organizational processes'),
('5', '5.1 (c)', 'Leadership & commitment- Ensure resource for the ISMS are available'),
('5', '5.1 (d)', 'Leadership & commitment- Communicate the importance of effective ISMS and compliance with its requirements'),
('5', '5.1 (e)', 'Leadership & commitment- Ensure the ISMS achieves its outcome'),
('5', '5.1 (f)', 'Leadership & commitment- Direct and support person to ensure ISMS effectiveness'),
('5', '5.1 (g)', 'Leadership & commitment- Promote continual improvement'),
('5', '5.1 (h)', 'Leadership & commitment- Support management roles in demonstrating leadership with their responsibilities'),
('5', '5.2 (a)', 'Policy - Establish the information security policy'),
('5', '5.2 (b)', 'Policy - Include IS objectives or a framework for setting'),
('5', '5.2 (c)', 'Policy - Commits to satisfy applicable IS requirements'),
('5', '5.2 (d)', 'Policy - Commits to continual improvement of the ISMS'),
('5', '5.2 (e)', 'Policy - Be available as documented information'),
('5', '5.2 (f)', 'Policy - Communicate within organization'),
('5', '5.2 (g)', 'Policy - Be available to interested parties'),
('5', '5.3 (a)', 'Organizational roles, responsibilities & authorities - Ensure the ISMS conforms to document requirements'),
('5', '5.3 (b)', 'Organizational roles, responsibilities & authorities - Report on the performance ISMS to top management'),
('6', '6.1', 'Actions to address risks & opportunities'),
('6', '6.1.1 (a)', 'General - Ensure the ISMS achieve the outcome(s)'),
('6', '6.1.1 (b)', 'General - Prevent or reduce undesired effects'),
('6', '6.1.1 (c)', 'General - Achieve continual improvement'),
('6', '6.1.1 (d)', 'General - Organizational shall plan actions to address the risks and opportunities'),
('6', '6.1.1 (e)', 'General - How to integrate and implement the actions into ISMS processes and evaluate its effectiveness'),
('6', '6.1.2 (a)', 'Information security risk assessment - Establish and maintains IS risk criteria'),
('6', '6.1.2 (b)', 'Information security risk assessment - Ensure IS risk assessments give consistent, valid, and comparable result'),
('6', '6.1.2 (c)', 'Information security risk assessment - Identify the IS risks'),
('6', '6.1.2 (d)', 'Information security risk assessment - Analyses the IS risks'),
('6', '6.1.2 (e)', 'Information security risk assessment - Evaluate the IS risks'),
('6', '6.1.3 (a)', 'Information security risk treatment - Select appropriate IS risk treatment options'),
('6', '6.1.3 (b)', 'Information security risk treatment - Determine all controls to be implemented'),
('6', '6.1.3 (c)', 'Information security risk treatment - Compare determined control with Annex A '),
('6', '6.1.3 (d)', 'Information security risk treatment - Produce statement of applicability that contains necessary control, justification for inclusion, and the necessary controls are implemented or not'),
('6', '6.1.3 (e)', 'Information security risk treatment - Formulate an IS risk treatment plan'),
('6', '6.1.3 (f)', 'Information security risk treatment - Obtain risk owners\' approval of the treatment plan and acceptance of residual risks'),
('6', '6.2 (a)', 'Information security objectives & plans - Consistency with IS policy'),
('6', '6.2 (b)', 'Information security objectives & plans - Measurable'),
('6', '6.2 (c)', 'Information security objectives & plans - Consider IS requirements and results from risk assessment and treatment'),
('6', '6.2 (d)', 'Information security objectives & plans - Monitor'),
('6', '6.2 (e)', 'Information security objectives & plans - Communicate'),
('6', '6.2 (f)', 'Information security objectives & plans - Updated'),
('6', '6.2 (g)', 'Information security objectives & plans - Available as document'),
('6', '6.2 (h)', 'Information security objectives & plans - Define what will be done'),
('6', '6.2 (i)', 'Information security objectives & plans - Define required resources'),
('6', '6.2 (j)', 'Information security objectives & plans - Define the person who take responsibility'),
('6', '6.2 (k)', 'Information security objectives & plans - Define when it will be completed'),
('6', '6.2 (l)', 'Information security objectives & plans - Define how the result will be eavluated'),
('6', '6.3', 'Planning of changes - Determine the need for changes to the ISMS'),
('7', '7.1', 'Resources - Determine and provide the resources needed for the establishment, implementation, maintenance and continual improvement of the ISMS'),
('7', '7.2 (a)', 'Competence - Determine competence of persons affecting IS performance'),
('7', '7.2 (b)', 'Competence - Ensure persons are competent through education, training, or experience'),
('7', '7.2 (c)', 'Competence - Take actions to gain needed competence and evaluate effectiveness'),
('7', '7.2 (d)', 'Competence - Retain in document as evidence'),
('7', '7.3 (a)', 'Awareness - Information security policy'),
('7', '7.3 (b)', 'Awareness - Contribution to the effectiveness of the ISMS'),
('7', '7.3 (c)', 'Awareness - Implications of not comforming with ISMS requirements'),
('7', '7.4 (a)', 'Communication - What to communicate'),
('7', '7.4 (b)', 'Communication - When to communicate'),
('7', '7.4 (c)', 'Communication - with whom to communicate'),
('7', '7.4 (d)', 'Communication - How to communicate'),
('7', '7.5', 'Documented information'),
('7', '7.5.1 (a)', 'General - Require documented information'),
('7', '7.5.1 (b)', 'General - Determined by the organization for the effectiveness of ISMS'),
('7', '7.5.2 (a)', 'Creating and updating - Identification and description'),
('7', '7.5.2 (b)', 'Creating and updating - Format and media'),
('7', '7.5.2 (c)', 'Creating and updating - Review and approval for suitability and adequacy'),
('7', '7.5.3 (a)', 'Control of documented information - Document shall available and suitable of use when needed'),
('7', '7.5.3 (b)', 'Control of documented information - Document shall be protected'),
('7', '7.5.3 (c)', 'Control of documented information - The organization shall address distribution, access, retrieval and use'),
('7', '7.5.3 (d)', 'Control of documented information - The organization shall address control of changes address storage and preservation including legibility'),
('7', '7.5.3 (e)', 'Control of documented information - The organization shall address control of changes'),
('7', '7.5.3 (f)', 'Control of documented information - The organization shall address retention and disposition'),
('8', '8.1', 'Operational planning and control - Plan, implement, control & document ISMS processes to manage risks'),
('8', '8.2', 'Information security risk assessment - Perform information security risk assessments regularly or when significant changes occur'),
('8', '8.3', 'Information security risk treatment - Implement the IS risk treatment plan and retain documented of the result'),
('9', '9.1 (a)', 'Monitoring, measurement, analysis and evaluation - Determine what needs to be monitored and measured'),
('9', '9.1 (b)', 'Monitoring, measurement, analysis and evaluation - Determine the methods for monitoring, measurement, analysis, and evaluation'),
('9', '9.1 (c)', 'Monitoring, measurement, analysis and evaluation - Determine when the monitoring and measuring shall be performed'),
('9', '9.1 (d)', 'Monitoring, measurement, analysis and evaluation - Determine who shall monitor and measure'),
('9', '9.1 (e)', 'Monitoring, measurement, analysis and evaluation - Determine when the result shall be analysed and evaluated'),
('9', '9.1 (f)', 'Monitoring, measurement, analysis and evaluation - Determine who shall analyse and evalaute the result'),
('9', '9.2', 'Internal audit'),
('9', '9.2.1 (a)', 'General - Conforms to the organization\'s ISMS requirements and the document'),
('9', '9.2.1 (b)', 'General - Identify the ISMS is effectively implemented and maintained'),
('9', '9.2.2', 'Internal audit progamme - Plan, establish, implement and maintain ad auidt programme(s)'),
('9', '9.2.2 (a)', 'Internal audit progamme - Define the audit criteria and scope for each audit'),
('9', '9.2.2 (b)', 'Internal audit progamme - Select auditors and conduct audits'),
('9', '9.2.2 (c)', 'Internal audit progamme - Report the result of audit to relevant management'),
('9', '9.3', 'Management review'),
('9', '9.3.1', 'General - Review the organization\'s ISMS at planned intervals'),
('9', '9.3.2 (a)', 'Management review inputs - Considerate of the status of action from previous management reviews'),
('9', '9.3.2 (b)', 'Management review inputs - Considerate changes in external and internal issue in ISMS'),
('9', '9.3.2 (c)', 'Management review inputs - Considerate of change in needs and expectation of interested parties'),
('9', '9.3.2 (d)', 'Management review inputs - Feedback on ISMS performance in nonconformities and corrective actions, monitoring and measurements results, audit result, and fulfilment of IS obejctives'),
('9', '9.3.2 (e)', 'Management review inputs - Feedback from interested parties'),
('9', '9.3.2 (f)', 'Management review inputs - Result of risk assessment and status of risk treatment plan'),
('9', '9.3.2 (g)', 'Management review inputs - Considerate opportunities for continual improvement'),
('9', '9.3.3', 'Management review result - Management review result must include improvement decisions, ISMS changes, and be documented as evidence');

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
('SV001', 'Survey 1', 'jabatan digital', '2025-11-21 10:00:00', '2025-11-21 15:08:00', 'Completed', 'testing survey 1', '4', '2025-11-21 00:00:00', '4', '2025-11-21'),
('SV002', 'survey 2', 'FSKM', '2025-11-21 15:21:00', '2025-11-22 15:21:00', 'Draft', 'test survey 2', '4', '2025-11-21 00:00:00', '4', '2025-11-21'),
('SV003', 'test', 'hea', '2025-11-24 14:35:00', '2025-11-26 11:35:00', 'Archived', 'testing testing', '4', '2025-11-24 00:00:00', '4', '2025-11-26'),
('SV004', 'test 2', 'kolej dahlia', '2025-11-30 14:59:00', '2025-12-05 14:59:00', 'Active', 'testing 1234555', '4', '2025-11-24 00:00:00', '4', '2025-11-24'),
('SV005', 'blabla bla', 'kokoko', '2025-11-25 15:23:00', '2025-11-27 14:23:00', 'Completed', 'lalalalal', '4', '2025-11-25 14:23:43', '4', '2025-11-25');

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
(41, 'SV001', 'AD009'),
(42, 'SV001', 'AD008'),
(43, 'SV001', 'AD011'),
(44, 'SV001', 'AD007'),
(45, 'SV002', 'AD004'),
(46, 'SV002', 'AD003'),
(47, 'SV002', 'AD010'),
(48, 'SV002', 'AD005'),
(49, 'SV002', 'AD006'),
(50, 'SV002', 'AD009'),
(51, 'SV002', 'AD008'),
(52, 'SV002', 'AD002'),
(53, 'SV002', 'AD001'),
(66, 'SV004', 'AD010'),
(67, 'SV004', 'AD005'),
(68, 'SV004', 'AD006'),
(93, 'SV005', 'AD012'),
(94, 'SV003', 'AD004'),
(95, 'SV003', 'AD003'),
(96, 'SV003', 'AD010'),
(97, 'SV003', 'AD005'),
(98, 'SV003', 'AD006'),
(99, 'SV003', 'AD009'),
(100, 'SV003', 'AD008'),
(101, 'SV003', 'AD011'),
(102, 'SV003', 'AD007'),
(103, 'SV003', 'AD002'),
(104, 'SV003', 'AD001'),
(105, 'SV003', 'AD013');

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
(4, 'admin@uitm.edu.my', '$2y$10$UTKIVYNidiMD1AFK7jT79.PSK5vnDObgjMRM/QXfOJK8AWwqC5zBa', NULL, 'System Administrator', NULL, 'Active', 'Verified', '2025-11-28 08:39:23', '2025-10-29 06:29:06', '2025-11-28 08:39:23', NULL, NULL, NULL, NULL),
(10, 'ali@gmail.com', '$2y$10$jI0046HbGA46J5.74k2z2utCruGzwm8MPXb7zfnvNUsXkXouLV.dq', NULL, 'ali bin abuu', 'FSKM', 'Inactive', '', '2025-11-24 00:45:34', '2025-11-12 01:36:41', '2025-11-24 00:45:34', 'UiTM', 'Manager', '', '+60182396060'),
(12, '2023864212@student.uitm.edu.my', NULL, '110380624589280730990', 'IYLIA MAISARAH MOHD KHAIROL', '', 'Active', 'Verified', '2025-11-24 00:25:52', '2025-11-21 08:46:20', '2025-11-24 00:32:13', '', '', '', '+60182396090');

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
(3, 10, 2, '2025-11-12 01:36:41', 'System'),
(4, 12, 2, '2025-11-24 00:26:20', 'Google');

-- --------------------------------------------------------

--
-- Table structure for table `user_survey`
--

CREATE TABLE `user_survey` (
  `user_survey_ID` varchar(10) NOT NULL,
  `survey_ID` varchar(50) DEFAULT NULL,
  `user_ID` int(11) DEFAULT NULL,
  `status` enum('in progress','completed','expired') DEFAULT 'in progress'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

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
  ADD PRIMARY KEY (`domain_ID`);

--
-- Indexes for table `element`
--
ALTER TABLE `element`
  ADD PRIMARY KEY (`element_ID`),
  ADD KEY `criteria_ID` (`criteria_ID`);

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
  ADD KEY `element_ID_fk` (`element_ID`);

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
  ADD KEY `sec_ID` (`sec_ID`);

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
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `survey_domain`
--
ALTER TABLE `survey_domain`
  MODIFY `survey_domain_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_role`
--
ALTER TABLE `user_role`
  MODIFY `user_role_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `criteria`
--
ALTER TABLE `criteria`
  ADD CONSTRAINT `criteria_ibfk_1` FOREIGN KEY (`domain_ID`) REFERENCES `domain` (`domain_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `element`
--
ALTER TABLE `element`
  ADD CONSTRAINT `element_ibfk_1` FOREIGN KEY (`criteria_ID`) REFERENCES `criteria` (`criteria_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `response_ibfk_1` FOREIGN KEY (`se_ID`) REFERENCES `score_element` (`se_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_ID_fk` FOREIGN KEY (`user_ID`) REFERENCES `user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `sub_req_ibfk_1` FOREIGN KEY (`sec_ID`) REFERENCES `section` (`sec_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `survey_domain`
--
ALTER TABLE `survey_domain`
  ADD CONSTRAINT `survey_domain_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `survey` (`survey_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `survey_domain_ibfk_2` FOREIGN KEY (`domain_id`) REFERENCES `domain` (`domain_ID`);

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
