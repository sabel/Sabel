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
  
  public function setEncoding($encoding)
  {
    $this->document->encoding = $encoding;
    
    return $this;
  }
  
  public function getEncoding()
  {
    return $this->document->encoding;
  }
  
  public function setVersion($version)
  {
    $this->document->xmlVersion = $version;
  }
  
  public function getVersion()
  {
    return $this->document->xmlVersion;
  }
  
  public function loadXML($xml, $ignoreErrors = false)
  {
    if ($ignoreErrors) {
      @$this->document->loadXML($xml);
    } else {
      $this->document->loadXML($xml);
    }
    
    return new Sabel_Xml_Element($this->document->documentElement);
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
  
  public function saveXML($node = null)
  {
    $this->document->formatOutput = true;
    
    if ($node === null) {
      return $this->document->saveXML();
    } else {
      return $this->document->saveXML($node);
    }
  }
  
  public function createElement($tagName, $nodeValue = null, $attrs = array())
  {
    if ($nodeValue === null) {
      $element = $this->document->createElement($tagName);
    } else {
      $element = $this->document->createElement($tagName, $nodeValue);
    }
    
    foreach ($attrs as $name => $value) {
      $element->setAttribute($name, $value);
    }
    
    return new Sabel_Xml_Element($element);
  }
  
  public function setDocumentElement($element)
  {
    if ($element instanceof Sabel_Xml_Element) {
      $element = $element->getElement();
    }
    
    $this->document->appendChild($element);
  }
}
