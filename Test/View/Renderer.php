<?php

/**
 * testcase for sabel.view.Renderer
 *
 * @category  View
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_View_Renderer extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_View_Renderer");
  }
  
  public function testRenderingFormString()
  {
    $renderer = new Sabel_View_Renderer();
    $contents = 'name: <?php echo $name ?>';
    $result = $renderer->rendering($contents, array("name" => "hoge"));
    $this->assertEquals("name: hoge", $result);
  }
  
  public function testRenderingFormFile()
  {
    $renderer = new Sabel_View_Renderer();
    $path = dirname(__FILE__) . "/templates/test.tpl";
    $result = $renderer->rendering(null, array("a" => "10", "b" => "20"), $path);
    
    $expected = <<<CONTENTS
a: 10<br/>
b: 20
CONTENTS;
    
    $this->assertEquals($expected, rtrim($result));
  }
}
