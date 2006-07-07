<?php

require_once('sabel/core/Context.php');
require_once('sabel/Functions.php');
require_once('third/Smarty/Smarty.class.php');

Sabel_Core_Context::addIncludePath('Sabel/');
Sabel_Core_Context::addIncludePath('app/commons/models/');

uses('sabel.logger.File');

uses('sabel.core.Const');

uses('sabel.core.ClassLoader');
uses('sabel.core.Utility');
uses('sabel.request.Parameters');
uses('sabel.request.Request');
uses('sabel.request.ParsedRequest');
uses('sabel.core.Exception');

uses('sabel.logger.File');

uses('sabel.controller.Page');
uses('sabel.template.Director');
uses('sabel.template.Engine');

uses('sabel.container.DI');

uses('sabel.user.User');
uses('sabel.storage.Storage');

uses('sabel.view.Helper');

uses('sabel.core.Pager');
uses('sabel.core.Spyc');
uses('third.Crypt_Blowfish.Blowfish');

uses('sabel.edo.RecordObject');
uses('sabel.edo.DBConnection');

interface SabelController
{
  public function dispatch();
}

class SabelCLIController implements SabelController
{
  public function dispatch()
  {
  }
}

/**
 * SabelPageWebController
 *
 * @author Mori Reo <mori.reo@servise.jp>
 */
class SabelPageWebController implements SabelController
{
  public function dispatch()
  {
    try {
      $parsedRequest = ParsedRequest::create();
      $loader = SabelClassLoader::create($parsedRequest);
      
      $controller = $loader->load();
      $controller->setup(new WebRequest());
      $controller->initialize();
      
      $aMethod = $parsedRequest->getMethod();
      
      if ($controller->hasMethod($aMethod)) {
        $controller->execute($aMethod);
      } else {
        $controller->execute(SabelConst::DEFAULT_METHOD);
      }
    } catch (Exception $e) {
      $logger = new Sabel_Logger_File();
      $logger->log($e->getMessage());
    }
  }

  protected function debugInformation()
  {
    // show debug information.
    // /* //
    echo "<br/><br/><br/>usefull debug information<hr/>\n";
    echo "<pre>";
    show_source($cpath);
    echo "<hr/>";
    show_source($tplpath);
    echo "<hr/>";
    echo htmlspecialchars($tpl);
    echo "<hr/>";
    print_r($_SERVER);
    echo "</pre>";
    // */
  }
}

?>
