<?php

/**
 * Sabel_Rss_Reader_Atom03
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Reader_Atom03 extends Sabel_Rss_Reader_Abstract
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
    $links = $this->documentElement->getChildren("link");
    
    foreach ($links as $link) {
      if ($link->getAttribute("rel") === "alternate") {
        return $link->getAttribute("href");
      }
    }
    
    return "";
  }
  
  /**
   * @return string
   */
  public function getTitle()
  {
    return $this->documentElement->getChild("title")->getNodeValue();
  }
  
  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->documentElement->getChild("tagline")->getNodeValue();
  }
  
  /**
   * @return string
   */
  public function getLastUpdated()
  {
    if (($date = $this->documentElement->getChild("modified")) === null) {
      if (isset($this->items[0])) {
        $date = $this->items[0]->getChild("modified");
      } else {
        return null;
      }
    }
    
    return date("Y-m-d H:i:s", strtotime($date->getNodeValue()));
  }
  
  /**
   * @return stdClass[]
   */
  public function getItems()
  {
    $items = array();
    foreach ($this->items as $i => $item) {
      $items[] = $this->toObject($item);
    }
    
    return $items;
  }
  
  protected function toObject(Sabel_Xml_Element $element)
  {
    $object = new stdClass();
    
    if ($title = $element->getChild("title")) {
      $object->title = $title->getNodeValue();
    } else {
      $object->title = "";
    }
    
    $uri = "";
    $links = $element->getChildren("link");
    
    foreach ($links as $link) {
      if ($link->getAttribute("rel") === "alternate") {
        $uri = $link->getAttribute("href");
        break;
      }
    }
    
    $object->uri = $uri;
    
    if ($summary = $element->getChild("summary")) {
      $object->description = $summary->getNodeValue();
    } else {
      $object->description = "";
    }
    
    if ($date = $element->getChild("modified")) {
      $object->date = date("Y-m-d H:i:s", strtotime($date->getNodeValue()));
    } else {
      $object->date = "";
    }
    
    if ($content = $element->getChild("content")) {
      $object->content = $content->getNodeValue();
    } else {
      $object->content = "";
    }
    
    return $object;
  }
}
