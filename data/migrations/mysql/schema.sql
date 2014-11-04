DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`    INTEGER      NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `login` VARCHAR(256) NOT NULL DEFAULT '',
  `name`  VARCHAR(256) NOT NULL DEFAULT '',
  `email` VARCHAR(256) NOT NULL DEFAULT '',
  `role`  VARCHAR(256) NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS `environments`;
CREATE TABLE `environments` (
  `id`        INTEGER      NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name`      VARCHAR(256) NOT NULL DEFAULT '',
  `isdefault` TINYINT      NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS `environments_paths`;
CREATE TABLE `environments_paths` (
  `ancestor_id`   INT     NOT NULL,
  `descendant_id` INT     NOT NULL,
  `length`        TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`ancestor_id`, `descendant_id`),
  FOREIGN KEY (`ancestor_id`) REFERENCES `environments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`descendant_id`) REFERENCES `environments`(`id`) ON DELETE CASCADE
);
CREATE INDEX `environments_paths_adl` ON `environments_paths` (`ancestor_id`, `descendant_id`, `length`);
CREATE INDEX `environments_paths_dl` ON `environments_paths` (`descendant_id`, `length`);

DROP TABLE IF EXISTS `environments_users`;
CREATE TABLE `environments_users` (
  `environment_id` INT NOT NULL,
  `user_id`        INT NOT NULL,
  PRIMARY KEY (`environment_id`, `user_id`),
  FOREIGN KEY (`environment_id`) REFERENCES `environments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

DROP TABLE IF EXISTS `revisions`;
CREATE TABLE `revisions` (
  `id`             INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `environment_id` INTEGER NOT NULL,
  `released_at`    DATETIME,
  `released_by`    VARCHAR(256),
  `comment`        TEXT,
  FOREIGN KEY (`environment_id`) REFERENCES `environments`(`id`) ON DELETE CASCADE
);
CREATE INDEX `revisions_env` ON `revisions` (`environment_id`);
CREATE INDEX `revisions_ra` ON `revisions` (`released_at`);

DROP TABLE IF EXISTS `revisions_logs`;
CREATE TABLE `revisions_logs` (
  `id`             INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `revision_id`    INTEGER NOT NULL,
  `created_at`     DATETIME,
  `created_by`     VARCHAR(256),
  `comment`        TEXT,
  FOREIGN KEY (`revision_id`) REFERENCES `revisions`(`id`) ON DELETE CASCADE
);
CREATE INDEX `revisions_logs_ca` ON `revisions_logs` (`created_at`);

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id`              INTEGER      NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `revision_id`     INTEGER      NOT NULL,
  `name`            VARCHAR(256) NOT NULL DEFAULT '',
  `ordering`        INTEGER      NOT NULL DEFAULT '0',
  `include_pattern` TEXT         NOT NULL DEFAULT '',
  `exclude_pattern` TEXT         NOT NULL DEFAULT '',
  FOREIGN KEY (`revision_id`) REFERENCES `revisions`(`id`) ON DELETE CASCADE
);
CREATE INDEX `groups_o` ON `groups` (`ordering`);

DROP TABLE IF EXISTS `group_classes`;
CREATE TABLE `group_classes` (
  `id`       INTEGER      NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name`     VARCHAR(256) NOT NULL DEFAULT '',
  `group_id` INTEGER      NOT NULL,
  FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE
);

DROP TABLE IF EXISTS `group_parameters`;
CREATE TABLE `group_parameters` (
  `id`              INTEGER      NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name`            VARCHAR(256) NOT NULL DEFAULT '',
  `parent_id`       INTEGER DEFAULT NULL,
  `group_class_id`  INTEGER NOT NULL,
  FOREIGN KEY (`parent_id`) REFERENCES `group_parameters`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`group_class_id`) REFERENCES `group_classes`(`id`) ON DELETE CASCADE
);

DROP TABLE IF EXISTS `group_values`;
CREATE TABLE `group_values` (
  `id`                 INTEGER      NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `value`              VARCHAR(256) NOT NULL DEFAULT '',
  `group_parameter_id` INTEGER NOT NULL,
  FOREIGN KEY (`group_parameter_id`) REFERENCES `group_parameters`(`id`) ON DELETE CASCADE
);
