<?php

/**
 * Sabel_DB_Util_Replication
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Util_Replication extends Sabel_DB_Model
{
  abstract public function choiceConnectionNameForMaster();
  abstract public function choiceSlaveConnectionName();

  protected function createSchemaAccessor()
  {
    $this->choiceSlaveConnectionName();
    return parent::createSchemaAccessor();
  }

  public function begin()
  {
    $this->choiceMasterConnectionName();
    parent::begin();
  }

  public function doSelect($query = null)
  {
    $this->choiceSlaveConnectionName();
    parent::doSelect($query);
  }

  public function doUpdate($data)
  {
    $this->choiceMasterConnectionName();
    parent::doUpdate($data);
  }

  public function doInsert($data, $incCol = null)
  {
    $this->choiceMasterConnectionName();
    parent::doInsert($data, $incCol);
  }

  protected function doDelete()
  {
    $this->choiceMasterConnectionName();
    parent::doDelete();
  }
}
