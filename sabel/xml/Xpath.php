<?php

/**
 * Sabel_Xml_Xpath
 *
 * @category   XML
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Xml_Xpath extends Sabel_Object
{
  protected static $xpaths = array();
  
  public static function create(DOMDocument $document)
  {
    foreach (self::$xpaths as $array) {
      if ($array["document"]->isSameNode($document)) {
        return $array["xpath"];
      }
    }
    
    $xpath = new DOMXPath($document);
    if ($element = $document->documentElement) {
      if ($namespace = $element->lookupNamespaceURI(null)) {
        $xpath->registerNamespace("_", $namespace);
      }
    }
    
    self::$xpaths[] = array("document" => $document, "xpath" => $xpath);
    
    return $xpath;
  }
}
