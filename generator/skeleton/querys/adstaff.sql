CREATE TABLE adstaff
(
  id INT8 PRIMARY KEY auto_increment,
  real_name  varchar(32) NOT NULL,
  login_name varchar(32) NOT NULL UNIQUE,
  login_pass varchar(32) NOT NULL
);

SET NAMES utf8;
INSERT INTO adstaff(real_name, login_name, login_pass) VALUES('はまなか', 'hama', 1192);
