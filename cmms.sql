-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2025 at 01:11 PM
-- Server version: 11.5.2-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cmms`
--

-- --------------------------------------------------------

--
-- Table structure for table `advisor_advisee`
--

CREATE TABLE `advisor_advisee` (
  `advisor_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `assigned_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `advisor_advisee`
--

INSERT INTO `advisor_advisee` (`advisor_id`, `student_id`, `assigned_at`) VALUES
(1, 1, '2025-06-22 19:19:51'),
(1, 3, '2025-06-22 19:19:51');

-- --------------------------------------------------------

--
-- Table structure for table `components`
--

CREATE TABLE `components` (
  `component_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `component_name` varchar(100) NOT NULL,
  `max_mark` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `components`
--

INSERT INTO `components` (`component_id`, `section_id`, `component_name`, `max_mark`) VALUES
(1, 1, 'Assignment', 10.00),
(2, 1, 'Quiz', 20.00),
(3, 1, 'Midterm', 30.00),
(6, 2, 'Quiz 1', 30.00);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `credit` int(11) NOT NULL,
  `max_cm` int(11) DEFAULT NULL,
  `max_fm` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_code`, `course_name`, `faculty_id`, `credit`, `max_cm`, `max_fm`) VALUES
(1, 'SECJ3483', 'WEB TECHNOLOGY', 1, 3, 60, 40),
(2, 'SECJ3623', 'MOBILE APPLICATION PROGRAMMING', 1, 3, 10, 90),
(3, 'SECJ3563', 'COMPUTATIONAL INTELLIGENCE', 1, 3, NULL, NULL),
(4, 'SECJ1111', 'MAP', 1, 3, 60, 40);

-- --------------------------------------------------------

--
-- Table structure for table `enrollment`
--

CREATE TABLE `enrollment` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `enrollment`
--

INSERT INTO `enrollment` (`enrollment_id`, `student_id`, `section_id`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 26, 1),
(14, 16, 1),
(15, 32, 1),
(16, 19, 1),
(17, 31, 1),
(18, 5, 1),
(19, 30, 1),
(20, 11, 1),
(21, 25, 1),
(22, 20, 1),
(23, 15, 1),
(24, 23, 1),
(25, 8, 1),
(26, 21, 1),
(27, 14, 1),
(28, 6, 1),
(29, 18, 1),
(30, 24, 1);

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `faculty_id` int(11) NOT NULL,
  `faculty_abbreviation` varchar(10) NOT NULL,
  `faculty_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`faculty_id`, `faculty_abbreviation`, `faculty_name`) VALUES
(1, 'FC', 'Faculty of Science'),
(2, 'ENG', 'Faculty of Engineering');

-- --------------------------------------------------------

--
-- Table structure for table `final_exam`
--

CREATE TABLE `final_exam` (
  `exam_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `mark` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `final_exam`
--

INSERT INTO `final_exam` (`exam_id`, `student_id`, `section_id`, `mark`) VALUES
(2, 1, 1, 30.00),
(3, 2, 1, 25.00),
(5, 26, 1, 27.00),
(6, 5, 1, 31.00),
(7, 6, 1, 39.00),
(8, 8, 1, 38.00),
(9, 11, 1, 37.00),
(10, 14, 1, 36.00),
(11, 15, 1, 36.00),
(12, 16, 1, 35.00),
(13, 18, 1, 35.50),
(14, 19, 1, 33.00),
(15, 20, 1, 28.00),
(16, 21, 1, 19.00),
(17, 23, 1, 21.00),
(18, 24, 1, 18.00),
(19, 25, 1, 17.00),
(20, 30, 1, 16.00),
(21, 31, 1, 15.00),
(22, 32, 1, 14.00);

-- --------------------------------------------------------

--
-- Table structure for table `lecturers`
--

CREATE TABLE `lecturers` (
  `lecturer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(30) NOT NULL,
  `staff_no` varchar(50) NOT NULL,
  `department` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lecturers`
--

INSERT INTO `lecturers` (`lecturer_id`, `user_id`, `title`, `staff_no`, `department`) VALUES
(1, 9, 'Dr.', 'S001', 'Software Engineering'),
(2, 10, 'Prof. Madya Dr.', 'S002', 'Software Engineering'),
(3, 11, 'Assoc. Prof.', 'S003', 'Computer Science'),
(7, 107, 'Mr.', 'S004', NULL),
(8, 108, 'Prof. Madya Dr.', 'S006', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `mark_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `component_id` int(11) NOT NULL,
  `mark` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marks`
--

INSERT INTO `marks` (`mark_id`, `student_id`, `component_id`, `mark`) VALUES
(1, 1, 1, 10.00),
(2, 1, 2, 15.00),
(3, 1, 3, 25.00),
(4, 2, 1, 9.00),
(5, 2, 2, 12.00),
(6, 2, 3, 20.00),
(10, 26, 1, 8.00),
(11, 5, 1, 10.00),
(12, 6, 1, 4.00),
(13, 8, 1, 3.00),
(14, 11, 1, 1.00),
(15, 14, 1, 6.60),
(16, 15, 1, 7.80),
(17, 16, 1, 5.30),
(18, 18, 1, 10.00),
(19, 19, 1, 6.20),
(20, 20, 1, 9.10),
(21, 21, 1, 9.90),
(22, 23, 1, 1.50),
(23, 24, 1, 2.70),
(24, 25, 1, 6.70),
(25, 30, 1, 8.70),
(26, 31, 1, 4.50),
(27, 32, 1, 5.60),
(28, 5, 2, 11.00),
(29, 6, 2, 10.00),
(30, 8, 2, 9.50),
(31, 11, 2, 6.30),
(32, 14, 2, 4.50),
(33, 15, 2, 3.40),
(34, 16, 2, 1.20),
(35, 18, 2, 4.60),
(36, 19, 2, 7.70),
(37, 20, 2, 9.80),
(38, 21, 2, 19.00),
(39, 23, 2, 20.00),
(40, 24, 2, 16.00),
(41, 25, 2, 15.00),
(42, 26, 2, 13.00),
(43, 30, 2, 12.00),
(44, 31, 2, 11.00),
(45, 32, 2, 10.00),
(46, 5, 3, 25.00),
(47, 6, 3, 23.00),
(48, 8, 3, 22.00),
(49, 11, 3, 21.00),
(50, 14, 3, 20.00),
(51, 15, 3, 19.00),
(52, 16, 3, 18.00),
(53, 18, 3, 17.00),
(54, 19, 3, 16.00),
(55, 20, 3, 15.00),
(56, 21, 3, 24.00),
(57, 23, 3, 25.00),
(58, 24, 3, 26.00),
(59, 25, 3, 27.00),
(60, 26, 3, 28.00),
(61, 30, 3, 29.00),
(62, 31, 3, 30.00),
(63, 32, 3, 29.00);

-- --------------------------------------------------------

--
-- Table structure for table `remark_requests`
--

CREATE TABLE `remark_requests` (
  `request_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `component_id` int(11) DEFAULT NULL,
  `justification` text NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remark_requests`
--

INSERT INTO `remark_requests` (`request_id`, `student_id`, `section_id`, `component_id`, `justification`, `status`, `created_at`) VALUES
(3, 1, 1, 1, 'help', 'Rejected', '2025-06-22 20:47:58'),
(4, 1, 1, 2, 'nooo', 'Approved', '2025-07-06 03:52:57');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(3, 'Advisor'),
(2, 'Lecturer'),
(4, 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `section_number` varchar(10) NOT NULL,
  `lecturer_id` int(11) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `course_id`, `section_number`, `lecturer_id`, `capacity`) VALUES
(1, 1, '01', 1, 30),
(2, 1, '02', 1, 30),
(3, 1, '03', NULL, 30),
(4, 1, '04', NULL, 30),
(5, 2, '01', NULL, 30),
(6, 2, '02', NULL, 30),
(7, 2, '03', NULL, 30),
(8, 2, '04', NULL, 30),
(9, 3, '01', NULL, 30),
(10, 3, '02', NULL, 30),
(11, 3, '03', NULL, 30),
(12, 3, '04', NULL, 30),
(14, 4, '01', 3, 30),
(15, 4, '02', NULL, 30);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `matric_no` varchar(50) NOT NULL,
  `program` varchar(100) DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `cgpa` decimal(3,2) DEFAULT NULL,
  `enrollment_year` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `matric_no`, `program`, `gpa`, `cgpa`, `enrollment_year`) VALUES
(1, 8, 'A22EC0062', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(2, 12, 'A22EC0099', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(3, 13, 'A22EC0100', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(4, 14, 'A22EC0101', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(5, 15, 'A22EC0102', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(6, 16, 'A22EC0103', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(7, 17, 'A22EC0104', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(8, 18, 'A22EC0105', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(9, 19, 'A22EC0106', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(10, 20, 'A22EC0107', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(11, 21, 'A22EC0108', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(12, 22, 'A22EC0109', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(13, 23, 'A22EC0110', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(14, 24, 'A22EC0111', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(15, 25, 'A22EC0112', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(16, 26, 'A22EC0113', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(17, 27, 'A22EC0114', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(18, 28, 'A22EC0115', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(19, 29, 'A22EC0116', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(20, 30, 'A22EC0117', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(21, 31, 'A22EC0118', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(22, 32, 'A22EC0119', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(23, 33, 'A22EC0120', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(24, 34, 'A22EC0121', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(25, 35, 'A22EC0122', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(26, 36, 'A22EC0123', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(27, 37, 'A22EC0124', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(28, 38, 'A22EC0125', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(29, 39, 'A22EC0126', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(30, 40, 'A22EC0127', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(31, 41, 'A22EC0128', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(32, 42, 'A22EC0129', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(33, 43, 'A22EC0130', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(34, 44, 'A22EC0131', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(35, 45, 'A22EC0132', 'Bachelor of Computer Science (Software Engineering)', NULL, NULL, NULL),
(37, 104, 'student104', 'BSc Computer Science', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `login_id` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `login_id`, `name`, `email`, `password`, `faculty_id`, `created_at`) VALUES
(8, 'A22EC0062', 'KUAN JI TONG', 'kuantong@graduate.utm.my', '$2y$10$bRBH15uzemCr6avnYzzckeCV5zVDehhu.ASy4WiSxKnXTn6LS75ke', 1, '2025-06-13 02:44:58'),
(9, 'S001', 'Ahmad Faizal Bin Ismail', 'faizal.ismail@utm.my', '$2y$10$hyy9npwhDXTPRmx6Vb4tle5HiF2gscFmU3x5tHlLn9zjnz3cuaoCa', 1, '2025-06-13 11:59:48'),
(10, 'S002', 'Noraini Bt. Yusof', 'noraini.yusof@utm.my', '$2y$10$WFX5gdDUEvTokX77aHoN/uCar3AavOYhpNC/aFnkji1EBqOzq2urm', 1, '2025-06-13 12:00:23'),
(11, 'S003', 'Muhammad Hafiz Bin Zainal', 'mhafiz.zainal@utm.my', '$2y$10$YdbUm7a3GoXulwHanBv9d.oj2wR6D7/9i7mYBLL/sxoS8RiZ7yk/O', 1, '2025-06-13 12:01:14'),
(12, 'A22EC0099', 'Oh Kai Xuan', 'ohxuan@graduate.utm.my', '$2y$10$eVy2LNkmGWJ9VCcbeoOe6ekxQVjbxw6EmZvFl60s3GFrxVo0LCShy', 1, '2025-06-13 20:34:44'),
(13, 'A22EC0100', 'Tan Wei Jie', 'weijie@graduate.utm.my', '$2y$10$iokki8iWhTwsDb/qf.Vutuc3hcseP4XbAO4SXfUEvGDSiA745vSOe', 1, '2025-06-13 20:36:44'),
(14, 'A22EC0101', 'Lim Hui Ying', 'huiying@graduate.utm.my', '$2y$10$SfrJA.cUvjm96Yf41cPWb.Fj9KMcg4EQJFiePxKAL.NtCTnSRCxN2', 1, '2025-06-13 20:38:44'),
(15, 'A22EC0102', 'Chong Zhi Hao', 'zhihao@graduate.utm.my', '$2y$10$LkigTra2zHxzPR5tumG0huci35JSEp/YOYXBaMOkgjridE4uSjrYe', 1, '2025-06-13 20:40:44'),
(16, 'A22EC0103', 'Nur Aisyah Binti Ramli', 'aisyah@graduate.utm.my', '$2y$10$XsS2kTlD7oRA9hP9TaX3xOKmqnjRb50CqZUyco7iweKojKeRmMADu', 1, '2025-06-13 20:42:44'),
(17, 'A22EC0104', 'Muhammad Danish Hakim', 'danish@graduate.utm.my', '$2y$10$dips7gNo3sj5F9.OmOESqeGX9ya0nw4jF9WwI.EG1FAj1QDISN3pG', 1, '2025-06-13 20:44:44'),
(18, 'A22EC0105', 'Lee Jia Wen', 'jiawen@graduate.utm.my', '$2y$10$YK1ju0R9XlhAZI.D6QfOiOtpzRHxLFiGh1LQ/8E.Cg/6LF/LCQDCS', 1, '2025-06-13 20:46:44'),
(19, 'A22EC0106', 'Ng Yi Xuan', 'yixuan@graduate.utm.my', '$2y$10$Eg45rSfEa2YC2OcwuPlVaO/PNrYiioQQjqR25kxL3/NgQ1wmzafW6', 1, '2025-06-13 20:48:44'),
(20, 'A22EC0107', 'Mohd Aiman Hakimi', 'aiman@graduate.utm.my', '$2y$10$ALwX9.TnyEXLHKu/OmkEN.Ti.r92o5HlD7n3iOfPj9iLr6.Ej3bgS', 1, '2025-06-13 20:50:44'),
(21, 'A22EC0108', 'Foo Jia Hui', 'jiahui@graduate.utm.my', '$2y$10$C1oJO6S18gBiuCHRnSOOmOShjUqUCqi0V6RByC43LjBdjgu.x/9em', 1, '2025-06-13 20:52:44'),
(22, 'A22EC0109', 'Syafiqah Binti Zulkifli', 'syafiqah@graduate.utm.my', '$2y$10$ndsTQkrSyWBeM6/j.aQPB.HJBfQku2iJZ/xgyRhoCX4qLvM/aiHq2', 1, '2025-06-13 20:54:44'),
(23, 'A22EC0110', 'Tan Zhen Yu', 'zhenyu@graduate.utm.my', '$2y$10$e1XhBCCyTXkxNiisfRXnP..utvu8Jg5MjkCv.ccHpY27zvimdqOiW', 1, '2025-06-13 20:56:44'),
(24, 'A22EC0111', 'Nor Hidayah Binti Ahmad', 'hidayah@graduate.utm.my', '$2y$10$RDTDQy.TE53PDMtQLru7xusQoaPed7XPX38.jkU/5r/ZxEJy7Iuna', 1, '2025-06-13 20:58:44'),
(25, 'A22EC0112', 'Jason Lim Yi Sheng', 'jason@graduate.utm.my', '$2y$10$hSEuCxwV46v5SV5Ho18k3.JUfnhuyR7S8Fn8CZHMO7pPHYwd1gUti', 1, '2025-06-13 21:00:44'),
(26, 'A22EC0113', 'Amira Binti Khairul', 'amira@graduate.utm.my', '$2y$10$AMtTr4czx3Vbf3As7x1VJuws0Nfk3GSDen1PnO64G8dUAsj1azXUy', 1, '2025-06-13 21:02:44'),
(27, 'A22EC0114', 'Wong Kai Lun', 'kailun@graduate.utm.my', '$2y$10$mFr66h9AgSr59L/birbfE.hZdtD4hukPVCm2gqTQeSCDuUp7V0r5W', 1, '2025-06-13 21:04:44'),
(28, 'A22EC0115', 'Nurul Aina Binti Azmi', 'aina@graduate.utm.my', '$2y$10$0EN1R8YvBCXLqFT14mNxReJxXFDE.JheWpi76E1BY.3nvloVJ/4Cu', 1, '2025-06-13 21:06:44'),
(29, 'A22EC0116', 'Chia Wei Han', 'weihan@graduate.utm.my', '$2y$10$UjETE6MYhumImkotEJJEFeOso.JtcRV85NxqcMvmwPyj8H7eb69J6', 1, '2025-06-13 21:08:44'),
(30, 'A22EC0117', 'Hafiz Bin Roslan', 'hafiz@graduate.utm.my', '$2y$10$0yuuQR4gk9j7uNvO0XgRjuaoj45afiT2GNrCOxbITRvR0mb70TO/u', 1, '2025-06-13 21:10:44'),
(31, 'A22EC0118', 'Liew Xin Yee', 'xinyee@graduate.utm.my', '$2y$10$SknSmJg45C36G0y5VFp8pOqv4ZsIKeOt4UTLt5WIQdcTJcefAn6Z2', 1, '2025-06-13 21:12:44'),
(32, 'A22EC0119', 'Muhammad Arif Danial', 'arifdanial@graduate.utm.my', '$2y$10$sECLxQb/1x.p.Qi.Gqb78..FQo1UkMr0gyD3co0U0Y7JA4jhwmpUq', 1, '2025-06-13 21:14:44'),
(33, 'A22EC0120', 'Khoo Zi Ning', 'zining@graduate.utm.my', '$2y$10$5WXBinYBprkEFVHCuix5wO78szMAR3A4Z2OpCNsZS7a8PE.GhdkTu', 1, '2025-06-13 21:16:44'),
(34, 'A22EC0121', 'Puteri Balqis', 'balqis@graduate.utm.my', '$2y$10$X8Nk/ABR6cHh/FeSvsQQ4eA1FPLUgKtIPcOzFwKXhzOQ9AAjMTJPi', 1, '2025-06-13 21:18:44'),
(35, 'A22EC0122', 'Goh Jun Hao', 'junhao@graduate.utm.my', '$2y$10$T5y1i6GY5Yxm4B7jn.TyiOnY.CyrzmygDp2GBTgjv79qnzWySw4OK', 1, '2025-06-13 21:20:44'),
(36, 'A22EC0123', 'Aina Syakirah', 'syakirah@graduate.utm.my', '$2y$10$QJhu1vnURtSiWGPrfsLsheIiEUxyeXw8M/UwaS3YWfdXSOQK/BTfS', 1, '2025-06-13 21:22:44'),
(37, 'A22EC0124', 'Teoh Wei Li', 'weili@graduate.utm.my', '$2y$10$wb8zRd0wyhSGqhDHV.3Ete5NmnsMzrn7FHwDliwamRi0dybLuMgLe', 1, '2025-06-13 21:24:44'),
(38, 'A22EC0125', 'Nabilah Binti Hafiz', 'nabilah@graduate.utm.my', '$2y$10$Bh3iDYEGUvcFVGHenZYjPeKyXMXzUwo.Ubpya1C4CPx7tiPt/Qula', 1, '2025-06-13 21:26:44'),
(39, 'A22EC0126', 'Low Yu Xuan', 'yuxuan@graduate.utm.my', '$2y$10$5b.3ahV1ItkGqdxpzRIBt./7gxGpi85KRXMfbhLvo/K728rgU3ya6', 1, '2025-06-13 21:28:44'),
(40, 'A22EC0127', 'Farhan Bin Zainal', 'farhan@graduate.utm.my', '$2y$10$zeQ2EUSDHBL3aqnEKhSJe.vMbxqAPwKTc.x3cdw.aZL8FnMXU/KQi', 1, '2025-06-13 21:30:44'),
(41, 'A22EC0128', 'Chin Mei Ling', 'meiling@graduate.utm.my', '$2y$10$Stso6FvHKeyh1eiv9I.GD.3l91MkO6CT7CRnQCr/jGL2eoTII61kK', 1, '2025-06-13 21:32:44'),
(42, 'A22EC0129', 'Azhar Bin Rahim', 'azhar@graduate.utm.my', '$2y$10$Gz9LKwFuN9.kRaW.FyZhqura1LwJRYAsoxY2XAzq1LXFVFZzsjZNC', 1, '2025-06-13 21:34:44'),
(43, 'A22EC0130', 'Tay Jin Xuan', 'jinxuan@graduate.utm.my', '$2y$10$JdagnXcZwzTcfZVIBaC6nu2.GtDHUYtzf94ef042ajoFVBTFrIoV.', 1, '2025-06-13 21:36:44'),
(44, 'A22EC0131', 'Nadiah Binti Zahid', 'nadiah@graduate.utm.my', '$2y$10$r74wUdWpNke7QQU7z1IzKubszWxHo4xMh4moMRq/h7AbQ1iDxrep6', 1, '2025-06-13 21:38:44'),
(45, 'A22EC0132', 'Yap Qi Ming', 'qiming@graduate.utm.my', '$2y$10$wL4pX7w3rnRBp.nuInQYg.TTD1UqtrDdyw8o5oTd.ja1WeoPZ/Otm', 1, '2025-06-13 21:40:44'),
(46, 'A22EC0002', 'Bob Student', 'bob@student.edu', 'hashedpassword', 1, '2025-06-20 22:30:13'),
(104, 'student104', 'Alice Brown', 'student104@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-06-25 06:22:50'),
(106, 'A001', 'Admin 1', 'admin1@gmail.com', '$2y$10$SK/j5XAxk7ZDsu8hKPDD5usc4kQQE0XZxkiRCByRGed5V3NQ6Cowy', 1, '2025-07-07 04:19:51'),
(107, 'S004', 'Muhammad You Tan bin Seck Tan', 'youtan@graduate.utm.my', '$2y$10$5/X87Ga95AwX/oqQHYczWOxstI9s5qezQoPg7IAJBEpg9dqZ4/IrS', 1, '2025-07-21 16:28:13'),
(108, 'S006', 'Ali', 'ali@graduate.utm.my', '$2y$10$KAlILKcUqQw3ifVnwMJ87ew/B1LUU/difmQedu.eijTZ2NllZBtLC', 1, '2025-07-21 17:27:20');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(106, 1),
(9, 2),
(10, 2),
(11, 2),
(107, 2),
(108, 2),
(9, 3),
(8, 4),
(12, 4),
(13, 4),
(14, 4),
(15, 4),
(16, 4),
(17, 4),
(18, 4),
(19, 4),
(20, 4),
(21, 4),
(22, 4),
(23, 4),
(24, 4),
(25, 4),
(26, 4),
(27, 4),
(28, 4),
(29, 4),
(30, 4),
(31, 4),
(32, 4),
(33, 4),
(34, 4),
(35, 4),
(36, 4),
(37, 4),
(38, 4),
(39, 4),
(40, 4),
(41, 4),
(42, 4),
(43, 4),
(44, 4),
(45, 4),
(104, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `advisor_advisee`
--
ALTER TABLE `advisor_advisee`
  ADD PRIMARY KEY (`advisor_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `components`
--
ALTER TABLE `components`
  ADD PRIMARY KEY (`component_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `fk_faculty_course` (`faculty_id`);

--
-- Indexes for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `fk_student_enrollment` (`student_id`),
  ADD KEY `fk_section_enrollment` (`section_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`faculty_id`),
  ADD UNIQUE KEY `faculty_abbreviation` (`faculty_abbreviation`),
  ADD UNIQUE KEY `faculty_name` (`faculty_name`);

--
-- Indexes for table `final_exam`
--
ALTER TABLE `final_exam`
  ADD PRIMARY KEY (`exam_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `lecturers`
--
ALTER TABLE `lecturers`
  ADD PRIMARY KEY (`lecturer_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`mark_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `component_id` (`component_id`);

--
-- Indexes for table `remark_requests`
--
ALTER TABLE `remark_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `component_id` (`component_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `name` (`role_name`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `fk_section_lecturer_1` (`lecturer_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `login_id` (`login_id`),
  ADD KEY `fk_faculty_user` (`faculty_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `components`
--
ALTER TABLE `components`
  MODIFY `component_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `enrollment`
--
ALTER TABLE `enrollment`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `final_exam`
--
ALTER TABLE `final_exam`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `lecturers`
--
ALTER TABLE `lecturers`
  MODIFY `lecturer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `mark_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `remark_requests`
--
ALTER TABLE `remark_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `advisor_advisee`
--
ALTER TABLE `advisor_advisee`
  ADD CONSTRAINT `advisor_advisee_ibfk_1` FOREIGN KEY (`advisor_id`) REFERENCES `lecturers` (`lecturer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `advisor_advisee_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `components`
--
ALTER TABLE `components`
  ADD CONSTRAINT `components_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`);

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_faculty_course` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD CONSTRAINT `fk_section_enrollment` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`),
  ADD CONSTRAINT `fk_student_enrollment` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `final_exam`
--
ALTER TABLE `final_exam`
  ADD CONSTRAINT `final_exam_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `final_exam_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`);

--
-- Constraints for table `lecturers`
--
ALTER TABLE `lecturers`
  ADD CONSTRAINT `lecturers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `marks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `marks_ibfk_2` FOREIGN KEY (`component_id`) REFERENCES `components` (`component_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `remark_requests`
--
ALTER TABLE `remark_requests`
  ADD CONSTRAINT `remark_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `remark_requests_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `remark_requests_ibfk_4` FOREIGN KEY (`component_id`) REFERENCES `components` (`component_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `fk_section_lecturer_1` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`lecturer_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_faculty_user` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
