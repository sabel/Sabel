<?php

/**
 * Paginate
 *
 * @category   DB
 * @package    lib
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Paginate extends Sabel_Object
{
  /**
   * @var object Sabel_DB_Model or Sabel_DB_Join
   */
  protected $model = null;
  
  /**
   * @var boolean
   */
  protected $isJoin = false;
  
  /**
   * @var string
   */
  protected $method = "select";
  
  /**
   * @var array
   */
  protected $attributes = array();
  
  public function __construct($model)
  {
    if (is_string($model)) {
      $model = MODEL($model);
    }
    
    if (is_model($model)) {
      $model->autoReinit(false);
    } elseif ($model instanceof Sabel_DB_Join) {
      $model->getModel()->autoReinit(false);
      $this->isJoin = true;
    } else {
      $message = __METHOD__ . "() invalid instance.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    $this->model = $model;
  }
  
  public function __get($key)
  {
    if (isset($this->attributes[$key])) {
      return $this->attributes[$key];
    } else {
      return null;
    }
  }
  
  public function getUriQuery($page)
  {
    if (!isset($this->attributes["uriQuery"])) {
      return "page={$page}";
    } else {
      $query = $this->attributes["uriQuery"];
      if ($query === "") {
        return "page={$page}";
      } else {
        return $query . "&page=" . $page;
      }
    }
  }
  
  public function setCondition($arg1, $arg2 = null)
  {
    $this->model->setCondition($arg1, $arg2);
    
    return $this;
  }
  
  public function setOrderBy($orderBy)
  {
    $this->model->setOrderBy($orderBy);
    
    return $this;
  }
  
  public function setMethod($method)
  {
    $this->method = $method;
    
    return $this;
  }
  
  public function build($limit, array $getValues = array())
  {
    if (isset($getValues["page"])) {
      $page = $getValues["page"];
      if (!is_numeric($page) || $page < 1) $page = 1;
    } else {
      $page = 1;
    }
    
    $model = $this->model;
    $attributes =& $this->attributes;
    
    $uriQuery = array();
    foreach ($getValues as $key => $val) {
      if ($key === "page") continue;
      $uriQuery[] = "{$key}=" . urlencode($val);
    }
    
    $attributes["uriQuery"] = implode("&", $uriQuery);
    
    if ($this->isJoin) {
      $count = $model->getCount(null, false);
    } else {
      $count = $model->getCount();
    }
    
    $attributes["count"] = $count;
    $attributes["limit"] = $limit;
    $attributes["page"]  = $page;
    
    $pager = Sabel_View_Pager::create($count, $limit);
    $pager->setPageNumber($page);
    $attributes["viewer"] = new Sabel_View_PageViewer($pager);
    
    if ($count === 0) {
      $attributes["offset"]  = 0;
      $attributes["results"] = array();
    } else {
      $offset = $pager->getSqlOffset();
      
      if ($this->isJoin) {
        $model->getModel()->setLimit($limit);
        $model->getModel()->setOffset($offset);
      } else {
        $model->setLimit($limit);
        $model->setOffset($offset);
      }
      
      $attributes["offset"]  = $offset;
      $attributes["results"] = $model->{$this->method}();
    }
    
    return $this;
  }
}
