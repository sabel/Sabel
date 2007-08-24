<?php

/**
 * Manipulator
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Manipulator extends Sabel_DB_Model_Manipulator
{
  const INSERT_DATETIME_COLUMN = "created_at";
  const UPDATE_DATETIME_COLUMN = "updated_at";

  public function before($method)
  {
    switch ($method) {
      case "save":
        return $this->beforeSave();

      case "insert":
        return $this->beforeInsert();

      case "update":
        return $this->beforeUpdate();
    }
  }

  public function after($method, $result)
  {
    $this->log();
  }

  private function beforeSave()
  {
    $model = $this->model;

    $columns  = $model->getColumnNames();
    $datetime = $this->now();

    if (in_array(self::UPDATE_DATETIME_COLUMN, $columns)) {
      $model->{self::UPDATE_DATETIME_COLUMN} = $datetime;
    }

    if (!$model->isSelected()) {
      if (in_array(self::INSERT_DATETIME_COLUMN, $columns)) {
        $model->{self::INSERT_DATETIME_COLUMN} = $datetime;
      }
    }

    $args = $this->arguments;

    if (isset($args[0]) && is_array($args[0])) {
      $validator = new Sabel_DB_Validator($model);
      $errors = $validator->validate($args[0]);
      if ($errors) return $errors;
    }
  }

  private function beforeInsert()
  {
    $columns  = $this->model->getColumnNames();
    $datetime = $this->now();

    if (!isset($this->arguments[0])) {
      return null;
    }

    if (in_array(self::UPDATE_DATETIME_COLUMN, $columns)) {
      $this->arguments[0][self::UPDATE_DATETIME_COLUMN] = $datetime;
    }

    if (in_array(self::INSERT_DATETIME_COLUMN, $columns)) {
      $this->arguments[0][self::INSERT_DATETIME_COLUMN] = $datetime;
    }
  }

  private function beforeUpdate()
  {
    $columns = $this->model->getColumnNames();

    if (!isset($this->arguments[0])) {
      return null;
    }

    if (in_array(self::UPDATE_DATETIME_COLUMN, $columns)) {
      $this->arguments[0][self::UPDATE_DATETIME_COLUMN] = $this->now();
    }
  }

  private function now()
  {
    return date("Y-m-d H:i:s");
  }

  private function log()
  {
    $stmt = $this->stmt;

    if (is_object($stmt)) {
      $sql = $stmt->getSql();
      switch ($stmt->getStatementType()) {

        /**
         * select sql log.
         */
        case Sabel_DB_Statement::SELECT:

          break;

        /**
         * insert sql log.
         */
        case Sabel_DB_Statement::INSERT:

          break;

        /**
         * update sql log.
         */
        case Sabel_DB_Statement::UPDATE:

          break;

        /**
         * delete sql log.
         */
        case Sabel_DB_Statement::DELETE:

          break;
      }
    }
  }
}
