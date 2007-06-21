<?php

/**
 * Sabel_DB_Join_Alias
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Join_Alias
{
  private static $objects  = array();
  private static $baseName = null;

  public static function setBaseTableName($name)
  {
    if (self::$baseName === null) {
      self::$baseName = $name;
    } else {
      throw new Exception("name of the base model is not revokable.");
    }
  }

  public static function regist($source, $object)
  {
    $object->setSourceName($source);
    self::$objects[$source][] = $object;
  }

  public static function change($source, $alias, $object)
  {
    self::$objects[$alias][] = $object;

    if (isset(self::$objects[$source])) {
      if (self::$baseName === $source) return;

      foreach (self::$objects[$source] as $object) {
        $object->setSourceName($alias);
        self::$objects[$alias][] = $object;
      }

      unset(self::$objects[$source]);

      Sabel_DB_Join_Result::getInstance()->changeKeyOfStructure($source, $alias);
    }
  }

  public static function clear()
  {
    self::$baseName = null;
    self::$objects  = array();
  }
}
