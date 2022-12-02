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

CREATE TABLE `contacts`
(
    `username`       varchar(100) NOT NULL,
    `friend_address` varchar(60)  NOT NULL,
    `friend_pubkey`  varchar(500) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

--
-- Dumping data for table `contacts`
--

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications`
(
    `id`         int          NOT NULL primary key auto_increment,
    `content_id` varchar(60)  NOT NULL,
    `username`   varchar(100) NOT NULL,
    `sender`     varchar(60)  NOT NULL,
    `secret`     varchar(200) NOT NULL,
    `subject`    text         NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts`
(
    `id`       varchar(60)  NOT NULL,
    `username` varchar(100) NOT NULL,
    `secret`   varchar(200) NOT NULL,
    `formatId` varchar(100) NOT NULL,
    `content`  varchar(200) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users`
(
    `username`   varchar(100)  NOT NULL,
    `address`    varchar(60)   NOT NULL,
    `publickey`  varchar(500)  NOT NULL,
    `privatekey` varchar(2000) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

--
-- Table structure for table `interactions`
--

CREATE TABLE `interactions`
(
    `id`         int          NOT NULL primary key auto_increment,
    `content_id` varchar(100) NOT NULL,
    `username`   varchar(60)  NOT NULL,
    `sender`     varchar(100) NOT NULL,
    `payload`    text         NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;


--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `posts`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
    ADD PRIMARY KEY (`username`) USING BTREE;

COMMIT;
