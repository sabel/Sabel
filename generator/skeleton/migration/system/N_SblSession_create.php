<?php

$create->column("sid")->type(_STRING)
                      ->length(64)
                      ->primary(true);

$create->column("data")->type(_TEXT);

$create->column("timeout")->type(_INT)
                          ->value(0);
