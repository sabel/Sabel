<?php

Sabel::using('Sabel_Map_Selecter');

/**
 * Selecter implementation
 *
 * @category   Map
 * @package    org.sabel.map.selecter
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Map_Selecter_Impl extends Sabel_Map_Selecter
{
  public function select($token, $candidate)
  {
    $result = false;
    
    if (($token === false || $token === "") && $candidate->hasDefaultValue()) {
      $token = $candidate->getDefaultValue();
    }
    
    if ($candidate->isMatchAll()) {
      $result = true;
    } elseif (($token === false || $token === "") && $candidate->isOmittable()) {
      $result = true;
    } elseif ($candidate->hasRequirement()) {
      $result = $candidate->compareWithRequirement($token);
    } elseif ($candidate->isConstant() && $token !== $candidate->getElementName()) {
      $result = false;
    } else {
      $result =(boolean) $token;
    }
    
    // token value as a candidate variable
    if ($result) {
      if ($candidate->equalsElementTypeWith(Sabel_Map_Candidate::VARIABLE)) {
        $candidate->setElementVariable($token);
      } elseif ($candidate->equalsElementTypeWith(Sabel_Map_Candidate::MODULE)) {
        $candidate->setModule($token);
        $candidate->setElementVariable($token);
      } elseif ($candidate->equalsElementTypeWith(Sabel_Map_Candidate::CONTROLLER)) {
        $candidate->setController($token);
        $candidate->setElementVariable($token);
      } elseif ($candidate->equalsElementTypeWith(Sabel_Map_Candidate::ACTION)) {
        $candidate->setAction($token);
        $candidate->setElementVariable($token);
      }
    }
    
    return $result;
  }
}