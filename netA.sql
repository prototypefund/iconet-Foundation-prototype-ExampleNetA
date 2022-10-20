-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 18, 2022 at 12:05 PM
-- Server version: 8.0.30-0ubuntu0.22.04.1
-- PHP Version: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `netA`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments`
(
    `id`         int         NOT NULL,
    `post_body`  text        NOT NULL,
    `posted_by`  varchar(60) NOT NULL,
    `posted_to`  varchar(60) NOT NULL,
    `date_added` datetime    NOT NULL,
    `removed`    tinyint(1)  NOT NULL,
    `post_id`    int         NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `friend_requests`
--

CREATE TABLE `friend_requests`
(
    `id`        int         NOT NULL,
    `user_to`   varchar(50) NOT NULL,
    `user_from` varchar(50) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `is_friend`
--

CREATE TABLE `is_friend`
(
    `user`   varchar(100) NOT NULL,
    `friend` varchar(100) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes`
(
    `id`       int         NOT NULL,
    `username` varchar(60) NOT NULL,
    `post_id`  int         NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages`
(
    `id`        int         NOT NULL,
    `user_to`   varchar(50) NOT NULL,
    `user_from` varchar(50) NOT NULL,
    `body`      text        NOT NULL,
    `date`      datetime    NOT NULL,
    `opened`    varchar(3)  NOT NULL,
    `viewed`    varchar(3)  NOT NULL,
    `deleted`   varchar(3)  NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications`
(
    `id`        int          NOT NULL,
    `user_to`   varchar(50)  NOT NULL,
    `user_from` varchar(50)  NOT NULL,
    `message`   text         NOT NULL,
    `link`      varchar(100) NOT NULL,
    `datetime`  datetime     NOT NULL,
    `opened`    varchar(3)   NOT NULL,
    `viewed`    varchar(3)   NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts`
(
    `id`          int          NOT NULL,
    `body`        text         NOT NULL,
    `added_by`    varchar(100) NOT NULL,
    `user_to`     varchar(100),
    `date_added`  datetime     NOT NULL,
    `user_closed` tinyint(1)   NOT NULL,
    `deleted`     tinyint(1)   NOT NULL,
    `likes`       int          NOT NULL,
    `image`       varchar(60)
) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

-- --------------------------------------------------------
--
-- Table structure for table `users`
--

CREATE TABLE `users`
(
    `first_name`  varchar(25)  NOT NULL,
    `last_name`   varchar(25)  NOT NULL,
    `username`    varchar(100) NOT NULL,
    `email`       varchar(100) NOT NULL,
    `password`    varchar(255) NOT NULL,
    `signup_date` date         NOT NULL,
    `profile_pic` varchar(255) NOT NULL,
    `num_posts`   int          NOT NULL,
    `num_likes`   int          NOT NULL,
    `user_closed` tinyint(1)   NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `is_friend`
--
ALTER TABLE `is_friend`
    ADD PRIMARY KEY (`user`, `friend`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
    ADD PRIMARY KEY (`username`);

ALTER TABLE `posts`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `comments`
    ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--


ALTER TABLE `posts`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `comments`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `is_friend`
--
ALTER TABLE `is_friend`
    ADD CONSTRAINT `is_friend_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE RESTRICT,
    ADD CONSTRAINT `is_friend_ibfk_2` FOREIGN KEY (`friend`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE RESTRICT;
COMMIT;

ALTER TABLE `posts`
    ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE RESTRICT,
    ADD CONSTRAINT `posts_friend_ibfk_2` FOREIGN KEY (`user_to`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
