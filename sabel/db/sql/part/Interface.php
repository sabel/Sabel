<?php

/**
 * Sabel_DB_Sql_Part_Interface
 *
 * @interface
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_DB_Sql_Part_Interface
{
  public function __construct($value, $format = null);
  public function getValue(Sabel_DB_Abstract_Sql $sql);
}
