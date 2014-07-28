DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  login VARCHAR(256),
  name VARCHAR(256),
  email VARCHAR(256),
  role VARCHAR(256)
);

DROP TABLE IF EXISTS environments;
CREATE TABLE environments (
  id SERIAL PRIMARY KEY,
  name VARCHAR(256)
);

DROP TABLE IF EXISTS environments_paths;
CREATE TABLE environments_paths (
  ancestor_id   INT NOT NULL DEFAULT 0,
  descendant_id INT NOT NULL DEFAULT 0,
  length SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (ancestor_id, descendant_id)
);
CREATE INDEX environments_paths_adl ON environments_paths (ancestor_id, descendant_id, length);
CREATE INDEX environments_paths_dl ON environments_paths (descendant_id, length);

DROP TABLE IF EXISTS environments_users;
CREATE TABLE environments_users (
  environment_id   INT NOT NULL DEFAULT 0,
  user_id INT NOT NULL DEFAULT 0,
  PRIMARY KEY (environment_id, user_id)
);
