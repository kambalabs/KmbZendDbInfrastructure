DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`   INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `login` VARCHAR(256) NOT NULL DEFAULT '',
  `name` VARCHAR(256) NOT NULL DEFAULT '',
  `email` VARCHAR(256) NOT NULL DEFAULT '',
  `role` VARCHAR(256) NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS `environments`;
CREATE TABLE `environments` (
  `id`   INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `name` VARCHAR(256) NOT NULL DEFAULT '',
  `isdefault` TINYINT NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS `environments_paths`;
CREATE TABLE `environments_paths` (
  `ancestor_id`   INT NOT NULL,
  `descendant_id` INT NOT NULL,
  `length` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`ancestor_id`, `descendant_id`)
);
CREATE INDEX `environments_paths_adl` ON `environments_paths` (`ancestor_id`, `descendant_id`, `length`);
CREATE INDEX `environments_paths_dl` ON `environments_paths` (`descendant_id`, `length`);

DROP TABLE IF EXISTS `environments_users`;
CREATE TABLE `environments_users` (
  `environment_id`   INT NOT NULL,
  `user_id` INT NOT NULL,
  PRIMARY KEY (`environment_id`, `user_id`)
);

DROP TABLE IF EXISTS `revisions`;
CREATE TABLE `revisions` (
  `id`             INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `environment_id` INTEGER NOT NULL,
  `updated_at`     DATETIME,
  `updated_by`     VARCHAR(256),
  `released_at`    DATETIME,
  `released_by`    VARCHAR(256),
  `comment`        TEXT
);

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id`              INTEGER      NOT NULL PRIMARY KEY AUTOINCREMENT,
  `revision_id`     INTEGER      NOT NULL,
  `name`            VARCHAR(256) NOT NULL DEFAULT '',
  `ordering`        INTEGER      NOT NULL DEFAULT '0',
  `include_pattern` TEXT         NOT NULL DEFAULT '',
  `exclude_pattern` TEXT         NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS `group_classes`;
CREATE TABLE `group_classes` (
  `id`       INTEGER      NOT NULL PRIMARY KEY AUTOINCREMENT,
  `name`     VARCHAR(256) NOT NULL DEFAULT '',
  `group_id` INTEGER      NOT NULL
);

DROP TABLE IF EXISTS `group_parameters`;
CREATE TABLE `group_parameters` (
  `id`              INTEGER      NOT NULL PRIMARY KEY AUTOINCREMENT,
  `name`            VARCHAR(256) NOT NULL DEFAULT '',
  `parent_id`       INTEGER DEFAULT NULL,
  `group_class_id`  INTEGER NOT NULL
);

DROP TABLE IF EXISTS `group_values`;
CREATE TABLE `group_values` (
  `id`                 INTEGER      NOT NULL PRIMARY KEY AUTOINCREMENT,
  `value`              VARCHAR(256) NOT NULL DEFAULT '',
  `group_parameter_id` INTEGER NOT NULL
);
