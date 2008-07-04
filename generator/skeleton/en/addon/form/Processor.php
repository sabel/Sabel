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
  /**
   * @var Sabel_Request
   */
  protected $request = null;
  
  public function execute($bus)
  {
    $this->request = $bus->get("request");
    $bus->get("controller")->setAttribute("form", $this);
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
          if ($this->isDatetimeValuesCompleted($date)) {
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
          if ($this->isDatetimeValuesCompleted($date, false)) {
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
  
  private function isDatetimeValuesCompleted($values, $isDatetime = true)
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
