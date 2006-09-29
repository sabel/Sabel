CREATE TABLE proxy
(
  id   int8 PRIMARY KEY AUTO_INCREMENT,
  host varchar(64) UNIQUE NOT NULL,
  rank char(4) NOT NULL,
  port int2 NOT NULL
);
