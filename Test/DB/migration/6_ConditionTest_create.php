<?php

$create->column("id")->type(_INT)->nullable(false)->primary(true)->increment(true)->value(_NULL);
$create->column("name")->type(_STRING)->nullable(true)->primary(false)->increment(false)->value(_NULL)->length(32);
$create->column("point")->type(_SMALLINT)->nullable(true)->primary(false)->increment(false)->value(100);
$create->column("bool_flag")->type(_BOOL)->nullable(true)->primary(false)->increment(false)->value(false);

$create->options("engine", "InnoDB");
