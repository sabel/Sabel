CREATE TABLE sites
(
  id    int8 PRIMARY KEY AUTO_INCREMENT,
  name  varchar(64) NOT NULL,
  url   varchar(256) NOT NULL,
  class varchar(32) NOT NULL,
  encoding varchar(16) NOT NULL
);

set names utf8;
INSERT INTO sites(name, url, class, encoding) VALUES('不倫・愛人・セフレ募集掲示板', 'http://www.happy-mail.net/777/mailbbs3/mailbbs.cgi', 'HappyMail_Net_777', 'EUC_JP');
