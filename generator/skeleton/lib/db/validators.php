<?php

/*
$emailValidator = array("function" => "validateEmailAddress",
                        "model"    => "MODEL_NAME",
                        "column"   => "COLUMN_NAME");

$passwdValidator = array("function"  => "validatePasswords",
                         "model"     => "MODEL_NAME",
                         "column"    => "COLUMN_NAME",
                         "arguments" => "RETYPE_INPUT_NAME");

$lengthValidator = array("function"  => "validateLength",
                         "model"     => "MODEL_NAME",
                         "column"    => "COLUMN_NAME",
                         "arguments" => array(MAX, MIN));
*/

// Sabel_DB_Validate_Config::addValidator($emailValidator);
// Sabel_DB_Validate_Config::addValidator($passwdValidator);
// Sabel_DB_Validate_Config::addValidator($lengthValidator);

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
