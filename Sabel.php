<?php

require_once('core/SabelConst.php');
require_once('core/SabelContext.php');
require_once('core/SabelClassLoader.php');
require_once('core/Request.php');
require_once('core/SessionManager.php');
require_once('core/SabelException.php');

require_once('core/SabelPageController.php');
require_once('core/RequestParser.php');
require_once('core/SabelTemplateDirector.php');
require_once('core/TemplateEngine.php');

require_once('view/Helper.php');

require_once('core/spyc.php');
require_once('third/Smarty/Smarty.class.php');
require_once('third/s2container.php5/s2container.inc.php');
require_once('third/s2dao.php5/s2dao.inc.php');

define("S2DAO_PHP5", "third/s2dao.php5");
define("PDO_DICON", S2DAO_PHP5 . "/pdo.dicon");
define("DAO_DICON", S2DAO_PHP5 . "/dao.dicon");

function __autoload($class = null){
    if(S2ContainerClassLoader::load($class)){
      return;
    }
}

/** S2Dao.PHP5 ClassLoader */
require_once(S2DAO_PHP5 . "/S2DaoClassLoader.class.php");

if( class_exists("S2ContainerClassLoader") ){
    S2ContainerClassLoader::import(S2DaoClassLoader::export());
}
if( class_exists("S2Container_MessageUtil") ){
    S2ContainerMessageUtil::addMessageResource(S2DAO_PHP5."/DaoMessages.properties");
}

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
