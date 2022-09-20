-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 19, 2022 at 02:43 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `iconet`
--

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
                            `username` varchar(100) NOT NULL,
                            `friend_address` varchar(60) NOT NULL,
                            `friend_pubkey` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `contacts`
--

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
                                 `id` int(11) NOT NULL,
                                 `username` varchar(100) NOT NULL,
                                 `sender` varchar(60) NOT NULL,
                                 `secret` varchar(128) NOT NULL,
                                 `link` varchar(60) NOT NULL,
                                 `text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
                         `id` int(11) NOT NULL,
                         `username` varchar(100) NOT NULL,
                         `secret` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
                         `username` varchar(100) NOT NULL,
                         `address` varchar(60) NOT NULL,
                         `publickey` varchar(128) NOT NULL,
                         `privatekey` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
    ADD PRIMARY KEY (`username`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
