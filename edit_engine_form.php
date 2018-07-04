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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/validateurlsyntax.php');

/**
 * Parameter form class.
 *
 * This provides an interface to manage servers configuration.
 *
 * Note that all mandatory fields (non-optional) of your model should be included in the
 * form definition. Mandatory fields which are not editable by the user should be
 * as hidden and constant.
 *
 *  @var id int unique id of the server
 *  @var servername  string name of the server
 *  @var url  string HTTP path of the server (ex: http://www.exemple.com) 
 *
 * @package    qtype
 * @copyright  2018 UCL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_serco_engine_edit_form extends moodleform {
    /** @var int timeout for SOAP calls, in seconds. */
    const DEFAULT_TIMEOUT = 10; // Seconds.

    /**
     * Create the admin form for gloabal param
     *
     * @param  void
     * @return void
     */
    protected function definition() {
      $mform = $this->_form;

      $attributes='size="40"';
        
      $mform->addElement('text', 'servername', get_string('servername', 'qtype_serco'),$attributes);
      $mform->addRule('servername', 'Server name is mandatory', 'required', null, 'client');

      $mform->addElement('text', 'url', get_string('url', 'qtype_serco'),$attributes);
      $mform->addRule('url', 'URL is mandatory', 'required', null, 'client');

      $mform->addElement('hidden', 'id');
      $mform->setType('id', PARAM_INT);

      $this->add_action_buttons();
    }
}
