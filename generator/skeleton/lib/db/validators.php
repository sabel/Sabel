<?php

/*
$emailValidator = array("function" => "validateEmailAddress",
                        "model"    => "MODEL_NAME",
                        "column"   => "COLUMN_NAME");

$passwdValidator = array("function"  => "validatePasswords",
                         "model"     => "MODEL_NAME",
                         "column"    => "COLUMN_NAME",
                         "arguments" => "REINPUT");

$lengthValidator = array("function"  => "validateLength",
                         "model"     => "MODEL_NAME",
                         "column"    => "COLUMN_NAME",
                         "arguments" => array(MAX, MIN));
*/

// Sabel_DB_Validate_Config::addValidator($emailValidator);
// Sabel_DB_Validate_Config::addValidator($passwdValidator);
// Sabel_DB_Validate_Config::addValidator($lengthValidator);

function checkEmailAddress($email)
{
  $regex = '/^[\w.\-_]+@([\w\-_]+\.)+[a-zA-Z]+$/';
  return (preg_match($regex, $email) !== 0);
}

function validateEmailAddress($model, $name, $localizedName)
{
  if ($model->$name !== null && !checkEmailAddress($model->$name)) {
    return "invalid mail address format.";
  }
}

function validatePasswords($model, $name, $localizedName, $reInput)
{
  if ($model->$name !== $model->$reInput) {
    return "passwords didn't match.";
  } else {
    $model->unsetValue($reInput);
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
