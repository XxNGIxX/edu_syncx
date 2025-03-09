-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 09, 2025 at 09:39 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `edu_syncx`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments_new`
--

CREATE TABLE `assignments_new` (
  `assignment_id` int(11) NOT NULL COMMENT '	รหัสงานที่ไม่ซ้ำ',
  `assignment_title` varchar(25) NOT NULL COMMENT 'ชื่องานที่มอบหมาย',
  `subject_id` int(10) NOT NULL COMMENT 'วิชาที่มอบหมายงาน',
  `total_points` int(11) NOT NULL COMMENT 'คะแนนเต็มของงาน',
  `deadline` date DEFAULT NULL COMMENT 'ส่งได้ถึง',
  `assignment_details` varchar(250) NOT NULL COMMENT 'รายละเอียดงาน',
  `class` varchar(10) NOT NULL,
  `turn_in` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments_new`
--

INSERT INTO `assignments_new` (`assignment_id`, `assignment_title`, `subject_id`, `total_points`, `deadline`, `assignment_details`, `class`, `turn_in`) VALUES
(62, 'แบบฝึกที่ 1', 28, 10, '2024-10-01', 'ให้นักเรียนบอกชื่อผลไม้มา 20 ชื่อ', '', '0000-00-00'),
(66, 'แบบฝึกที่ 1.2', 28, 11, '2024-10-01', 'ให้นักเรียนบอกชื่อผลไม้มา 20 ชื่อ', '', '0000-00-00');

-- --------------------------------------------------------

--
-- Table structure for table `parent`
--

CREATE TABLE `parent` (
  `parent_id` int(10) NOT NULL,
  `user_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parent`
--

INSERT INTO `parent` (`parent_id`, `user_id`) VALUES
(17, 97),
(18, 100),
(19, 102),
(20, 104),
(21, 106),
(22, 108),
(23, 110),
(24, 112);

-- --------------------------------------------------------

--
-- Table structure for table `scores_new`
--

CREATE TABLE `scores_new` (
  `score_id` int(11) NOT NULL COMMENT 'รหัสคะแนนที่ไม่ซ้ำ',
  `student_id` int(11) DEFAULT NULL COMMENT 'รหัสนักเรียน (Foreign Key)',
  `assignment_id` int(11) DEFAULT NULL COMMENT 'รหัสงานที่มอบหมาย (Foreign Key)',
  `score` int(11) DEFAULT NULL COMMENT 'คะแนนที่นักเรียนได้รับ',
  `submission_status` int(20) DEFAULT NULL COMMENT 'สถานะการส่งงาน (ส่งแล้ว/ไม่ส่งงาน)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scores_new`
--

INSERT INTO `scores_new` (`score_id`, `student_id`, `assignment_id`, `score`, `submission_status`) VALUES
(113, 43, 62, 10, 1),
(114, 48, 62, 0, 2),
(115, 49, 62, 5, 1),
(116, 50, 62, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `students_new`
--

CREATE TABLE `students_new` (
  `student_id` int(11) NOT NULL COMMENT 'รหัสนักเรียนที่ไม่ซ้ำ',
  `class` varchar(10) DEFAULT NULL COMMENT 'ระดับชั้นเรียน (เช่น 1/1)',
  `user_id` int(10) DEFAULT NULL,
  `student_number` int(3) NOT NULL,
  `parent_id` int(10) NOT NULL,
  `degree` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students_new`
--

INSERT INTO `students_new` (`student_id`, `class`, `user_id`, `student_number`, `parent_id`, `degree`) VALUES
(42, '2/1', 96, 1, 17, 2),
(43, '1/1', 98, 1, 17, 1),
(44, '2/1', 99, 2, 18, 2),
(45, '2/1', 101, 3, 19, 2),
(46, '2/1', 103, 4, 20, 2),
(47, '2/1', 105, 5, 21, 2),
(48, '1/1', 107, 2, 22, 1),
(49, '1/1', 109, 3, 23, 1),
(50, '1/1', 111, 4, 24, 1);

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `subject_id` int(10) NOT NULL COMMENT 'รหัสวิชา',
  `subject_name` varchar(30) NOT NULL COMMENT 'ชื่อวิชา',
  `term` varchar(10) NOT NULL COMMENT 'เทอม',
  `year` int(11) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `class` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`subject_id`, `subject_name`, `term`, `year`, `teacher_id`, `class`) VALUES
(28, 'อังกฤษ', '1', 2567, 30, 1),
(35, 'อังกฤษ', '1', 2567, 30, 2),
(39, 'อังกฤษ', '1', 2567, 30, 3),
(40, 'อังกฤษ', '1', 2567, 31, 2);

-- --------------------------------------------------------

--
-- Table structure for table `teachers_new`
--

CREATE TABLE `teachers_new` (
  `teacher_id` int(11) NOT NULL COMMENT 'รหัสครูที่ไม่ซ้ำ',
  `user_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers_new`
--

INSERT INTO `teachers_new` (`teacher_id`, `user_id`) VALUES
(30, 113),
(31, 114);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(10) NOT NULL COMMENT 'รหัสผู้ใช้',
  `email` varchar(100) NOT NULL COMMENT 'อีเมล',
  `role` varchar(10) NOT NULL COMMENT 'บทบาทการเข้าใช้งาน',
  `password` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `lname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `tel` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `email`, `role`, `password`, `fname`, `lname`, `tel`) VALUES
(1, 'admin@gmail.com', 'admin', '88888888', 'edu_syncx', 'admin', '0925646405'),
(96, 'a@gmail.com', 'นักเรียน', 'S7610942', 'aina', 'lingala', '0985125141'),
(97, 'b@gmail.com', 'ผู้ปกครอง', '012345', 'sun', 'lingala', '0218541557'),
(98, 'c@gmail.com', 'นักเรียน', 'S7523149', 'sulhil', 'lingala', '0985125144'),
(99, 'd@gmail.com', 'นักเรียน', 'S3154206', 'fis', 'ni', '0985125144'),
(100, 'f@gmail.com', 'ผู้ปกครอง', 'P8571094', 'rose', 'xcdd', '0218541557'),
(101, 'lisa@gmail.com', 'นักเรียน', 'S2974613', 'lisa', 'lalabu', '0985125141'),
(102, 'sali@gmail.com', 'ผู้ปกครอง', 'P6912438', 'sali', 'lalabu', '0218541557'),
(103, 'mina@gmail.com', 'นักเรียน', 'S3826047', 'mina', 'jang', '0985125144'),
(104, 'nami@gmail.com', 'ผู้ปกครอง', 'P1324967', 'nami', 'jang', '3'),
(105, 'sana@gmail.com', 'นักเรียน', 'S7918354', 'sana', 'minaki', '0985125144'),
(106, 'sasa@gmail.com', 'ผู้ปกครอง', 'P8412670', 'sasa', 'minaki', '0218541557'),
(107, 'tai@gmail.com', 'นักเรียน', 'S5089716', 'aa', 'bus', '0985125141'),
(108, 'tani@gmail.com', 'ผู้ปกครอง', 'P1065743', 'tani', 'bus', '3'),
(109, 'hai@gmail.com', 'นักเรียน', 'S4391580', 'tai', 'bus', '0985125141'),
(110, 'bb@gmail.com', 'ผู้ปกครอง', 'P4319502', 'bb', 'bus', '0'),
(111, 'row@gmail.com', 'นักเรียน', 'S3207859', 'row', 'what', '0985125142'),
(112, 'pow@gmail.com', 'ผู้ปกครอง', 'P7896301', 'pow', 'what', '0'),
(113, 'teach2@gmail.com', 'ครู', 'T8035612', 'teach', 'y', '55555'),
(114, 'r@gmail.com', 'ครู', 'T3890475', 'teach', 'lingala', '5555533');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments_new`
--
ALTER TABLE `assignments_new`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `parent`
--
ALTER TABLE `parent`
  ADD PRIMARY KEY (`parent_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `scores_new`
--
ALTER TABLE `scores_new`
  ADD PRIMARY KEY (`score_id`),
  ADD KEY `assignment_id` (`assignment_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `students_new`
--
ALTER TABLE `students_new`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `teachers_new`
--
ALTER TABLE `teachers_new`
  ADD PRIMARY KEY (`teacher_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments_new`
--
ALTER TABLE `assignments_new`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '	รหัสงานที่ไม่ซ้ำ', AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `parent`
--
ALTER TABLE `parent`
  MODIFY `parent_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `scores_new`
--
ALTER TABLE `scores_new`
  MODIFY `score_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสคะแนนที่ไม่ซ้ำ', AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `students_new`
--
ALTER TABLE `students_new`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสนักเรียนที่ไม่ซ้ำ', AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `subject_id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'รหัสวิชา', AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `teachers_new`
--
ALTER TABLE `teachers_new`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสครูที่ไม่ซ้ำ', AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'รหัสผู้ใช้', AUTO_INCREMENT=115;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments_new`
--
ALTER TABLE `assignments_new`
  ADD CONSTRAINT `assignments_new_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`);

--
-- Constraints for table `parent`
--
ALTER TABLE `parent`
  ADD CONSTRAINT `parent_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `scores_new`
--
ALTER TABLE `scores_new`
  ADD CONSTRAINT `scores_new_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments_new` (`assignment_id`),
  ADD CONSTRAINT `scores_new_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students_new` (`student_id`);

--
-- Constraints for table `students_new`
--
ALTER TABLE `students_new`
  ADD CONSTRAINT `students_new_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `students_new_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`parent_id`);

--
-- Constraints for table `subject`
--
ALTER TABLE `subject`
  ADD CONSTRAINT `subject_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers_new` (`teacher_id`);

--
-- Constraints for table `teachers_new`
--
ALTER TABLE `teachers_new`
  ADD CONSTRAINT `teachers_new_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
