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
  protected $controller = null;
  
  public function onBeforeAction($controller)
  {
    $this->controller = $controller;
    $filters = array_filter(array_keys(get_object_vars($controller)),
                            create_function('$in', 'return (strstr($in, "filter"));'));
                            
    asort($filters);
    foreach ($filters as $pos => $filterName) {
      $filter = $this->controller->$filterName;
      if (isset($filter["before"])) {
        $this->doFilters($filter["before"]);
      }
    }
  }
  
  public function onAfterAction($controller)
  {
    $this->controller = $controller;
    $filters = array_filter(array_keys(get_object_vars($controller)),
                            create_function('$in', 'return (strstr($in, "filter"));'));
    
    asort($filters);
    foreach ($filters as $pos => $filterName) {
      $filter = $this->controller->$filterName;
      if (isset($filter["after"])) {
        $this->doFilters($filter["after"]);
      }
    }
  }
  
  protected function doFilters($filters)
  {
    $actionName = $this->controller->getAction();
    
    if (isset($filters["exclude"]) && isset($filters["include"])) {
      throw new Sabel_Exception_Runtime("exclude and include can't define in same time");
    }
    
    if (isset($filters["exclude"])) {
      if (in_array($actionName, $filters["exclude"])) {
        return false;
      } else {
        unset($filters["exclude"]);
        $this->applyFilters($filters);
      }
    } elseif (isset($filters["include"])) {
      if (in_array($actionName, $filters["include"])) {
        unset($filters["include"]);
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