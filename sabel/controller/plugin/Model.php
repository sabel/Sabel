<?php

/**
 * model plugin
 *
 * @category   Controller
 * @package    org.sabel.controller.plugin
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Plugin_Model implements Sabel_Controller_Page_Plugin
{
  public function onBeforeAction($controller)
  {
    if ($controller->issetModels()) {
      foreach ($controller->getModels() as $model) {
        $modelName = strtolower($model);
        $this->setAttribute($modelName, MODEL($model));
      }
    }
  }
  
  public function onAfterAction($controller) {}
  
  public function fill($model, $options = null)
  {
    if (!$model instanceof Sabel_DB_Model) {
      throw new Sabel_Exception_Runtime("model isn't Sabel_DB_Model");
    }
    
    $requests = Sabel_Context::getPageController()->getRequests();
    
    if ($options === null) $options = array("ignores" => array());
    
    foreach ($model->getColumnNames() as $column) {
      if (!in_array($column, $options["ignores"])) {
        if (isset($requests[$column])) $model->$column = $requests[$column];
      }
    }
    
    return $model;
  }
  
  public function onRedirect($controller) {}
  public function onException($controller, $exception) {}
}