DROP TABLE IF EXISTS users CASCADE;
CREATE TABLE users (
  id    SERIAL PRIMARY KEY,
  login VARCHAR(256) NOT NULL DEFAULT '',
  name  VARCHAR(256) NOT NULL DEFAULT '',
  email VARCHAR(256) NOT NULL DEFAULT '',
  role  VARCHAR(256) NOT NULL DEFAULT ''
);

DROP TABLE IF EXISTS environments CASCADE;
CREATE TABLE environments (
  id        SERIAL PRIMARY KEY,
  name      VARCHAR(256) NOT NULL DEFAULT '',
  isdefault SMALLINT NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS environments_paths CASCADE;
CREATE TABLE environments_paths (
  ancestor_id   INT      NOT NULL,
  descendant_id INT      NOT NULL,
  length        SMALLINT NOT NULL DEFAULT 0,
  PRIMARY KEY (ancestor_id, descendant_id),
  FOREIGN KEY (ancestor_id) REFERENCES environments(id) ON DELETE CASCADE,
  FOREIGN KEY (descendant_id) REFERENCES environments(id) ON DELETE CASCADE
);
CREATE INDEX environments_paths_adl ON environments_paths (ancestor_id, descendant_id, length);
CREATE INDEX environments_paths_dl ON environments_paths (descendant_id, length);

DROP TABLE IF EXISTS environments_users CASCADE;
CREATE TABLE environments_users (
  environment_id INT NOT NULL,
  user_id        INT NOT NULL,
  PRIMARY KEY (environment_id, user_id),
  FOREIGN KEY (environment_id) REFERENCES environments(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

DROP TABLE IF EXISTS revisions CASCADE;
CREATE TABLE revisions (
  id             SERIAL PRIMARY KEY,
  environment_id INT NOT NULL,
  released_at    TIMESTAMP,
  released_by    VARCHAR(256),
  comment        TEXT,
  FOREIGN KEY (environment_id) REFERENCES environments(id) ON DELETE CASCADE
);
CREATE INDEX revisions_env ON revisions (environment_id);
CREATE INDEX revisions_ra ON revisions (released_at);

DROP TABLE IF EXISTS revisions_logs CASCADE;
CREATE TABLE revisions_logs (
  id             SERIAL PRIMARY KEY,
  revision_id    INT NOT NULL,
  created_at     TIMESTAMP,
  created_by     VARCHAR(256),
  comment        TEXT,
  FOREIGN KEY (revision_id) REFERENCES revisions(id) ON DELETE CASCADE
);
CREATE INDEX revisions_logs_ca ON revisions_logs (created_at);

DROP TABLE IF EXISTS groups CASCADE;
CREATE TABLE groups (
  id              SERIAL PRIMARY KEY,
  revision_id     INT          NOT NULL,
  name            VARCHAR(256) NOT NULL DEFAULT '',
  ordering        INT          NOT NULL DEFAULT '0',
  include_pattern TEXT         NOT NULL DEFAULT '',
  exclude_pattern TEXT         NOT NULL DEFAULT '',
  FOREIGN KEY (revision_id) REFERENCES revisions(id) ON DELETE CASCADE
);
CREATE INDEX groups_o ON groups (ordering);

DROP TABLE IF EXISTS group_classes CASCADE;
CREATE TABLE group_classes (
  id       SERIAL PRIMARY KEY,
  name     VARCHAR(256) NOT NULL DEFAULT '',
  group_id INT          NOT NULL,
  FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
);

DROP TABLE IF EXISTS group_parameters CASCADE;
CREATE TABLE group_parameters (
  id              SERIAL PRIMARY KEY,
  name            VARCHAR(256) NOT NULL DEFAULT '',
  parent_id       INT DEFAULT NULL,
  group_class_id  INT NOT NULL,
  FOREIGN KEY (parent_id) REFERENCES group_parameters(id) ON DELETE CASCADE,
  FOREIGN KEY (group_class_id) REFERENCES group_classes(id) ON DELETE CASCADE
);

DROP TABLE IF EXISTS group_values CASCADE;
CREATE TABLE group_values (
  id                 SERIAL PRIMARY KEY,
  value              VARCHAR(256) NOT NULL DEFAULT '',
  group_parameter_id INT NOT NULL,
  FOREIGN KEY (group_parameter_id) REFERENCES group_parameters(id) ON DELETE CASCADE
);
