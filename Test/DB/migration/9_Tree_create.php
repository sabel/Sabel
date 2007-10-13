<?php

$create->column("id")->type(_INT)->primary(true);
$create->column("name")->type(_STRING)->length(6)->nullable(false);
$create->column("tree_id")->type(_INT);

$create->fkey("tree_id");
$create->options("engine", "InnoDB");
