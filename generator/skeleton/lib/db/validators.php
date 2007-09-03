<?php

/*
$emailValidator = array("function" => "validateEmailAddress",
                        "model"    => "Member",
                        "column"   => "email");

$passwdValidator = array("function"  => "validatePasswords",
                         "model"     => "Member",
                         "column"    => "password",
                         "arguments" => "retype");

$lengthValidator = array("function"  => "validateLength",
                         "model"     => "Member",
                         "column"    => "nickname",
                         "arguments" => array(array(10, 4)));

Sabel_DB_Validate_Config::addValidator($emailValidator);
Sabel_DB_Validate_Config::addValidator($passwdValidator);
Sabel_DB_Validate_Config::addValidator($lengthValidator);
*/

function validateEmailAddress($model, $name, $localizedName)
{
  if ($model->$name !== null) {
    $regex = '/^[\w.\-_]+@([\w\-_]+\.)+[a-zA-Z]+$/';
    if (preg_match($regex, $model->$name) === 0) {
      return "invalid email address.";
    }
  }
}

function validatePasswords($model, $name, $localizedName, $retypeName)
{
  if ($model->$name !== $model->$retypeName) {
    return "passwords didn't match.";
  } else {
    $model->unsetValue($retypeName);
  }
}

function validateLength($model, $name, $localizedName, $max, $min = 0)
{
  if ($model->$name !== null) {
    $func = (extension_loaded("mbstring")) ? "mb_strlen" : "strlen";
    $length = $func($model->$name);
    if ($length > $max) {
      return "$name should be $max characters or less.";
    } elseif ($length < $min) {
      return "$name should be $min characters or more.";
    }
  }
}
