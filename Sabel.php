<?php

require_once('core/SabelConst.php');
require_once('core/SabelContext.php');
require_once('core/SabelClassLoader.php');
require_once('core/Request.php');
require_once('core/SessionManager.php');

require_once('core/SabelPageController.php');
require_once('core/RequestPerser.php');
require_once('core/TemplateEngine.php');

require_once('view/Helper.php');


abstract class SabelController
{
  abstract public function dispatch();
}

abstract class SabelWebController extends SabelController
{
}

class SabelCLIController extends SabelController
{
  public function dispatch()
  {
    // @todo implement
  }
}

/**
 * SabelPageWebController
 * 
 * @author Mori Reo <mori.reo@servise.jp>
 */
class SabelPageWebController extends SabelWebController
{
  protected $request;
  protected $loader;
  protected $controller;

  public function __construct()
  {
    $p = new RequestPerser();
    $this->request = $p->perse();
    $this->loader = SabelClassLoader::create($this->request);
  }

  public function dispatch()
  {
    $this->process();
  }

  protected function process()
  {
    $this->makeController();
    $this->processView();
  }

  protected function makeController()
  {
    $aMethod = $this->request->getAction();

    $this->controller = $this->loader->load();
    $this->controller->init();
    $this->controller->param   = $this->request->getParameter();
    $this->controller->session = SessionManager::makeInstance();
    $this->controller->te      = new TemplateEngine();
    
    if ($this->controller->hasMethod($aMethod)) {
      $this->controller->execute($aMethod);
    } else if ($this->controller->hasMethod('defaults')) {
      $this->controller->execute('defaults');
    } else {
      // todo exception ?
    }
  }

  protected function processView()
  {
    $controller = $this->controller;

    $aModule     = $this->request->getModule();
    $aController = $this->request->getController();
    $aMethod     = $this->request->getAction();

    $tplpath  = SabelConst::MODULES_DIR . $aModule . '/';
    $tplpath .= SabelConst::VIEWS_DIR . $aController . '/';

    $tplname = $aMethod . SabelConst::TEMPLATE_POSTFIX;

    $controller->te->selectPath($tplpath);
    $controller->te->selectName($tplname);

    $controller->te->rendering();
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
