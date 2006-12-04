<?php

class Sabel_Map_Selecter_Impl extends Sabel_Map_Selecter
{
  public function select($token, $candidate)
  {
    $result = false;
    
    if ($candidate->isOmittable() && $candidate->hasRequirement()) {
      if ($token === false) {
        $result = true;
      } else {
        $result = $candidate->compareWithRequirement($token);
      }
    } elseif ($candidate->hasRequirement()) {
      $result = $candidate->compareWithRequirement($token);
    } elseif ($candidate->isConstant() && $token !== $candidate->getElementName()) {
      $result = false;
    } elseif ($token === false && $candidate->isOmittable()) {
      $result = true;
    } elseif ($token === false) {
      $result = false;
    } else {
      $result = true;
    }
    
    return $result;
  }
}