<?php

/**
 * Sabel_DB_Type_Interface
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage type
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_DB_Type_Interface
{
  public function getType();
  public function add(Sabel_DB_Type_Interface $next);
  public function send(Sabel_DB_Schema_Column $column, $type);
}
