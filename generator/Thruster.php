<?php

class Thruster
{
  protected $methods = array('grade', 'check');
  
  public function generate()
  {
    $argv = $_SERVER['argv'];
    
    ob_start();
    require('template.php');
    $content = ob_get_clean();
    
    $content = str_replace('[?php', '<?php', $content);
    $content = str_replace('?]', '?>', $content);
    $content = str_replace('@@ACTION_NAME@@', ucfirst($argv[1]), $content);
    
    return $content;
  }
}

$g = new Thruster();
$classFile = $g->generate();

$fp = fopen(ucfirst($_SERVER['argv'][1]) . '.php', 'w');
fwrite($fp, $classFile);
fclose($fp);

?>
