-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2024 at 01:28 PM
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
-- Database: `u347017890_actswebportal`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `announcement_type` enum('text','image') NOT NULL,
  `announcement_title` varchar(255) NOT NULL,
  `announcement_content` text DEFAULT NULL,
  `availability` enum('always','today','week','month','specificDate') NOT NULL,
  `specific_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `app_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category` tinyint(1) NOT NULL COMMENT '1 = New Student, 2 = Transferee',
  `year` int(11) NOT NULL,
  `course` int(11) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `mname` varchar(255) DEFAULT NULL,
  `lname` varchar(255) NOT NULL,
  `birth` date NOT NULL,
  `gen` tinyint(1) NOT NULL COMMENT '1=Male, 2=Female',
  `s_status` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `is_p_verified` tinyint(1) NOT NULL DEFAULT 0,
  `nationality` varchar(20) NOT NULL,
  `address` varchar(500) NOT NULL,
  `mother` varchar(255) NOT NULL,
  `mother_phone` varchar(255) DEFAULT NULL,
  `mother_occu` varchar(255) NOT NULL,
  `father` varchar(255) NOT NULL,
  `father_phone` varchar(255) DEFAULT NULL,
  `father_occu` varchar(255) NOT NULL,
  `guardian` varchar(255) NOT NULL,
  `guardian_phone` varchar(255) NOT NULL,
  `is_gp_verified` tinyint(1) NOT NULL DEFAULT 0,
  `relation` varchar(255) NOT NULL,
  `elem` varchar(255) NOT NULL,
  `elem_year` int(11) NOT NULL,
  `junior` varchar(255) NOT NULL,
  `junior_year` int(11) NOT NULL,
  `senior` varchar(255) DEFAULT NULL,
  `strand` varchar(255) DEFAULT NULL,
  `senior_year` int(11) DEFAULT NULL,
  `college` varchar(255) DEFAULT NULL,
  `old_course` varchar(255) DEFAULT NULL,
  `college_year` int(11) DEFAULT NULL,
  `card_tor` mediumblob NOT NULL,
  `agreement` varchar(500) NOT NULL,
  `prom_date` date NOT NULL,
  `signature` mediumblob NOT NULL,
  `date_applied` timestamp NULL DEFAULT NULL,
  `app_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Pending, 2=Approved, 3=Rejected',
  `app_remarks` varchar(255) DEFAULT NULL,
  `app_remarks_date` timestamp NULL DEFAULT NULL,
  `app_process_by` varchar(255) DEFAULT NULL,
  `expiry` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attend_id` int(11) NOT NULL,
  `student_num` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL COMMENT '1 = Absent, 2 = Late',
  `attend_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

CREATE TABLE `classrooms` (
  `room_id` int(11) NOT NULL,
  `room_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `room_capacity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_schedule`
--

CREATE TABLE `class_schedule` (
  `sched_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `is_repeating` tinyint(1) NOT NULL DEFAULT 0,
  `repeating_data` text DEFAULT NULL,
  `sched_date` date DEFAULT NULL,
  `time_from` time NOT NULL,
  `time_to` time NOT NULL,
  `sy_id` int(11) NOT NULL,
  `sem_id` int(11) NOT NULL,
  `sched_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_schedule_sections`
--

CREATE TABLE `class_schedule_sections` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_strand`
--

CREATE TABLE `course_strand` (
  `id` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '1=strand, 2=course',
  `name` varchar(255) NOT NULL,
  `acronym` varchar(20) NOT NULL,
  `posting_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_verify`
--

CREATE TABLE `email_verify` (
  `id` int(11) NOT NULL,
  `code` int(5) NOT NULL,
  `expires` int(11) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `id` int(100) NOT NULL,
  `fname` varchar(120) NOT NULL,
  `mname` varchar(120) NOT NULL,
  `lname` varchar(120) NOT NULL,
  `prefix` varchar(10) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `image` mediumblob DEFAULT NULL,
  `posting_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forgot_pass`
--

CREATE TABLE `forgot_pass` (
  `id` int(11) NOT NULL,
  `code` int(5) NOT NULL,
  `expires` int(11) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `cs_id` int(10) NOT NULL,
  `student_num` int(10) NOT NULL,
  `prelim` int(10) NOT NULL,
  `midterm` int(10) NOT NULL,
  `prefinal` int(10) NOT NULL,
  `final` int(10) NOT NULL,
  `final_grade` int(10) NOT NULL,
  `scale` decimal(5,2) NOT NULL,
  `remarks` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `phone_verify`
--

CREATE TABLE `phone_verify` (
  `id` int(11) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `code` int(11) NOT NULL,
  `expires` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `req_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `doc_type` varchar(500) NOT NULL,
  `copies` int(3) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `req_date` date NOT NULL DEFAULT current_timestamp(),
  `req_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Pending, 2=Processing, 3=Rejected, 4=Fulfilled',
  `req_remarks` varchar(255) DEFAULT NULL,
  `pickup_date` date DEFAULT NULL,
  `req_process_date` timestamp NULL DEFAULT NULL,
  `req_process_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_info`
--

CREATE TABLE `school_info` (
  `id` int(11) NOT NULL,
  `sc_name` varchar(255) NOT NULL,
  `sc_add` varchar(500) NOT NULL,
  `sc_num` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_years`
--

CREATE TABLE `school_years` (
  `sy_id` int(11) NOT NULL,
  `year_start` int(11) NOT NULL,
  `year_end` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `sy_created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `year` int(4) NOT NULL,
  `course` int(11) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 35,
  `students_count` int(11) NOT NULL,
  `section_sy` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `sem_id` int(11) NOT NULL,
  `semester` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1= 1st Sem, 2=nd Sem',
  `sy_id` int(11) NOT NULL,
  `sem_is_active` tinyint(4) NOT NULL DEFAULT 0,
  `sem_start` timestamp NULL DEFAULT NULL,
  `sem_end` timestamp NULL DEFAULT NULL,
  `sem_created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_list`
--

CREATE TABLE `student_list` (
  `student_num` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `student_year` int(11) NOT NULL,
  `student_course` int(11) NOT NULL,
  `sy_enrolled` int(11) NOT NULL,
  `sem_enrolled` int(11) NOT NULL,
  `enrolled_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Enrolled, 0=Not Enrolled',
  `enrolled_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `admit_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_section`
--

CREATE TABLE `student_section` (
  `id` int(11) NOT NULL,
  `student_num` int(11) NOT NULL,
  `section_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_subjects`
--

CREATE TABLE `student_subjects` (
  `ss_id` int(11) NOT NULL,
  `student_num` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `sem_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `school_year_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_description` varchar(255) NOT NULL,
  `subject_code` varchar(255) NOT NULL,
  `sub_grade_year` int(11) NOT NULL,
  `sub_course` int(11) DEFAULT NULL,
  `semester` tinyint(4) NOT NULL COMMENT '1 = 1st, 2=2nd',
  `units` int(11) NOT NULL,
  `has_lab` tinyint(1) NOT NULL,
  `sub_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `trans_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `mode` varchar(255) NOT NULL,
  `ref_num` varchar(255) NOT NULL,
  `trans_date` date NOT NULL,
  `sender` varchar(255) NOT NULL,
  `amount` int(11) NOT NULL,
  `proof` mediumblob NOT NULL,
  `trans_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Pending, 2=Approved, 3=Rejected',
  `trans_remarks` varchar(255) DEFAULT NULL,
  `trans_process_date` timestamp NULL DEFAULT NULL,
  `trans_process_by` varchar(255) DEFAULT NULL,
  `purpose` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_accounts`
--

CREATE TABLE `user_accounts` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `posting_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` mediumblob DEFAULT NULL,
  `account_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=student, 2=admin, 3=superadmin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`app_id`),
  ADD KEY `applications_ibfk_1` (`user_id`),
  ADD KEY `course` (`course`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attend_id`),
  ADD KEY `student_num` (`student_num`);

--
-- Indexes for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `class_schedule`
--
ALTER TABLE `class_schedule`
  ADD PRIMARY KEY (`sched_id`),
  ADD KEY `faculty_id` (`faculty_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `class_schedule_sections`
--
ALTER TABLE `class_schedule_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `course_strand`
--
ALTER TABLE `course_strand`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_verify`
--
ALTER TABLE `email_verify`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forgot_pass`
--
ALTER TABLE `forgot_pass`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `phone_verify`
--
ALTER TABLE `phone_verify`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`req_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `school_info`
--
ALTER TABLE `school_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school_years`
--
ALTER TABLE `school_years`
  ADD PRIMARY KEY (`sy_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `section_sy` (`section_sy`),
  ADD KEY `course` (`course`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`sem_id`),
  ADD KEY `sy_id` (`sy_id`);

--
-- Indexes for table `student_list`
--
ALTER TABLE `student_list`
  ADD PRIMARY KEY (`student_num`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `student_list_ibfk_2` (`sem_enrolled`),
  ADD KEY `student_course` (`student_course`),
  ADD KEY `sy_enrolled` (`sy_enrolled`);

--
-- Indexes for table `student_section`
--
ALTER TABLE `student_section`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `student_num` (`student_num`);

--
-- Indexes for table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD PRIMARY KEY (`ss_id`),
  ADD KEY `student_num` (`student_num`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `sem_id` (`sem_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `school_year_id` (`school_year_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD KEY `sub_course` (`sub_course`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`trans_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_accounts`
--
ALTER TABLE `user_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attend_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `class_schedule`
--
ALTER TABLE `class_schedule`
  MODIFY `sched_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `class_schedule_sections`
--
ALTER TABLE `class_schedule_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `course_strand`
--
ALTER TABLE `course_strand`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `email_verify`
--
ALTER TABLE `email_verify`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `forgot_pass`
--
ALTER TABLE `forgot_pass`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `phone_verify`
--
ALTER TABLE `phone_verify`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `req_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `school_info`
--
ALTER TABLE `school_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `school_years`
--
ALTER TABLE `school_years`
  MODIFY `sy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `sem_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_list`
--
ALTER TABLE `student_list`
  MODIFY `student_num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_section`
--
ALTER TABLE `student_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_subjects`
--
ALTER TABLE `student_subjects`
  MODIFY `ss_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `trans_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_accounts`
--
ALTER TABLE `user_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_accounts` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`course`) REFERENCES `course_strand` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_num`) REFERENCES `student_list` (`student_num`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `class_schedule`
--
ALTER TABLE `class_schedule`
  ADD CONSTRAINT `class_schedule_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `class_schedule_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `classrooms` (`room_id`) ON UPDATE CASCADE;

--
-- Constraints for table `class_schedule_sections`
--
ALTER TABLE `class_schedule_sections`
  ADD CONSTRAINT `class_schedule_sections_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class_schedule` (`sched_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `class_schedule_sections_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON UPDATE CASCADE;

--
-- Constraints for table `forgot_pass`
--
ALTER TABLE `forgot_pass`
  ADD CONSTRAINT `forgot_pass_ibfk_1` FOREIGN KEY (`email`) REFERENCES `user_accounts` (`email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_accounts` (`id`);

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`section_sy`) REFERENCES `school_years` (`sy_id`),
  ADD CONSTRAINT `sections_ibfk_2` FOREIGN KEY (`course`) REFERENCES `course_strand` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `semesters`
--
ALTER TABLE `semesters`
  ADD CONSTRAINT `semesters_ibfk_1` FOREIGN KEY (`sy_id`) REFERENCES `school_years` (`sy_id`);

--
-- Constraints for table `student_list`
--
ALTER TABLE `student_list`
  ADD CONSTRAINT `student_list_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_accounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `student_list_ibfk_2` FOREIGN KEY (`sem_enrolled`) REFERENCES `semesters` (`sem_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `student_list_ibfk_3` FOREIGN KEY (`student_course`) REFERENCES `course_strand` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `student_list_ibfk_4` FOREIGN KEY (`sy_enrolled`) REFERENCES `school_years` (`sy_id`) ON UPDATE CASCADE;

--
-- Constraints for table `student_section`
--
ALTER TABLE `student_section`
  ADD CONSTRAINT `student_section_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `student_section_ibfk_2` FOREIGN KEY (`student_num`) REFERENCES `student_list` (`student_num`);

--
-- Constraints for table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD CONSTRAINT `student_subjects_ibfk_1` FOREIGN KEY (`student_num`) REFERENCES `student_list` (`student_num`) ON UPDATE CASCADE,
  ADD CONSTRAINT `student_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `student_subjects_ibfk_3` FOREIGN KEY (`sem_id`) REFERENCES `semesters` (`sem_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `student_subjects_ibfk_4` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `student_subjects_ibfk_5` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`sy_id`) ON UPDATE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`sub_course`) REFERENCES `course_strand` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_accounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
