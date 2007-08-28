<?php

/*
$custom = array("function" => "validateEmailAddress",
                "model"    => "Members",
                "column"   => "email");

Sabel_DB_Validate_Config::addValidator($custom);
*/

function validateEmailAddress($model, $name)
{
  if ($model->$name !== null) {
    $result = preg_match("/^[\w.\-_]+@([\w\-_]+\.)+[a-zA-Z]+$/", $model->$name);

    if ($result === 0) {
      return "invalid email format";
    }
  }
}
