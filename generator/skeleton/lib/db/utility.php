<?php

class Join      extends Sabel_DB_Join {}
class Relation  extends Sabel_DB_Join_Relation {}
class Condition extends Sabel_DB_Condition {}
class SqlPart   extends Sabel_DB_Sql_Part {}

define("EQUAL",         Condition::EQUAL);
define("ISNULL",        Condition::ISNULL);
define("ISNOTNULL",     Condition::ISNOTNULL);
define("IN",            Condition::IN);
define("BETWEEN",       Condition::BETWEEN);
define("LIKE",          Condition::LIKE);
define("GREATER_EQUAL", Condition::GREATER_EQUAL);
define("GREATER_THAN",  Condition::GREATER_THAN);
define("LESS_EQUAL",    Condition::LESS_EQUAL);
define("LESS_THAN",     Condition::LESS_THAN);
define("DIRECT",        Condition::DIRECT);
