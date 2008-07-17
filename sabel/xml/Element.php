<?php

/**
 * Sabel_Xml_Element
 *
 * @category   XML
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Xml_Element extends Sabel_Object
{
  /**
   * @var string
   */
  public $tagName = "";
  
  /**
   * @var DOMNode
   */
  protected $element = null;
  
  public function __construct(DOMNode $element)
  {
    $this->element = $element;
    
    switch ($element->nodeType) {
      case XML_ELEMENT_NODE:
        $this->tagName = $element->tagName;
        break;
      
      case XML_ELEMENT_NODE:
        $this->tagName = "#text";
        break;
    }
  }
  
  public function getDocument()
  {
    return $this->element->ownerDocument;
  }
  
  public function getElement()
  {
    return $this->element;
  }
  
  public function getNodeType()
  {
    return $this->element->nodeType;
  }
  
  public function getContent()
  {
    return $this->element->textContent;
  }
  
  public function getAttribute($name)
  {
    return $this->element->getAttribute($name);
  }
  
  public function hasAttribute($name)
  {
    return $this->element->hasAttribute($name);
  }
  
  public function __get($tagName)
  {
    $elements = $this->getChildren($tagName);
    
    switch ($elements->length) {
      case 0:
        return null;
      
      case 1:
        return $elements->getElementAt(0);

      default:
        return $elements;
    }
  }
  
  public function find($query)
  {
    $element = $this->element;
    if ($element->nodeType === XML_DOCUMENT_NODE ||
        $element->nodeType === XML_HTML_DOCUMENT_NODE) {
      $xpath = new DOMXPath($element);
    } else {
      $xpath = new DOMXPath($element->ownerDocument);
    }
    
    $nodes = $xpath->evaluate($query, $element);
    
    $elements = array();
    if ($nodes->length > 0) {
      for ($i = 0, $c = $nodes->length; $i < $c; $i++) {
        $element = $nodes->item($i);
        if ($element->nodeType === XML_ELEMENT_NODE) {
          $elements[] = new self($element);
        }
      }
    }
    
    return new Sabel_Xml_Elements($elements);
  }
  
  public function getChild($tagName)
  {
    return $this->find($tagName)->getElementAt(0);
  }
  
  public function getChildren($tagName = null)
  {
    if ($tagName === null) {
      $elements = array();
      $nodes = $this->element->childNodes;
      if ($nodes->length > 0) {
        for ($i = 0, $c = $nodes->length; $i < $c; $i++) {
          $element = $nodes->item($i);
          if ($element->nodeType === XML_ELEMENT_NODE) {
            $elements[] = new self($element);
          }
        }
      }
      
      return new Sabel_Xml_Elements($elements);
    } else {
      return $this->find($tagName);
    }
  }
  
  public function getParent()
  {
    $parent = $this->element->parentNode;
    
    if ($parent->nodeType === XML_DOCUMENT_NODE) {
      return null;
    } else {
      return new self($parent);
    }
  }
  
  public function getFirstChild()
  {
    $firstChild = $this->element->firstChild;
    
    if ($firstChild === null) {
      return null;
    } elseif ($firstChild->nodeType === XML_ELEMENT_NODE) {
      return new self($firstChild);
    } else {
      $_firstChild = new self($firstChild);
      return $_firstChild->getNextSibling();
    }
  }
  
  public function getPreviousSibling()
  {
    $element = $this->element;
    
    while (true) {
      $element = $element->previousSibling;
      if ($element === null) {
        return null;
      } elseif ($element->nodeType === XML_ELEMENT_NODE) {
        return new self($element);
      }
    }
  }
  
  public function getNextSibling()
  {
    $element = $this->element;
    
    while (true) {
      $element = $element->nextSibling;
      if ($element === null) {
        return null;
      } elseif ($element->nodeType === XML_ELEMENT_NODE) {
        return new self($element);
      }
    }
  }
}
