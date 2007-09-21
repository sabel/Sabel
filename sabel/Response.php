<?php

interface Sabel_Response
{
  public function setResponse($key, $value);
  public function getResponse($key);
  public function setResponses($array);
  public function getResponses();
}
