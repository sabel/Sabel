CREATE TABLE characters
(
  id            int8         PRIMARY KEY auto_increment,
  adstaff_id    int8         NOT NULL,
  name          varchar(64)  NOT NULL,
  mail_address  varchar(128) NOT NULL,
  location_id   int4         NOT NULL,
  invitation_id int4         NOT NULL,
  freetimes_id  int4         NOT NULL,
  city          varchar(64)  NOT NULL,
  age           int4         NOT NULL,
  birthday      date         NOT NULL,
  subject       varchar(256) NOT NULL,
  message       text         NOT NULL,
  make_date     datetime     NOT NULL
);
