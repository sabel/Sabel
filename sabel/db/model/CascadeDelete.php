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
    $mdlName    = $model->getModelName();

    $model = Sabel_DB_Transaction::begin($model);

    $models  = array();
    $pKey    = $model->getPrimaryKey();
    $idValue = $model->$pKey;

    $childNames = $cascade[$mdlName];

    foreach ($childNames as $name) {
      $keys    = $this->getKeys($mdlName, $name, $pKey);
      $results = $this->pushStack($name, $keys["fkey"], $idValue);
      if ($results) $models[] = $results;
    }

    foreach ($models as $children) {
      $this->makeChainModels($children, $cascade);
    }

    $this->clearCascadeStack();

    $model->delete();
    Sabel_DB_Transaction::commit();
  }

  private function makeChainModels($children, &$cascade)
  {
    $childObj = $children[0];
    $mdlName  = $childObj->getModelName();
    if (!isset($cascade[$mdlName])) return;

    $models = array();
    $pKey   = $childObj->getPrimaryKey();
    $childNames = $cascade[$mdlName];

    foreach ($childNames as $name) {
      $keys = $this->getKeys($mdlName, $name, $pKey);
      foreach ($children as $child) {
        $results = $this->pushStack($name, $keys["fkey"], $child->$keys["id"]);
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

    return getRelationalKeys(MODEL($parent), $keys);
  }

  private function clearCascadeStack()
  {
    $stack = array_reverse($this->cascadeStack);

    foreach ($stack as $param => $fKey) {
      list($mdlName, $idValue) = explode(":", $param);
      $model = Sabel_DB_Transaction::load($mdlName);
      $model->delete($fKey, $idValue);
    }
  }
}
