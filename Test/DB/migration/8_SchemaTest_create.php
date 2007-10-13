<?php

$create->column("id")->type(_INT)->nullable(false);
$create->column("name")->type(_STRING)->length(128)->nullable(false)->default("hoge");
$create->column("bint")->type(_BIGINT)->default(90000000000);
$create->column("sint")->type(_SMALLINT)->default(30000);
$create->column("txt")->type(_TEXT);
$create->column("bl")->type(_BOOL);
$create->column("ft")->type(_FLOAT)->default(1.234);
$create->column("dbl")->type(_DOUBLE)->default(1.23456);
$create->column("dt")->type(_DATE);
