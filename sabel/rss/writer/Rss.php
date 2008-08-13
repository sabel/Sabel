<?php

/**
 * Sabel_Rss_Writer_Rss
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Writer_Rss extends Sabel_Rss_Writer_Abstract
{
  public function build(array $items)
  {
    $rss = $this->createRss();
    $this->createChannel($rss);
    $this->createItems($rss, $items);
    
    return $this->document->saveXML();
  }
  
  protected function createRss()
  {
    $rss = $this->document->createElement("rss");
    $rss->setAttribute("version", "2.0");
    $rss->setAttribute("xmlns:dc", "http://purl.org/dc/elements/1.1/");
    $rss->setAttribute("xmlns:content", "http://purl.org/rss/1.0/modules/content/");
    $rss->setAttribute("xml:lang", $this->info["language"]);
    
    $this->document->appendChild($rss);
    
    return $rss;
  }
  
  protected function createChannel($rss)
  {
    $info    = $this->info;
    $dom     = $this->document;
    $channel = $dom->createElement("channel");
    
    $title = $dom->createElement("title");
    $title->nodeValue = htmlescape($info["title"]);
    $channel->appendChild($title);
  
    $link = $dom->createElement("link");
    $link->nodeValue = $info["home"];
    $channel->appendChild($link);
    
    if (isset($info["description"])) {
      $desc = $dom->createElement("description");
      $desc->nodeValue = htmlescape($info["description"]);
      $channel->appendChild($desc);
    }
    
    if (isset($info["updated"])) {
      $date = $dom->createElement("lastBuildDate");
      $date->nodeValue = date("r", strtotime($info["updated"]));
      $channel->appendChild($date);
    }
    
    if (isset($info["image"])) {
      $_image = $info["image"];
      $url = $dom->createElement("url");
      $url->nodeValue = $_image["uri"];
      
      $title = $dom->createElement("title");
      if (isset($_image["title"])) {
        $title->nodeValue = htmlescape($_image["title"]);
      } else {
        $title->nodeValue = $info["title"];
      }
      
      $link = $dom->createElement("link");
      if (isset($_image["link"])) {
        $link->nodeValue = $_image["link"];
      } else {
        $link->nodeValue = $info["home"];
      }
      
      $image = $dom->createElement("image");
      $image->appendChild($title);
      $image->appendChild($url);
      $image->appendChild($link);
      $channel->appendChild($image);
    }
    
    $rss->appendChild($channel);
  }
  
  protected function createItems($rss, $items)
  {
    $dom = $this->document;
    
    foreach ($items as $_item) {
      $item = $dom->createElement("item");
      
      if (isset($_item["title"])) {
        $title = $dom->createElement("title");
        $title->nodeValue = htmlescape($_item["title"]);
        $item->appendChild($title);
      }
      
      if (isset($_item["uri"])) {
        $link = $dom->createElement("link");
        $link->nodeValue = $_item["uri"];
        $item->appendChild($link);
      }
      
      if (isset($_item["date"])) {
        $pubDate = $dom->createElement("pubDate");
        $pubDate->nodeValue = date("r", strtotime($_item["date"]));
        $item->appendChild($pubDate);
      }
      
      $content = $_item["content"];
      if (isset($_item["summary"])) {
        $content = $_item["summary"];
      }
      
      $desc = $dom->createElement("description");
      $desc->appendChild($dom->createCDATASection($content));
      $item->appendChild($desc);
      $rss->appendChild($item);
    }
  }
}
