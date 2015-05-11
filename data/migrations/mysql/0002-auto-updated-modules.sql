DROP TABLE IF EXISTS `auto_updated_modules` CASCADE;
CREATE TABLE `auto_updated_modules` (
  `id`             INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `environment_id` INTEGER NOT NULL,
  `module_name`    VARCHAR(256),
  `branch`         VARCHAR(256),
  FOREIGN KEY (`environment_id`) REFERENCES `environments`(`id`) ON DELETE CASCADE
);
