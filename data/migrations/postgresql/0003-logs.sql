DROP TABLE IF EXISTS logs;
CREATE TABLE logs (
  id             SERIAL PRIMARY KEY,
  created_at     TIMESTAMP,
  created_by     VARCHAR(256),
  comment        TEXT
);
CREATE INDEX logs_ca ON logs (created_at);
