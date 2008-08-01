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
        
      case XML_CDATA_SECTION_NODE:
        $this->tagName = "#cdata";
        break;
    }
  }
  
  public function getDocument()
  {
    return $this->element->ownerDocument;
  }
  
  public function getRawElement()
  {
    return $this->element;
  }
  
  public function reproduce()
  {
    return new self($this->element->cloneNode(true));
  }
  
  public function getNodeType()
  {
    return $this->element->nodeType;
  }
  
  public function setNodeValue($value)
  {
    $this->element->nodeValue = $value;
  }
  
  public function getNodeValue()
  {
    return $this->element->nodeValue;
  }
  
  public function setValue($value)
  {
    $this->setNodeValue($value);
  }
  
  public function getValue()
  {
    return $this->getNodeValue();
  }
  
  public function setAttribute($name, $value)
  {
    $this->element->setAttribute($name, $value);
  }
  
  public function getAttribute($name)
  {
    return $this->element->getAttribute($name);
  }
  
  public function getAttributes()
  {
    return new Sabel_Xml_Attributes($this->element->attributes);
  }
  
  public function at($name, $value = null)
  {
    if ($value === null) {
      return $this->getAttribute($name);
    } else {
      $this->setAttribute($name, $value);
    }
  }
  
  public function hasAttribute($name)
  {
    return $this->element->hasAttribute($name);
  }
  
  public function appendChild($element)
  {
    if ($element instanceof self) {
      $element = $element->getRawElement();
    }
    
    $this->element->appendChild($element);
  }
  
  public function insertBefore($element)
  {
    if ($element instanceof self) {
      $element = $element->getRawElement();
    }
    
    $parent = $this->getParent()->getRawElement();
    $parent->insertBefore($element, $this->element);
  }
  
  public function insertAfter($element)
  {
    if ($next = $this->getNextSibling()) {
      $next->insertBefore($element);
    } else {
      $parent = $this->getParent();
      $parent->appendChild($element);
    }
  }
  
  public function __get($tagName)
  {
    return $this->getChildren($tagName);
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
  
  public function select($query)
  {
    $_exp = explode(" ", $query);
    
    if ($_exp[1] === ".") {
      $target = "../" . $this->tagName;
    } else {
      $target = str_replace(".", "/", $_exp[1]);
    }
    
    unset($_exp[0]);
    unset($_exp[1]);
    unset($_exp[2]);
    
    return $this->find($target . "[" . Sabel_Xml_Query::toXpath(implode(" ", $_exp)) . "]");
  }
  
  public function delete($query)
  {
    $elements = $this->select($query);
    
    if ($elements->length > 0) {
      foreach ($elements as $element) {
        $element->remove();
      }
    }
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
  
  public function getParent($target = null)
  {
    if ($target === null) {
      $parent = $this->element->parentNode;
      if ($parent->nodeType === XML_DOCUMENT_NODE) {
        return null;
      } else {
        return new self($parent);
      }
    } else {
      $element = $this;
      while (true) {
        if (($element = $element->getParent()) === null) {
          return null;
        } elseif ($element->tagName === $target) {
          return $element;
        }
      }
    }
  }
  
  public function getFirstChild()
  {
    if (($firstChild = $this->element->firstChild) === null) {
      return null;
    } elseif ($firstChild->nodeType === XML_ELEMENT_NODE) {
      return new self($firstChild);
    } else {
      $_firstChild = new self($firstChild);
      return $_firstChild->getNextSibling();
    }
  }
  
  public function getLastChild()
  {
    if (($lastChild = $this->element->lastChild) === null) {
      return null;
    } elseif ($lastChild->nodeType === XML_ELEMENT_NODE) {
      return new self($lastChild);
    } else {
      $_lastChild = new self($lastChild);
      return $_lastChild->getPreviousSibling();
    }
  }
  
  public function getPreviousSibling()
  {
    $element = $this->element;
    
    while (true) {
      if (($element = $element->previousSibling) === null) {
        return null;
      } elseif ($element->nodeType === XML_ELEMENT_NODE) {
        return new self($element);
      }
    }
  }
  
  public function getPreviousSiblings()
  {
    $elements = array();
    $element  = $this;
    
    while (true) {
      if (($element = $element->getPreviousSibling()) === null) {
        break;
      } else {
        $elements[] = $element;
      }
    }
    
    return new Sabel_Xml_Elements($elements);
  }
  
  public function getNextSibling()
  {
    $element = $this->element;
    
    while (true) {
      if (($element = $element->nextSibling) === null) {
        return null;
      } elseif ($element->nodeType === XML_ELEMENT_NODE) {
        return new self($element);
      }
    }
  }
  
  public function getNextSiblings()
  {
    $elements = array();
    $element  = $this;
    
    while (true) {
      if (($element = $element->getNextSibling()) === null) {
        break;
      } else {
        $elements[] = $element;
      }
    }
    
    return new Sabel_Xml_Elements($elements);
  }
  
  public function getSiblings()
  {
    return new Sabel_Xml_Elements(array_merge(
      $this->getPreviousSiblings()->reverse()->getRawElements(),
      $this->getNextSiblings()->getRawElements()
    ));
  }
  
  public function remove()
  {
    $parent = $this->getParent();
    
    if ($element = $parent->getRawElement()->removeChild($this->element)) {
      return new self($element);
    } else {
      return null;
    }
  }
  
  public function swap($element)
  {
    if (!$element instanceof self) {
      $element = new self($element);
    }
    
    $_self = $this->reproduce();
    $_elem = $element->reproduce();
    $this->insertAfter($_self);
    $element->insertAfter($_elem);
    
    $parent = $this->getParent()->getRawElement();
    $parent->replaceChild($element->getRawElement(), $this->getRawElement());
    $parent->replaceChild($_self->getRawElement(), $_elem->getRawElement());
  }
}
