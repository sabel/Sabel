<?php

/**
 * Sabel_Rss_Reader_Abstract
 *
 * @abstract
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Rss_Reader_Abstract extends Sabel_Object implements Iterator
{
  /**
   * @var Sabel_Xml_Element
   */
  protected $documentElement = null;
  
  /**
   * @var Sabel_Xml_Elements
   */
  protected $items = null;
  
  /**
   * @var int
   */
  protected $pointer = 0;
  
  /**
   * @param Sabel_Xml_Element $element
   */
  abstract public function __construct(Sabel_Xml_Element $element);
  
  /**
   * @return string
   */
  abstract public function getUri();
  
  /**
   * @return string
   */
  abstract public function getTitle();
  
  /**
   * @return string
   */
  abstract public function getDescription();
  
  /**
   * @return string
   */
  abstract public function getLastUpdated();
  
  /**
   * @return Sabel_ValueObject
   */
  abstract protected function toObject(Sabel_Xml_Element $element);
  
  /**
   * @return Sabel_Xml_Elements
   */
  public function getItemElements()
  {
    return $this->items;
  }
  
  /**
   * @return Sabel_ValueObject[]
   */
  public function getItems()
  {
    $items = array();
    foreach ($this->items as $i => $item) {
      $items[] = $this->toObject($item);
    }
    
    return $items;
  }
  
  public function current()
  {
    if (($element = $this->items[$this->pointer]) === null) {
      return null;
    } else {
      return $this->toObject($element);
    }
  }
  
  public function key()
  {
    return $this->pointer;
  }
  
  public function next()
  {
    $this->pointer++;
  }
  
  public function rewind()
  {
    $this->pointer = 0;
  }
  
  public function valid()
  {
    return ($this->pointer < $this->items->length);
  }
}
