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
  
  /**
   * @var string
   */
  protected $docPath = "";
  
  /**
   * @var string
   */
  protected $docType = "XML";
  
  /**
   * @var array
   */
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
  
  public function load($type, $path, $ignoreErrors = false)
  {
    $docTypes = array("XML", "HTML");
    
    if (!in_array($type, $docTypes, true)) {
      $message = __METHOD__ . "() invalid document type.";
      throw new Sabel_Exception_Runtime($message);
    } elseif (!is_file($path)) {
      $message = __METHOD__ . "() '{$path}' is not a file.";
      throw new Sabel_Exception_Runtime($message);
    } elseif (!is_readable($path)) {
      $message = __METHOD__ . "() '{$path}' is not readable.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    $this->docPath = $path;
    $this->docType = $type;
    
    if ($type === "XML") {
      return $this->loadXML(file_get_contents($path), $ignoreErrors);
    } elseif ($type === "HTML") {
      return $this->loadHTML(file_get_contents($path), $ignoreErrors);
    }
  }
  
  public function save($path = null)
  {
    if ($path === null) {
      $path = $this->docPath;
    }
    
    if ($this->docType === "XML") {
      return $this->saveXML($path);
    } else {
      return $this->saveHTML($path);
    }
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
  
  public function saveXML($path, $node = null)
  {
    $xml = $this->toXML($node);
    file_put_contents($path, $xml);
    
    return $xml;
  }
  
  public function toXML($node = null)
  {
    if ($node === null) {
      return $this->document->saveXML();
    } else {
      return $this->document->saveXML($node);
    }
  }
  
  public function saveHTML($path)
  {
    $html = $this->toHTML();
    file_put_contents($path, $html);
    
    return $html;
  }
  
  public function toHTML()
  {
    return $this->document->saveHTML();
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
