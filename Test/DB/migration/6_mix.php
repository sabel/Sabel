<?php

##### Student_create #####

$create->column("id")->type(_INT)
                     ->primary(true);

$create->column("name")->type(_STRING)
                       ->nullable(false);

$create->options("engine", "InnoDB");

##### Course_create #####

$create->column("id")->type(_INT)
                     ->primary(true);

$create->column("name")->type(_STRING)
                       ->nullable(false);

$create->options("engine", "InnoDB");

##### StudentCourse_create #####

$create->column("student_id")->type(_INT)
                             ->nullable(false);

$create->column("course_id")->type(_INT)
                            ->nullable(false);

$create->column("val")->type(_STRING)
                      ->nullable(false);

$create->primary(array("student_id", "course_id"));
$create->options("engine", "InnoDB");

