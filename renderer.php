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
 * Description 'question' renderer class.
 * Display the UI to communicate with the configured service.
 * Generates the output for queries result.
 *
 * @package    qtype
 * @subpackage serco
 * @copyright  2018 UCL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
 
global $PAGE;

// Add javascript and CSSZ to header of the page ! 
$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/question/type/serco/javascript/bootstrap.min.css'),true);
$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/question/type/serco/javascript/bootstrap-table.min.css'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/question/type/serco/javascript/jquery.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/question/type/serco/javascript/bootstrap.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/question/type/serco/javascript/bootstrap-table.min.js'),true);
 
class qtype_serco_renderer extends qtype_renderer {
  private $myTableForm;    
  
  public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
		global $DB;
		
		$id = $qa->get_question()->id;
		$qoption = $DB->get_record('qtype_serco', array('questionid' => $qa->get_question()->id));
	  if($qoption->emptylist == false) $empty = 'no';
	  else $empty = 'yes';

    $serverid		  = $qoption->serverid;
	  $type 			  = $qoption->collectiontype;
		$collection 	= $qoption->collection;
    $isquestion 	= $qoption->isquestion;

    $result   = $DB->get_record('qtype_serco_engines', array('id' => $serverid));
    $path     = $result->url;

    $maxline      = get_config('qtype_sercoc','tablenbline');
    $displayline  = get_config('qtype_sercoc','screennbline');
    $accesstype   = get_config('qtype_sercoc','accesstype');
        
		if($maxline == 0) $maxline = 10;
		if($displayline == 0) $displayline = $maxline;
		$tableHeight  = (95+$displayline*20);
		if($accesstype != 0) return html_writer::tag('div', $qa->get_question()->format_questiontext($qa).'<p><iframe src="'.$path.'/serviceinclude.php?related='.$type.'&amp;empty='.$empty.'" width="100%" height="300"></iframe></p>', array('class' => 'qtext'));

    $response = file_get_contents(new moodle_url($CFG->wwwroot.'/question/type/serco/service.php').'?action=service&CollectionType='.$type.'&serverid='.$serverid.'&outputType=json&collection='.$collection.'&query=&order=asc&offset=0&limit=1');
    $obj=json_decode($response);
		$myForm = '';
		$myForm = $myForm . '<div style="background-color:transparent;">';
		$myForm = $myForm . '<form>';
		$myForm = $myForm . '	<div class="form-group" style="height:50px">';
    $myForm = $myForm . '		<div class="row">';
		$myForm = $myForm . '			<div class="col-sm-3" style="min-width:180px;max-width:181px;"><input type="text" style="height:24px;" class="form-control" id="search_data_'.$id.'"></div>';
		$myForm = $myForm . '			<div class="col-sm-3"><button  id="search_data_'.$id.'_btn" type="button" class="btn btn-xs" style="background-color:#00214e;color:white;" onclick="search'.$type.'_'.$id.'();">'.get_string('find', 'qtype_serco').'</button></div>';
		$myForm = $myForm . '		</div>';
		$myForm = $myForm . '	</div>';
		$myForm = $myForm . '</form>';
		$myForm = $myForm . '</div>';
    $myForm = $myForm . '<div class="row">';
    $this->display_table($id, $obj, $maxline, $serverid, $type, $tableHeight,$collection,$isquestion);
//    if($type != 'ICD10') $this->display_table($id, $obj, $maxline, $serverid, $type, $tableHeight,$collection);
//    else $this->display_tree($id, $obj, $maxline, $serverid, $type, $tableHeight,$collection);
    $myForm = $myForm . $this->myTableForm;
    $myForm = $myForm . '</div>';
 		$myForm = $myForm . '<script>';
		$myForm = $myForm . '  $(document).on("keypress", "input", function(e) {';	
		$myForm = $myForm . '    if(e.keyCode == 13 && e.target.type !== "submit") {';	
    $myForm = $myForm . '      var btid = $(this).attr("id") + "_btn";';
		$myForm = $myForm . '      $("#"+btid).click();';	
		$myForm = $myForm . '      e.preventDefault();';	
		$myForm = $myForm . '      return $(e.target).blur().focus();';	
		$myForm = $myForm . '    }';	
		$myForm = $myForm . '  });';	
    $myForm = $myForm . '  $(".no-records-found").hide();';	
    if($isquestion == 1) {
      $myForm = $myForm . '$("#icd_table_'.$id.'").closest("div.serco").removeClass("serco");';
    }      
		$myForm = $myForm . '</script>';
		
		//return html_writer::tag('div','<div style="margin-bottom:7px;">'.$qa->get_question()->name.'</div>'.$qa->get_question()->format_questiontext($qa).$myForm, array('class' => 'qtext'));
    return html_writer::tag('div',$qa->get_question()->format_questiontext($qa).$myForm, array('class' => 'qtext'));
  }

  public function formulation_heading() {
    return get_string('informationtext', 'qtype_serco');
  }
    
  public function data_preprocessing($question) {
    $question = parent::data_preprocessing($question);
  }
    
  public function specific_feedback(question_attempt $qa) {
    $question = $qa->get_question();
    $response = $qa->get_last_qt_var('answer', '');
  }
  
  public  function display_table($id, $obj, $maxline, $serverid, $type, $tableHeight,$collection,$isquestion) {
    $this->myTableForm = "";
		$this->myTableForm = $this->myTableForm . '<div id="div_table_'.$id.'" class="table-responsive" style="">';
		$this->myTableForm = $this->myTableForm . '  <table id="icd_table_'.$id.'" class="table table-sm table-striped table-bordered" style="overflow-y: auto;width:100%;" data-toggle="table" data-height="'.$tableHeight.'" data-side-pagination="server" data-pagination="true"   data-pagination-h-align="left" data-page-list="[]" data-row-style="rowStyle" data-page-size='.$maxline.'>';
		$this->myTableForm = $this->myTableForm . '    <thead><tr>';
    foreach($obj->rows[0] as $key => $value) {
      if($key != 'id') $this->myTableForm = $this->myTableForm . '      <th data-field="'.$key.'">'.ucfirst($key).'</th>';
    }
    $this->myTableForm = $this->myTableForm . '    </tr></thead>';
		$this->myTableForm = $this->myTableForm . '	';
		$this->myTableForm = $this->myTableForm . '  </table>';

    if($isquestion == 1) {
      $this->myTableForm = $this->myTableForm . '<br>';
      $this->myTableForm = $this->myTableForm . '  <table id="icd_table_'.$id.'_answers" class="table table-sm table-striped table-bordered">';
      $this->myTableForm = $this->myTableForm . '    <thead><tr>';
      $this->myTableForm = $this->myTableForm . '      <th>Answer</th>';
      $this->myTableForm = $this->myTableForm . '      <th>Action</th>';
      $this->myTableForm = $this->myTableForm . '    </tr></thead>';
      $this->myTableForm = $this->myTableForm . '  </table>';
    }      
    
		$this->myTableForm = $this->myTableForm . '</div>';

		$this->myTableForm = $this->myTableForm . '<script>';
		$this->myTableForm = $this->myTableForm . '  var table_'.$id.' = $("#icd_table_'.$id.'");';
    $this->myTableForm = $this->myTableForm . '  table_'.$id.'.bootstrapTable({formatNoMatches: function () {return "'.get_string('NoRecordFound', 'qtype_serco').' ";},formatShowingRows: function (pageFrom, pageTo, totalRows) {return "'.get_string('showing', 'qtype_serco').' "+pageFrom+" '.get_string('to', 'qtype_serco').' "+pageTo+" '.get_string('of', 'qtype_serco').' "+totalRows+" '.get_string('record', 'qtype_serco').'";}});';
		$this->myTableForm = $this->myTableForm . '  function search'.$type.'_'.$id.'() {';
		$this->myTableForm = $this->myTableForm . '    var param = $("#search_data_'.$id.'").val();';
		$this->myTableForm = $this->myTableForm . '    myUrl = "'.new moodle_url($CFG->wwwroot.'/question/type/serco/service.php').'?action=service&CollectionType='.$type.'&serverid='.$serverid.'&outputType=json&collection='.$collection.'&query="+param;';
		$this->myTableForm = $this->myTableForm . '    table_'.$id.'.bootstrapTable("refresh",{url: myUrl});';
		$this->myTableForm = $this->myTableForm . '    table_'.$id.'.bootstrapTable("selectPage", 1);';
		$this->myTableForm = $this->myTableForm . '  }';
		$this->myTableForm = $this->myTableForm . '  function rowStyle(row, index) {';
 		$this->myTableForm = $this->myTableForm . '  	return {';
   	$this->myTableForm = $this->myTableForm . '  		classes: "",';
   	$this->myTableForm = $this->myTableForm . '  		css: {"font-size": "12px;padding:1px;"}';
 		$this->myTableForm = $this->myTableForm . '   };';
		$this->myTableForm = $this->myTableForm . '  }';	
 		$this->myTableForm = $this->myTableForm . '</script>';
  } 
  
  public  function display_tree($id, $obj, $maxline, $serverid, $type, $tableHeight,$collection) {
    $this->myTableForm = "";
    $this->myTableForm = $this->myTableForm . '<div class="css-treeview">';
    $this->myTableForm = $this->myTableForm . ' <ul id="head-treeview">';
    $this->myTableForm = $this->myTableForm . ' </ul>';
    $this->myTableForm = $this->myTableForm . '</div>';
		$this->myTableForm = $this->myTableForm . '<script>';
    $this->myTableForm = $this->myTableForm . '  function search'.$type.'_'.$id.'() {';
		$this->myTableForm = $this->myTableForm . '   var param = $("#search_data_'.$id.'").val();';
 		$this->myTableForm = $this->myTableForm . '   myUrl = "'.new moodle_url($CFG->wwwroot.'/question/type/serco/service.php').'?action=service&CollectionType='.$type.'&serverid='.$serverid.'&outputType=json&collection='.$collection.'&query="+param;';
 		$this->myTableForm = $this->myTableForm . '   var jqxhr = $.getJSON({url:myUrl,async: false}, function() { ';
 		$this->myTableForm = $this->myTableForm . '        }).done(function(result) {';
 		$this->myTableForm = $this->myTableForm . '    $("#head-treeview").empty();';
 		$this->myTableForm = $this->myTableForm . '    var arrayLength = result["rows"].length;';
 		$this->myTableForm = $this->myTableForm . '    var mytree = "";';
 		$this->myTableForm = $this->myTableForm . '    var head = "";';
 		$this->myTableForm = $this->myTableForm . '    var currentHead = "";';
 		$this->myTableForm = $this->myTableForm . '    for (var i = 0; i < arrayLength; i++) {';
 		$this->myTableForm = $this->myTableForm . '      currentHead = (result["rows"][i].code).substr(0, 1);';
 		$this->myTableForm = $this->myTableForm . '      if(currentHead != head) {';
 		$this->myTableForm = $this->myTableForm . '        if(head != "") {';
 		$this->myTableForm = $this->myTableForm . '          mytree = mytree + "</ul></li>";';
 		$this->myTableForm = $this->myTableForm . '        }';
		$this->myTableForm = $this->myTableForm . '        head = currentHead;';

 		$this->myTableForm = $this->myTableForm . '        mytree = mytree + "<li><input type=\"checkbox\" id=\"item" + result.rows[i].code + "\" /><label for=\"item" + result.rows[i].code + "\">" + result.rows[i].code + " - " + result.rows[i].description+ "</label>";';

 		$this->myTableForm = $this->myTableForm . '        mytree = mytree + " <ul>"';
 		$this->myTableForm = $this->myTableForm . '      } else {';
 		$this->myTableForm = $this->myTableForm . '        mytree = mytree + "    <li><a href=\"#\">"+ result["rows"][i].code + " - "+ result["rows"][i].description + "</a></li>";';
 		$this->myTableForm = $this->myTableForm . '      }';

 		$this->myTableForm = $this->myTableForm . '    }';
 		$this->myTableForm = $this->myTableForm . '    mytree = mytree + "</ul></li>";';
 		$this->myTableForm = $this->myTableForm . '    $("#head-treeview").append(mytree);';
    
 		$this->myTableForm = $this->myTableForm . '        }).fail(function() {';
 		$this->myTableForm = $this->myTableForm . '        }).always(function() {';
 		$this->myTableForm = $this->myTableForm . '        });';
    $this->myTableForm = $this->myTableForm . '  }';	
 		$this->myTableForm = $this->myTableForm . '</script>';
  } 
  
}
