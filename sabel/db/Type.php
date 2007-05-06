<?php

/**
 * Sabel_DB_Type_Const
 *
 * @interface
 * @category   DB
 * @package    org.sabel.db
 * @subpackage type
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_DB_Type
{
  const INT       = "INT";
  const BIGINT    = "BIGINT";
  const FLOAT     = "FLOAT";
  const DOUBLE    = "DOUBLE";
  const STRING    = "STRING";
  const TEXT      = "TEXT";
  const BOOL      = "BOOL";
  const DATETIME  = "DATETIME";
  const BYTE      = "BYTE";

  // might erase...
  const DATE      = "DATE";
  const TIME      = "TIME";
}
