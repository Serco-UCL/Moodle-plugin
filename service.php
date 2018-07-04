<?php
/*
 * This file is part of the UCL-Serco
 *
 * Copyright (C) 2018 UniversitÃ© de Louvain-la-Neuve (UCL-TICE)
 *
 * Written by
 *        Erin Dupuis   (erin.dupuis@uclouvain.be)
 *        Arnaud Willame (arnaud.willame@uclouvain.be)
 *        Domenico Palumbo (dominique.palumbo@uclouvain.be)
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3 of
 * the License, or (at your option) any later version.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
 */
 
/**
 * Service.php it's an abstraction of the distant service configured in the tool
 *
 * @package    question type
 * @subpackage serco
 * @copyright  2018 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

global $CFG;
global $DB;

if(ISSET($_GET["action"]) && $_GET["action"] != '') $action = $_GET["action"];
else die(1);

$response = '';

switch($action) {
  /******************************************************************************************************/
  case "service" :

    if(ISSET($_GET["serverid"])) $serverid = $_GET["serverid"];
    else $serverid = 1;

    if(ISSET($_GET["CollectionType"])) $collectionType = $_GET["CollectionType"];
    else $collectionType = '';
    
    if(ISSET($_GET["collection"])) $collection = $_GET["collection"];
    else $collection = '';

    $collectionTypeRef  = $collectionType;
    $collectionRef      = $collection;
    
    if(ISSET($_GET["query"])) $query = $_GET["query"];
    else $query = '';

    if(ISSET($_GET["limit"])) $limit = $_GET["limit"];
    else $limit = 100000;

    if(ISSET($_GET["offset"])) $offset = $_GET["offset"];
    else $offset = 0;

    if(ISSET($_GET["order"])) $order = $_GET["order"];
    else $order = 'asc';
    $response = file_get_contents(getPath($DB,$serverid).'/service/index.php?related='.$collectionTypeRef.':'.$collectionRef.'&query='.urlencode($query).'&outputType=json&limit='.$limit.'&offset='.$offset.'&order='.$order);
    if (strpos($response,'"response":')>0) $response = substr($response,strpos($response,'"response":')+11);
    $response = str_replace('"docs":','"rows":',$response);
    if(strpos($response,'"totalQueryReturned":')>0) {
      $response = str_replace('"total":','"allRecords":',$response);
      $response = substr(str_replace('"totalQueryReturned":','"total":',$response),0, -1);
    }
  break;
  /******************************************************************************************************/
  case "info" :    
    if(ISSET($_GET["serverid"])) $serverid = $_GET["serverid"];
    else $serverid = 1;
    
    $response = file_get_contents(getPath($DB,$serverid).'/service/index.php?info&lang='.current_language());
  break;
  case "debug" :    
    if(ISSET($_GET["serverid"])) $serverid = $_GET["serverid"];
    else $serverid = 1;
  
    echo(getPath($DB,$serverid).'/service/index.php?info&lang='.current_language());
    echo(getPath($DB,$serverid).'/service/index.php?related=ICD10:FULL&query=&outputType=json&limit=10&offset=0&order=asc');
    die(0);
  break;
  /******************************************************************************************************/
  case "querycollectiontype" :    
    if(ISSET($_GET["serverid"])) $serverid = $_GET["serverid"];
    else $serverid = 0;

    $response = file_get_contents(getPath($DB,$serverid).'/service/index.php?info&lang='.current_language());
    $obj=json_decode($response);
    
    $arr = [];
    for($i=0;$i<count($obj->response->docs->Collection_Type);$i++) {
      $arr[] = $obj->response->docs->Collection_Type[$i];
    }
    $response = json_encode($arr);
  break;
  /******************************************************************************************************/
  case "querycollection" :    
    if(ISSET($_GET["serverid"])) $serverid = $_GET["serverid"];
    else $serverid = 1;

    if(ISSET($_GET["collectiontype"])) $collectiontype = $_GET["collectiontype"];
    else $collectiontype = '';

    $response = file_get_contents(getPath($DB,$serverid).'/service/index.php?info&lang='.current_language());
    $obj=json_decode($response);

    for($i=0;$i<count($obj->response->docs->Collection_Type);$i++) {
      if($obj->response->docs->Collection_Type[$i]->ref == $collectiontype) {
        $response = json_encode($obj->response->docs->Collection_Type[$i]);
        break;
      }
    }
  break;
  /******************************************************************************************************/
}
echo($response);
/******************************************************************************************************/  
function getPath($DB,$id) {
  $result   = $DB->get_record('qtype_serco_engines', array('id' => $id));
  return $result->url;
}
/******************************************************************************************************/
?>
