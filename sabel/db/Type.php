<?php

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
  const DATE      = "_DATE";
  const BYTE      = "_BYTE";
  const UNKNOWN   = "_UNKNOWN";
}
