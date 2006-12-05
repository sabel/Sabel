<?php

/**
 * Selecter implementation
 *
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Map_Selecter_Impl extends Sabel_Map_Selecter
{
  public function select($token, $candidate)
  {
    $result = false;
    
    if ($candidate->isMatchAll()) {
      $result = true;
    } elseif ($candidate->isOmittable() && $candidate->hasRequirement()) {
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
    
    // token value as a candidate variable
    if ($result) {
      if ($candidate->equalsElementTypeWith(Sabel_Map_Candidate::VARIABLE)) {
        $candidate->setElementVariable($token);
      } elseif ($candidate->equalsElementTypeWith(Sabel_Map_Candidate::MODULE)) {
        $candidate->setElementVariable($token);
      } elseif ($candidate->equalsElementTypeWith(Sabel_Map_Candidate::CONTROLLER)) {
        $candidate->setElementVariable($token);
      } elseif ($candidate->equalsElementTypeWith(Sabel_Map_Candidate::ACTION)) {
        $candidate->setElementVariable($token);
      }
    }
    
    return $result;
  }
}