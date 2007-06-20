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

    // @todo use foreign key of schema.

    $bridge = MODEL($this->bridgeName);
    $pKey   = $model->getPrimaryKey();
    $bridge->setCondition($model->getTableName() . "_" . $pKey, $model->$pKey);

    if ($constraints) {
      $bridge->setConstraint($constraints);
    }

    $joiner  = new Sabel_DB_Join($bridge);

    $cModel  = MODEL($child);
    $cPkey   = $cModel->getPrimaryKey();
    $keys    = array("id" => $cPkey, "fkey" => $cModel->getTableName() . "_" . $cPkey);
    $results = $joiner->add($cModel, null, null, $keys)->join();

    if (!$results) return false;

    $children = array();
    foreach ($results as $bridgeModel) {
      $children[] = $bridgeModel->$child;
    }

    return $children;
  }
}
