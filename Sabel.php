<?php

/**
 * 定数クラス
 *
 */
class SabelConst
{
}

class SabelContext
{
  private static $parameters = array();

  public static function getController()
  {
    // @TODO configuration.
    return new SabelPageWebController();
  }

  public static function setParameter($name, $value)
  {
    self::$parameters[$name] = $value;
  }

  public static function getParameter($name)
  {
    return self::$parameters[$name];
  }

  public static function getRequestModule()
  {
    return $_REQUEST['module'];
  }

  public static function getRequestAction()
  {
    return $_REQUEST['action'];
  }
}

abstract class SabelController
{
  abstract public function dispatch();

  public function dump($var)
  {
    print "<pre>";
    var_dump($var);
    print "</pre>";
  }
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
 * フロントコントローラ
 *
 */
class SabelFrontWebController extends SabelWebController
{
  public function dispatch()
  {
  }
}

/**
 * ページコントローラ
 *
 */
class SabelPageWebController extends SabelWebController
{
  public function parseURI() {
    global $sabelfilepath;

    $uri = $_SERVER['REQUEST_URI'];

    $path = split('/', $sabelfilepath);
    array_shift($path);
    foreach ($path as $p => $v) {
      if ($v == $path[count($path) - 2]) {
	$dir = $v;
      }
    }

    $sp = split('/', $uri);
    array_shift($sp);

    $request = array();
    $matched = true;
    foreach ($sp as $p => $v) {
      if ($matched)   $request[] = $v;
      // if ($v == $dir) $matched = true;
    }

    return new PersedRequest($request);
  }

  public function dispatch()
  {
    $request = $this->parseURI();

    $controllerClassName  = $request->getModule();
    $controllerClassName .= '_' . $request->getController();

    $cpath  = 'app/modules/'  . $request->getModule();
    $cpath .= '/controllers/' . $request->getController();
    $cpath .= '.php';

    // コントローラクラスのロード
    if (is_file($cpath)) {
      require_once($cpath);
      $aModule = $request->getModule();
      $aMethod = $request->getAction();
      UTIL::$module = $aModule;
      UTIL::$controller = $request->getController();
      UTIL::$method = $aMethod;
      $ins = new $controllerClassName();
    } else {
      $cpath = 'app/modules/Defaults/controllers/Default.php';
      require_once($cpath);
      $aModule = 'Defaults';
      $aMethod = 'top';
      $ins = new Defaults_Default();
    }

    $ins->init();
    $ins->param = $request->getParameter();

    // テンプレートエンジンを分離する
    require_once('Savant3/Savant3.php');
    $ins->te = new Savant3();
    
    // Action Method を実行
    if (method_exists($ins, $aMethod)) {
      $ins->$aMethod();
    } else {
      $ins->defaults();
    }

    // この部分も分離する {
    $tplpath  = 'app/modules/'      . $aModule;
    $tplpath .= '/views/templates/' . $aMethod;
    $tplpath .= '.tpl.php';
    
    if (file_exists($tplpath)) {
      $tpl = $ins->te->fetch($tplpath);
      echo $tpl;
    }
    // }

    // show debug information.
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
  }
}

abstract class Request
{
  abstract public function get($name);
}

class PostRequest extends Request
{
  public function get($name)
  {
    if (isset($_POST[$name])) {
      return $_POST[$name];
    } else {
      return false;
    }
  }
}

abstract class SabelPageController
{
  protected $parameters;
  protected $request;

  public function init()
  {
    $this->request = new PostRequest();
  }

  public function __set($name, $value)
  {
    $this->parameters[$name] = $value;
  }

  public function __get($name)
  {
    return $this->parameters[$name];
  }

  public function showActionMethods()
  {
    print "<pre>";

    $methods = get_class_methods($this);
    foreach ($methods as $key => $val) {
      if ($val[0] != '_') print $val . "<br/>\n";
    }

    print "</pre>";
  }

  public function checkReferer($validURIs)
  {
    $ref = $_SERVER['HTTP_REFERER'];
    $absolute = 'http://'.$_SERVER['HTTP_HOST'] . $validURIs[0];
    if ($ref == $absolute) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * HTTP Redirect to another location.
   * this method will be avoid "back button" problem.
   *
   * @param string $to /Module/Controller/Method
   */
  public function redirect($to)
  {
    $absolute  = 'http://' . $_SERVER['HTTP_HOST'];
    $absolute .= $to;
    $redirect = 'Location: ' . $absolute;
    header($redirect);

    // HTTPヘッダ(30x)を送信した後は処理を継続しない
    exit;
  }

  /**
   * forwaring anothor controller or method of same controller
   *
   */
  public function forward($to)
  {
    
  }
}

class PersedRequest
{
  private $request;

  public function __construct($request)
  {
    $this->request = $request;
  }

  public function getModule()
  {
    if (!empty($this->request[0])) {
      return $this->request[0];
    } else {
      return 'Defaults';
    }
  }

  public function getController()
  {
    if (isset($this->request[1])) {
      return $this->request[1];
    } else {
      return 'Default';
    }
  }

  public function getAction()
  {
    if (isset($this->request[2])) {
      return $this->request[2];
    } else {
      return 'top';
    }
  }

  public function getParameter()
  {
    return $this->request[3];
  }
}

class SabelAjaxController extends SabelController
{
  public function dispatch()
  {
  }
}

define('LNAME', 'name');
define('MODULE', 'module');
define('CONTROLLER', 'controller');
define('METHOD', 'method');
define('ABSOLUTE', 'absolute');
define('IMG', 'img');
define('PARAM', 'param');

class UTIL
{
  public static $module;
  public static $controller;
  public static $method;
}

function linkTo($ar)
{
  $absolute = 'http://' . $_SERVER['HTTP_HOST'];
  $buf = array();
  array_push($buf, '<a href="');

  if (isset($ar[ABSOLUTE])) {
    array_push($buf, $absolute);
  }

  if (isset($ar[MODULE])) {
    array_push($buf, '/'.$ar[MODULE]);
    array_push($buf, '/'.$ar[CONTROLLER]);
    array_push($buf, '/'.$ar[METHOD]);
  } elseif (isset($ar[CONTROLLER])) {
    array_push($buf, '/'.UTIL::$module);
    array_push($buf, '/'.$ar[CONTROLLER]);
    array_push($buf, '/'.$ar[METHOD]);
  } elseif (isset($ar[METHOD])) {
    array_push($buf, '/'.UTIL::$module);
    array_push($buf, '/'.UTIL::$controller);
    array_push($buf, '/'.$ar[METHOD]);
  }

  if (isset($ar[PARAM])) {
    array_push($buf, '/'.$ar[PARAM]);
  }

  array_push($buf, '">');

  if (isset($ar[LNAME])) {
    array_push($buf, $ar[LNAME]);
    if (isset($ar[IMG])) {
      array_push($buf, '<img src="'.$absolute .'/'. $ar[IMG].'">');
    }
  } else {
    if (isset($ar[IMG])) {
      array_push($buf, '<img src="'.$absolute .'/'. $ar[IMG].'">');
    } else {
      array_push($buf, 'link');
    }
  }

  array_push($buf, '</a>');

  echo join('', $buf);
}

function js_include($file)
{
  $path = 'http://'.$_SERVER['HTTP_HOST'] . '/js/' . $file;
  echo '<script type="text/javascript" src="'.$path.'"></script>'."\n";
}


/**
 * below utility classes.
 */

function p($str)
{
  echo $str;
}

function ep($str)
{
  echo htmlspeicalchars($str);
}

?>