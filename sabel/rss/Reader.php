<?php

/**
 * Sabel_Rss_Reader
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Reader
{
  protected static $documents = array();
  
  public static function create($url, $xmlConfig = array())
  {
    $document = new Sabel_Xml_Document($xmlConfig);
    self::$documents[] = $document;
    
    $element = $document->loadXML(file_get_contents($url));
    
    if ($element === null) {
      // @todo exception?
    }
    
    switch (strtolower($element->tagName)) {
      case "rdf:rdf":
        return new Sabel_Rss_Reader_Rdf($element);
      
      case "rss":
        return new Sabel_Rss_Reader_Rss($element);
        break;
      
      case "feed":
        if ($element->getAttribute("version") === "0.3") {
          return new Sabel_Rss_Reader_Atom03($element);
        } else {
          return new Sabel_Rss_Reader_Atom10($element);
        }
        break;
      
      default:
        $message = "";
        throw new Sabel_Exception_Runtime($message);
    }
  }
}
