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
class Sabel_Plugin_Filter extends Sabel_Plugin_Base
{
  const ARG       = '$in';
  const STATEMENT = 'return (strstr($in, "filter"));';
  
  const BEFORE  = "before";
  const AFTER   = "after";
  
  const EXCLUDE_KEY = "exclude";
  const INCLUDE_KEY = "include";
  
  public function onBeforeAction()
  {
    $values  = array_keys(get_object_vars($this->controller));
    $lamda   = create_function(self::ARG, self::STATEMENT);
    $filters = array_filter($values, $lamda);
                            
    asort($filters);
    
    foreach ($filters as $filterName) {
      $filter = $this->controller->$filterName;
      if (isset($filter[self::BEFORE])) {
        if (redirected($this->doFilters($filter[self::BEFORE]))) {
          return false;
        }
      }
    }
  }
  
  public function onAfterAction()
  {
    $values  = array_keys(get_object_vars($this->controller));
    $lamda   = create_function(self::ARG, self::STATEMENT);
    $filters = array_filter($values, $lamda);
    
    asort($filters);
    
    foreach ($filters as $filterName) {
      $filter = $this->controller->$filterName;
      if (isset($filter[self::AFTER])) {
        if (redirected($this->doFilters($filter[self::AFTER]))) {
          return false;
        }
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
        return $this->applyFilters($filters);
      }
    } elseif (isset($filters[self::INCLUDE_KEY])) {
      if (in_array($actionName, $filters[self::INCLUDE_KEY])) {
        unset($filters[self::INCLUDE_KEY]);
        return $this->applyFilters($filters);
      }
    } else {
      return $this->applyFilters($filters);
    }
  }
  
  protected function applyFilters($filters)
  {
    if (0 === count($filters)) return;
    
    foreach ($filters as $filter) {
      if ($this->controller->hasMethod($filter)) {
        Sabel_Context::log("apply filter " . $filter);
        $result = $this->controller->$filter();
        
        if ($result === false) {
          break;
        } elseif (redirected($result)) {
          return Sabel_Controller_Page::REDIRECTED;
          break;
        }
      } else {
        throw new Sabel_Exception_Runtime($filter . " is not found in any actions");
      }
    }
  }
}
