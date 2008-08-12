<?php

/**
 * Sabel_Rss_Reader_Rdf
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Reader_Rdf extends Sabel_Rss_Reader_Abstract
{
  /**
   * @var Sabel_Xml_Element
   */
  protected $channel = null;
  
  public function __construct(Sabel_Xml_Element $element)
  {
    $this->documentElement = $element;
    $this->channel = $element->getChild("channel", $element->getAttribute("xmlns"));
    $this->items = $element->getChildren("item", $element->getAttribute("xmlns"));
  }
  
  /**
   * @return string
   */
  public function getUri()
  {
    if (($link = $this->channel->getChild("link")) === null) {
      return "";
    } else {
      return $link->getNodeValue();
    }
  }
  
  /**
   * @return string
   */
  public function getTitle()
  {
    if (($title = $this->channel->getChild("title")) === null) {
      return "";
    } else {
      return $title->getNodeValue();
    }
  }
  
  /**
   * @return string
   */
  public function getDescription()
  {
    if (($desc = $this->channel->getChild("description")) === null) {
      return "";
    } else {
      return $desc->getNodeValue();
    }
  }
  
  /**
   * @return string
   */
  public function getLastUpdated()
  {
    if (($date = $this->channel->getChild("dc:date")) === null) {
      if (isset($this->items[0])) {
        $date = $this->items[0]->getChild("dc:date");
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
  
  /**
   * @return stdClass
   */
  public function toObject(Sabel_Xml_Element $element)
  {
    $object = new stdClass();
    
    if ($title = $element->getChild("title")) {
      $object->title = $title->getNodeValue();
    } else {
      $object->title = "";
    }
    
    if ($link = $element->getChild("link")) {
      $object->uri = $link->getNodeValue();
    } else {
      $object->uri = "";
    }
    
    if ($desc = $element->getChild("description")) {
      $object->description = $desc->getNodeValue();
    } else {
      $object->description = "";
    }
    
    if ($date = $element->getChild("dc:date")) {
      $object->date = date("Y-m-d H:i:s", strtotime($date->getNodeValue()));
    } else {
      $object->date = "";
    }
    
    if ($content = $element->getChild("content:encoded")) {
      $object->content = $content->getNodeValue();
    } else {
      $object->content = "";
    }
    
    return $object;
  }
}
