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
  protected $model = null;
  protected $bridgeName = null;

  public function __construct($model, $bridgeName)
  {
    $this->model = $model;
    $this->bridgeName = $bridgeName;
  }

  public function getChild($child, $constraints = null)
  {
    $model = $this->model;

    $bridge = MODEL($this->bridgeName);
    $pKey   = $model->getPrimaryKey();
    $fKey   = convert_to_tablename($child) . "_" . $model->getPrimaryKey();
    $bridge->setCondition($fKey, $model->$pKey);

    if ($constraints) {
      $bridge->setConstraint($constraints);
    }

    $joiner  = new Sabel_DB_Join($bridge);
    $keys    = array("fKey" => $fKey, "id" => $pKey);
    $results = $joiner->add(MODEL($child), $keys)->join();

    if (!$results) return false;

    $children = array();
    foreach ($results as $bridgeModel) {
      $children[] = $bridgeModel->$child;
    }

    return $children;
  }
}
