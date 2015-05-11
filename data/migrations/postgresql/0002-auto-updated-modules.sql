DROP TABLE IF EXISTS auto_updated_modules CASCADE;
CREATE TABLE auto_updated_modules (
  id             SERIAL PRIMARY KEY,
  environment_id INT NOT NULL,
  module_name    VARCHAR(256),
  branch         VARCHAR(256),
  FOREIGN KEY (environment_id) REFERENCES environments(id) ON DELETE CASCADE
);
