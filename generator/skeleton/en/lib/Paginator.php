<?php

/**
 * Paginator
 *
 * @category   DB
 * @package    lib
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Paginator extends Sabel_Object
{
  /**
   * @var object Sabel_Db_Model or Sabel_Db_Join
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
  
  /**
   * @array
   */
  protected $defaultOrder = array();
  
  /**
   * @array
   */
  protected $orderColumns = array();
  
  public function __construct($model, $pageKey = "page")
  {
    if (is_string($model)) {
      $model = MODEL($model);
    }
    
    if (is_model($model)) {
      $model->autoReinit(false);
    } elseif ($model instanceof Sabel_Db_Join) {
      $model->getModel()->autoReinit(false);
      $this->isJoin = true;
    } else {
      $message = __METHOD__ . "() invalid instance.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    $this->model = $model;
    $this->attributes["pageKey"] = $pageKey;
  }
  
  public function __set($key, $value)
  {
    $this->attributes[$key] = $value;
  }
  
  public function __get($key)
  {
    if (isset($this->attributes[$key])) {
      return $this->attributes[$key];
    } else {
      return null;
    }
  }
  
  public function prev($text, $attrs = array())
  {
    return $this->createLink($text, $this->getUriQuery($this->viewer->getPrevious()), $attrs);
  }
  
  public function next($text, $attrs = array())
  {
    return $this->createLink($text, $this->getUriQuery($this->viewer->getNext()), $attrs);
  }
  
  public function getUriQuery($page)
  {
    $pageKey = $this->attributes["pageKey"];
    if (!isset($this->attributes["uriQuery"])) {
      return "{$pageKey}={$page}";
    } else {
      if (($query = $this->attributes["uriQuery"]) === "") {
        return "{$pageKey}={$page}";
      } else {
        return $query . "&{$pageKey}=" . $page;
      }
    }
  }
  
  public function setCondition($arg1, $arg2 = null)
  {
    $this->model->setCondition($arg1, $arg2);
    
    return $this;
  }
  
  public function setDefaultOrder($column, $mode)
  {
    $this->defaultOrder[$column] = $mode;
    
    return $this;
  }
  
  public function setOrderColumns($columns)
  {
    $this->orderColumns = $columns;
  }
  
  public function setMethod($method)
  {
    $this->method = $method;
    
    return $this;
  }
  
  public function build($limit, array $getValues = array())
  {
    $page = 1;
    $pageKey = $this->attributes["pageKey"];
    
    if (isset($getValues[$pageKey])) {
      $page = $getValues[$pageKey];
      if (!is_numeric($page) || $page < 1) $page = 1;
    }
    
    $model = $this->model;
    $attributes =& $this->attributes;
    
    $uriQuery = array();
    foreach ($getValues as $key => $val) {
      if ($key === $pageKey) {
        unset($getValues[$key]);
      } elseif (is_array($val)) {
        foreach ($val as $k => $v) {
          $uriQuery[] = urlencode("{$key}[{$k}]") . "=" . urlencode($v);
        }
      } else {
        $uriQuery[] = urlencode($key) . "=" . urlencode($val);
      }
    }
    
    $attributes["uriQuery"] = implode("&", $uriQuery);
    $count = ($this->isJoin) ? $model->getCount(null, false) : $model->getCount();
    
    $attributes["count"] = $count;
    $attributes["limit"] = $limit;
    $attributes["page"]  = $page;
    
    $pager = new Sabel_View_Pager($count, $limit);
    $pager->setPageNumber($page);
    $attributes["viewer"] = new Sabel_View_PageViewer($pager);
    
    if ($count === 0) {
      $attributes["offset"]  = 0;
      $attributes["results"] = array();
    } else {
      $offset = $pager->getSqlOffset();
      $this->_setOrderBy($getValues);
      $model->setLimit($limit);
      $model->setOffset($offset);
      
      $attributes["offset"]  = $offset;
      $attributes["results"] = $model->{$this->method}();
      
      if ($this->uri === null && $request = Sabel_Context::getRequest()) {
        $attributes["uri"] = get_uri_prefix() . "/" . $request->getUri();
      }
    }
    
    return $this;
  }
  
  protected function createLink($text, $query, $attrs)
  {
    $_attrs = "";
    if (is_array($attrs) && !empty($attrs)) {
      $tmp = array();
      foreach ($attrs as $attr => $value) {
        $tmp[] = $attr . '="' . $value . '"';
      }
      
      $_attrs = " " . implode(" ", $tmp);
    }
    
    $format = '<a%s href="%s?%s">%s</a>';
    return sprintf($format, $_attrs, $this->uri, $query, $text);
  }
  
  protected function _setOrderBy($getValues)
  {
    $orderValues  = array();
    $orderColumns = $this->orderColumns;
    
    if ($orderColumns !== false) {
      $oColNum = count($orderColumns);
      $pageKey = $this->attributes["pageKey"];
      
      if ($this->isJoin) {
        $columns = $this->model->getModel()->getColumnNames();
      } else {
        $columns = $this->model->getColumnNames();
      }
      
      foreach ($getValues as $key => $val) {
        if (preg_match('/^[A-Z]/', $key{0}) === 1 && strpos($key, "_") !== false) {
          list ($mname, $cname) = explode("_", $key, 2);
          $key = $mname . "." . $cname;
        } else {
          if (!in_array($key, $columns, true)) continue;
        }
        
        if ($oColNum === 0 || in_array($key, $orderColumns, true)) {
          $orderValues[$key] = $val;
        }
      }
    }
    
    if (empty($orderValues)) {
      if (empty($this->defaultOrder)) {
        return;
      } else {
        $orderValues = $this->defaultOrder;
      }
    }
    
    $model = $this->model;
    $orders = array();
    
    if (empty($orderColumns)) {
      foreach ($orderValues as $column => $order) {
        $order = strtolower($order);
        if ($order !== "asc" && $order !== "desc") $order = "asc";
        $model->setOrderBy($column, $order);
      }
    } else {
      foreach ($orderColumns as $column) {
        if (!isset($orderValues[$column])) continue;
        
        $order = strtolower($orderValues[$column]);
        if ($order !== "asc" && $order !== "desc") $order = "asc";
        $model->setOrderBy($column, $order);
      }
    }
  }
}
