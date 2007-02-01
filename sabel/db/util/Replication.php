<?php

/**
 * utility class for Replication.
 * please copy into the app/models of your application.
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Replication extends Sabel_DB_Model
{
  abstract public function choiceMasterConnectionName();
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
    return parent::doSelect($query);
  }

  public function doUpdate($data)
  {
    $this->choiceMasterConnectionName();
    parent::doUpdate($data);
  }

  public function doInsert($data, $incCol = null)
  {
    $this->choiceMasterConnectionName();
    return parent::doInsert($data, $incCol);
  }

  protected function doDelete()
  {
    $this->choiceMasterConnectionName();
    parent::doDelete();
  }
}
