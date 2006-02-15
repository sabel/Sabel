<?php

require_once('core/SabelConst.php');
require_once('core/SabelContext.php');
require_once('core/SabelClassLoader.php');
require_once('core/Request.php');
require_once('core/SessionManager.php');

require_once('core/SabelPageController.php');
require_once('core/RequestParser.php');
require_once('core/SabelTemplateDirector.php');
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
    $p = new RequestParser();
    $this->request = $p->parse();
    $this->loader = SabelClassLoader::create($this->request);
  }

  public function dispatch()
  {
    $this->process();
  }

  protected function process()
  {
    $this->makeController();
    $this->processTemplate();
  }

  protected function makeController()
  {
    $aMethod = $this->request->getAction();

    $this->controller = $this->loader->load();
    $this->controller->setup();
    $this->controller->param   = $this->request->getParameter();
    $this->controller->session = SessionManager::makeInstance();
    $this->controller->te      = new TemplateEngine();
    $this->controller->initialize();
    
    if ($this->controller->hasMethod($aMethod)) {
      $this->controller->execute($aMethod);
    } else if ($this->controller->hasMethod(SabelConst::DEFAULT_METHOD)) {
      $this->controller->execute(SabelConst::DEFAULT_METHOD);
    }
  }

  protected function processTemplate()
  {
    $d = new DefaultTemplateDirector($this->request);
    $this->controller->te->selectPath($d->decidePath());
    $this->controller->te->selectName($d->decideName());
    $this->controller->te->rendering();
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
