CREATE TABLE freetimes
(
  id INT8 PRIMARY KEY auto_increment,
  name varchar(64) NOT NULL
);

SET NAMES utf8;
INSERT INTO freetimes(name) VALUES('平日の午前');
INSERT INTO freetimes(name) VALUES('平日の午後');
INSERT INTO freetimes(name) VALUES('平日のいつでも');
INSERT INTO freetimes(name) VALUES('土曜の午前');
INSERT INTO freetimes(name) VALUES('土曜の午後');
INSERT INTO freetimes(name) VALUES('土曜のいつでも');
INSERT INTO freetimes(name) VALUES('日祝の午前');
INSERT INTO freetimes(name) VALUES('日祝の午後');
INSERT INTO freetimes(name) VALUES('日祝のいつでも');
INSERT INTO freetimes(name) VALUES('いつでも');
