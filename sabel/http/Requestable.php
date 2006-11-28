<?php

interface Sabel_Http_Requestable
{
  public function connect($host, $port);
  public function send($data);
  public function disconnect();
}