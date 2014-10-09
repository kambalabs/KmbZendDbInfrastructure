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
  PRIMARY KEY (ancestor_id, descendant_id),
  FOREIGN KEY (ancestor_id) REFERENCES environments(id) ON DELETE CASCADE,
  FOREIGN KEY (descendant_id) REFERENCES environments(id) ON DELETE CASCADE
);
CREATE INDEX environments_paths_adl ON environments_paths (ancestor_id, descendant_id, length);
CREATE INDEX environments_paths_dl ON environments_paths (descendant_id, length);

DROP TABLE IF EXISTS environments_users;
CREATE TABLE environments_users (
  environment_id INT NOT NULL DEFAULT 0,
  user_id        INT NOT NULL DEFAULT 0,
  PRIMARY KEY (environment_id, user_id),
  FOREIGN KEY (environment_id) REFERENCES environments(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

DROP TABLE IF EXISTS revisions;
CREATE TABLE revisions (
  id             SERIAL PRIMARY KEY,
  environment_id INT NOT NULL DEFAULT 0,
  updated_at     TIMESTAMP,
  updated_by     VARCHAR(256),
  released_at    TIMESTAMP,
  released_by    VARCHAR(256),
  comment        TEXT,
  FOREIGN KEY (environment_id) REFERENCES environments(id) ON DELETE CASCADE
);
CREATE INDEX revisions_env ON revisions (environment_id);
CREATE INDEX revisions_ra ON revisions (released_at);
CREATE INDEX revisions_ua ON revisions (updated_at);

DROP TABLE IF EXISTS groups;
CREATE TABLE groups (
  id              SERIAL PRIMARY KEY,
  revision_id     INT          NOT NULL DEFAULT 0,
  name            VARCHAR(256) NOT NULL DEFAULT '',
  ordering        INT          NOT NULL DEFAULT '0',
  include_pattern TEXT         NOT NULL DEFAULT '',
  exclude_pattern TEXT         NOT NULL DEFAULT '',
  FOREIGN KEY (revision_id) REFERENCES revisions(id) ON DELETE CASCADE
);
CREATE INDEX groups_o ON groups (ordering);

DROP TABLE IF EXISTS puppet_classes;
CREATE TABLE puppet_classes (
  id       SERIAL PRIMARY KEY,
  name     VARCHAR(256) NOT NULL DEFAULT '',
  group_id INT          NOT NULL DEFAULT '0',
  FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
);

DROP TABLE IF EXISTS parameters;
CREATE TABLE parameters (
  id              SERIAL PRIMARY KEY,
  name            VARCHAR(256) NOT NULL DEFAULT '',
  parent_id       INT DEFAULT NULL,
  puppet_class_id INT DEFAULT NULL,
  FOREIGN KEY (parent_id) REFERENCES parameters(id) ON DELETE CASCADE,
FOREIGN KEY (puppet_class_id) REFERENCES puppet_classes(id) ON DELETE CASCADE
);

DROP TABLE IF EXISTS values;
CREATE TABLE values (
  id           SERIAL PRIMARY KEY,
  name         VARCHAR(256) NOT NULL DEFAULT '',
  parameter_id INT DEFAULT NULL,
  FOREIGN KEY (parameter_id) REFERENCES parameters(id) ON DELETE CASCADE
);
