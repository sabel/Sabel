<?php

require_once('sabel/core/Context.php');
require_once('sabel/Functions.php');

Sabel_Core_Context::addIncludePath('Sabel/');
Sabel_Core_Context::addIncludePath('app/commons/models/');

uses('core.logger.File');

require_once('sabel/core/Const.php');

require_once('sabel/core/ClassLoader.php');
require_once('sabel/core/Utility.php');
require_once('sabel/request/Parameters.php');
require_once('sabel/request/Request.php');
require_once('sabel/request/ParsedRequest.php');
require_once('sabel/core/Exception.php');

require_once('sabel/controller/Page.php');
require_once('sabel/template/Director.php');
require_once('sabel/template/Engine.php');

require_once('sabel/container/DI.php');

require_once('sabel/user/User.php');
//require_once('sabel/config/Config.php');
require_once('sabel/storage/Storage.php');
require_once('sabel/cache/Cache.php');

require_once('sabel/view/Helper.php');

require_once('sabel/core/Pager.php');
require_once('sabel/core/spyc.php');
require_once('third/Smarty/Smarty.class.php');
require_once('third/Crypt_Blowfish/Blowfish.php');

require_once('sabel/edo/RecordObject.php');
require_once('sabel/edo/DBConnection.php');

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
      $logger = new Core_Logger_File();
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
