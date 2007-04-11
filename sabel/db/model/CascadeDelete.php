<?php

/**
 * Sabel_DB_Model_CascadeDelete
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Model_CascadeDelete
{
  protected $model = null;
  protected $keys  = array();

  protected $cascadeStack = array();

  public function __construct($mdlName, $id)
  {
    $this->model = MODEL($mdlName, $id);
  }

  public function execute($configClassName)
  {
    $model   = $this->model;
    $config  = new $configClassName();

    $cascade    = $config->getChain();
    $this->keys = $config->getKeys();
    $mdlName    = convert_to_modelname($model->getTableName());

    $model->startTransaction();

    $models  = array();
    $pKey    = $model->getPrimaryKey();
    $idValue = $model->$pKey;

    $childNames = $cascade[$mdlName];

    foreach ($childNames as $name) {
      $keys    = $this->getKeys($mdlName, $name, $pKey);
      $results = $this->pushStack($name, $keys["fKey"], $idValue);
      if ($results) $models[] = $results;
    }

    foreach ($models as $children) {
      $this->makeChainModels($children, $cascade);
    }

    $this->clearCascadeStack();

    $model->remove();
    $model->commit();
  }

  private function makeChainModels($children, &$cascade)
  {
    $childObj = $children[0];
    $mdlName  = convert_to_modelname($childObj->getTableName());
    if (!isset($cascade[$mdlName])) return;

    $models = array();
    $pKey   = $childObj->getPrimaryKey();
    $childNames = $cascade[$mdlName];

    foreach ($childNames as $name) {
      $keys = $this->getKeys($mdlName, $name, $pKey);
      foreach ($children as $child) {
        $results = $this->pushStack($name, $keys["fKey"], $child->$keys["id"]);
        if ($results) $models[] = $results;
      }
    }
    if (!$models) return;

    foreach ($models as $children) {
      $this->makeChainModels($children, $cascade);
    }
  }

  protected function pushStack($child, $fKey, $idValue)
  {
    $model  = MODEL($child);
    $models = $model->select($fKey, $idValue);

    if ($models) {
      $this->cascadeStack["{$child}:{$idValue}"] = $fKey;
    }

    return $models;
  }

  protected function getKeys($parent, $child, $pKey)
  {
    if (isset($this->keys[$parent][$child])) {
      $keys = $this->keys[$parent][$child];
    } else {
      $keys = null;
    }

    return Sabel_DB_Relation_Key::create(MODEL($parent), $keys);
  }

  private function clearCascadeStack()
  {
    $stack = array_reverse($this->cascadeStack);

    foreach ($stack as $param => $fKey) {
      list($mdlName, $idValue) = explode(":", $param);
      $model = MODEL($mdlName);
      $model->addTransaction();
      $model->remove($fKey, $idValue);
    }
  }
}
