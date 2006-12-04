<?php

class Sabel_Map_Candidates
{
  protected $candidates = array();
  
  public function addCandidate($c)
  {
    $this->candidates[] = $c;
  }
}