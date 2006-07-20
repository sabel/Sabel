<?php

interface SabelTemplateDirector
{
  public function decidePath();
  public function decideName();
}

class TemplateDirectorFactory
{
  public static function create($request = null)
  {
    $prequest = ParsedRequest::create($request);

    $classPath  = SabelConst::MODULES_DIR . $prequest->getModule();
    $classPath .= '/extensions/CustomTemplateDirector.php';

    $commonsPath  = SabelConst::COMMONS_DIR;
    $commonsPath .= '/extensions/CustomTemplateDirector.php';

    if (is_file($classPath)) {
      require_once($classPath);
      $instance = new CustomTemplateDirector($prequest);
    } else if (is_file($commonsPath)) {
      require_once($commonsPath);
      $instance = new CustomTemplateDirector($prequest);
    } else {
      $instance = new DefaultTemplateDirector($prequest);
    }

    return $instance;
  }
}

class DefaultTemplateDirector implements SabelTemplateDirector
{
  protected $request;

  public function __construct($request)
  {
    $this->request = $request;
  }

  protected function getPath()
  {
    $tplpath  = SabelConst::MODULES_DIR;
    $tplpath .= $this->request->getModule() . '/';
    $tplpath .= SabelConst::TEMPLATE_DIR;

    return $tplpath;
  }

  public function decidePath()
  {
    return $this->getPath();
  }

  protected function getName()
  {
    // makeing template name string such as "controller.method.tpl"
    $tplname  = $this->request->getController();
    $tplname .= SabelConst::TEMPLATE_NAME_SEPARATOR;
    $tplname .= $this->request->getMethod();
    $tplname .= SabelConst::TEMPLATE_POSTFIX;

    return $tplname;
  }

  public function decideName()
  {
    return $this->getName();
  }

  public function getFullPath()
  {
    return $this->getPath() . $this->getName();
  }
}