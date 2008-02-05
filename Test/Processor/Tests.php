<?php

// require_once ('Test/Processor/Flow.php');
require_once ("Test/Processor/Abstract.php");
require_once ("Test/Processor/Request.php");
require_once ("Test/Processor/Router.php");
require_once ("Test/Processor/Addon.php");
require_once ("Test/Processor/Controller.php");
require_once ("Test/Processor/Location.php");
require_once ("Test/Processor/Response.php");

define("PROCESSORS_DIR", "generator" . DS . "skeleton" . DS . "lib" . DS . "processor");

class Test_Processor_Tests extends SabelTestSuite
{
  public static function suite()
  {
    $suite = self::createSuite();
    
    $suite->addTest(Test_Processor_Request::suite());
    $suite->addTest(Test_Processor_Router::suite());
    $suite->addTest(Test_Processor_Addon::suite());
    $suite->addTest(Test_Processor_Controller::suite());
    $suite->addTest(Test_Processor_Location::suite());
    $suite->addTest(Test_Processor_Response::suite());
    //$suite->addTest(Test_Processor_Flow::suite());
    
    return $suite;
  }
}

class TestMapConfig extends Sabel_Map_Configurator
{
  public function configure()
  {
    $this->route("devel")
           ->uri("devel/:controller/:action/:param")
           ->module("devel")
           ->defaults(array(":controller" => "main",
                            ":action"     => "index",
                            ":param"      => null));
    
    $this->route("default")
           ->uri(":controller/:action")
           ->module("index")
           ->defaults(array(":controller" => "index",
                            ":action"     => "index"));
  }
}

class TestAddonConfig implements Sabel_Config
{
  public function configure()
  {
    $addons = array();
    $addons[] = "hoge";
    $addons[] = "fuga";
    
    return $addons;
  }
}
