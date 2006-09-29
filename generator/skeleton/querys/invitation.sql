CREATE TABLE invitation
(
  id   int8         PRIMARY KEY auto_increment,
  name varchar(64)  NOT NULL
);

set names utf8;
INSERT INTO invitation(name) VALUES('恋人');
INSERT INTO invitation(name) VALUES('愛人');
INSERT INTO invitation(name) VALUES('不倫');
INSERT INTO invitation(name) VALUES('セフレ');
INSERT INTO invitation(name) VALUES('メルトモ');
