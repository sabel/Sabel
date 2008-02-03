<?php

/**
 * Sabel_View_Pager
 *
 * @category   Template
 * @package    org.sabel.template
 * @author     Hamanaka Kazuhiro <hamanaka.kazuhiro@sabel.jp>
 * @copyright  2002-2006 Hamanaka Kazuhiro <hamanaka.kazuhiro@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Pager extends Sabel_Object
{
  private static $instance = null;
  
  private $pageItem     = 25;
  private $pageNumber   = 0;
  private $numberOfItem = 0;
  
  private function __construct($count, $limit)
  {
    if (isset($count)) $this->setNumberOfItem($count);
    if (isset($limit)) $this->setLimit($limit);
  }
  
  public static function create($count = null, $limit = null)
  {
    if (self::$instance === null || isset($count, $limit))
      self::$instance = new self($count, $limit);
    return self::$instance;
  }
  
  public function __set($key, $value)
  {
    $method = "set" . ucfirst($key);
    if (method_exists($this, $method)) $this->$method($value);
  }
  
  public function __get($key)
  {
    $method = "get" . ucfirst($key);
    if (method_exists($this, $method)) return $this->$method();
  }
  
  public function __call($key, $args)
  {
    $method = "set" . ucfirst($key);
    if (method_exists($this, $method)) $this->$method($args[0]);
  }
  
  public function setNumberOfItem($item)
  {
    if ($item < 0 || !is_numeric($item)) {
      throw new Sabel_Exception_InvalidArgument("invalid number of item: " . $item);
    }
    
    $this->numberOfItem =(int) $item;
  }
  
  public function getNumberOfItem()
  {
    return $this->numberOfItem;
  }
  
  public function setLimit($limit)
  {
    $this->pageItem =(int) max($limit, 1);
  }
  
  public function getLimit()
  {
    return $this->pageItem;
  }
  
  public function setPageNumber($page)
  {
    $this->pageNumber =(int) max($page, 1);
  }
  
  public function getPageNumber()
  {
    return (int) max(min($this->getTotalPageNumber(), $this->pageNumber), 1);
  }
  
  public function getTotalPageNumber()
  {
    return (int) ceil(max($this->numberOfItem / $this->pageItem, 1));
  }
  
  public function getSqlOffset()
  {
    return (int) floor($this->pageItem * ($this->getPageNumber() - 1));
  }
}
