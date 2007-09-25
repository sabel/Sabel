<?php

/**
 * Paginate
 *
 * @category   DB
 * @package    lib.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Paginate extends Sabel_Object
{
  protected
    $manipulator = null,
    $isJoin      = false,
    $attributes  = array(),
    $method      = "select";
  
  public function __construct($manipulator)
  {
    if ($manipulator instanceof Sabel_DB_Manipulator) {
      $manipulator->autoReinit(false);
      $this->method = "select";
    } elseif ($manipulator instanceof Sabel_DB_Join) {
      $manipulator->getManipulator()->autoReinit(false);
      $this->method = "join";
      $this->isJoin = true;
    } else {
      throw new Exception("Paginate::__construct() invalid instance.");
    }
    
    $this->manipulator = $manipulator;
  }
  
  public function __get($key)
  {
    if (isset($this->attributes[$key])) {
      return $this->attributes[$key];
    } else {
      return null;
    }
  }
  
  public function setMethod($method)
  {
    $this->method = $method;
  }
  
  public function build($limit, $page)
  {
    if (!is_numeric($page) || $page < 1) {
      $page = 1;
    }
    
    $attributes =& $this->attributes;
    $manipulator = $this->manipulator;
    
    if ($this->isJoin) {
      $count = $manipulator->getCount(null, false);
    } else {
      $count = $manipulator->getCount();
    }
    
    $attributes["count"] = $count;
    $attributes["limit"] = $limit;
    $attributes["page"]  = $page;
    
    $pager = Sabel_View_Pager::create($count, $limit);
    $pager->setPageNumber($page);
    $attributes["pager"] = $pager;
    
    if ($count === 0) {
      $attributes["offset"]  = 0;
      $attributes["results"] = false;
    } else {
      $offset = $pager->getSqlOffset();
      
      if ($this->isJoin) {
        $manip = $manipulator->getManipulator();
        $manip->setConstraint("limit",  $limit);
        $manip->setConstraint("offset", $offset);
      } else {
        $manipulator->setConstraint("limit",  $limit);
        $manipulator->setConstraint("offset", $offset);
      }
      
      $attributes["offset"]  = $offset;
      $attributes["results"] = $manipulator->{$this->method}();
    }
    
    return $this;
  }
}
