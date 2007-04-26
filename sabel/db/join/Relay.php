<?php

/**
 * Sabel_DB_Join_Relay
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Join_Relay extends Sabel_DB_Join_Base
{
  public function getSourceModel()
  {
    return $this->sourceModel;
  }

  public function getObjects()
  {
    return $this->objects;
  }
}
