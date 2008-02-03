<?php

if (!defined("VIEW_DIR_NAME")) define("VIEW_DIR_NAME", "views");
if (!defined("TPL_SUFFIX")) define("TPL_SUFFIX", ".tpl");
if (!defined("DS")) define("DS", DIRECTORY_SEPARATOR);

define ("COMPILE_DIR_PATH", SABEL_BASE . "/Test/data/application/data/compiled");

require_once ("Test/View/Template.php");
require_once ("Test/View/TemplateFile.php");
require_once ("Test/View/TemplateDb.php");
require_once ("Test/View/Renderer.php");
require_once ("Test/View/Pager.php");
require_once ("Test/View/PageViewer.php");

/**
 * load tests for sabel.view
 *
 * @category  View
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_View_Tests extends SabelTestSuite
{
  public static function suite()
  {
    $base = dirname(__FILE__) . DS . "templates";
    if (!defined("MODULES_DIR_PATH")) define("MODULES_DIR_PATH", $base);
    
    $suite = self::createSuite();
    
    $suite->addTest(Test_View_TemplateFile::suite());
    $suite->addTest(Test_View_TemplateDb::suite());
    $suite->addTest(Test_View_Renderer::suite());
    $suite->addTest(Test_View_Pager::suite());
    $suite->addTest(Test_View_PageViewer::suite());
    
    return $suite;
  }
}
