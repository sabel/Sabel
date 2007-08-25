<?php

/**
 * Sabel_DB_Model_Bridge
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Model_Bridge
{
  protected
    $model      = null,
    $bridgeName = null,
    $joinKey    = array();

  public function __construct(Sabel_DB_Model $model, $bridgeName)
  {
    $this->model      = $model;
    $this->bridgeName = $bridgeName;
  }

  public function setJoinKey($mdlName, $keys)
  {
    $this->joinKey[$mdlName] = $keys;
  }

  public function getChild($child, $constraints = null)
  {
    $model   = $this->model;
    $mdlName = $model->getModelName();
    $joinKey = $this->joinKey;
    $bridge  = MODEL($this->bridgeName);
    $manip   = new Manipulator($bridge);
    $foreign = $bridge->getSchema()->getForeignKeys();

    list ($pkey, $fkey) = $this->getJoinKey($foreign, $mdlName, $model);
    $manip->setCondition("{$this->bridgeName}.{$fkey}", $model->$pkey);
    if ($constraints) $manip->setConstraint($constraints);

    $cModel  = MODEL($child);
    $mdlName = $cModel->getModelName();
    list ($pkey, $fkey) = $this->getJoinKey($foreign, $mdlName, $cModel);
    $keys    = array("id" => $pkey, "fkey" => $fkey);
    $joiner  = new Sabel_DB_Join($manip);
    $results = $joiner->add($cModel, null, null, $keys)->join();

    if (!$results) return false;

    $children = array();
    foreach ($results as $bridgeModel) {
      $children[] = $bridgeModel->$child;
    }

    return $children;
  }

  private function getJoinKey($foreign, $mdlName, $model)
  {
    $joinKey = $this->joinKey;

    if (isset($joinKey[$mdlName])) {
      return array($joinKey[$mdlName]["id"], $joinKey[$mdlName]["fkey"]);
    } elseif ($foreign === null) {
      return array("id", convert_to_tablename($mdlName) . "_id");
    } else {
      $tblName = $model->getTableName();
      foreach ($foreign as $fkey => $params) {
        if ($params["referenced_table"] === $tblName) {
          return array($params["referenced_column"], $fkey);
        }
      }

      throw new Exception("please specify keys for reference.");
    }
  }
}
