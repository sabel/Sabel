<?php

define("INT_MAX", PHP_INT_MAX);
define("INT_MIN", -2147483648);
define("SMALLINT_MAX",  32767);
define("SMALLINT_MIN", -32768);

/**
 * Sabel_DB_Type
 *
 * @interface
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_DB_Type
{
  const INT       = "_INT";
  const BIGINT    = "_BIGINT";
  const SMALLINT  = "_SMALLINT";
  const FLOAT     = "_FLOAT";
  const DOUBLE    = "_DOUBLE";
  const STRING    = "_STRING";
  const TEXT      = "_TEXT";
  const BOOL      = "_BOOL";
  const DATETIME  = "_DATETIME";
  const BYTE      = "_BYTE";

  // @todo
  const DATE      = "_DATE";
  const TIME      = "_TIME";
}
