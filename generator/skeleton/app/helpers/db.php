<?php

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
define("IS_NOT_NULL", Sabel_DB_Condition_Object::ISNOTNULL);
define("IN",          Sabel_DB_Condition_Object::IN);
define("BETWEEN",     Sabel_DB_Condition_Object::BETWEEN);
define("LIKE",        Sabel_DB_Condition_Object::LIKE);
define("COMPARE",     Sabel_DB_Condition_Object::COMPARE);

function trans_begin($model = null)
{
  if ($model === null) {
    Sabel_DB_Transaction::activate();
  } else {
    Sabel_DB_Transaction::begin($model->getConnectionName());
  }
}

function trans_commit()
{
  Sabel_DB_Transaction::commit();
}

function trans_rollback()
{
  Sabel_DB_Transaction::rollback();
}
