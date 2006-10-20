<?php

/**
 * Sabel_DB_Schema_Type_Sender
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage schema
 * @subpackage type
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_DB_Schema_Type_Sender
{
  public function add($chain);
  public function send($columnObj, $type);
}
