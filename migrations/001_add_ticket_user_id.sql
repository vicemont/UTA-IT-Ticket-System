ALTER TABLE `tickets`
  ADD COLUMN `user_id` int(10) unsigned DEFAULT NULL AFTER `id`,
  ADD KEY `tickets_user_id_idx` (`user_id`),
  ADD CONSTRAINT `tickets_user_id_fk`
    FOREIGN KEY (`user_id`) REFERENCES `users_staff` (`id`) ON DELETE SET NULL;
