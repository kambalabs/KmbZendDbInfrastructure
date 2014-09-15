DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id    SERIAL PRIMARY KEY,
  login VARCHAR(256) NOT NULL DEFAULT '',
  name  VARCHAR(256) NOT NULL DEFAULT '',
  email VARCHAR(256) NOT NULL DEFAULT '',
  role  VARCHAR(256) NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS environments;
CREATE TABLE environments (
  id        SERIAL PRIMARY KEY,
  name      VARCHAR(256) NOT NULL DEFAULT '',
  isdefault SMALLINT NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS environments_paths;
CREATE TABLE environments_paths (
  ancestor_id   INT      NOT NULL DEFAULT 0,
  descendant_id INT      NOT NULL DEFAULT 0,
  length        SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (ancestor_id, descendant_id)
);
CREATE INDEX environments_paths_adl ON environments_paths (ancestor_id, descendant_id, length);
CREATE INDEX environments_paths_dl ON environments_paths (descendant_id, length);

DROP TABLE IF EXISTS environments_users;
CREATE TABLE environments_users (
  environment_id INT NOT NULL DEFAULT 0,
  user_id        INT NOT NULL DEFAULT 0,
  PRIMARY KEY (environment_id, user_id)
);

DROP TABLE IF EXISTS revisions;
CREATE TABLE revisions (
  id             SERIAL PRIMARY KEY,
  environment_id INT NOT NULL DEFAULT 0,
  updated_at     TIMESTAMP,
  updated_by     VARCHAR(256),
  released_at    TIMESTAMP,
  released_by    VARCHAR(256),
  comment        TEXT
);

DROP TABLE IF EXISTS groups;
CREATE TABLE groups (
  id              SERIAL PRIMARY KEY,
  revision_id     INT          NOT NULL DEFAULT 0,
  name            VARCHAR(256) NOT NULL DEFAULT '',
  ordering        INT          NOT NULL DEFAULT '0',
  include_pattern TEXT         NOT NULL DEFAULT '',
  exclude_pattern TEXT         NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS puppet_classes;
CREATE TABLE puppet_classes (
  id       SERIAL PRIMARY KEY,
  name     VARCHAR(256) NOT NULL DEFAULT '',
  group_id INT          NOT NULL DEFAULT '0'
);

DROP TABLE IF EXISTS parameters;
CREATE TABLE parameters (
  id              SERIAL PRIMARY KEY,
  name            VARCHAR(256) NOT NULL DEFAULT '',
  parent_id       INT DEFAULT NULL,
  puppet_class_id INT DEFAULT NULL
);

DROP TABLE IF EXISTS values;
CREATE TABLE values (
  id           SERIAL PRIMARY KEY,
  name         VARCHAR(256) NOT NULL DEFAULT '',
  parameter_id INT DEFAULT NULL
);
