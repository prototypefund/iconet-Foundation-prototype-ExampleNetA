SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `comments` (
  `id` int NOT NULL,
  `post_body` text NOT NULL,
  `posted_by` varchar(60) NOT NULL,
  `posted_to` varchar(60) NOT NULL,
  `date_added` datetime NOT NULL,
  `removed` tinyint(1) NOT NULL,
  `post_id` int NOT NULL,
  `external_url` varchar(255)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `friend_requests` (
  `user_to` varchar(50) NOT NULL,
  `user_from` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `is_friend` (
  `user` varchar(100) NOT NULL,
  `friend` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `likes` (
  `username` varchar(60) NOT NULL,
  `post_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `user_to` varchar(50) NOT NULL,
  `user_from` varchar(50) NOT NULL,
  `body` text NOT NULL,
  `date` datetime NOT NULL,
  `opened` varchar(3) NOT NULL,
  `viewed` varchar(3) NOT NULL,
  `deleted` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_to` varchar(50) NOT NULL,
  `user_from` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(100) NOT NULL,
  `datetime` datetime NOT NULL,
  `opened` varchar(3) NOT NULL,
  `viewed` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `posts` (
  `id` int NOT NULL,
  `body` text NOT NULL,
  `added_by` varchar(100) NOT NULL,
  `user_to` varchar(100),
  `date_added` datetime NOT NULL,
  `user_closed` tinyint(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `likes` int NOT NULL,
  `image` varchar(60)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `users` (
  `first_name` varchar(25) NOT NULL,
  `last_name` varchar(25) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `signup_date` date NOT NULL,
  `profile_pic` varchar(255) NOT NULL,
  `num_posts` int NOT NULL,
  `num_likes` int NOT NULL,
  `user_closed` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `friend_requests`
  ADD PRIMARY KEY (`user_from`,`user_to`);

ALTER TABLE `is_friend`
  ADD PRIMARY KEY (`user`,`friend`),
  ADD KEY `is_friend_ibfk_2` (`friend`);

ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posts_ibfk_1` (`added_by`),
  ADD KEY `posts_friend_ibfk_2` (`user_to`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`username`);


ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `posts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;


ALTER TABLE `is_friend`
  ADD CONSTRAINT `is_friend_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `is_friend_ibfk_2` FOREIGN KEY (`friend`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE `posts`
  ADD CONSTRAINT `posts_friend_ibfk_2` FOREIGN KEY (`user_to`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
