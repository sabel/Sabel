<?php

/**
 * Sabel_DB_Model
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Model
{
  public static function load($mdlName)
  {
    return self::getClass($mdlName);
  }

  public static function fusion($mdlNames)
  {
    $models = array();
    foreach ($mdlNames as $name) $models[] = self::getClass($name);
    return new Sabel_DB_Fusion($models, $mdlNames);
  }

  private static function getClass($mdlName)
  {
    if (class_exists($mdlName, false)) return new $mdlName();

    if (!class_exists('Sabel_DB_Empty', false)) {
      eval('class Sabel_DB_Empty extends Sabel_DB_Relation
            {
              public function __construct() { $this->isModel = true; }
            }'
          );
    }

    $model = new Sabel_DB_Empty();
    $prop  = new Sabel_DB_Property($mdlName, array());
    $model->setProperty($prop);
    return $model;
  }
}
