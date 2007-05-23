<?php

/**
 * Filter plugin
 *
 * @category   Controller
 * @package    org.sabel.controller.plugin
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Plugin_Filter extends Sabel_Controller_Page_Plugin
{
  const ARG       = '$in';
  const STATEMENT = 'return (strstr($in, "filter"));';
  
  const BEFORE  = "before";
  const AFTER   = "after";
  
  const EXCLUDE_KEY_KEY = "EXCLUDE_KEY";
  const INCLUDE_KEY_KEY = "INCLUDE_KEY";
  
  public function onBeforeAction()
  {
    $values  = array_keys(get_object_vars($this->controller));
    $lamda   = create_function(self::ARG, self::STATEMENT);
    $filters = array_filter($values, $lamda);
                            
    asort($filters);
    
    foreach ($filters as $pos => $filterName) {
      $filter = $this->controller->$filterName;
      if (isset($filter[self::BEFORE])) {
        $this->doFilters($filter[self::BEFORE]);
      }
    }
  }
  
  public function onAfterAction()
  {
    $values  = array_keys(get_object_vars($this->controller));
    $lamda   = create_function(self::ARG, self::STATEMENT);
    $filters = array_filter($values, $lamda);
    
    asort($filters);
    
    foreach ($filters as $pos => $filterName) {
      $filter = $this->controller->$filterName;
      if (isset($filter[self::AFTER])) {
        $this->doFilters($filter[self::AFTER]);
      }
    }
  }
  
  protected function doFilters($filters)
  {
    $actionName = $this->controller->getAction();
    
    if (isset($filters[self::EXCLUDE_KEY]) && isset($filters[self::INCLUDE_KEY])) {
      $msg = "EXCLUDE_KEY and INCLUDE_KEY can't define in same time";
      throw new Sabel_Exception_Runtime($msg);
    }
    
    if (isset($filters[self::EXCLUDE_KEY])) {
      if (in_array($actionName, $filters[self::EXCLUDE_KEY])) {
        return false;
      } else {
        unset($filters[self::EXCLUDE_KEY]);
        $this->applyFilters($filters);
      }
    } elseif (isset($filters[self::INCLUDE_KEY])) {
      if (in_array($actionName, $filters[self::INCLUDE_KEY])) {
        unset($filters[self::INCLUDE_KEY]);
        $this->applyFilters($filters);
      }
    } else {
      $this->applyFilters($filters);
    }
  }
  
  protected function applyFilters($filters)
  {
    if (0 === count($filters)) return;
    
    foreach ($filters as $filter) {
      if ($this->controller->hasMethod($filter)) {
        if ($this->controller->$filter() === false) {
          Sabel_Context::log("apply filter " . $filter);
          break;
        } else {
        }
      } else {
        throw new Sabel_Exception_Runtime($filter . " is not found in any actions");
      }
    }
  }
}
