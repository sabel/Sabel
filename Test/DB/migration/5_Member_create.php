<?php

$create->column("id")->type(_INT)->nullable(false)->primary(true)->increment(false)->value(_NULL);
$create->column("member_sub_group_id")->type(_INT)->nullable(false)->primary(false)->increment(false)->value(_NULL);
$create->column("location_id")->type(_INT)->nullable(false)->primary(false)->increment(false)->value(_NULL);
$create->column("name")->type(_STRING)->nullable(false)->primary(false)->increment(false)->value(_NULL)->length(32);
$create->column("email")->type(_STRING)->nullable(false)->primary(false)->increment(false)->value(_NULL)->length(255);
$create->column("is_temp")->type(_BOOL)->nullable(true)->primary(false)->increment(false)->value(false);
$create->column("created_at")->type(_DATETIME)->nullable(false)->primary(false)->increment(false)->value(_NULL);
$create->column("updated_at")->type(_DATETIME)->nullable(false)->primary(false)->increment(false)->value(_NULL);

$create->unique("email");
$create->fkey("location_id")->table("location")->column("id")->onDelete("NO ACTION")->onUpdate("NO ACTION");
$create->fkey("member_sub_group_id")->table("member_sub_group")->column("id")->onDelete("NO ACTION")->onUpdate("NO ACTION");
$create->options("engine", "InnoDB");
