CREATE TABLE IF NOT EXISTS "solutions_utrack_data" (
  "id" AUTO_INCREMENT,
  "user" VARCHAR(16) NOT NULL,
  "data_name_id" integer NOT NULL,
  "data_value" VARCHAR(65000),
  "ts" TIMESTAMP DEFAULT NULL,
  "data_source_id" integer DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "solutions_utrack_events" (
  "id" AUTO_INCREMENT,
  "user" VARCHAR(32) NOT NULL,
  "event_name_id" integer NOT NULL,
  "event_value" VARCHAR(65000),
  "ts" TIMESTAMP DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "solutions_utrack_cases_data_source" (
  "id" AUTO_INCREMENT,
  "hash" VARCHAR(32) NOT NULL,
  "value" VARCHAR(256)
);

CREATE TABLE IF NOT EXISTS "solutions_utrack_cases_data_name" (
  "id" AUTO_INCREMENT,
  "hash" VARCHAR(32) NOT NULL,
  "value" VARCHAR(256)
);

CREATE TABLE IF NOT EXISTS "solutions_utrack_cases_event_name" (
  "id" AUTO_INCREMENT,
  "hash" VARCHAR(32) NOT NULL,
  "value" VARCHAR(256)
);
