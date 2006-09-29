CREATE TABLE location (
    id int4 PRIMARY KEY auto_increment,
    omit_name character varying(16) NOT NULL,
    name      character varying(16) NOT NULL,
    area_name varchar(32) NOT NULL
);

SET NAMES utf8;
INSERT INTO location(omit_name, name, area_name) VALUES ( '北海道', '北海道',   '北海道');
INSERT INTO location(omit_name, name, area_name) VALUES ( '青森',   '青森県',   '東北');
INSERT INTO location(omit_name, name, area_name) VALUES ( '岩手',   '岩手県',   '東北');
INSERT INTO location(omit_name, name, area_name) VALUES ( '宮城',   '宮城県',   '東北');
INSERT INTO location(omit_name, name, area_name) VALUES ( '秋田',   '秋田県',   '東北');
INSERT INTO location(omit_name, name, area_name) VALUES ( '山形',   '山形県',   '東北');
INSERT INTO location(omit_name, name, area_name) VALUES ( '福島',   '福島県',   '東北');
INSERT INTO location(omit_name, name, area_name) VALUES ( '茨城',   '茨城県',   '関東');
INSERT INTO location(omit_name, name, area_name) VALUES ( '栃木',   '栃木県',   '関東');
INSERT INTO location(omit_name, name, area_name) VALUES ( '群馬',   '群馬県',   '関東');
INSERT INTO location(omit_name, name, area_name) VALUES ( '埼玉',   '埼玉県',   '関東');
INSERT INTO location(omit_name, name, area_name) VALUES ( '千葉',   '千葉県',   '関東');
INSERT INTO location(omit_name, name, area_name) VALUES ( '東京',   '東京都',   '関東');
INSERT INTO location(omit_name, name, area_name) VALUES ( '神奈川', '神奈川県', '関東');
INSERT INTO location(omit_name, name, area_name) VALUES ( '山梨',   '山梨県',   '関東');
INSERT INTO location(omit_name, name, area_name) VALUES ( '新潟',   '新潟県',   '北陸');
INSERT INTO location(omit_name, name, area_name) VALUES ( '富山',   '富山県',   '北陸');
INSERT INTO location(omit_name, name, area_name) VALUES ( '石川',   '石川県',   '北陸');
INSERT INTO location(omit_name, name, area_name) VALUES ( '福井',   '福井県',   '北陸');
INSERT INTO location(omit_name, name, area_name) VALUES ( '長野',   '長野県',   '北陸');
INSERT INTO location(omit_name, name, area_name) VALUES ( '岐阜',   '岐阜県',   '中部');
INSERT INTO location(omit_name, name, area_name) VALUES ( '静岡',   '静岡県',   '中部');
INSERT INTO location(omit_name, name, area_name) VALUES ( '愛知',   '愛知県',   '中部');
INSERT INTO location(omit_name, name, area_name) VALUES ( '三重',   '三重県',   '中部');
INSERT INTO location(omit_name, name, area_name) VALUES ( '滋賀',   '滋賀県',   '近畿');
INSERT INTO location(omit_name, name, area_name) VALUES ( '京都',   '京都府',   '近畿');
INSERT INTO location(omit_name, name, area_name) VALUES ( '大阪',   '大阪府',   '近畿');
INSERT INTO location(omit_name, name, area_name) VALUES ( '兵庫',   '兵庫県',   '近畿');
INSERT INTO location(omit_name, name, area_name) VALUES ( '奈良',   '奈良県',   '近畿');
INSERT INTO location(omit_name, name, area_name) VALUES ( '和歌山', '和歌山県', '近畿');
INSERT INTO location(omit_name, name, area_name) VALUES ( '鳥取',   '鳥取県',   '中国');
INSERT INTO location(omit_name, name, area_name) VALUES ( '島根',   '島根県',   '中国');
INSERT INTO location(omit_name, name, area_name) VALUES ( '岡山',   '岡山県',   '中国');
INSERT INTO location(omit_name, name, area_name) VALUES ( '広島',   '広島県',   '中国');
INSERT INTO location(omit_name, name, area_name) VALUES ( '山口',   '山口県',   '中国');
INSERT INTO location(omit_name, name, area_name) VALUES ( '徳島',   '徳島県',   '四国');
INSERT INTO location(omit_name, name, area_name) VALUES ( '香川',   '香川県',   '四国');
INSERT INTO location(omit_name, name, area_name) VALUES ( '愛媛',   '愛媛県',   '四国');
INSERT INTO location(omit_name, name, area_name) VALUES ( '高知',   '高知県',   '四国');
INSERT INTO location(omit_name, name, area_name) VALUES ( '福岡',   '福岡県',   '九州');
INSERT INTO location(omit_name, name, area_name) VALUES ( '佐賀',   '佐賀県',   '九州');
INSERT INTO location(omit_name, name, area_name) VALUES ( '長崎',   '長崎県',   '九州');
INSERT INTO location(omit_name, name, area_name) VALUES ( '熊本',   '熊本県',   '九州');
INSERT INTO location(omit_name, name, area_name) VALUES ( '大分',   '大分県',   '九州');
INSERT INTO location(omit_name, name, area_name) VALUES ( '宮崎',   '宮崎県',   '九州');
INSERT INTO location(omit_name, name, area_name) VALUES ( '鹿児島', '鹿児島県', '九州');
INSERT INTO location(omit_name, name, area_name) VALUES ( '沖縄',   '沖縄県',   '沖縄');
