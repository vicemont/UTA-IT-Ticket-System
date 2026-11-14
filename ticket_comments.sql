-- Run this in the users_staff_db database. Your tickets table already exists.
CREATE TABLE IF NOT EXISTS `ticket_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `author_role` enum('user','staff') NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_comments_ticket_idx` (`ticket_id`),
  CONSTRAINT `ticket_comments_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_comments_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users_staff` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;