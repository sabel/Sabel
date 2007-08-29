<?php

$libDb = RUN_BASE . DS . "lib" . DS . "db" . DS;

Sabel::fileUsing($libDb . "utility.php");
Sabel::fileUsing($libDb . "validators.php");
Sabel::fileUsing($libDb . "Manipulator.php");
Sabel::fileUsing($libDb . "Form.php");

Sabel_DB_Config::initialize();
