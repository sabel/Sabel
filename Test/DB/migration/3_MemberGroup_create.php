<?php

$create->column("id")->type(_INT)->nullable(false)->primary(true)->increment(false)->value(_NULL);
$create->column("super_group_id")->type(_INT)->nullable(false)->primary(false)->increment(false)->value(_NULL);
$create->column("name")->type(_STRING)->nullable(false)->primary(false)->increment(false)->value(_NULL)->length(32);

$create->fkey("super_group_id")->table("super_group")->column("id")->onDelete("NO ACTION")->onUpdate("NO ACTION");
$create->options("engine", "InnoDB");
