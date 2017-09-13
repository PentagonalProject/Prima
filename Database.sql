--
-- DATABASE
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------
--
-- Table structure for table `options`
--

CREATE TABLE options (
  option_id BIGINT(10) NOT NULL,
  option_name varchar(255) NOT NULL,
  option_value LONGTEXT,
  option_autoload VARCHAR(10) NOT NULL DEFAULT 'no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`option_id`),
  ADD UNIQUE KEY `option_name` (`option_name`);

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `option_id` BIGINT(11) NOT NULL AUTO_INCREMENT;

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` BIGINT(11) NOT NULL,
  `first_name` VARCHAR(64) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` VARCHAR(64) COLLATE utf8_unicode_ci NOT NULL,
  `username` VARCHAR(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` VARCHAR(60) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'sha1 string PasswordHash - (phpass by openwall)',
  `private_key` VARCHAR(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT  'Private Grant token API',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT '1990-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT 'use `1990-01-01 00:00:00` to prevent error sql time stamp zero value'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username_email` (`username`,`email`),
  ADD KEY `username` (`username`),
  ADD KEY `email` (`email`);

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` BIGINT(11) NOT NULL AUTO_INCREMENT;

--
-- Table structure for table `users_meta`
--

CREATE TABLE `users_meta` (
  `meta_id` BIGINT(11) NOT NULL,
  `meta_user_id` BIGINT(11) NOT NULL,
  `meta_name` VARCHAR(255) NOT NULL,
  `meta_value` LONGTEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for table `users_meta`
--
ALTER TABLE `users_meta`
  ADD PRIMARY KEY (`meta_id`),
  ADD UNIQUE KEY `meta_name_selector` (`meta_name`);

--
-- AUTO_INCREMENT for table `users_meta`
--
ALTER TABLE `users_meta`
  MODIFY `meta_id` BIGINT(11) NOT NULL AUTO_INCREMENT;
