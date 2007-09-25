<?php

class Admin_Controllers_Database extends Sabel_Controller_Page
{
  public function lists()
  {
    $accessor = new Sabel_DB_Schema_Accessor($this->db);
    $this->tables = $accessor->getTableList();
    $this->setAttribute("db", $this->db);
  }
}
