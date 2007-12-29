<?php

$create->column("id")->type(_INT)->nullable(false);
$create->column("name")->type(_STRING)->length(128)->nullable(false)->value("hoge");
$create->column("bint")->type(_BIGINT)->value(90000000000);
$create->column("sint")->type(_SMALLINT)->value(30000);
$create->column("txt")->type(_TEXT);
$create->column("bl")->type(_BOOL);
$create->column("ft")->type(_FLOAT)->value(1.234);
$create->column("dbl")->type(_DOUBLE)->value(1.23456);
$create->column("dt")->type(_DATE);
