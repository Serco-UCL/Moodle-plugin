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
 
require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/question/type/serco/enginemanager.php');
require_once($CFG->dirroot . '/question/type/serco/edit_engine_form.php');

$id = optional_param('id', 0, PARAM_INT);

// Check the user is logged in.
require_login();
$context = context_system::instance();
require_capability('moodle/question:config', $context);

// Includes
admin_externalpage_setup('qtypesettingserco', '', null, new moodle_url('/question/type/serco/editengine.php', array('id' => $id)));
$PAGE->set_title('Serco server configuration');
$PAGE->navbar->add('serco conf.');

// Create form.
$mform = new qtype_serco_engine_edit_form('editengine.php');

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/question/type/serco/engines.php'));
} else if ($data = $mform->get_data()) {
    $engine = new stdClass();
  if (!empty($data->id)) {
    $engine->id = $data->id;
  }
  $engine->servername   = $data->servername;
  $engine->url          = $data->url;

  if (!empty($data->id)) {
    add_to_log(SITEID, 'qtype_serco', 'edit server','question/type/serco/engines.php', $data->id);
  } else {
    add_to_log(SITEID, 'qtype_serco', 'create server','question/type/serco/engines.php', $engine->servername);
  }

  qtype_serco_engine_manager::get()->save($engine);
  redirect(new moodle_url('/question/type/serco/engines.php'));
}

// Prepare defaults.
$defaults = new stdClass();
$defaults->id = $id;
if ($id) {
  $engine = qtype_serco_engine_manager::get()->load($id);
  $defaults->servername   = $engine->servername;
  $defaults->url          = $engine->url;
}
$mform->set_data($defaults);

// Display the form.
echo $OUTPUT->header();
echo $OUTPUT->heading_with_help('Edit server configuration', 'editquestionengine', 'qtype_serco');
$mform->display();
echo $OUTPUT->footer();
