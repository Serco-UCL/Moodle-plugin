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
 * Defines the editing form for the description question type.
 *
 * @package    qtype
 * @subpackage serco
 * @copyright  2018 UCL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

 global $PAGE;
//$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/question/type/serco/javascript/bootstrap.min.css'),true);
$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/question/type/serco/javascript/bootstrap-table.min.css'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/question/type/serco/javascript/jquery.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/question/type/serco/javascript/bootstrap.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/question/type/serco/javascript/bootstrap-table.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/question/type/serco/javascript/serco.js'),true);

/**
 * Description editing form definition.
 *
 * @copyright  2018 UCL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_serco_edit_form extends question_edit_form {
  /**
  * Add question-type specific form fields for configuration.
  *
  * @param MoodleQuickForm $mform the form being built.
  */
  protected function definition_inner($mform) {
      global $DB;
      global $USER;
      $servers        = array();
      $collectiontype = array();
      $collection     = array();

      // We don't need these default elements.
      $mform->removeElement('defaultmark');
      $mform->removeElement('generalfeedback');
      $mform->addElement('hidden', 'generalfeedback',0);
      $mform->addElement('hidden', 'defaultmark', 0);
      $mform->setType('defaultmark', PARAM_RAW);

      $server = $DB->get_records_sql('SELECT * FROM {qtype_serco_engines}');
      foreach ($server as $s) {
        $servers[$s->id] = $s->servername;
      }

      if(get_string('namehelper', 'qtype_serco') != '') 
        $mform->addHelpButton('name', 'namehelper', 'qtype_serco');
      if(get_string('questionhelper', 'qtype_serco') != '') 
        $mform->addHelpButton('questiontext', 'questionhelper', 'qtype_serco');
      $myselect = $mform->addElement('select', 'serverid',get_string('server', 'qtype_serco'),  $servers, $attributes);
      if(get_string('serveridhelper', 'qtype_serco') != '') 
        $mform->addHelpButton('serverid', 'serveridhelper', 'qtype_serco');
      $myselect->setSelected(get_config('qtype_sercoc','defaultserver'));
      $mform->addElement('select', 'collectiontype2',get_string('searchtype', 'qtype_serco'),  $collectiontype, $attributes);
      if(get_string('collectiontypeidhelper', 'qtype_serco') != '') 
        $mform->addHelpButton('collectiontype2', 'collectiontypeidhelper', 'qtype_serco');
      $mform->addElement('select', 'collection2', get_string('searchlevel', 'qtype_serco'),  $collection, $attributes);
      if(get_string('collectionidhelper', 'qtype_serco') != '') 
        $mform->addHelpButton('collection2', 'collectionidhelper', 'qtype_serco');
      $mform->addElement('text', 'collectiontype', '', 'qtype_serco');
      $mform->addElement('text', 'collection', '', 'qtype_serco');
      $mform->addRule('serverid', null, 'required', null, 'client');
      $mform->addRule('collectiontype2', null, 'required', null, 'client');
      $mform->addElement('advcheckbox', 'emptylist', get_string('emptylisttitle', 'qtype_serco'), get_string('emptylist', 'qtype_serco'), array('group' => 1), array(0, 1));
      if(get_string('emptylisthelper', 'qtype_serco') != '') 
        $mform->addHelpButton('emptylist', 'emptylisthelper', 'qtype_serco');
      $mform->setType('emptylist', PARAM_RAW);

      $mform->addElement('advcheckbox', 'isquestion', get_string('isquestiontitle', 'qtype_serco'), get_string('isquestion', 'qtype_serco'), array('group' => 1), array(0, 1));
      if(get_string('isquestionhelper', 'qtype_serco') != '') 
        $mform->addHelpButton('isquestion', 'isquestionhelper', 'qtype_serco');
      $mform->setType('isquestion', PARAM_RAW);

      
      $mform->addElement('text', 'userid', '', 'qtype_serco');
      $mform->setDefault('userid', $USER->id);
    }

  /**
  * Perform an preprocessing needed on the data passed to {@link set_data()}
  * before it is used to initialise the form.
  * @param object $question the data being passed to the form.
  * @return object $question the modified data.
  */
  protected function data_preprocessing($question) {
      return $question;
  }    

	//********************************************************************************	
  public function qtype() {
    return 'Serco';
  }
	//********************************************************************************	

	protected function definition() {
    global $COURSE, $CFG, $DB, $PAGE;
    $qtype = $this->qtype();
    $langfile = "qtype_{$qtype}";
    $mform = $this->_form;
    // Standard fields at the start of the form.
    $mform->addElement('header', 'generalheader', get_string("general", 'form'));

    if (!isset($this->question->id)) {
        if (!empty($this->question->formoptions->mustbeusable)) {
            $contexts = $this->contexts->having_add_and_use();
        } else {
            $contexts = $this->contexts->having_cap('moodle/question:add');
        }
        // Adding question.
        $mform->addElement('questioncategory', 'category', get_string('category', 'question'),   array('contexts' => $contexts));
    } else if (!($this->question->formoptions->canmove || $this->question->formoptions->cansaveasnew)) {
        // Editing question with no permission to move from category.
        //$mform->addElement('questioncategory', 'category', get_string('category', 'question'),   array('contexts' => array($this->categorycontext)));
        $mform->addElement('hidden', 'category', get_string('category', 'question'),   array('contexts' => array($this->categorycontext)));
        $mform->addElement('hidden', 'usecurrentcat', 1);
        $mform->setType('usecurrentcat', PARAM_BOOL);
        $mform->setConstant('usecurrentcat', 1);
    } else {
        // Editing question with permission to move from category or save as new q.
        $currentgrp = array();
        //$currentgrp[0] = $mform->createElement('questioncategory', 'category', get_string('categorycurrent', 'question'), array('contexts' => array($this->categorycontext)));
        $currentgrp[0] = $mform->createElement('hidden', 'category', get_string('categorycurrent', 'question'), array('contexts' => array($this->categorycontext)));
        if ($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew) {
            // Not move only form.
            //$currentgrp[1] = $mform->createElement('checkbox', 'usecurrentcat', '', get_string('categorycurrentuse', 'question'));
            $currentgrp[1] = $mform->createElement('hidden', 'usecurrentcat', '', get_string('categorycurrentuse', 'question'));
            $mform->setDefault('usecurrentcat', 1);
        }
        $currentgrp[0]->freeze();
        $currentgrp[0]->setPersistantFreeze(false);
        $mform->addGroup($currentgrp, 'currentgrp', ''/*get_string('categorycurrent', 'question')*/, null, false);
        //$mform->addElement('questioncategory', 'categorymoveto', get_string('categorymoveto', 'question'), array('contexts' => array($this->categorycontext)));
        $mform->addElement('hidden', 'categorymoveto', get_string('categorymoveto', 'question'), array('contexts' => array($this->categorycontext)));
        if ($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew) {
            // Not move only form.
            $mform->disabledIf('categorymoveto', 'usecurrentcat', 'checked');
        }
    }

    $mform->addElement('text', 'name', get_string('name', 'qtype_serco'), array('size' => 50, 'maxlength' => 255));
    $mform->setType('name', PARAM_TEXT);
    $mform->addRule('name', null, 'required', null, 'client');
    $mform->addElement('editor', 'questiontext', get_string('description', 'qtype_serco'), array('rows' => 15), $this->editoroptions);
    $mform->setType('questiontext', PARAM_RAW);
    $mform->addRule('questiontext', null, 'required', null, 'client');
    $mform->addElement('text', 'defaultmark', get_string('defaultmark', 'question'), array('size' => 7));
    $mform->setType('defaultmark', PARAM_FLOAT);
    $mform->setDefault('defaultmark', 1);
    $mform->addRule('defaultmark', null, 'required', null, 'client');
    $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'), array('rows' => 10), $this->editoroptions);
    $mform->setType('generalfeedback', PARAM_RAW);
    $mform->addHelpButton('generalfeedback', 'generalfeedback', 'question');
    // Any questiontype specific fields.
    $this->definition_inner($mform);
/*
        if (core_tag_tag::is_enabled('core_question', 'question')) {
            $this->add_tag_fields($mform);
        }
*/		
    if (!empty($this->question->id)) {
        $mform->addElement('header', 'createdmodifiedheader', get_string('createdmodifiedheader', 'question'));
        $a = new stdClass();
        if (!empty($this->question->createdby)) {
            $a->time = userdate($this->question->timecreated);
            $a->user = fullname($DB->get_record(
                    'user', array('id' => $this->question->createdby)));
        } else {
            $a->time = get_string('unknown', 'question');
            $a->user = get_string('unknown', 'question');
        }
        $mform->addElement('static', 'created', get_string('created', 'question'), get_string('byandon', 'question', $a));
        if (!empty($this->question->modifiedby)) {
            $a = new stdClass();
            $a->time = userdate($this->question->timemodified);
            $a->user = fullname($DB->get_record('user', array('id' => $this->question->modifiedby)));
            $mform->addElement('static', 'modified', get_string('modified', 'question'), get_string('byandon', 'question', $a));
        }
    }
    $this->add_hidden_fields();
    $mform->addElement('hidden', 'qtype');
    $mform->setType('qtype', PARAM_ALPHA);
    $mform->addElement('hidden', 'makecopy');
    $mform->setType('makecopy', PARAM_INT);
    $buttonarray = array();
    $buttonarray[] = $mform->createElement('submit', 'updatebutton', get_string('savechangesandcontinueediting', 'question'));

    if ($this->can_preview()) {
        $previewlink = $PAGE->get_renderer('core_question')->question_preview_link($this->question->id, $this->context, true);
        $buttonarray[] = $mform->createElement('static', 'previewlink', '', $previewlink);
    }
    $mform->addGroup($buttonarray, 'updatebuttonar', '', array(' '), false);
    $mform->closeHeaderBefore('updatebuttonar');
    $this->add_action_buttons(true, get_string('savechanges'));
    if ((!empty($this->question->id)) && (!($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew))) {
        $mform->hardFreezeAllVisibleExcept(array('categorymoveto', 'buttonar', 'currentgrp'));
    }

  }	
	
}
