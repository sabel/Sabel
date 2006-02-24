<?php

require_once('core/SabelConst.php');
require_once('core/SabelContext.php');
require_once('core/SabelClassLoader.php');
require_once('core/Request.php');
require_once('core/SabelException.php');

require_once('core/SabelPageController.php');
require_once('core/RequestParser.php');
require_once('core/SabelTemplateDirector.php');
require_once('core/TemplateEngine.php');

require_once('user/User.php');
require_once('storage/Storage.php');

require_once('view/Helper.php');

require_once('core/spyc.php');
require_once('third/Smarty/Smarty.class.php');
require_once('third/Crypt_Blowfish/Blowfish.php');

abstract class SabelController
{
  abstract public function dispatch();
}

/**
 * SabelPageWebController
 * 
 * @author Mori Reo <mori.reo@servise.jp>
 */
class SabelPageWebController extends SabelController
{
  public function dispatch()
  {
    $parsedRequest = RequestParser::parse();
    $loader = SabelClassLoader::create($parsedRequest);

    $controller = $loader->load();
    $controller->setup($parsedRequest);
    $controller->initialize();

    $aMethod = $parsedRequest->getMethod();

    if ($controller->hasMethod($aMethod)) {
      $st = split(' ', microtime());
      $controller->execute($aMethod);
      $en = split(' ', microtime());
      print $en[0] - $st[0];
      print "<br/>\n" . $en[1] . "/" . $st[1] . "<br/>\n";
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
