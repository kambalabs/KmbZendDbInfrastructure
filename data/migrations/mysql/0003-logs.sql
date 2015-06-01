DROP TABLE IF EXISTS `logs` CASCADE;
CREATE TABLE `logs` (
  `id`             INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `created_at`     DATETIME,
  `created_by`     VARCHAR(256),
  `comment`        TEXT
);
CREATE INDEX `logs_ca` ON `logs` (`created_at`);
