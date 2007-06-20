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
    $joinKeys   = array();

  public function __construct($model, $bridgeName)
  {
    $this->model      = $model;
    $this->bridgeName = $bridgeName;
  }

  public function setJoinKeys($mdlName, $keys)
  {
    $this->joinKeys[$mdlName] = $keys;
  }

  public function getChild($child, $constraints = null)
  {
    $model    = $this->model;
    $mdlName  = $model->getModelName();
    $joinKeys = $this->joinKeys;
    $bridge   = MODEL($this->bridgeName);
    $foreign  = $bridge->getSchema()->getForeignKeys();

    if (isset($joinKeys[$mdlName])) {
      $pkey = $joinKeys[$mdlName]["id"];
      $fkey = $joinKeys[$mdlName]["fkey"];
    } elseif ($foreign === null) {
      $pkey = "id";
      $fkey = convert_to_tablename($mdlName) . "_id";
    } else {
      list ($pkey, $fkey) = $this->getJoinParam($foreign, $model->getTableName());
    }

    $bridge->setCondition($fkey, $model->$pkey);

    if ($constraints) {
      $bridge->setConstraint($constraints);
    }

    $joiner  = new Sabel_DB_Join($bridge);
    $cModel  = MODEL($child);
    $mdlName = $cModel->getModelName();

    if (isset($joinKeys[$mdlName])) {
      $pkey = $joinKeys[$mdlName]["id"];
      $fkey = $joinKeys[$mdlName]["fkey"];
    } elseif ($foreign === null) {
      $pkey = "id";
      $fkey = convert_to_tablename($mdlName) . "_id";
    } else {
      list ($pkey, $fkey) = $this->getJoinParam($foreign, $cModel->getTableName());
    }

    $keys    = array("id" => $pkey, "fkey" => $fkey);
    $results = $joiner->add($cModel, null, null, $keys)->join();

    if (!$results) return false;

    $children = array();
    foreach ($results as $bridgeModel) {
      $children[] = $bridgeModel->$child;
    }

    return $children;
  }

  private function getJoinParam($foreign, $tblName)
  {
    foreach ($foreign as $fkey => $params) {
      if ($params["referenced_table"] === $tblName) {
        return array($params["referenced_column"], $fkey);
      }
    }

    throw new Exception("please specify keys for reference.");
  }
}
