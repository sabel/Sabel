<?php

$create->column("id")->type(_INT)->nullable(false)->primary(true)->increment(false)->default(_NULL);
$create->column("member_group_id")->type(_INT)->nullable(false)->primary(false)->increment(false)->default(_NULL);
$create->column("name")->type(_STRING)->nullable(false)->primary(false)->increment(false)->default(_NULL)->length(32);

$create->fkey("member_group_id")->table("member_group")->column("id")->onDelete("NO ACTION")->onUpdate("NO ACTION");
$create->options("engine", "InnoDB");