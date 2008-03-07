<?php

/**
 * Processor_Form
 *
 * @category   Addon
 * @package    addon.form
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Form_Processor extends Sabel_Bus_Processor
{
  const MAX_LIFETIME = 1200;
  
  /**
   * @var Form_Object
   */
  private $form = null;
  
  /**
   * @var Sabel_Token_Storage
   */
  private $storage = null;
  
  /**
   * @var string
   */
  private $unityId = "";
  
  /**
   * @var string
   */
  private $token = "";
  
  protected function createStorage($namespace)
  {
    $config = array("namespace" => $namespace);
    return new Sabel_Token_Storage_Database($config);
  }
  
  public function execute($bus)
  {
    $this->extract("request", "controller");
    
    $controller = $this->controller;
    $controller->setAttribute("form", $this);
    $action = $bus->get("destination")->getAction();
    if (!$controller->hasMethod($action)) return;
    
    $annot = $controller->getReflection()->getMethodAnnotation($action, "unity");
    if (!isset($annot[0][0])) return;
    
    $this->unityId = $annot[0][0];
    $token = $this->request->getValueWithMethod("token");
    $sid = $bus->get("session")->getId();
    $this->storage = $this->createStorage($sid . "_" . $this->unityId);
    
    if ($token === null) return;
    
    if ($form = $this->storage->fetch($token)) {
      if ($this->request->isPost()) {
        $this->applyPostValues($form)->unsetErrors();
      }
      
      $this->form  = $form;
      $this->token = $token;
      
      $controller->setAttribute($form->getFormName(), $form);
      $controller->setAttribute("token", $token);
    } else {
      $bus->get("response")->notFound();
    }
  }
  
  public function create($model, $as = null)
  {
    if ($as !== null) {
      $name = $as;
    } elseif (is_model($model)) {
      $name = $model->getName();
    } elseif (is_string($model)) {
      $name = $model;
    } else {
      $message = "invalid argument(1) type. "
               . "must be a string or instance of model.";
      
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    $name = lcfirst($name) . "Form";
    $form = new Form_Object($model, $name);
    $this->controller->setAttribute($name, $form);
    
    if ($this->unityId !== "") {
      $this->form  = $form;
      $this->token = md5(uniqid(mt_rand(), true));
      $this->controller->setAttribute("token", $this->token);
    }
    
    return $form;
  }
  
  public function clear()
  {
    if ($this->token !== "" && $this->unityId !== "") {
      $this->storage->clear($this->token);
      $this->form = null;
    }
  }
  
  public function shutdown($bus)
  {
    if ($this->form !== null && $this->token !== "") {
      $this->storage->store($this->token, $this->form, self::MAX_LIFETIME);
    }
  }
  
  public function applyPostValues($form)
  {
    $values = $this->request->fetchPostValues();
    if (empty($values)) return $form;
    
    $model     = $form->getModel();
    $allowCols = $form->getAllowColumns();
    $mdlName   = $model->getName();
    
    foreach ($values as $key => $value) {
      if (strpos($key, "::") === false) continue;
      list ($name, $colName) = explode("::", $key);
      if ($name !== $mdlName || !in_array($colName, $allowCols)) continue;
      
      if ($colName === "datetime") {
        foreach ($value as $key => $date) {
          if ($this->isCompleteDateValues($date)) {
            if (!isset($date["second"])) {
              $date["second"] = "00";
            }
            
            $model->$key = $date["year"]   . "-"
                         . $date["month"]  . "-"
                         . $date["day"]    . " "
                         . $date["hour"]   . ":"
                         . $date["minute"] . ":"
                         . $date["second"];
          } else {
            $model->$key = null;
          }
        }
      } elseif ($colName === "date") {
        foreach ($value as $key => $date) {
          if ($this->isCompleteDateValues($date, false)) {
            $date = "{$date["year"]}-{$date["month"]}-{$date["day"]}";
            $model->$key = $date;
          } else {
            $model->$key = null;
          }
        }
      } else {
        $model->$colName = $value;
      }
    }
    
    foreach ($model->getColumns() as $colName => $column) {
      if (!$column->isBool()) continue;
      $key = "{$mdlName}::{$colName}";
      if (isset($values[$key])) {
        $model->$colName = ($values[$key] === "1");
      }
    }
    
    return $form;
  }
  
  private function isCompleteDateValues($values, $isDatetime = true)
  {
    $keys = array("year", "month", "day");
    
    if ($isDatetime) {
      $keys = array_merge($keys, array("hour", "minute", "second"));
    }
    
    foreach ($keys as $key) {
      if ($values[$key] === "") return false;
    }
    
    return true;
  }
}
