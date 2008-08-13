<?php

/**
 * Sabel_Rss_Reader_Atom10
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Reader_Atom10 extends Sabel_Rss_Reader_Abstract
{
  public function __construct(Sabel_Xml_Element $element)
  {
    $this->documentElement = $element;
    $this->items = $element->getChildren("entry");
  }
  
  /**
   * @return string
   */
  public function getUri()
  {
    if (($link = $this->documentElement->getChild("link")) === null) {
      return null;
    } else {
      return $link->getAttribute("href");
    }
  }
  
  /**
   * @return string
   */
  public function getTitle()
  {
    if (($title = $this->documentElement->getChild("title")) === null) {
      return null;
    } else {
      return $title->getNodeValue();
    }
  }
  
  /**
   * @return string
   */
  public function getDescription()
  {
    if (($subtitle = $this->documentElement->getChild("subtitle")) === null) {
      return null;
    } else {
      return $subtitle->getNodeValue();
    }
  }
  
  /**
   * @return string
   */
  public function getLastUpdated()
  {
    if (($date = $this->documentElement->getChild("updated")) === null) {
      if (isset($this->items[0])) {
        $date = $this->items[0]->getChild("updated");
      } else {
        return null;
      }
    }
    
    return date("Y-m-d H:i:s", strtotime($date->getNodeValue()));
  }
  
  /**
   * @return Sabel_ValueObject
   */
  protected function toObject(Sabel_Xml_Element $element)
  {
    $object = new Sabel_ValueObject();
    
    if ($title = $element->getChild("title")) {
      $object->title = $title->getNodeValue();
    }
    
    if ($link = $element->getChild("link")) {
      $object->uri = $link->getAttribute("href");
    }
    
    if ($summary = $element->getChild("summary")) {
      $object->description = $summary->getNodeValue();
    }
    
    if ($date = $element->getChild("updated")) {
      $object->date = date("Y-m-d H:i:s", strtotime($date->getNodeValue()));
    }
    
    if ($content = $element->getChild("content")) {
      $object->content = $content->getNodeValue();
    }
    
    return $object;
  }
}
