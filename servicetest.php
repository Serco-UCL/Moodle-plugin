<html>
	<head>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<script src="https://code.jquery.com/jquery-3.3.1.min.js"  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </head>
  <body>
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
 * Servicetest.php check if the servers configured in the plugin are online.
 * It give also information about how this service is used in moodle by giving the use of collection type
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

  global $DB;
  
  $serverid = -1;
  $userid   = -1;
  
  if(ISSET($_GET["serverid"]))  $serverid = $_GET["serverid"];
  if(ISSET($_GET["userid"]))    $userid = $_GET["userid"];

  if($serverid > -1) {
    $result = $DB->get_records_sql('SELECT collectiontype, url, count(collectiontype) nbcollectiontype FROM mdl_qtype_serco t1,mdl_qtype_serco_engines t2 WHERE serverid = '.$serverid.' AND t1.serverid= t2.id GROUP BY url, collectiontype');

    echo('<table class="table table-bordered table-striped">');
    echo('  <theader>');
    echo('    <tr>');
    echo('      <th style="background-color:white;">'.get_string('collectiontype', 'qtype_serco').'</th>');
    echo('      <th style="background-color:white;">'.get_string('used',    'qtype_serco').'</th>');
    echo('      <th style="background-color:white;">'.get_string('status',  'qtype_serco').'</th>');
    echo('  </theader>');
    echo('  <tbody>');

    foreach ($result as $server){
      if($server->nbcollectiontype > 0) {
        $collectiontype = $server->collectiontype;
        $jsonurl        = $server->url.'/service/index.php?info';
        $response       = file_get_contents($jsonurl);
        $collectiontypeName        = '';
        $collectiontypeDescription = '';

        if($response === false) {
          echo('    <tr>');
          echo('      <td></td>');
          echo('      <td></td>');
          echo('      <td><span style="color:red;font-weight:bold;">KO</span></td>');
          echo('    </tr>');
        } else {
          $obj=json_decode($response);
          for($i=0;$i<count($obj->response->docs->Collection_Type);$i++) {
            if($obj->response->docs->Collection_Type[$i]->ref == $collectiontype) {
              $collectiontypeName         = $obj->response->docs->Collection_Type[$i]->name;
              $collectiontypeDescription  = $obj->response->docs->Collection_Type[$i]->description;
              break;
            }
          }
          echo('    <tr>');
          echo('      <td>'.$collectiontypeName.' ('.$collectiontypeDescription.')</td>');
          echo('      <td>'.$server->nbcollectiontype.'</td>');
          echo('      <td><span style="color:#50ff00;font-weight:bold;">OK</span></td>');
          echo('    </tr>');
        }
      }
    }
    echo('  </tbody>');
    echo('</table><br>');

    $result = $DB->get_records_sql("SELECT CONCAT(t1.id,'-',cour_serco.instanceid,'-',collectiontype) mainid
                                          ,collectiontype
                                          ,t1.*
                                          ,t2.*
                                          ,cour_serco.* 
                                      FROM {user} t1
                                         ,(SELECT userid, instanceid 
                                             FROM {role_assignments} t1 
                                             LEFT JOIN {context} t2 ON t1.contextid = t2.id 
                                              AND t1.roleid = 3
                                            WHERE t2.instanceid in (SELECT distinct c.id
                                                                      FROM {course} c
                                                                      JOIN {quiz} quiz        ON quiz.course = c.id
                                                                      JOIN {quiz_slots} slot  ON slot.quizid = quiz.id
                                                                      JOIN {question} q       ON q.id = slot.questionid
                                                                     WHERE q.qtype = 'serco')
                                           ) cour_serco
                                          ,{course} t2
                                          ,(SELECT distinct c.id
                                                  ,ser.collectiontype as collectiontype
                                              FROM {course} c
                                              JOIN {quiz} quiz              ON quiz.course = c.id
                                              JOIN {quiz_slots} slot        ON slot.quizid = quiz.id
                                              JOIN {question} q             ON q.id = slot.questionid
                                              JOIN {qtype_serco} ser        ON q.id = ser.questionid 
                                             WHERE q.qtype = 'serco'
                                               AND ser.serverid=".$serverid.") qtype
                                     WHERE t1.id 	  = cour_serco.userid
                                       AND t2.id 	  = cour_serco.instanceid
                                       AND qtype.id = cour_serco.instanceid");

    echo('<table class="table table-bordered table-striped">');
    echo('  <theader>');
    echo('    <tr>');
    echo('      <th style="background-color:white;">'.get_string('lastname',      'qtype_serco').'</th>');
    echo('      <th style="background-color:white;">'.get_string('firstname',     'qtype_serco').'</th>');
    echo('      <th style="background-color:white;">'.get_string('email',         'qtype_serco').'</th>');
    echo('      <th style="background-color:white;">'.get_string('coursname',     'qtype_serco').'</th>');
    echo('      <th style="background-color:white;">'.get_string('collectiontype','qtype_serco').'</th>');
    echo('  </theader>');
    echo('  <tbody>');
    
    foreach ($result as $cours){
      echo('    <tr>');
      echo('      <td>'.$cours->lastname.'</td>');
      echo('      <td>'.$cours->firstname.'</td>');
      echo('      <td>'.$cours->email.'</td>');
      echo('      <td>'.$cours->shortname.'</td>');
      echo('      <td>'.$cours->collectiontype.'</td>');
      echo('    </tr>');
    }
    
    echo('  </tbody>');
    echo('</table>');

  }

  if($userid > -1) {
    
        $result = $DB->get_records_sql("SELECT CONCAT(t1.id,'-',cour_serco.instanceid,'-',collectiontype) mainid
                                          ,collectiontype
                                          ,t1.*
                                          ,t2.*
                                          ,t3.name qname
                                          ,cour_serco.* 
                                      FROM {user} t1
                                         ,(SELECT userid, instanceid 
                                             FROM {role_assignments} t1 
                                             LEFT JOIN {context} t2 ON t1.contextid = t2.id 
                                              AND t1.roleid = 3
                                              AND userid    = ".$userid."
                                            WHERE t2.instanceid in (SELECT distinct c.id
                                                                      FROM {course} c
                                                                      JOIN {quiz} quiz        ON quiz.course = c.id
                                                                      JOIN {quiz_slots} slot  ON slot.quizid = quiz.id
                                                                      JOIN {question} q       ON q.id = slot.questionid
                                                                     WHERE q.qtype = 'serco')
                                           ) cour_serco
                                          ,{course} t2
                                          ,{question} t3
                                          ,(SELECT distinct c.id, q.id qid
                                                  ,ser.collectiontype as collectiontype
                                              FROM {course} c
                                              JOIN {quiz} quiz              ON quiz.course = c.id
                                              JOIN {quiz_slots} slot        ON slot.quizid = quiz.id
                                              JOIN {question} q             ON q.id = slot.questionid
                                              JOIN {qtype_serco} ser        ON q.id = ser.questionid 
                                             WHERE q.qtype = 'serco') qtype
                                     WHERE t1.id 	  = cour_serco.userid
                                       AND t2.id 	  = cour_serco.instanceid
                                       AND t3.id    = qtype.qid
                                       AND qtype.id = cour_serco.instanceid");
    echo('<h4>'.get_string('listofsercoquestion', 'qtype_serco').'</h4>');
    echo('<table class="table table-bordered table-striped">');
    echo('  <theader>');
    echo('    <tr>');
    echo('      <th style="background-color:white;">'.get_string('coursname',     'qtype_serco').'</th>');
    echo('      <th style="background-color:white;">'.get_string('questionheader','qtype_serco').'</th>');
    echo('      <th style="background-color:white;">'.get_string('collectiontype','qtype_serco').'</th>');
    echo('  </theader>');
    echo('  <tbody>');
   
    foreach ($result as $cours){
      echo('    <tr>');
      echo('      <td>'.$cours->shortname.'</td>');
      echo('      <td>'.$cours->qname.'</td>');
      echo('      <td>'.$cours->collectiontype.'</td>');
      echo('    </tr>');
    }
     
    echo('  </tbody>');
    echo('</table>');
  }

?>      
  </body>
</html>  