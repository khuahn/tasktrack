-- BEGIN EXISTING DUMP (kept intact)
-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql101.infinityfree.com
-- Generation Time: Oct 10, 2025 at 10:00 PM
-- Server version: 11.4.7-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_39835366_track`
--

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `task_id`, `user_id`, `note`, `created_at`) VALUES
(1, 4, 3, 'Test Modal', '2025-10-08 17:56:34'),
(2, 4, 3, 'Test modal 2', '2025-10-08 18:12:45'),
(3, 2, 3, 'Terst', '2025-10-08 18:31:39'),
(4, 2, 3, 'test', '2025-10-08 18:31:41'),
(5, 2, 3, 'test', '2025-10-08 18:31:42'),
(6, 2, 3, 'test', '2025-10-08 18:31:45'),
(7, 2, 3, 'trst', '2025-10-08 18:31:47'),
(8, 2, 3, 'trsty', '2025-10-08 18:31:53'),
(9, 3, 3, 'Test', '2025-10-09 13:47:56'),
(10, 3, 3, 'Test 2', '2025-10-09 18:04:34'),
(11, 4, 3, 'Tesrt 3', '2025-10-09 18:04:41'),
(12, 2, 3, 'Test', '2025-10-09 18:04:51'),
(13, 2, 3, 'Test to bottom', '2025-10-09 18:05:02'),
(14, 2, 3, 'Ching chong', '2025-10-09 18:05:39'),
(15, 2, 3, 'Ching chong 2\r\n 2', '2025-10-09 18:08:06'),
(16, 2, 3, 'Test', '2025-10-09 18:11:13'),
(17, 3, 3, 'Test 1', '2025-10-09 18:11:34'),
(18, 2, 3, 'Test', '2025-10-09 18:11:59'),
(19, 2, 3, 'Test 3', '2025-10-09 18:12:05'),
(20, 3, 3, 'Test 2', '2025-10-09 18:12:15'),
(21, 2, 3, 'test', '2025-10-09 18:16:53'),
(22, 4, 3, 'Test', '2025-10-09 18:24:26'),
(23, 4, 3, 'Test 2', '2025-10-09 19:40:22'),
(24, 4, 3, 'test 3', '2025-10-09 19:40:48'),
(25, 4, 3, 'testr 4', '2025-10-09 19:43:00'),
(26, 4, 3, 'asdasd2', '2025-10-09 19:43:05'),
(27, 4, 3, 'dasd', '2025-10-09 19:46:01'),
(28, 2, 3, 'test', '2025-10-09 19:49:35'),
(29, 4, 3, 'Sum Ting Wong', '2025-10-09 19:50:15'),
(30, 3, 3, 'Wee Tu Low', '2025-10-09 19:50:32'),
(31, 3, 3, 'Test', '2025-10-09 20:42:53'),
(32, 4, 3, 'test', '2025-10-09 20:43:01'),
(33, 3, 3, 'Test', '2025-10-09 20:43:08'),
(34, 2, 3, 'Test', '2025-10-09 20:43:21'),
(35, 2, 3, 'Test', '2025-10-09 20:43:26'),
(36, 4, 3, 'test 2', '2025-10-09 20:45:43'),
(37, 3, 3, 'test 23', '2025-10-09 20:45:59'),
(38, 2, 3, 'Testong', '2025-10-09 20:56:17'),
(39, 3, 3, 'Testing', '2025-10-09 20:56:31'),
(40, 4, 3, 'Test', '2025-10-09 20:58:38'),
(41, 3, 3, 'We to low', '2025-10-09 20:59:34'),
(42, 3, 3, 'last', '2025-10-09 20:59:41'),
(43, 4, 3, 'we to lpw', '2025-10-09 20:59:49'),
(44, 3, 3, 'test', '2025-10-09 21:00:08'),
(45, 4, 3, 'Cached issue', '2025-10-09 21:00:40'),
(46, 4, 3, 'Caching', '2025-10-09 21:00:55'),
(47, 4, 3, 'To chii', '2025-10-09 21:01:04'),
(48, 2, 3, 'tEST', '2025-10-10 13:16:58'),
(49, 2, 3, 'tEST', '2025-10-10 13:17:07'),
(50, 3, 3, 'TEST', '2025-10-10 13:17:23'),
(51, 4, 3, 'test 3', '2025-10-10 14:35:23'),
(52, 2, 3, 'test i', '2025-10-10 14:35:47'),
(53, 3, 3, 'Rest', '2025-10-10 14:36:16'),
(54, 3, 3, 'sedeasqew', '2025-10-10 14:48:08'),
(55, 3, 3, 'asdasd', '2025-10-10 14:48:15'),
(56, 2, 3, 'Test', '2025-10-10 14:59:05'),
(57, 3, 3, 'Test', '2025-10-10 15:05:32'),
(58, 3, 3, 'Test', '2025-10-10 15:05:38'),
(59, 2, 3, 'Test 2', '2025-10-10 15:06:34'),
(60, 3, 3, 'Test', '2025-10-10 15:11:00'),
(61, 2, 3, 'Test', '2025-10-10 15:11:22'),
(62, 2, 3, 'TEST', '2025-10-10 15:11:31'),
(63, 3, 3, 'TEst', '2025-10-10 15:11:43'),
(64, 2, 3, 'Test', '2025-10-10 15:12:13'),
(65, 4, 3, 'test', '2025-10-10 15:12:20'),
(66, 2, 3, 'test', '2025-10-10 15:12:27'),
(67, 3, 3, 'Tis', '2025-10-10 15:16:04'),
(68, 4, 3, 'test', '2025-10-10 18:36:36'),
(69, 3, 3, 'Quick man', '2025-10-10 18:36:49');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `priority` enum('LOW','MID','HIGH','PRIO','PEND','DONE') NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `name`, `link`, `priority`, `assigned_to`, `assigned_by`, `assigned_at`, `updated_at`, `completed_at`, `notes`) VALUES
(1, 'John Smith', 'https://google.com', 'PRIO', 4, 3, '2025-10-08 17:43:30', '2025-10-08 17:43:30', NULL, NULL),
(2, 'Ching Chong', 'https://bing.com', 'HIGH', 3, 3, '2025-10-08 17:44:58', '2025-10-10 15:12:27', NULL, NULL),
(3, 'Wee Tu Low', 'https://facebook.com', 'MID', 3, 3, '2025-10-08 17:46:27', '2025-10-10 18:36:49', NULL, NULL),
(4, 'Sum Ting Wong', 'https://youtube.com', 'LOW', 3, 3, '2025-10-08 17:47:25', '2025-10-10 18:36:36', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`) VALUES
(1, 'NONLOP');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(32) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teamlead','member') NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `frozen` tinyint(1) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `team_id`, `frozen`) VALUES
(1, 'Admin', '$2y$10$g3vpwzpQZNWxdr///FPI.OLKaTt2GmyXfwQbc279.J1evOtW7jIIy', 'admin', NULL, 0),
(3, 'Jac', '$2y$10$cFEUF96QHA05FnSPcbwPbOG9QHAzu.apgNKT3XzMtzFXC1tMRhHs6', 'teamlead', 1, 0),
(4, 'User', '$2y$10$f23D239ejB2F/clG4aTGsOCRXQ9ERGgqUofPLCSNEmZil4TipzMda', 'member', 1, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `team_id` (`team_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
=======
-- Updates for TaskTrack to support restore audit and done-page features
-- Safe to run repeatedly (IF NOT EXISTS guards)

-- 1) Audit trail for task restore events
CREATE TABLE IF NOT EXISTS task_events (
  id INT PRIMARY KEY AUTO_INCREMENT,
  task_id INT NOT NULL,
  user_id INT NOT NULL,
  event_type ENUM('RESTORE') NOT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_task_events_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
  CONSTRAINT fk_task_events_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_task_events_task_created (task_id, created_at),
  INDEX idx_task_events_type_created (event_type, created_at)
);

-- No schema changes required to tasks/notes/users; existing columns are used.
-- Application now logs a row into task_events on restore.

-- APPEND: Updates for TaskTrack to support restore audit and done-page features
-- Safe to run repeatedly (IF NOT EXISTS guards)

CREATE TABLE IF NOT EXISTS task_events (
  id INT PRIMARY KEY AUTO_INCREMENT,
  task_id INT NOT NULL,
  user_id INT NOT NULL,
  event_type ENUM('RESTORE') NOT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_task_events_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
  CONSTRAINT fk_task_events_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_task_events_task_created (task_id, created_at),
  INDEX idx_task_events_type_created (event_type, created_at)
);

-- END APPEND
