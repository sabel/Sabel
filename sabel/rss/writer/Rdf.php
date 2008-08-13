<?php

/**
 * Sabel_Rss_Writer_Rdf
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Writer_Rdf extends Sabel_Rss_Writer_Abstract
{
  public function build(array $items)
  {
    $rdf = $this->createRdf();
    $this->createChannel($rdf, $items);
    $this->createImage($rdf);
    $this->createItems($rdf, $items);
    
    return $this->document->saveXML();
  }
  
  protected function createRdf()
  {
    $rdf = $this->document->createElement("rdf:RDF");
    $rdf->setAttribute("xmlns", "http://purl.org/rss/1.0/");
    $rdf->setAttribute("xmlns:rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
    $rdf->setAttribute("xmlns:content", "http://purl.org/rss/1.0/modules/content/");
    $rdf->setAttribute("xmlns:dc", "http://purl.org/dc/elements/1.1/");
    $rdf->setAttribute("xml:lang", $this->info["language"]);
    
    $this->document->appendChild($rdf);
    
    return $rdf;
  }
  
  protected function createChannel($rdf, $items)
  {
    $info    = $this->info;
    $dom     = $this->document;
    $channel = $dom->createElement("channel");
    
    if (isset($info["rss"])) {
      $channel->setAttribute("rdf:about", $info["rss"]);
    }
    
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
    
    if (isset($info["image"])) {
      $image = $dom->createElement("image");
      $image->setAttribute("rdf:resource", $info["image"]["uri"]);
      $channel->appendChild($image);
    }
    
    if (isset($info["updated"])) {
      $date = $dom->createElement("dc:date");
      $date->nodeValue = date("c", strtotime($info["updated"]));
      $channel->appendChild($date);
    }
    
    $seq = $dom->createElement("rdf:Seq");
    foreach ($items as $item) {
      $li = $dom->createElement("rdf:li");
      $li->setAttribute("rdf:resource", $item["uri"]);
      $seq->appendChild($li);
    }
    
    $items = $dom->createElement("items");
    $items->appendChild($seq);
    $channel->appendChild($items);
    
    $rdf->appendChild($channel);
  }
  
  protected function createImage($rdf)
  {
    $info = $this->info;
    $dom  = $this->document;
    
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
      
      $rdf->appendChild($image);
    }
  }
  
  protected function createItems($rdf, $items)
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
        $item->setAttribute("rdf:about", $_item["uri"]);
        $item->appendChild($link);
      }
      
      if (isset($_item["date"])) {
        $date = $dom->createElement("dc:date");
        $date->nodeValue = date("c", strtotime($_item["date"]));
        $item->appendChild($date);
      }
      
      if (isset($_item["summary"])) {
        $desc = $dom->createElement("description");
        $desc->nodeValue = htmlescape($_item["summary"]);
        $item->appendChild($desc);
      }
      
      if (isset($_item["content"])) {
        $content = $dom->createElement("content:encoded");
        $content->appendChild($dom->createCDATASection($_item["content"]));
        $item->appendChild($content);
      }
      
      $rdf->appendChild($item);
    }
  }
}
