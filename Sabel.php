<?php

require_once('core/functions.php');

require_once('core/SabelConst.php');
require_once('core/SabelContext.php');
require_once('core/SabelClassLoader.php');
require_once('core/SabelUtility.php');
require_once('core/Request.php');
require_once('core/ParsedRequest.php');
require_once('core/SabelException.php');
require_once('core/Logger.php');

require_once('core/SabelPageController.php');
require_once('core/SabelTemplateDirector.php');
require_once('core/TemplateEngine.php');

require_once('core/SabelDIContainer.php');

require_once('user/User.php');
require_once('config/Config.php');
require_once('storage/Storage.php');
require_once('cache/Cache.php');

require_once('view/Helper.php');

require_once('core/SabelPager.php');
require_once('core/spyc.php');
require_once('third/Smarty/Smarty.class.php');
require_once('third/Crypt_Blowfish/Blowfish.php');

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