<?php

function a($uri, $anchor, $param = null)
{
  if ($param === null) {
    return '<a href="'.uri($uri).'">'.$anchor.'</a>';
  } else {
    return '<a href="'.uri($uri).$param.'">'.$anchor.'</a>';
  }
}

function ah($param, $anchor)
{
  return a($param, htmlspecialchars($anchor));
}

function uri($param)
{
  $aCreator = new Sabel_View_Uri();
  $ignored = "";
  if (defined("URI_IGNORE")) {
    $ignored = $_SERVER["SCRIPT_NAME"];
  }
  return $ignored . $aCreator->uri($param);
}

function linkto($file)
{
  $ignored = "";
  if (defined("URI_IGNORE")) {
    $ignored = dirname($_SERVER["SCRIPT_NAME"]);
  }
  return $ignored . "/" . $file;
}

function css($file)
{
  $ignored = "";
  if (defined("URI_IGNORE")) {
    $ignored = dirname($_SERVER["SCRIPT_NAME"]);
    $fmt = '  <link rel="stylesheet" href="%s" type="text/css" />';
    return sprintf($fmt, $ignored . "/css/" . $file . ".css");;
  } else {
    $fmt = '  <link rel="stylesheet" href="%s" type="text/css" />';
    return sprintf($fmt, "/css/{$file}.css");
  }
}

/***   helpers for sabel.db   ***/

$SABEL_DB_DIR = "sabel" . DIR_DIVIDER . "db" . DIR_DIVIDER;

require ($SABEL_DB_DIR . "Join.php");
require ($SABEL_DB_DIR . "join"      . DIR_DIVIDER . "Relation.php");
require ($SABEL_DB_DIR . "condition" . DIR_DIVIDER . "Object.php");
require ($SABEL_DB_DIR . "condition" . DIR_DIVIDER . "Or.php");
require ($SABEL_DB_DIR . "condition" . DIR_DIVIDER . "And.php");

class Join         extends Sabel_DB_Join {}
class Relation     extends Sabel_DB_Join_Relation {}
class Condition    extends Sabel_DB_Condition_Object {}
class OrCondition  extends Sabel_DB_Condition_Or {}
class AndCondition extends Sabel_DB_Condition_And {}

define("NOT",         Sabel_DB_Condition_Object::NOT);
define("IS_NULL",     Sabel_DB_Condition_Object::ISNULL);
define("IS_NOT_NULL", Sabel_DB_Condition_Object::NOTNULL);

function trans_begin($model)
{
  return Sabel_DB_Transaction::begin($model);
}

function trans_commit()
{
  Sabel_DB_Transaction::commit();
}

function trans_rollback()
{
  Sabel_DB_Transaction::rollback();
}
