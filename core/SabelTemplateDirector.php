<?php

interface SabelTemplateDirector
{
  public function decidePath();
  public function decideName();
}

class DefaultTemplateDirector implements SabelTemplateDirector
{
  protected $request;

  public function __construct(ParsedRequest $request)
  {
    $this->request = $request;
  }

  public function decidePath()
  {
    $tplpath  = SabelConst::MODULES_DIR;
    $tplpath .= $this->request->getModule() . '/';
    $tplpath .= SabelConst::TEMPLATE_DIR;
    
    return $tplpath;
  }

  public function decideName()
  {
    // makeing template name string such as "controller.method.tpl"
    $tplname  = $this->request->getController();
    $tplname .= SabelConst::TEMPLATE_NAME_SEPARATOR; // may be '.'
    $tplname .= $this->request->getAction();
    $tplname .= SabelConst::TEMPLATE_POSTFIX;

    return $tplname;
  }

  public function getFullPath()
  {
    return $this->decidePath() . $this->decideName();
  }
}

?>