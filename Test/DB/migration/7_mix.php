<?php

##### Student_create #####

$create->column("id")->type(_INT)->nullable(false)->primary(true);
$create->column("name")->type(_STRING)->nullable(false)->length(32);
$create->options("engine", "InnoDB");

##### Course_create #####

$create->column("id")->type(_INT)->nullable(false)->primary(true);
$create->column("name")->type(_STRING)->nullable(false)->length(32);
$create->options("engine", "InnoDB");

##### StudentCourse_create #####

$create->column("student_id")->type(_INT)->nullable(false);
$create->column("course_id")->type(_INT)->nullable(false);

$create->fkey("student_id");
$create->fkey("course_id");
$create->unique(array("student_id", "course_id"));
$create->options("engine", "InnoDB");
