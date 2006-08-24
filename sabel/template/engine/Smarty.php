<?

class Sabel_Template_Engine_Smarty extends BaseEngineImpl implements TemplateEngineImpl
{
  private $smarty  = null;
  
  public function __construct()
  {
    $this->smarty = new Smarty();
  }
  
  public function assign($key, $value)
  {
    $this->smarty->assign($key, $value);
  }
  
  public function retrieve()
  {
    $this->smarty->template_dir = $this->tplpath;
    $this->smarty->compile_id   = $this->tplpath;
    return $this->smarty->fetch($this->tplname);
  }
  
  public function configuration()
  {
    $this->smarty->compile_dir = RUN_BASE . '/data/compiled';
    $this->smarty->load_filter('output','trimwhitespace');
  }
  
  public function display()
  {
    $this->smarty->template_dir = $this->tplpath;
    $this->smarty->compile_id   = $this->tplpath;
    $this->smarty->display($this->tplname);
  }
}