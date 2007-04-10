<?php

/**
 * Sabel_DB_Relation_Join_Alias
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Relation_Join_Alias
{
  protected static $objects = array();

  public static function regist($source, $object)
  {
    $object->setSourceName($source);
    self::$objects[$source][] = $object;
  }

  public static function change($source, $alias, $object)
  {
    self::$objects[$alias][] = $object;

    if (isset(self::$objects[$source])) {
      foreach (self::$objects[$source] as $object) {
        $object->setSourceName($alias);
        self::$objects[$alias][] = $object;
      }

      unset(self::$objects[$source]);

      $resultBuilder = Sabel_DB_Relation_Join_Result::getInstance();
      $resultBuilder->changeKeyOfStructure($source, $alias);
    }
  }

  public static function clear()
  {
    self::$objects = array();
  }
}
