-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 28, 2026 at 08:17 PM
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
-- Database: `todoteam`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_encrypted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `project_id`, `user_id`, `receiver_id`, `message`, `is_encrypted`, `created_at`) VALUES
(1, 4, 4, NULL, 'سلام', 0, '2026-06-28 10:48:54'),
(2, 4, 4, NULL, 'بله', 0, '2026-06-28 11:16:18'),
(3, 4, 9, NULL, 'ممنون', 0, '2026-06-28 11:26:58'),
(4, 4, 9, NULL, 'CECBxqi0SLe7oIctfdQuwg==', 1, '2026-06-28 11:49:42'),
(5, 4, 9, 4, '9ktorQy7t9GsvVNJBP8lkQ==', 1, '2026-06-28 11:53:22');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'personal',
  `description` text DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `type`, `description`, `owner_id`, `created_at`) VALUES
(1, 'kkkk', 'personal', 'jhgj', 3, '2026-06-22 09:08:25'),
(2, 'dsc', 'personal', 'dsds', 4, '2026-06-22 09:27:15'),
(3, 'sds', 'personal', 'dsfs', 4, '2026-06-22 09:31:54'),
(4, 'dfdfsdf', 'personal', 'dsfds', 4, '2026-06-22 09:36:28'),
(6, 'لباس فروشی', 'personal', 'سلام', 5, '2026-06-24 16:30:22'),
(7, 'لباس فروشی', 'personal', 'سلام', 6, '2026-06-24 16:31:43'),
(8, 'bookshop', 'personal', '', 4, '2026-06-28 12:53:45'),
(9, 'To Do list', 'personal', 'ساخت سامانه مدیریت تسک گروهی', 10, '2026-06-28 13:15:15');

-- --------------------------------------------------------

--
-- Table structure for table `project_members`
--

CREATE TABLE `project_members` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role` enum('manager','member') DEFAULT 'member',
  `position` varchar(100) DEFAULT 'مدیر پروژه'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `project_members`
--

INSERT INTO `project_members` (`id`, `project_id`, `user_id`, `role`, `position`) VALUES
(4, 4, 4, 'manager', 'ihi'),
(8, 4, 7, 'member', 'عضو تیم'),
(9, 4, 8, 'member', 'FrontEnd'),
(10, 4, 9, 'member', 'Frontend'),
(11, 8, 4, '', 'مدیر پروژه'),
(12, 9, 10, '', 'مدیر پروژه'),
(13, 9, 11, 'member', 'فرانت اند'),
(14, 9, 12, 'member', 'بک اند');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `status` enum('todo','doing','done') DEFAULT 'todo',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `title`, `description`, `assigned_to`, `created_by`, `status`, `priority`, `due_date`, `created_at`) VALUES
(8, 4, 'header', NULL, 9, 4, 'done', 'medium', '2026-06-30', '2026-06-27 15:02:38'),
(9, 4, 'footer', 'بسیار توجه شود', 7, 4, 'done', 'medium', '2026-06-30', '2026-06-27 16:05:28'),
(10, 9, 'header 2', 'دقت مهم است', 11, 10, 'todo', 'medium', '2026-04-23', '2026-06-28 13:31:48'),
(11, 9, 'navigation bar', 'طراحی دقیق بک اند', 12, 10, 'done', 'high', '2026-04-23', '2026-06-28 15:26:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('manager','member') DEFAULT 'member',
  `avatar` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expire` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `password`, `role`, `avatar`, `reset_token`, `reset_expire`, `created_at`) VALUES
(3, 'ننن', '', 'nnn@gmail.com', '$2y$10$fMHNmo3rps5t7BPWKqPpK.W94yjCa.zZcAFmKXZAQhk3/hTqbnGT2', 'manager', NULL, NULL, NULL, '2026-06-22 09:08:25'),
(4, 'fdnkn', 'fdsd', 'jd@gmail.com', '$2y$10$RVR6SnfkhDBwZSHVHnSHnuL.up7yGLrB92bmZd0FdLviSJUMwXxvK', 'manager', NULL, NULL, NULL, '2026-06-22 09:27:15'),
(5, 'نگین', 'Nera', 'negingharani@gmail.com', '$2y$10$JJM90ylDxH2o4gbQO/6bs.ZpGioWV7KJGOu25BaoFaY2zGUoLWJU2', 'manager', NULL, NULL, NULL, '2026-06-24 16:30:22'),
(6, 'نگین', 'Neraa', 'negingharan@gmail.com', '$2y$10$M8c3auPkk9HhEHG7q4pPr.f4xiRZ83YK5Vd8MWEykL6KyrBEVKqiO', 'manager', NULL, NULL, NULL, '2026-06-24 16:31:43'),
(7, 'احمد', 'Ahmad', 'Ahmad@gmail.com', '$2y$10$DPctVYoqeKg.5sGS5P0r1e.6rnjWXTIbYA2mQ6Y1MrFkxcm7ROaTu', 'member', NULL, NULL, NULL, '2026-06-27 10:00:54'),
(8, 'sss', 'dd', 'emai@gmail.com', '$2y$10$D.l9fePK2MEF2poI2DNdO.rBFmO9i/ahTVaS/FUpZQSO6i6moEFGa', 'member', NULL, NULL, NULL, '2026-06-27 14:08:43'),
(9, 'سارا احمدی', 'Sara', 'sara@gmail.com', '$2y$10$jZJBvKn2H7xOh8eJRY7jDuc1e6ox5Hp08MyPqZ7ecClqkJA2oXhU2', 'member', NULL, NULL, NULL, '2026-06-27 14:57:08'),
(10, 'نگین قرنی', 'Negin_02', 'negingharani8@gmail.com', '$2y$10$U0jC4j6QkUKGq4zUosJbKeVCbJ7NvmUeIUCAigKwLs0vLCvhImiaO', 'manager', NULL, NULL, NULL, '2026-06-28 13:14:06'),
(11, 'رویا جوادی', 'Roya_02', 'roya@gmail.com', '$2y$10$zevxM0BgKrLC4yB7lJuaqeHRN4t.Wj7WPuGUCTFiR.GhrS1SITI1K', 'member', NULL, NULL, NULL, '2026-06-28 13:31:04'),
(12, 'مهگل ناجی', 'Mahgol_02', 'mahgol@gmail.com', '$2y$10$ZjiRfh75Xbz1XgqIEgj.H.vOxI9XOBS12jHRpJJ1WzKbQNrr6NWpa', 'member', NULL, NULL, NULL, '2026-06-28 15:25:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `project_members`
--
ALTER TABLE `project_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tasks_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
