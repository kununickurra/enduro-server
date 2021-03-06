-- Create the database
CREATE DATABASE enduro;
CREATE USER 'rider'@'localhost' IDENTIFIED BY 'speed';
USE enduro;
GRANT ALL PRIVILEGES ON enduro.* TO 'rider'@'localhost';
FLUSH PRIVILEGES;

-- Create database tables.
CREATE TABLE IF NOT EXISTS trip (
  id INTEGER NOT NULL AUTO_INCREMENT,
  name VARCHAR(128) NOT NULL,
  starttime DATETIME,
  endtime DATETIME,
  PRIMARY KEY(id)
);

CREATE UNIQUE INDEX trip_primary ON trip (id);

CREATE TABLE IF NOT EXISTS trip_log (
    id INTEGER NOT NULL AUTO_INCREMENT,
    trip_id INTEGER NOT NULL,
    latitude DECIMAL(9,6) NOT NULL,
    longitude DECIMAL(9,6) NOT NULL,
    occurred DATETIME NOT NULL,

    PRIMARY KEY(id),
    FOREIGN KEY (trip_id) REFERENCES trip(id)
);

CREATE UNIQUE INDEX trip_log_primary ON trip_log (id);
CREATE INDEX trip_log_fk_trip ON trip_log (trip_id);


CREATE TABLE IF NOT EXISTS itinerary (
  id INTEGER NOT NULL AUTO_INCREMENT,
  name VARCHAR(128) NOT NULL,
  PRIMARY KEY(id)
);

CREATE UNIQUE INDEX itinerary_primary ON itinerary (id);

CREATE TABLE IF NOT EXISTS anchor (
  id INTEGER NOT NULL AUTO_INCREMENT,
  itinerary_id INTEGER NOT NULL,
  sequence INTEGER NOT NULL,
  latitude DECIMAL(9,6) NOT NULL,
  longitude DECIMAL(9,6) NOT NULL,
  PRIMARY KEY(id)
);

CREATE UNIQUE INDEX anchor_primary ON anchor (id);
CREATE INDEX anchor_fk_itinerary ON anchor (itinerary_id);

TRUNCATE TABLE trip_log;
TRUNCATE TABLE trip;
