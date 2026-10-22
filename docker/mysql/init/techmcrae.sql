-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 30, 2026 at 05:13 PM
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
-- Database: `techmcrae`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `employee_id` varchar(10) NOT NULL,
  `attendance_date` date NOT NULL,
  `attendance_hour` time NOT NULL,
  `type` enum('WFO','WFH') NOT NULL,
  `status` enum('Check In','Check Out') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`employee_id`, `attendance_date`, `attendance_hour`, `type`, `status`, `created_at`) VALUES
('TMC0001', '2025-12-15', '08:00:00', 'WFO', 'Check In', '2026-05-24 12:37:04'),
('TMC0001', '2025-12-15', '17:00:00', 'WFO', 'Check Out', '2026-05-24 12:37:04'),
('TMC0001', '2026-01-15', '08:05:00', 'WFO', 'Check In', '2026-05-24 12:37:04'),
('TMC0001', '2026-01-15', '17:05:00', 'WFO', 'Check Out', '2026-05-24 12:37:04'),
('TMC0001', '2026-02-18', '08:00:00', 'WFH', 'Check In', '2026-05-24 12:37:04'),
('TMC0001', '2026-02-18', '17:00:00', 'WFH', 'Check Out', '2026-05-24 12:37:04'),
('TMC0001', '2026-05-22', '08:00:00', 'WFH', 'Check In', '2026-05-24 12:31:27'),
('TMC0001', '2026-05-22', '17:00:00', 'WFH', 'Check Out', '2026-05-24 12:31:27'),
('TMC0001', '2026-05-23', '08:10:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0001', '2026-05-23', '17:30:00', 'WFO', 'Check Out', '2026-05-24 12:31:27'),
('TMC0001', '2026-05-24', '08:05:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0002', '2025-12-15', '08:15:00', 'WFO', 'Check In', '2026-05-24 12:37:04'),
('TMC0002', '2025-12-15', '17:15:00', 'WFO', 'Check Out', '2026-05-24 12:37:04'),
('TMC0002', '2026-01-15', '08:20:00', 'WFH', 'Check In', '2026-05-24 12:37:04'),
('TMC0002', '2026-01-15', '17:20:00', 'WFH', 'Check Out', '2026-05-24 12:37:04'),
('TMC0002', '2026-04-20', '08:00:00', 'WFO', 'Check In', '2026-05-24 12:37:04'),
('TMC0002', '2026-04-20', '17:00:00', 'WFO', 'Check Out', '2026-05-24 12:37:04'),
('TMC0003', '2025-12-15', '08:30:00', 'WFO', 'Check In', '2026-05-24 12:37:04'),
('TMC0003', '2025-12-15', '17:30:00', 'WFO', 'Check Out', '2026-05-24 12:37:04'),
('TMC0003', '2026-01-15', '08:10:00', 'WFH', 'Check In', '2026-05-24 12:37:04'),
('TMC0003', '2026-01-15', '17:10:00', 'WFH', 'Check Out', '2026-05-24 12:37:04'),
('TMC0004', '2025-12-15', '07:55:00', 'WFH', 'Check In', '2026-05-24 12:37:04'),
('TMC0004', '2025-12-15', '16:55:00', 'WFH', 'Check Out', '2026-05-24 12:37:04'),
('TMC0004', '2026-01-15', '07:50:00', 'WFO', 'Check In', '2026-05-24 12:37:04'),
('TMC0004', '2026-01-15', '16:50:00', 'WFO', 'Check Out', '2026-05-24 12:37:04'),
('TMC0004', '2026-02-18', '08:12:00', 'WFH', 'Check In', '2026-05-24 12:37:04'),
('TMC0004', '2026-02-18', '17:12:00', 'WFH', 'Check Out', '2026-05-24 12:37:04'),
('TMC0004', '2026-05-22', '08:15:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0004', '2026-05-22', '17:25:00', 'WFO', 'Check Out', '2026-05-24 12:31:27'),
('TMC0004', '2026-05-23', '08:00:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0004', '2026-05-23', '17:15:00', 'WFO', 'Check Out', '2026-05-24 12:31:27'),
('TMC0004', '2026-05-24', '08:12:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0005', '2025-12-15', '08:05:00', 'WFO', 'Check In', '2026-05-24 12:37:04'),
('TMC0005', '2025-12-15', '17:05:00', 'WFO', 'Check Out', '2026-05-24 12:37:04'),
('TMC0005', '2026-02-18', '07:45:00', 'WFH', 'Check In', '2026-05-24 12:37:04'),
('TMC0005', '2026-02-18', '16:45:00', 'WFH', 'Check Out', '2026-05-24 12:37:04'),
('TMC0005', '2026-04-20', '08:10:00', 'WFH', 'Check In', '2026-05-24 12:37:04'),
('TMC0005', '2026-04-20', '17:10:00', 'WFH', 'Check Out', '2026-05-24 12:37:04'),
('TMC0005', '2026-05-22', '08:10:00', 'WFH', 'Check In', '2026-05-24 12:31:27'),
('TMC0005', '2026-05-22', '17:00:00', 'WFH', 'Check Out', '2026-05-24 12:31:27'),
('TMC0005', '2026-05-23', '08:05:00', 'WFH', 'Check In', '2026-05-24 12:31:27'),
('TMC0005', '2026-05-23', '17:00:00', 'WFH', 'Check Out', '2026-05-24 12:31:27'),
('TMC0005', '2026-05-24', '08:00:00', 'WFH', 'Check In', '2026-05-24 12:31:27'),
('TMC0006', '2026-01-15', '08:00:00', 'WFO', 'Check In', '2026-05-24 12:37:04'),
('TMC0006', '2026-01-15', '17:00:00', 'WFO', 'Check Out', '2026-05-24 12:37:04'),
('TMC0006', '2026-05-23', '07:50:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0006', '2026-05-23', '17:20:00', 'WFO', 'Check Out', '2026-05-24 12:31:27'),
('TMC0006', '2026-05-24', '07:55:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0007', '2026-02-18', '08:30:00', 'WFH', 'Check In', '2026-05-24 12:37:04'),
('TMC0007', '2026-02-18', '17:30:00', 'WFH', 'Check Out', '2026-05-24 12:37:04'),
('TMC0007', '2026-05-23', '08:15:00', 'WFH', 'Check In', '2026-05-24 12:31:27'),
('TMC0007', '2026-05-23', '17:05:00', 'WFH', 'Check Out', '2026-05-24 12:31:27'),
('TMC0007', '2026-05-24', '08:20:00', 'WFH', 'Check In', '2026-05-24 12:31:27'),
('TMC0008', '2026-02-18', '08:00:00', 'WFO', 'Check In', '2026-05-24 12:37:04'),
('TMC0008', '2026-02-18', '17:00:00', 'WFO', 'Check Out', '2026-05-24 12:37:04'),
('TMC0008', '2026-04-20', '08:00:00', 'WFO', 'Check In', '2026-05-24 12:37:04'),
('TMC0008', '2026-04-20', '17:00:00', 'WFO', 'Check Out', '2026-05-24 12:37:04'),
('TMC0008', '2026-05-22', '08:20:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0008', '2026-05-22', '17:10:00', 'WFO', 'Check Out', '2026-05-24 12:31:27'),
('TMC0008', '2026-05-24', '08:30:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0009', '2026-05-23', '09:00:00', 'WFH', 'Check In', '2026-05-24 12:31:27'),
('TMC0009', '2026-05-23', '18:00:00', 'WFH', 'Check Out', '2026-05-24 12:31:27'),
('TMC0009', '2026-05-24', '09:15:00', 'WFH', 'Check In', '2026-05-24 12:31:27'),
('TMC0010', '2026-03-12', '08:05:00', 'WFO', 'Check In', '2026-05-24 12:37:04'),
('TMC0010', '2026-03-12', '17:05:00', 'WFO', 'Check Out', '2026-05-24 12:37:04'),
('TMC0010', '2026-05-22', '08:05:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0010', '2026-05-22', '17:15:00', 'WFO', 'Check Out', '2026-05-24 12:31:27'),
('TMC0010', '2026-05-24', '08:00:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0011', '2026-03-12', '08:00:00', 'WFO', 'Check In', '2026-05-24 12:37:04'),
('TMC0011', '2026-03-12', '17:00:00', 'WFO', 'Check Out', '2026-05-24 12:37:04'),
('TMC0011', '2026-05-23', '08:00:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0011', '2026-05-23', '17:10:00', 'WFO', 'Check Out', '2026-05-24 12:31:27'),
('TMC0011', '2026-05-24', '08:05:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0012', '2026-03-12', '08:15:00', 'WFH', 'Check In', '2026-05-24 12:37:04'),
('TMC0012', '2026-03-12', '17:15:00', 'WFH', 'Check Out', '2026-05-24 12:37:04'),
('TMC0012', '2026-04-20', '08:05:00', 'WFH', 'Check In', '2026-05-24 12:37:04'),
('TMC0012', '2026-04-20', '17:05:00', 'WFH', 'Check Out', '2026-05-24 12:37:04'),
('TMC0012', '2026-05-24', '08:10:00', 'WFH', 'Check In', '2026-05-24 12:31:27'),
('TMC0013', '2026-03-12', '07:30:00', 'WFO', 'Check In', '2026-05-24 12:37:04'),
('TMC0013', '2026-03-12', '16:30:00', 'WFO', 'Check Out', '2026-05-24 12:37:04'),
('TMC0013', '2026-05-23', '07:35:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0013', '2026-05-23', '16:45:00', 'WFO', 'Check Out', '2026-05-24 12:31:27'),
('TMC0013', '2026-05-24', '07:30:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0014', '2026-03-12', '08:20:00', 'WFO', 'Check In', '2026-05-24 12:37:04'),
('TMC0014', '2026-03-12', '17:20:00', 'WFO', 'Check Out', '2026-05-24 12:37:04'),
('TMC0014', '2026-05-22', '08:30:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0014', '2026-05-22', '17:40:00', 'WFO', 'Check Out', '2026-05-24 12:31:27'),
('TMC0014', '2026-05-24', '08:25:00', 'WFO', 'Check In', '2026-05-24 12:31:27'),
('TMC0015', '2026-04-20', '08:30:00', 'WFO', 'Check In', '2026-05-24 12:37:04'),
('TMC0015', '2026-04-20', '17:30:00', 'WFO', 'Check Out', '2026-05-24 12:37:04'),
('TMC0015', '2026-05-24', '08:00:00', 'WFH', 'Check In', '2026-05-24 12:31:27'),
('TMC0099', '2026-05-30', '20:40:02', 'WFH', 'Check In', '2026-05-30 13:40:02'),
('TMC0099', '2026-05-30', '20:46:11', 'WFH', 'Check Out', '2026-05-30 13:46:11');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('M','F') NOT NULL DEFAULT 'M',
  `password` varchar(255) NOT NULL DEFAULT 'password',
  `role` enum('employee','admin') DEFAULT 'employee',
  `department` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `remember_token` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `name`, `email`, `phone_number`, `address`, `birth_date`, `gender`, `password`, `role`, `department`, `created_at`, `remember_token`) VALUES
('TMC0001', 'Bruce Wayne', 'bruce@techmcrae.com', '081122334455', 'Wayne Manor, Gotham City', '1972-02-19', 'M', 'password', 'employee', 'Engineering', '2026-05-24 12:31:27', NULL),
('TMC0002', 'Jason Todd', 'jason@techmcrae.com', '081234567890', 'Crime Alley, Gotham City', '1988-08-16', 'M', 'password', 'employee', 'Engineering', '2026-05-24 12:31:27', NULL),
('TMC0003', 'Tim Drake', 'tim@techmcrae.com', '081345678901', 'Drake Estate, Gotham City', '1993-07-19', 'M', 'password', 'employee', 'Engineering', '2026-05-24 12:31:27', NULL),
('TMC0004', 'Dick Grayson', 'dick@techmcrae.com', '081456789012', 'Blüdhaven Apartments', '1980-11-11', 'M', 'password', 'employee', 'Marketing', '2026-05-24 12:31:27', 'RuimRrCNpmioCa1n4wh0B5tFYV9YCdmOU5JvEbn9QfCMx6zHHIC1l6mp667B'),
('TMC0005', 'Barbara Gordon', 'barbara@techmcrae.com', '081567890123', 'Clock Tower, Gotham City', '1987-09-23', 'F', 'password', 'employee', 'Engineering', '2026-05-24 12:31:27', 'BwOx45xQLM5dBBLJ9olnAIGfRLsTSGg6bM20Jfg0RoXQYEYgLwnOaAOAe4Kb'),
('TMC0006', 'Damian Wayne', 'damian@techmcrae.com', '081678901234', 'Wayne Manor, Gotham City', '2006-03-20', 'M', 'password', 'employee', 'Finance', '2026-05-24 12:31:27', NULL),
('TMC0007', 'Stephanie Brown', 'stephanie@techmcrae.com', '081789012345', 'Gotham Apartments', '1992-05-14', 'F', 'password', 'employee', 'HR', '2026-05-24 12:31:27', NULL),
('TMC0008', 'Cassandra Cain', 'cassandra@techmcrae.com', '081890123456', 'Batcave Base, Gotham City', '1986-01-26', 'F', 'password', 'employee', 'Design', '2026-05-24 12:31:27', NULL),
('TMC0009', 'Duke Thomas', 'duke@techmcrae.com', '081901234567', 'The Narrows, Gotham City', '1998-10-05', 'M', 'password', 'employee', 'Marketing', '2026-05-24 12:31:27', NULL),
('TMC0010', 'Selina Kyle', 'selina@techmcrae.com', '081211112222', 'East End Penthouse, Gotham', '1975-03-14', 'F', 'password', 'employee', 'Design', '2026-05-24 12:31:27', NULL),
('TMC0011', 'Diana', 'diana@techmcrae.com', '081222223333', 'Themyscira Embassy, Metropolis', '1980-01-01', 'F', 'password', 'employee', 'HR', '2026-05-24 12:31:27', NULL),
('TMC0012', 'Clark Kent', 'clark@techmcrae.com', '081233334444', '344 Clinton St, Metropolis', '1978-06-18', 'M', 'password', 'employee', 'Marketing', '2026-05-24 12:31:27', NULL),
('TMC0013', 'Barry Allen', 'barry@techmcrae.com', '081244445555', 'Central City Labs', '1989-03-14', 'M', 'password', 'employee', 'Engineering', '2026-05-24 12:31:27', NULL),
('TMC0014', 'Hal Jordan', 'hal@techmcrae.com', '081255556666', 'Coast City Hangar 4', '1982-02-20', 'M', 'password', 'employee', 'Engineering', '2026-05-24 12:31:27', NULL),
('TMC0015', 'Arthur Curry', 'arthur@techmcrae.com', '081266667777', 'Amnesty Island Lighthouse', '1986-01-29', 'M', 'password', 'employee', 'Finance', '2026-05-24 12:31:27', NULL),
('TMC0099', 'Alfred Pennyworth', 'alfred@techmcrae.com', '081199999999', 'Wayne Manor Masters Wing', '1943-04-16', 'M', 'password', 'admin', 'HR', '2026-05-24 12:31:27', 'Ttd1Iz20HNEgFIn5dhpdTpj1MSAK0ExFWNkm1GtwdrMaUJssNis3RqJSYAjA'),
('TMC0100', 'Lucius Fox', 'lucius@techmcrae.com', '081100000000', 'Fox Plaza Towers, Gotham City', '1955-11-20', 'M', 'password', 'admin', 'HR', '2026-05-24 12:31:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_leave_balances`
--

CREATE TABLE `employee_leave_balances` (
  `employee_id` varchar(50) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `remaining` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_leave_balances`
--

INSERT INTO `employee_leave_balances` (`employee_id`, `leave_type_id`, `remaining`, `updated_at`) VALUES
('TMC0001', 4, 3, '2026-05-27 17:44:20'),
('TMC0002', 4, 3, '2026-05-27 17:44:20'),
('TMC0003', 4, 3, '2026-05-27 17:44:20'),
('TMC0004', 4, 3, '2026-05-27 17:44:20'),
('TMC0005', 3, 90, '2026-05-27 17:44:20'),
('TMC0006', 4, 3, '2026-05-27 17:44:20'),
('TMC0007', 3, 90, '2026-05-27 17:44:20'),
('TMC0008', 3, 90, '2026-05-27 17:44:20'),
('TMC0009', 4, 3, '2026-05-27 17:44:20'),
('TMC0010', 3, 90, '2026-05-27 17:44:20'),
('TMC0011', 3, 90, '2026-05-27 17:44:20'),
('TMC0012', 4, 3, '2026-05-27 17:44:20'),
('TMC0013', 4, 3, '2026-05-27 17:44:20'),
('TMC0014', 4, 3, '2026-05-27 17:44:20'),
('TMC0015', 4, 3, '2026-05-27 17:44:20'),
('TMC0099', 4, 3, '2026-05-27 17:44:20'),
('TMC0100', 4, 3, '2026-05-27 17:44:20');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(10) NOT NULL,
  `leave_from` date NOT NULL,
  `leave_to` date NOT NULL,
  `leave_days` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `admin_id` varchar(10) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `employee_id`, `leave_from`, `leave_to`, `leave_days`, `leave_type_id`, `reason`, `status`, `admin_id`, `admin_notes`, `requested_at`, `updated_at`) VALUES
(1, 'TMC0001', '2026-03-13', '2026-03-15', 3, 1, 'Family emergency, need to travel out of town urgently.', 'Pending', NULL, NULL, '2026-05-24 12:31:27', '2026-05-30 10:52:28'),
(2, 'TMC0002', '2026-01-10', '2026-01-12', 2, 2, 'Severe migraine and fever.', 'Approved', 'TMC0100', 'Approved. Rest up and get well soon.', '2026-05-24 12:37:04', '2026-05-30 10:54:32'),
(3, 'TMC0003', '2026-04-25', '2026-04-27', 2, 5, 'Need to attend to an urgent family matter out of state.', 'Approved', 'TMC0099', 'Approved. Take care of your family.', '2026-05-24 12:37:04', '2026-05-30 10:54:32'),
(4, 'TMC0003', '2026-05-15', '2026-05-26', 10, 1, 'Personal medical checkup and recovery period.', 'Approved', 'TMC0099', 'Approved. Please coordinate handover with the engineering lead.', '2026-05-24 12:31:27', '2026-05-30 10:22:43'),
(5, 'TMC0004', '2025-11-12', '2025-11-14', 2, 5, 'Car broke down on the highway, need to handle towing and urgent repairs.', 'Approved', 'TMC0099', 'Approved. Hope the car gets fixed quickly.', '2026-05-24 12:37:04', '2026-05-30 10:54:32'),
(6, 'TMC0004', '2026-04-10', '2026-04-12', 3, 1, 'Moving into a new apartment house.', 'Approved', 'TMC0099', 'Approved. Enjoy your new place!', '2026-05-24 12:31:27', '2026-05-30 10:22:43'),
(7, 'TMC0005', '2025-10-01', '2026-01-01', 90, 3, 'Taking maternity leave for the birth of my child.', 'Approved', 'TMC0099', 'Approved. Wishing you a safe delivery and wonderful time with the baby!', '2026-05-24 12:37:04', '2026-05-30 10:54:32'),
(8, 'TMC0005', '2026-02-20', '2026-02-22', 3, 2, 'Emergency wisdom tooth extraction surgery.', 'Approved', 'TMC0100', 'Get well soon! Medical certificate confirmed.', '2026-05-24 12:31:27', '2026-05-30 10:22:43'),
(9, 'TMC0006', '2026-03-05', '2026-03-06', 2, 5, 'Pet had a sudden medical emergency, need to take him to the vet clinic.', 'Approved', 'TMC0099', 'Approved. Hoping your pet recovers quickly.', '2026-05-24 12:31:27', '2026-05-30 10:54:32'),
(10, 'TMC0007', '2025-12-22', '2025-12-31', 8, 1, 'Year-end holiday trip with family to Bali.', 'Approved', 'TMC0099', 'Approved. Enjoy your holidays!', '2026-05-24 12:31:27', '2026-05-30 10:54:32'),
(11, 'TMC0007', '2026-06-01', '2026-06-05', 5, 1, 'Annual family vacation planner.', 'Pending', NULL, NULL, '2026-05-24 12:31:27', '2026-05-30 10:22:43'),
(12, 'TMC0008', '2026-03-02', '2026-03-03', 1, 2, 'Food poisoning, unable to commute to the office.', 'Approved', 'TMC0099', 'Approved. Drink plenty of fluids and rest.', '2026-05-24 12:37:04', '2026-05-30 10:54:32'),
(13, 'TMC0008', '2026-03-25', '2026-03-26', 2, 5, 'Urgent domestic home repair due to water pipeline bursting.', 'Rejected', 'TMC0099', 'Rejected. This request conflicts directly with the critical core systems deployment schedule.', '2026-05-24 12:31:27', '2026-05-30 10:22:43'),
(14, 'TMC0009', '2026-01-20', '2026-01-24', 5, 1, 'Taking some time off to recharge and visit relatives.', 'Approved', 'TMC0100', 'Approved. Have a great trip.', '2026-05-24 12:31:27', '2026-05-30 10:54:32'),
(15, 'TMC0010', '2026-01-15', '2026-01-20', 5, 1, 'Going on a short getaway to Europe.', 'Approved', 'TMC0100', 'Approved. Make sure to delegate your pending tasks before you leave.', '2026-05-24 12:31:27', '2026-05-30 10:54:32'),
(16, 'TMC0011', '2026-04-01', '2026-04-03', 3, 1, 'Attending a cultural festival out of town.', 'Approved', 'TMC0099', 'Approved. Enjoy the festival!', '2026-05-24 12:31:27', '2026-05-30 10:54:32'),
(17, 'TMC0011', '2026-05-10', '2026-05-11', 1, 2, 'Woke up with a bad sore throat and mild fever.', 'Approved', 'TMC0100', 'Approved. Get well soon.', '2026-05-24 12:37:04', '2026-05-30 10:54:32'),
(18, 'TMC0012', '2026-04-01', '2026-04-05', 5, 4, 'Welcoming our newborn baby! Need time to support my wife.', 'Approved', 'TMC0100', 'Approved. Congratulations to you and your family!', '2026-05-24 12:37:04', '2026-05-30 10:54:32'),
(19, 'TMC0012', '2026-06-10', '2026-06-12', 3, 1, 'Attending a close relatives wedding ceremony.', 'Pending', NULL, NULL, '2026-05-24 12:31:27', '2026-05-30 10:52:15'),
(20, 'TMC0013', '2026-02-10', '2026-02-10', 1, 2, 'Stomach bug, need a day of rest.', 'Approved', 'TMC0100', 'Approved. Take it easy.', '2026-05-24 12:31:27', '2026-05-30 10:54:32'),
(21, 'TMC0014', '2026-06-15', '2026-06-15', 1, 2, 'Severe seasonal flu, running a very high fever.', 'Pending', NULL, NULL, '2026-05-24 12:31:27', '2026-05-30 10:22:43'),
(22, 'TMC0015', '2026-03-22', '2026-03-26', 5, 1, 'Annual scuba diving trip to Raja Ampat.', 'Approved', 'TMC0099', 'Approved. Stay safe and enjoy the dives.', '2026-05-24 12:31:27', '2026-05-30 10:54:32');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `leave_type_id` int(11) NOT NULL,
  `leave_name` varchar(50) NOT NULL,
  `allocation_limit` int(11) NOT NULL,
  `reset_frequency` enum('Monthly','Yearly') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`leave_type_id`, `leave_name`, `allocation_limit`, `reset_frequency`) VALUES
(1, 'Annual Leave', 12, 'Yearly'),
(2, 'Sick Leave', 7, 'Monthly'),
(3, 'Maternity Leave', 90, 'Yearly'),
(4, 'Paternity Leave', 3, 'Yearly'),
(5, 'Emergency Leave', 3, 'Monthly'),
(6, 'Unpaid Leave', 365, 'Yearly');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`employee_id`,`attendance_date`,`status`),
  ADD KEY `idx_date` (`attendance_date`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `employee_leave_balances`
--
ALTER TABLE `employee_leave_balances`
  ADD PRIMARY KEY (`employee_id`,`leave_type_id`),
  ADD KEY `leave_type_id` (`leave_type_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_leave_type_id` (`leave_type_id`),
  ADD KEY `idx_admin_id` (`admin_id`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`leave_type_id`),
  ADD UNIQUE KEY `leave_name` (`leave_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `leave_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_leave_balances`
--
ALTER TABLE `employee_leave_balances`
  ADD CONSTRAINT `employee_leave_balances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_leave_balances_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`leave_type_id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `fk_leave_requests_admin` FOREIGN KEY (`admin_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
