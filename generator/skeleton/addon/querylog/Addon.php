<?php

/**
 * QueryLog_Addon
 *
 * @category   Addon
 * @package    addon.querylog
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class QueryLog_Addon extends Sabel_Object
{
  public function execute($bus)
  {
    $bus->attachExecuteBeforeEvent("view", $this, "output");
  }
  
  public function output($bus)
  {
    $queries = Sabel_DB_Statement::getExecutedQueries();
    if (empty($queries)) return;
    
    $buf = array();
    for ($i = 0, $c = count($queries); $i < $c; $i++) {
      $query = $queries[$i];
      $sql = '<em style="font-weight: bold;">SQL:</em> ' . $query["sql"];
      
      if (!empty($query["binds"])) {
        $binds = array();
        foreach ($query["binds"] as $k => $v) {
          $binds[] = "$k => $v";
        }
        
        $sql .= '<br /><em style="font-weight: bold;">Bind Values:</em> '
              . implode(", ", $binds);
      }
      
      $buf[$i]["sql"]  = $sql;
      $buf[$i]["time"] = $query["time"];
      
      if ($query["time"] > 2000) {
        // e.g send mail to admin.
      }
    }
    
    if (ENVIRONMENT === PRODUCTION) {
      $bus->get("response")->setResponse("addon_querylog", "");
    } else {
      ob_start();
      @include (dirname(__FILE__) . DIRECTORY_SEPARATOR . "html.tpl");
      $bus->get("response")->setResponse("addon_querylog", ob_get_clean());
    }
  }
}
