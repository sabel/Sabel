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
   * @var array
   */
  protected $attributes = array();
  
  /**
   * @var string
   */
  protected $method = "select";
  
  /**
   * @var array
   */
  protected $parameters = array();
  
  public function __construct($model)
  {
    if (is_string($model)) {
      $model = MODEL($model);
    }
    
    if (is_model($model)) {
      $model->autoReinit(false);
      $this->method = "select";
    } elseif ($model instanceof Sabel_DB_Join) {
      $model->getModel()->autoReinit(false);
      $this->method = "join";
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
    unset($this->parameters["page"]);
    $queryString = array("page={$page}");
    
    foreach ($this->parameters as $key => $val) {
      $queryString[] = "{$key}={$val}";
    }
    
    return implode("&", $queryString);
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
  
  public function setParameters(array $parameters)
  {
    $this->parameters = $parameters;
  }
  
  public function addParameter($key, $val)
  {
    $this->parameters[$key] = $val;
  }
  
  public function setMethod($method)
  {
    $this->method = $method;
    
    return $this;
  }
  
  public function build($limit, $page)
  {
    if (!is_numeric($page) || $page < 1) {
      $page = 1;
    }
    
    $model = $this->model;
    $attributes =& $this->attributes;
    
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
