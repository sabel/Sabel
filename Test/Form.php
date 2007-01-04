<?php

/**
 * test case for SabelPager
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Form extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Form");
  }
  
  public function testSelect()
  {
    $select = new Sabel_Form_Select('name');
    $select->addOption(new Sabel_Form_Option('optElem', 'value'));
    $expOne = '<select name="name"><option value="value">optElem</option></select>';
    $this->assertEquals($expOne, $select->toHtml(true));
    
    $select->addOption(new Sabel_Form_Option('optElem', 'value'));
    $expTwo  = '<select name="name">';
    $expTwo .=   '<option value="value">optElem</option>';
    $expTwo .=   '<option value="value">optElem</option>';
    $expTwo .= '</select>';
    $this->assertEquals($expTwo, $select->toHtml(true));
  }
  
  public function testSelectMultiple()
  {
    $select = new Sabel_Form_Select('name');
    $this->assertFalse($select->isMultiple());
    $select->multiple();
    $this->assertTrue($select->isMultiple());
  }
  
  public function testSelectMultipleOption()
  {
    $select = new Sabel_Form_Select('name');
    $select->multiple();
    $select->addOption(new Sabel_Form_Option('optElem', 'value'));
    $expOne = '<select name="name" multiple="multiple"><option value="value">optElem</option></select>';
    $this->assertEquals($expOne, $select->toHtml(true));
  }
  
  public function testOptionContentsMissing()
  {
    $option = new Sabel_Form_Option('optElement');
    $exp = '<option value="optElement">optElement</option>';
    $this->assertEquals($exp, $option->toHtml(true));
  }
  
  public function testOptionSelected()
  {
    $option = new Sabel_Form_Option('optContents', 'value', true);
    $exp = '<option selected="selected" value="value">optContents</option>';
    $this->assertEquals($exp, $option->toHtml(true));
  }
  
  public function testOptionFull()
  {
    $option = new Sabel_Form_Option('optElement', 'value');
    $exp = '<option value="value">optElement</option>';
    $this->assertEquals($exp, $option->toHtml(true));
  }
  
  public function testSelectOptionAndOptionGroup()
  {
    $select = new Sabel_Form_Select('name');
    $select->addOption(new Sabel_Form_Option('outer'));
    
    $og = new Sabel_Form_OptionGroup('label');
    $og->addOption(new Sabel_Form_Option('inner'));
    $select->addOptionGroup($og);
    
    $exp  = '<select name="name">';
    $exp .=   '<option value="outer">outer</option>';
    $exp .=   '<optgroup label="label">';
    $exp .=     '<option value="inner">inner</option>';
    $exp .=   '</optgroup>';
    $exp .= '</select>';
    
    $this->assertEquals($exp, $select->toHtml(true));
  }
}
