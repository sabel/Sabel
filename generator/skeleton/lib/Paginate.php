<?php

/**
 * Paginate
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Paginate
{
  protected
    $manipulator = null,
    $attributes  = array(),
    $method      = "select";
  
  public function __construct(Sabel_DB_Manipulator $manipulator)
  {
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
    $manipulator->autoReinit(false);
    $count = $manipulator->getCount();
    
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
      $manipulator->setConstraint("limit",  $limit);
      $manipulator->setConstraint("offset", $offset);
      
      $attributes["offset"]  = $offset;
      $attributes["results"] = $manipulator->{$this->method}();
    }
    
    return $this;
  }
}
