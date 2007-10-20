<?php

Sabel::using("Sabel_DB_Join");
Sabel::using("Sabel_DB_Join_Relation");
Sabel::using("Sabel_DB_Condition_Object");
Sabel::using("Sabel_DB_Condition_Or");
Sabel::using("Sabel_DB_Condition_And");

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

function trans_begin()
{
  Sabel_DB_Transaction::activate();
}

function trans_commit()
{
  Sabel_DB_Transaction::commit();
}

function trans_rollback()
{
  Sabel_DB_Transaction::rollback();
}
