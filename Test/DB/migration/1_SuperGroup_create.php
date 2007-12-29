<?php

$create->column("id")->type(_INT)->nullable(false)->primary(true)->increment(false)->value(_NULL);
$create->column("name")->type(_STRING)->nullable(false)->primary(false)->increment(false)->value(_NULL)->length(32);

$create->options("engine", "InnoDB");
