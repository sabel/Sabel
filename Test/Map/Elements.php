<?php

/**
 * testcase for sabel.map.Elements
 * using classes: sabel.map.Element
 *
 * @category  Map
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Map_Elements extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Map_Elements");
  }
  
  public function testHas()
  {
    $options = array();
    $options["defaults"][":controller"] = "index";
    $options["defaults"][":action"] = "index";
    
    $cElement = new Sabel_Map_Element(":controller", $options);
    $aElement = new Sabel_Map_Element(":action", $options);
    
    $elements = new Sabel_Map_Elements();
    $elements->add($cElement);
    $elements->add($aElement);
    
    $this->assertTrue($elements->has("action"));
    $this->assertTrue($elements->has("controller"));
    $this->assertFalse($elements->has("hoge"));
    
    $this->assertTrue($elements->hasAt(0));
    $this->assertTrue($elements->hasAt(1));
    $this->assertFalse($elements->hasAt(2));
  }
  
  public function testGet()
  {
    $options = array();
    $options["defaults"][":controller"] = "index";
    $options["defaults"][":action"] = "index";
    
    $cElement = new Sabel_Map_Element(":controller", $options);
    $aElement = new Sabel_Map_Element(":action", $options);
    
    $elements = new Sabel_Map_Elements();
    $elements->add($cElement);
    $elements->add($aElement);
    
    $this->assertTrue(is_object($elements->getElement("action")));
    $this->assertTrue(is_object($elements->getElement("controller")));
    $this->assertNull($elements->getElement("hoge"));
    
    $this->assertTrue(is_object($elements->getElementAt(0)));
    $this->assertTrue(is_object($elements->getElementAt(1)));
    $this->assertNull($elements->getElementAt(2));
  }
  
  public function testHasDefaults()
  {
    $options = array();
    $options["defaults"][":controller"] = "index";
    $options["defaults"][":action"] = "index";
    
    $cElement = new Sabel_Map_Element(":controller", $options);
    $aElement = new Sabel_Map_Element(":action", $options);
    
    $elements = new Sabel_Map_Elements();
    $elements->add($cElement);
    $elements->add($aElement);
    
    $this->assertTrue($elements->hasDefaults());
    
    $options = array("defaults" => array());
    $cElement = new Sabel_Map_Element(":controller", $options);
    $aElement = new Sabel_Map_Element(":action", $options);
    
    $elements = new Sabel_Map_Elements();
    $elements->add($cElement);
    $elements->add($aElement);
    
    $this->assertFalse($elements->hasDefaults());
  }
  
  public function testArrayElements()
  {
    $options = array();
    $options["defaults"][":controller"] = "index";
    $options["defaults"][":action"] = "index";
    
    $cElement = new Sabel_Map_Element(":controller", $options);
    $aElement = new Sabel_Map_Element(":action", $options);
    
    $elements = new Sabel_Map_Elements();
    $elements->add($cElement);
    $elements->add($aElement);
    
    $arrayElements = $elements->getNamedElements();
    $this->assertTrue($arrayElements["controller"] instanceof Sabel_Map_Element);
    $this->assertTrue($arrayElements["action"] instanceof Sabel_Map_Element);
    $this->assertFalse(isset($arrayElements["hoge"]));
  }
  
  public function testTypeOfElement()
  {
    $options = array();
    $options["defaults"][":uri"] = array("a", "b", "c");
    
    $element = new Sabel_Map_Element(":uri[]", $options);
    $this->assertTrue($element->isTypeOf(Sabel_Map_Element::TYPE_ARRAY));
  }
}
