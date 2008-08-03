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
  
  protected $config = array(
    "preserveWhiteSpace" => false,
    "formatOutput"       => true,
  );
  
  public function __construct(array $config = array())
  {
    $this->document = new DOMDocument();
    
    $cnf = array_merge($this->config, $config);
    $this->document->preserveWhiteSpace = $cnf["preserveWhiteSpace"];
    $this->document->formatOutput = $cnf["formatOutput"];
    $this->document->defaultNamespaces = array();
    $this->document->xpath = null;
    
    $this->config = $cnf;
  }
  
  public function getRawDocument()
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
    $document = $this->document;
    ($ignoreErrors) ? @$document->loadXML($xml) : $document->loadXML($xml);
    
    $xpath = new DOMXpath($document);
    preg_match_all('/xmlns=("[^"]+"|\'[^\']+\')/U', $xml, $matches);
    
    if (isset($matches[1])) {
      foreach ($matches[1] as $i => $namespace) {
        $_ns = substr($namespace, 1, -1);
        $_pf = "default" . $i;
        $document->defaultNamespaces[$_ns] = $_pf;
        $xpath->registerNamespace($_pf, $_ns);
      }
    }
    
    $document->xpath = $xpath;
    
    if ($document->documentElement) {
      return new Sabel_Xml_Element($document->documentElement);
    } else {
      return null;
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
  
  public function saveXML($path = null, $node = null)
  {
    $savedXml = $this->toXML($node);
    if ($path !== null) file_put_contents($path, $savedXml);
    
    return $savedXml;
  }
  
  public function toXML($node = null)
  {
    if ($node === null) {
      return $this->document->saveXML();
    } else {
      return $this->document->saveXML($node);
    }
  }
  
  public function createElement($tagName, $value = null)
  {
    if ($value === null) {
      return new Sabel_Xml_Element($this->document->createElement($tagName));
    } else {
      return new Sabel_Xml_Element($this->document->createElement($tagName, $value));
    }
  }
  
  public function createCDATA($text)
  {
    return new Sabel_Xml_Element($this->document->createCDATASection($text));
  }
  
  public function setDocumentElement($element)
  {
    if ($element instanceof Sabel_Xml_Element) {
      $element = $element->getRawElement();
    }
    
    $this->document->appendChild($element);
  }
}
