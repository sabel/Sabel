<?php

/**
 * Sabel_Rss_Writer_Atom03
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Writer_Atom03 extends Sabel_Rss_Writer_Abstract
{
  public function build(array $items)
  {
    $feed = $this->createFeed();
    $this->createItems($feed, $items);
    
    return $this->document->saveXML();
  }
  
  protected function createFeed()
  {
    $info = $this->info;
    $dom  = $this->document;
    $feed = $dom->createElement("feed");
    $feed->setAttribute("version",  "0.3");
    $feed->setAttribute("xmlns",    "http://purl.org/atom/ns#");
    $feed->setAttribute("xmlns:dc", "http://purl.org/dc/elements/1.1/");
    $feed->setAttribute("xml:lang", $this->info["language"]);
    
    $title = $dom->createElement("title");
    $title->nodeValue = htmlescape($info["title"]);
    $feed->appendChild($title);
  
    $link = $dom->createElement("link");
    $link->setAttribute("rel",  "alternate");
    $link->setAttribute("type", "text/html");
    $link->setAttribute("href", $info["home"]);
    $feed->appendChild($link);
    
    if (isset($info["description"])) {
      $tagline = $dom->createElement("tagline");
      $tagline->nodeValue = htmlescape($info["description"]);
      $feed->appendChild($tagline);
    }
    
    if (isset($info["updated"])) {
      $modified = $dom->createElement("modified");
      $modified->nodeValue = date("c", strtotime($info["updated"]));
      $feed->appendChild($modified);
    }
    
    $dom->appendChild($feed);
    
    return $feed;
  }
  
  protected function createItems($feed, $items)
  {
    $dom = $this->document;
    
    foreach ($items as $_item) {
      $item = $dom->createElement("entry");
      
      if (isset($_item["title"])) {
        $title = $dom->createElement("title");
        $title->nodeValue = htmlescape($_item["title"]);
        $item->appendChild($title);
      }
      
      if (isset($_item["uri"])) {
        $link = $dom->createElement("link");
        $link->setAttribute("rel",  "alternate");
        $link->setAttribute("type", "text/html");
        $link->setAttribute("href", $_item["uri"]);
        $item->appendChild($link);
      }
      
      if (isset($_item["date"])) {
        $modified = $dom->createElement("modified");
        $modified->nodeValue = date("c", strtotime($_item["date"]));
        $item->appendChild($modified);
      }
      
      if (isset($_item["summary"])) {
        $summary = $dom->createElement("summary");
        $summary->setAttribute("type", "text/plain");
        $summary->nodeValue = htmlescape($_item["summary"]);
        $item->appendChild($summary);
      }
      
      if (isset($_item["content"])) {
        $content = $dom->createElement("content");
        $content->setAttribute("type", "text/html");
        $content->setAttribute("mode", "escaped");
        $content->appendChild($dom->createCDATASection($_item["content"]));
        $item->appendChild($content);
      }
      
      $feed->appendChild($item);
    }
  }
}
