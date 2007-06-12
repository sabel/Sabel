<?php

interface Sabel_Plugin_Acl_Authentication
{
  public function authenticate();
  public function fetchIdentity();
}
