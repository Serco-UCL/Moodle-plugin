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
 * Question type class for the description 'question' type.
 *
 * @package    qtype
 * @subpackage serco
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');


/**
 * The description 'question' type.
 *
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_serco extends question_type {
	// serco type is not a real question...
    public function is_real_question_type() {
        return false;
    }

	// serco type is not a real question...
    public function is_usable_by_random() {
        return false;
    }

	// serco type is not a real question...
    public function can_analyse_responses() {
        return false;
    }

	// save the data and put mark to 0 because it's not a question...
    public function save_question($question, $form) {
        // Make very sure that descriptions can't be created with a grade of
        // anything other than 0.
        
        $form->defaultmark = 0;
        return parent::save_question($question, $form);
    }

   // save the data of the additionals fields of the serco type (called options...).
   public function save_question_options($question) {
		// update database with question type additional values emptylist and collectiontype
        global $DB;

        if ($options = $DB->get_record('qtype_serco', array('questionid' => $question->id))) {
            $options->emptylist  	    = $question->emptylist;
            $options->serverid 	      = $question->serverid;
            $options->collectiontype 	= $question->collectiontype;
            $options->collection	    = $question->collection;
            $options->isquestion      = $question->isquestion;
			
            $DB->update_record('qtype_serco', $options);
        } else {
            $options = new stdClass();
            $options->questionid 	    = $question->id;
            $options->emptylist  	    = $question->emptylist;
            $options->serverid 	      = $question->serverid;
            $options->collectiontype 	= $question->collectiontype;
            $options->collection	    = $question->collection;
            $options->isquestion      = $question->isquestion;

            $DB->insert_record('qtype_serco', $options);
        }
   }
   
   // get the data of the additionals fields of the serco type (called options...).
   public function get_question_options($question) {
        global $DB, $OUTPUT;
        // Get additional information from database
        // and attach it to the question object.
        
        if (!$question->options = $DB->get_record('qtype_serco', array('questionid' => $question->id))) {
            echo $OUTPUT->notification('Error: Missing question options!');
            return false;
        }

        return true;
   }

   // If the question is deleted the additional data should be too...
   public function delete_question($questionid, $contextid) {
        global $DB;
        $DB->delete_records('qtype_serco', array('questionid' => $questionid));

        parent::delete_question($questionid, $contextid);
    }

	// serco type is not a real question... 
    public function actual_number_of_questions($question) {
        // Used for the feature number-of-questions-per-page
        // to determine the actual number of questions wrapped by this question.
        // The question type description is not even a question
        // in itself so it will return ZERO!
        return 0;
    }

	// serco type is not a real question...
    public function get_random_guess_score($questiondata) {
        return null;
    }
    
    public function display_question_editing_page($mform, $question, $wizardnow) {
        global $OUTPUT;
        $mform->display();
    }    
    
    // Describe the additional option fields to display it...
    public function extra_question_fields() {
        return array('qtype_serco',
                     'serverid',            // server id for collection service
                     'collectiontype',      // type of collection
                     'collection',		      // collection
                     'emptylist',           // list is empty is search is empty and emplist true
                     'isquestion',          // If true serco is used as question
                     );
    }


    // IMPORT/EXPORT FUNCTIONS --------------------------------- .

    /*
     * Imports question from the Moodle XML format
     *
     * Imports question using information from extra_question_fields function
     * If some of you fields contains id's you'll need to reimplement this
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {
        $question_type = $data['@']['type'];
        if ($question_type != $this->name()) {
            return false;
        }

        $extraquestionfields = $this->extra_question_fields();
        if (!is_array($extraquestionfields)) {
            return false;
        }

        // Omit table name.
        array_shift($extraquestionfields);
        $qo = $format->import_headers($data);
        $qo->qtype = $question_type;

        foreach ($extraquestionfields as $field) {
            $qo->$field = $format->getpath($data, array('#', $field, 0, '#'), '');
        }

        // Run through the answers.
        $answers = $data['#']['answer'];
        $a_count = 0;
        $extraanswersfields = $this->extra_answer_fields();
        if (is_array($extraanswersfields)) {
            array_shift($extraanswersfields);
        }

        return $qo;
    }

    /*
     * Export question to the Moodle XML format
     *
     * Export question using information from extra_question_fields function
     * If some of you fields contains id's you'll need to reimplement this
     */
    public function export_to_xml($question, qformat_xml $format, $extra=null) {
        $extraquestionfields = $this->extra_question_fields();
        if (!is_array($extraquestionfields)) {
            return false;
        }

        // Omit table name.
        array_shift($extraquestionfields);
        $expout='';
        foreach ($extraquestionfields as $field) {
            $exportedvalue = $format->xml_escape($question->options->$field);
            $expout .= "    <{$field}>{$exportedvalue}</{$field}>\n";
        }

        $extraanswersfields = $this->extra_answer_fields();
        if (is_array($extraanswersfields)) {
            array_shift($extraanswersfields);
        }

        return $expout;
    }

    
}

// Just to add some traces in the apache error.log. Dirty must be removed...
function var_error_log( $object=null ){
    ob_start();                    // start buffer capture
    var_dump( $object );           // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean();                // end capture
    error_log( $contents );        // log contents of the result of var_dump( $object )
}
