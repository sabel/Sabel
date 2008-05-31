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
   * @var Sabel_Storage
   */
  private $storage = null;
  
  /**
   * @var string
   */
  private $token = "";
  
  protected function createStorage($clientId)
  {
    $config = array("namespace" => $clientId);
    $this->storage = new Sabel_Storage_Database($config);
  }
  
  public function execute($bus)
  {
    $this->extract("request", "controller");
    
    $controller = $this->controller;
    $action = $bus->get("destination")->getAction();
    if (!$controller->hasMethod($action)) return;
    
    $this->createStorage($bus->get("session")->getClientId());
    $controller->setAttribute("form", $this);
    
    $reflection = $controller->getReflection();
    $annotation  = $reflection->getMethodAnnotation($action, "form");
    $this->token = $this->request->getValueWithMethod("token");
    
    if (isset($annotation[0][0])) {
      if ($this->token === null || ($form = $this->get()) === null) {
        $bus->get("response")->getStatus()->setCode(Sabel_Response::BAD_REQUEST);
      } else {
        $controller->setAttribute($annotation[0][0], $form);
      }
    }
  }
  
  public function create($model)
  {
    if (is_string($model)) {
      $model = MODEL($model);
    }
    
    $this->form  = $form = new Form_Object($model);
    $this->token = md5hash();
    $this->controller->setAttribute("token", $this->token);
    
    return $form;
  }
  
  public function get($token = null)
  {
    if ($token === null) {
      $token = $this->token;
    }
    
    $form = $this->storage->fetch($token);
    $this->controller->setAttribute("token", $token);
    
    if ($form !== null) {
      $this->form  = $form;
      $this->token = $token;
    }
    
    return $form;
  }
  
  public function clear($token = null)
  {
    if ($token === null) {
      $token = $this->token;
    }
    
    $this->storage->clear($token);
    $this->form = null;
  }
  
  public function shutdown($bus)
  {
    if ($this->form !== null && $this->token !== "") {
      $this->storage->store($this->token, $this->form, self::MAX_LIFETIME);
    }
  }
  
  public function applyPostValues(Form_Object $form, array $allowCols = array())
  {
    $values = $this->request->fetchPostValues();
    if (empty($values)) return $form;
    
    if (empty($allowCols)) {
      $allowCols = $form->getAllowColumns();
    }
    
    $mdlName = $form->getModel()->getName();
    $separator = Form_Object::NAME_SEPARATOR;
    
    foreach ($values as $key => $value) {
      if (strpos($key, $separator) === false) continue;
      list ($name, $colName) = explode($separator, $key);
      if ($name !== $mdlName) continue;
      
      if ($colName === "sbl_datetime" || $colName === "sbl_date") {
        list ($k, ) = each($value);
        if (!in_array($k, $allowCols, true)) continue;
      } elseif (!in_array($colName, $allowCols, true)) {
        continue;
      }
      
      if ($colName === "sbl_datetime") {
        foreach ($value as $key => $date) {
          if (!isset($date["second"])) $date["second"] = "00";
          if ($this->isCompleteDateValues($date)) {
            $form->set($key, $date["year"]   . "-" .
                             $date["month"]  . "-" .
                             $date["day"]    . " " .
                             $date["hour"]   . ":" .
                             $date["minute"] . ":" .
                             $date["second"]);
          } else {
            $form->set($key, null);
          }
        }
      } elseif ($colName === "sbl_date") {
        foreach ($value as $key => $date) {
          if ($this->isCompleteDateValues($date, false)) {
            $date = "{$date["year"]}-{$date["month"]}-{$date["day"]}";
            $form->set($key, $date);
          } else {
            $form->set($key, null);
          }
        }
      } else {
        $form->set($colName, $value);
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
      if (!isset($values[$key]) || $values[$key] === "") return false;
    }
    
    return true;
  }
}
