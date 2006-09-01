#!/opt/local/php/bin/php

<?php

class ModelGenerator
{
  public static function generate($name)
  {
    $target = "app/index/models/${name}.php";
    echo "generate $target \n";
    $fp = fopen($target, 'w');
    $className = $name;
    
    ob_start();
    @include("skeleton/model/Standerd.php");
    $contents = ob_get_contents();
    ob_end_clean();
    $contents = str_replace('#php', '?php', $contents);
    fwrite($fp, $contents);
    fclose($fp);
  }
}

class ControllerGenerator
{
  public static function generate($name)
  {
    $name = strtolower($name);
    $target = "app/index/controllers/${name}.php";
    echo "generate $target \n";
    $fp = fopen($target, 'w');
    $controllerName = $name;
    
    ob_start();
    @include("skeleton/controller/Standerd.php");
    $contents = ob_get_contents();
    ob_end_clean();
    $contents = str_replace('#php', '?php', $contents);
    fwrite($fp, $contents);
    fclose($fp);
  }
}

class FixtureGenerator
{
  public static function generate($name)
  {
    $name = strtolower($name);
    $target = "fixtures/${name}.php";
    echo "generate $target \n";
    $fp = fopen($target, 'w');
    $name = $name;
    
    ob_start();
    @include("skeleton/fixture/Standerd.php");
    $contents = ob_get_contents();
    ob_end_clean();
    $contents = str_replace('#php', '?php', $contents);
    fwrite($fp, $contents);
    fclose($fp);
  }
}

class ViewGenerator
{
  public static function generate($type, $name)
  {
    $name = strtolower($name);
    $target = "app/index/views/${name}.${type}.tpl";
    echo "generate $target \n";
    $fp = fopen($target, 'w');
    
    $skeleton = '';
    switch ($type) {
      case 'lists':
        $skeleton = 'skeleton/view/lists.tpl';
        break;
      case 'show':
        $skeleton = 'skeleton/view/show.tpl';
        break;
      case 'edit':
        $skeleton = 'skeleton/view/edit.tpl';
        break;
      case 'delete':
        $skeleton = 'skeleton/view/delete.tpl';
        break;
      case 'create':
        $skeleton = 'skeleton/view/create.tpl';
        break;
    }
    
    ob_start();
    @include($skeleton);
    $contents = ob_get_contents();
    $contents = str_replace('<#',   '<?',   $contents);
    $contents = str_replace('<#=',  '<?=',  $contents);
    $contents = str_replace('<#hn', '<?hn', $contents);
    $contents = str_replace('#>',   '?>',   $contents);
    ob_end_clean();
    fwrite($fp, $contents);
    fclose($fp);
  }
}

class Generator
{
  public static function main()
  {
    $type = $_SERVER['argv'][1];
    $name = $_SERVER['argv'][2];
    
    switch ($type) {
      case 'model':
        print "generate model ${name}\n";
        ModelGenerator::generate($name);
        FixtureGenerator::generate($name);
        break;
      case 'controller':
        print "generate controller ${name}\n";
        ControllerGenerator::generate($name);
        break;
      case 'view':
        ViewGenerator::generate('lists',  $name);
        ViewGenerator::generate('show',   $name);
        ViewGenerator::generate('edit',   $name);
        ViewGenerator::generate('delete', $name);
        break;
      case 'scaffold':
        ModelGenerator::generate($name);
        ControllerGenerator::generate($name);
        ViewGenerator::generate('lists',  $name);
        ViewGenerator::generate('show',   $name);
        ViewGenerator::generate('edit',   $name);
        ViewGenerator::generate('delete', $name);
        ViewGenerator::generate('create', $name);
        FixtureGenerator::generate($name);
        break;
      default:
        break;
    }
  }
}

Generator::main();