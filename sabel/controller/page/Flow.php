<?php

class Sabel_Controller_Page_Flow extends Sabel_Controller_Page
{
  public function redirect($to)
  {
    return parent::redirect($to . "?token=" . $this->token);
  }
}
