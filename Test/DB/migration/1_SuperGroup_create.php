<?php

$create->column("id")->type(_INT)->nullable(false)->primary(true)->increment(false)->default(_NULL);
$create->column("name")->type(_STRING)->nullable(false)->primary(false)->increment(false)->default(_NULL)->length(32);

$create->options("engine", "InnoDB");