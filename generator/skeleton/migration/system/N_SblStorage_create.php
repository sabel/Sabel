<?php

$create->column("id")->type(_STRING)
                     ->length(128)
                     ->primary(true);

$create->column("data")->type(_TEXT);

$create->column("timeout")->type(_INT)
                          ->value(0);
