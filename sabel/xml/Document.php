<?php

/**
 * Sabel_Xml_Document
 *
 * @category   XML
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Xml_Document extends Sabel_Object
{
  /**
   * @var DOMDocument
   */
  protected $document = null;
  
  public function __construct()
  {
    $this->document = new DOMDocument();
  }
  
  public function getDocument()
  {
    return $this->document;
  }
  
  public function loadXML($xml, $ignoreErrors = false)
  {
    if ($ignoreErrors) {
      @$this->document->loadXML($xml);
    } else {
      $this->document->loadXML($xml);
    }
    
    $element = new Sabel_Xml_Element($this->document->firstChild);
    
    if ($element->getNodeType() === XML_ELEMENT_NODE) {
      return $element;
    } else {
      while (true) {
        $element = $element->getNextSibling();
        if ($element === null) {
          return null;
        } elseif ($element->getNodeType() === XML_ELEMENT_NODE) {
          return $element;
        }
      }
    }
  }
  
  public function loadHTML($html, $ignoreErrors = false)
  {
    if ($ignoreErrors) {
      @$this->document->loadHTML($html);
    } else {
      $this->document->loadHTML($html);
    }
    
    $doc = new Sabel_Xml_Element($this->document);
    return $doc->getChild("html");
  }
}
