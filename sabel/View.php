<?php

/**
 * Sabel_View
 *
 * @category   Template
 * @package    org.sabel.template
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel_View
{
  private static $values = array();

  private $resource = null;

  public function __set($key, $value)
  {
    self::$values[$key] = $value;
  }

  public final function assign($key, $value)
  {
    self::$values[$key] = $value;
    return $this;
  }
  
  public static function getAssigns()
  {
    return self::$values;
  }
  
  public static function clearAssigns()
  {
    self::$values = array();
  }

  public final function assignByArray($assignments)
  {
    if (is_array($assignments)) {
      self::$values = array_merge(self::$values, $assignments);
    }

    return $this;
  }

  public final function enableCache()
  {
    $this->renderer->enableCache();
    return $this;
  }

  public final function setResource($resource)
  {
    $this->resource = $resource;
    return $this;
  }

  public final function isResourceMissing()
  {
    return $this->resource->isResourceMissing();
  }

  /**
   * rendering template resource
   *
   * @param Sabel_View_Resource $resource
   */
  public final function rendering($resource = null)
  {
    if ($resource instanceof Sabel_View_Resource) {
      return $resource->fetch(self::$values);
    } elseif ($this->resource instanceof Sabel_View_Resource) {
      return $this->resource->fetch(self::$values);
    } else {
      throw new Exception("invalid resource");
    }
  }

  public static function render($name = null, $additional)
  {
    if (isset($additional["assign"])) {
      self::$values = array_merge(self::$values, $additional["assign"]);
    }
    
    if (isset($additional["resource"])) {
      if ($additional["resource"] === "string") {
        $resource = new Sabel_View_Resource_String();
        $resource->set($name);
        $resource->setRenderer(new Sabel_View_Renderer_Class());
        return $resource->fetch(self::$values);
      }
    } else {
      return Sabel_View_Locator_Factory::create()
                                         ->make($name)
                                         ->locate($name)
                                         ->fetch(self::$values);
    }
  }
}
