<?php

/**
 * Sabel_DB_Model_Relation
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage model
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Model_Relation
{
  private
    $joinTablePairs  = array(),
    $joinColList     = array(),
    $joinConditions  = array(),
    $refStructure    = array(),
    $joinColCache    = array(),
    $joinModels      = array(),
    $aliases         = array();

  public function setColumns($tblName, $columns)
  {
    $this->joinColList[$tblName] = $columns;
  }

  public function setCondition($tblName, $condition)
  {
    $this->joinConditions[$tblName] = $condition;
  }

  public function setParent($ctblName, $ptblName)
  {
    if (!is_array($ptblName)) $ptblName = array($ptblName);

    foreach ($ptblName as $parent) {
      $this->refStructure[$ctblName][] = $parent;
    }
  }

  public function setTablePair($ctblName, $ptblName)
  {
    $this->joinTablePairs[] = array($ctblName, $ptblName);
  }

  public function setTablePairs($pairs)
  {
    $this->joinTablePairs = $pairs;
  }
  
  public function setAlias($aliasName, $tblName)
  {
    $this->aliases[$aliasName] = $tblName;
  }

  public function toRelationPair($mdlName, $pair)
  {
    if (strpos($pair, ':') === false) {
      $child  = $mdlName;
      $parent = $pair;
    } else {
      list($child, $parent) = explode(':', $pair);
    }

    $child = $this->createChildKey($child, $parent);
    list ($parent, $alias) = $this->createParentKey($parent);

    list ($ct, $ck) = explode('.', $child);
    list ($pt, $pk) = explode('.', $parent);

    return array('child'  => $child,
                 'parent' => $parent,
                 'ctable' => $ct,
                 'ptable' => $pt,
                 'ckey'   => $ck,
                 'pkey'   => $pk,
                 'alias'  => $alias);
  }

  public function createChildKey($child, $parent)
  {
    if (strpos($child, '.') === false) {
      $key = convert_to_tablename($parent) . '_id';
    } else {
      list($child, $key) = explode('.', $child);
    }
    return convert_to_tablename($child) . '.' . $key;
  }

  public function createParentKey($parent)
  {
    $alias = null;
    if (strpos($parent, '(') !== false) {
      preg_match('/\((\w+)\)\./', $parent, $matches);
      $alias = $matches[1];
    }
    
    $parent = str_replace("({$alias})", '', $parent);

    if (strpos($parent, '.') === false) {
      $key = 'id';
    } else {
      list($parent, $key) = explode('.', $parent);
    }
    
    return array(convert_to_tablename($parent) . '.' . $key, $alias);
  }

  public function getUniqueTables($tablePairs = null)
  {
    if ($tablePairs === null) $tablePairs = $this->joinTablePairs;

    $joinTables = array();
    foreach ($tablePairs as $pair) $joinTables = array_merge($joinTables, array_values($pair));
    return array_unique($joinTables);
  }

  public function join($model, $modelPairs, $joinType, $columns)
  {
    if (!$model instanceof Sabel_DB_Model)
      throw new Exception('Error:join() first argument must be an instance of Sabel_DB_Model.');

    $mdlName = convert_to_modelname($model->getTableName());
    $aliases =& $this->aliases;
    
    foreach ($modelPairs as $pair) {
      $res   = $this->toRelationPair($mdlName, $pair);
      $ptbl  = $res['ptable'];
      $alias = ($res['alias']) ? convert_to_tablename($res['alias']) : null;
      
      if ($alias === null) {
        $key  = $ptbl;
        $cond = $res['parent'];
      } else {
        $key  = $ptbl . ' AS ' . $alias;
        $cond = $alias . '.' . $res['pkey'];
        $aliases[$alias] = $ptbl;
      }
      
      $this->joinTablePairs[] = array($res['ctable'], ($alias) ? $alias : $ptbl);
      $this->refStructure["{$res['ctable']}"][] = ($alias) ? $alias : $ptbl;

      if (!isset($this->joinConditions[$key])) {
        $this->joinConditions[$key] = "{$res['child']} = $cond";
      }
    }

    $colList =& $this->joinColList;
    $tblName = $model->getTableName();
    $colList[$tblName] = $model->getColumnNames();

    $joinTables = array_diff($this->getUniqueTables(), array($tblName));

    foreach ($joinTables as $tblName) {
      $name    = (isset($aliases[$tblName])) ? $aliases[$tblName] : $tblName;
      $mdlName = convert_to_modelname($name);
      if (isset($columns[$mdlName])) {
        $colList[$name] = $columns[$mdlName];
      } else {
        $colList[$tblName] = $model->getColumnNames($name);
      }
    }
    
    return $this->execJoin($model, $joinType, $joinTables);
  }

  public function execJoin($model, $joinType = 'INNER', $joinTables = null)
  {
    if (!$model instanceof Sabel_DB_Model)
      throw new Exception('Error:execJoin() first argument must be an instance of Sabel_DB_Model.');

    $sql     = array('SELECT ');
    $colList = $this->joinColList;
    $myTable = $model->getTableName();

    foreach ($colList[$myTable] as $column) $sql[] = "{$myTable}.{$column}, ";

    if (!$joinTables) {
      $joinTables = array_diff($this->getUniqueTables(), (array)$myTable);
    }

    foreach ($joinTables as $tblName) {
      foreach ($colList[$tblName] as $column) {
        $this->joinColCache[$tblName][] = $column;
        $sql[] = "{$tblName}.{$column} AS pre_{$tblName}_{$column}, ";
      }
    }

    $sql   = array(substr(join('', $sql), 0, -2));
    $sql[] = " FROM $myTable";

    foreach ($this->joinConditions as $parent => $condition) {
      $sql[] = " $joinType JOIN $parent ON $condition";
    }
    
    $resultSet = $model->doSelect(join('', $sql));
    if ($resultSet->isEmpty()) return false;

    $results = array();
    $obj     = MODEL(convert_to_modelname($myTable));
    $rows    = $resultSet->fetchAll();

    foreach ($rows as $row) {
      $models = $this->createEachModels($row, $joinTables);

      $ref = $this->refStructure;
      foreach ($joinTables as $tblName) {
        if (!isset($ref[$tblName])) continue;
        foreach ($ref[$tblName] as $parent) {
          $mdlName = convert_to_modelname($parent);
          $models[$tblName]->$mdlName = $models[$parent];
        }
      }

      $self = clone $obj;
      $self->transrate($row);

      foreach ($ref[$myTable] as $parent) {
        $mdlName = convert_to_modelname($parent);
        $self->$mdlName = $models[$parent];
      }
      $results[] = $self;
    }
    return $results;
  }

  protected function createEachModels($row, $joinTables)
  {
    $models   = array();
    $acquire  = array();
    $aliases  = $this->aliases;
    $colCache = $this->joinColCache;
    $objects  = $this->createObjects($joinTables);

    foreach ($joinTables as $tblName) {
      $name   = (isset($aliases[$tblName])) ? $aliases[$tblName] : $tblName;
      $model  = clone $objects[$name];
      $preCol = "pre_{$tblName}_" . $model->getPrimaryKey();

      foreach ($colCache[$tblName] as $column) {
        $preCol = "pre_{$tblName}_{$column}";
        $acquire[$tblName][$column] = $row[$preCol];
        unset($row[$preCol]);
      }

      $model->transrate($acquire[$tblName]);
      $models[$tblName] = $model;
    }
    return $models;
  }

  protected function createObjects($tblNames)
  {
    $aliases =  $this->aliases; 
    $models  =& $this->joinModels;
    if ($models) return $models;
    
    foreach ($tblNames as $tblName) {
      $name = (isset($aliases[$tblName])) ? $aliases[$tblName] : $tblName;
      if (!isset($models[$name])) $models[$name] = MODEL(convert_to_modelname($name));
    }
    return $models;
  }
}
