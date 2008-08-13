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
    
    return null;
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
    if (($tagline = $this->documentElement->getChild("tagline")) === null) {
      return null;
    } else {
      return $tagline->getNodeValue();
    }
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
   * @return Sabel_ValueObject
   */
  protected function toObject(Sabel_Xml_Element $element)
  {
    $object = new Sabel_ValueObject();
    
    if ($title = $element->getChild("title")) {
      $object->title = $title->getNodeValue();
    }
    
    $links = $element->getChildren("link");
    foreach ($links as $link) {
      if ($link->getAttribute("rel") === "alternate") {
        $object->uri = $link->getAttribute("href");
        break;
      }
    }
    
    if ($summary = $element->getChild("summary")) {
      $object->description = $summary->getNodeValue();
    }
    
    if ($date = $element->getChild("modified")) {
      $object->date = date("Y-m-d H:i:s", strtotime($date->getNodeValue()));
    }
    
    if ($content = $element->getChild("content")) {
      $object->content = $content->getNodeValue();
    }
    
    return $object;
  }
}
