-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2025 at 03:26 AM
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
(2, 'SECJ3623', 'MOBILE APPLICATION PROGRAMMING', 1, 3, NULL, NULL),
(3, 'SECJ3563', 'COMPUTATIONAL INTELLIGENCE', 1, 3, NULL, NULL);

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
(1, 'FC', 'Faculty of Computing');

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
(3, 'Academic Advisor'),
(1, 'Admin'),
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
(1, 1, '01', 2, 30),
(2, 1, '02', NULL, 30),
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
(13, 1, '05', NULL, 30);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `login_id` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `title` varchar(20) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `login_id`, `name`, `title`, `email`, `password`, `faculty_id`, `created_at`) VALUES
(1, 'A22EC0063', 'KUAN JI TONG', '', 'kuantong@graduate.utm.my', '$2y$10$zMyHAzFBHuH1dm68g.H4uOryPfrZG4VwP8L4t2VcHV.qQGnGHDrHi', 1, '2025-06-11 04:39:38'),
(2, 'S001', 'Ahmad Faizal bin Ismail', 'Dr.', 'afaizal@utm.my', '$2y$10$mBjDhyCVKINFbmEEqlhZlu24gUl6Qcih846/f2wC.C.WzilS70OwW', 1, '2025-06-11 20:23:06'),
(3, 'S002', 'Noraini bt. Mohamad', 'Prof. Madya Dr.', 'noraini.m@utm.my', '$2y$10$q3o9h7jVXTTKtRqUh6y86uq19dI27YDYwzTkXOHewh6g2f89RFV7C', 1, '2025-06-11 20:28:25'),
(4, 'S003', 'Lim Wei Jian', 'Dr.', 'wjlim@utm.my', '$2y$10$GI4StB.3Kp7Cu.OHFCGUbeKlaqya2lsyqdMgU4XxzmnR/mRo4.fDW', 1, '2025-06-11 20:29:17'),
(5, 'S004', 'Siti Hajar bt. Yusof', 'Ir. Dr.', 'sitihajar@utm.my', '$2y$10$q4cDPcC6e/3ApNXVhIJIzOYPktUegcze5F21mrOXNwYoEZu9hHvVK', 1, '2025-06-11 20:29:53'),
(6, 'S005', 'Mohd Rizal bin Abdul Rahman', 'Prof. Dr.', 'rizalrahman@utm.my', '$2y$10$wEpMAyxXpqAHJK8CHqbgPuL.C8ZeqDuQuTMfACN1HnoQJlzPBz5ja', 1, '2025-06-11 20:30:18');

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
(2, 2),
(3, 2),
(4, 2),
(5, 2),
(6, 2),
(1, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `fk_faculty_course` (`faculty_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`faculty_id`),
  ADD UNIQUE KEY `faculty_abbreviation` (`faculty_abbreviation`),
  ADD UNIQUE KEY `faculty_name` (`faculty_name`);

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
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_faculty_course` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `fk_section_lecturer_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

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
