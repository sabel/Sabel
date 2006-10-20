<?php

/**
 * Sabel_DB_Basic
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Basic extends Sabel_DB_Wrapper
{
  public function __construct($table = null)
  {
    if (isset($table)) $this->table = $table;
    parent::__construct();
  }
}
