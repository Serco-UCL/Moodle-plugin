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
 
// Check the user is logged in.
require_login();
$context = context_system::instance();
require_capability('moodle/question:config', $context);

admin_externalpage_setup('qtypesettingserco');

$enginemanager = qtype_serco_engine_manager::get();
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/question/type/serco/javascript/jquery.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/question/type/serco/javascript/bootstrap.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/question/type/serco/javascript/bootstrap-table.min.js'),true);


// See if any action was requested.
$delete = optional_param('delete', 0, PARAM_INT);
$reload = optional_param('reload', 0, PARAM_INT);

if ($delete) {
  $engine = $enginemanager->load($delete);

  if (optional_param('confirm', false, PARAM_BOOL) && confirm_sesskey()) {
    add_to_log(SITEID, 'qtype_serco', 'delete server', 'question/type/serco/engines.php', $engine->servername);
    $enginemanager->delete($delete);
    redirect($PAGE->url);
  } else {
    echo $OUTPUT->header();
    echo $OUTPUT->confirm('Are you sure ?', new moodle_url('/question/type/serco/engines.php', array('delete' => $delete, 'confirm' => 'yes', 'sesskey' => sesskey())),$PAGE->url);
    echo $OUTPUT->footer();
    die();
  }
}

if ($reload) {
    $enginemanager->reload_types($reload);
    redirect($PAGE->url);
}    

// Header.
echo $OUTPUT->header();
echo $OUTPUT->heading_with_help(get_string('sercoconf', 'qtype_serco'), 'configuredquestionengines', 'qtype_serco');

$strsave = get_string('submit');

echo('<fieldset class="clearfix" id="id_generalheader">');
echo('  <legend class="" id=""><span style="color:#00acdf;text-decoration: none;"  href="#sercogenparamdiv">'.get_string('globalconfig', 'qtype_serco').'</span></legend>');
echo('  <div id="sercogenparamdiv" class="">');
echo('    <div class="row" style="padding-left:15px;border: 0px solid;margin-bottom:20px;width:90%;margin-left:5px">');
echo('      <form autocomplete="off" action="engineconfig.php" method="post" accept-charset="utf-8" id="mform01" class="mform">');

echo('        <div class="form-group row  fitem">');
echo('          <div class="col-md-3"><label class="col-form-label d-inline " for="listofserver">'.get_string('defaultserverlist', 'qtype_serco').'</label></div>');
echo('          <div class="col-md-9 form-inline felement" data-fieldtype="select">');
if(get_string('defaultserverlisthelp', 'qtype_serco') != '') 
  echo('          <a class="btn btn-link p-a-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="<div class=&quot;no-overflow&quot;>'.get_string('defaultserverlisthelp', 'qtype_serco').'</div> " data-html="true" tabindex="0" data-trigger="focus"><i class="icon fa fa-question-circle text-info fa-fw" aria-hidden="true" title="'.get_string('defaultserverlisthelper', 'qtype_serco').'" aria-label="'.get_string('defaultserverlisthelper', 'qtype_serco').'"></i></a>');
$engines = $DB->get_records_sql('SELECT * FROM {qtype_serco_engines}');
$id = get_config('qtype_sercoc','defaultserver');
if($id == null) $id=1;
echo('          <select class="custom-select" name="defaultserver" id="id_defaultserver">');
foreach ($engines as $server) {
  if($server->id == $id) {
    echo('        <option value="'.$server->id.'"  selected=" ">'.$server->servername.'</option>');
  } else {
    echo('        <option value="'.$server->id.'">'.$server->servername.'</option>');
  }
}
echo('          </select>');
echo('        </div>');
echo('      </div>');

echo('      <div class="form-group row  fitem">');
echo('        <div class="col-md-3"><label class="col-form-label d-inline " for="accesstype">'.get_string('accesstype', 'qtype_serco').'</label></div>');
echo('        <div class="col-md-9 form-inline felement" data-fieldtype="select">');
if(get_string('accesstypehelp', 'qtype_serco') != '') 
  echo('          <a class="btn btn-link p-a-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="<div class=&quot;no-overflow&quot;>'.get_string('accesstypehelp', 'qtype_serco').'</div> " data-html="true" tabindex="0" data-trigger="focus"><i class="icon fa fa-question-circle text-info fa-fw" aria-hidden="true" title="'.get_string('accesstypehelper', 'qtype_serco').'" aria-label="'.get_string('accesstypehelper', 'qtype_serco').'"></i></a>');
echo('          <select class="custom-select" name="accesstype" id="id_accesstype">');
if(get_config('qtype_sercoc','accesstype') != 1) {
  echo('          <option value="0"  selected="">Service</option>');
  echo('          <option value="1">Iframe</option>');
} else {
  echo('          <option value="0">Service</option>');
  echo('          <option value="1" selected="">Iframe</option>');
}
echo('          </select>');
echo('        </div>');
echo('      </div>');

echo('      <div id="addinfo">');
echo('        <div class="form-group row  fitem">');
echo('          <div class="col-md-3"><label class="col-form-label d-inline " for="tablenbline">'.get_string('tablenbline', 'qtype_serco').'</label></div>');
echo('          <div class="col-md-9 form-inline felement" data-fieldtype="text">');
if(get_string('tablenblinehelp', 'qtype_serco') != '') 
  echo('            <a class="btn btn-link p-a-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="<div class=&quot;no-overflow&quot;>'.get_string('tablenblinehelp', 'qtype_serco').'</div> " data-html="true" tabindex="0" data-trigger="focus"><i class="icon fa fa-question-circle text-info fa-fw" aria-hidden="true" title="'.get_string('tablenblinehelper', 'qtype_serco').'" aria-label="'.get_string('tablenblinehelper', 'qtype_serco').'"></i></a>');
echo('            <input class="form-control " name="tablenbline" id="id_tablenbline" size="3" value="'.get_config('qtype_sercoc','tablenbline').'" size="" type="text">');
echo('          </div>');
echo('        </div>');
  
echo('        <div class="form-group row  fitem">');
echo('          <div class="col-md-3"><label class="col-form-label d-inline " for="screennbline">'.get_string('screennbline', 'qtype_serco').'</label></div>');
echo('          <div class="col-md-9 form-inline felement" data-fieldtype="text">');
if(get_string('screennblinehelp', 'qtype_serco') != '') 
  echo('            <a class="btn btn-link p-a-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="<div class=&quot;no-overflow&quot;>'.get_string('screennblinehelp', 'qtype_serco').'</div> " data-html="true" tabindex="0" data-trigger="focus"><i class="icon fa fa-question-circle text-info fa-fw" aria-hidden="true" title="'.get_string('screennblinehelper', 'qtype_serco').'" aria-label="'.get_string('screennblinehelper', 'qtype_serco').'"></i></a>');
echo('            <input class="form-control " name="screennbline" id="id_screennbline" size="3" value="'.get_config('qtype_sercoc','screennbline').'" size="" type="text">');
echo('          </div>');
echo('        </div>');
echo('      </div>');

echo('      <button type="submit" class="btn btn-success" style="margin-bottom:10px;">'.$strsave.'</button>');
echo('    </form>');
echo('  </div>');
echo('</div>');
echo('</fieldset>');

echo('<fieldset class="clearfix" id="id_generalheader">');
echo('  <legend class="" id=""><span style="color:#00acdf;text-decoration: none;"  aria-expanded="false" aria-controls="id_sercolstserverdiv" aria-expanded="true" id="">'.get_string('listofserver', 'qtype_serco').'</span></legend>');
echo('  <div id="sercolstserverdiv" class="">');
//echo('<a class="btn btn-link p-a-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="<div class=&quot;no-overflow&quot;>'.get_string('listofserverhelp', 'qtype_serco').'</div> " data-html="true" tabindex="0" data-trigger="focus"><i class="icon fa fa-question-circle text-info fa-fw" aria-hidden="true" title="'.get_string('listofserverhelper', 'qtype_serco').'" aria-label="'.get_string('listofserverhelper', 'qtype_serco').'"></i></a>');
// Add new engine link.
echo('<a href="'.new moodle_url('/question/type/serco/editengine.php').'">'.get_string('addanotherserver', 'qtype_serco').'</a> ');
if(get_string('addanotherserverhelp', 'qtype_serco') != '') 
  echo('<a class="btn btn-link p-a-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="<div class=&quot;no-overflow&quot;>'.get_string('addanotherserverhelp', 'qtype_serco').'</div> " data-html="true" tabindex="0" data-trigger="focus"><i class="icon fa fa-question-circle text-info fa-fw" aria-hidden="true" title="'.get_string('addanotherserverhelper', 'qtype_serco').'" aria-label="'.get_string('addanotherserverhelper', 'qtype_serco').'"></i></a>');

$stredit    = get_string('edit');
$strdelete  = get_string('delete');
$strrefresh = get_string('refresh');
echo('<br><br>');
$engines = $DB->get_records_sql('SELECT COUNT(t2.serverid) nbq,t1.* FROM {qtype_serco_engines} t1  LEFT JOIN {qtype_serco} t2 ON t1.id = t2.serverid GROUP BY serverid ORDER BY COUNT(t2.serverid) desc');
foreach ($engines as $server) {
  $id = $server->id;
  echo html_writer::start_tag('p');
  echo html_writer::start_tag('b');
  echo(' '.$server->servername.' ');
  echo html_writer::end_tag('b');
  echo(' '.$server->url.' ');
  echo ' ' , $OUTPUT->action_icon(new moodle_url('/question/type/serco/editengine.php', array('id' => $id)), new pix_icon('t/edit', $stredit));
  //echo ' ' , $OUTPUT->action_icon(new moodle_url('/question/type/serco/engines.php', array('reload' => $id)), new pix_icon('t/reload', $strrefresh));      
  if($DB->count_records('qtype_serco',  array('serverid' => $id)) == 0) {
    echo ' ' , $OUTPUT->action_icon(new moodle_url('/question/type/serco/engines.php', array('delete' => $id)), new pix_icon('t/delete', $strdelete));      
    echo('<a class="btn btn-link p-a-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="<div class=&quot;no-overflow&quot;>'.$server->description.'</div> " data-html="true" tabindex="0" data-trigger="focus"><i class="icon fa fa-question-circle text-info fa-fw" aria-hidden="true" title="'.get_string('serverdescription', 'qtype_serco').'" aria-label="'.get_string('serverdescription', 'qtype_serco').'"></i></a>');
  } else {
    echo('<a class="btn btn-link p-a-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="<div class=&quot;no-overflow&quot;>'.$server->description.'</div> " data-html="true" tabindex="0" data-trigger="focus"><i class="icon fa fa-question-circle text-info fa-fw" aria-hidden="true" title="'.get_string('serverdescription', 'qtype_serco').'" aria-label="'.get_string('serverdescription', 'qtype_serco').'"></i></a>');
    echo('<iframe src="'.new moodle_url('/question/type/serco/servicetest.php?serverid').'='.$server->id.'" width="100%" height="250" style="margin-top:15px;"></iframe>');
  }
  echo html_writer::end_tag('p');
}
echo('</div>');
echo('</fieldset>');

echo("<script>");
echo("  $('#id_accesstype').on('change', function() {");
echo("   if($('#id_accesstype').val() == 0) {");
echo("     $('#id_tablenbline').removeAttr('readonly');");
echo("     $('#id_screennbline').removeAttr('readonly');");
echo("   } else {");
echo("     $('#id_tablenbline').attr('readonly','true');");
echo("     $('#id_screennbline').attr('readonly','true');");
echo("   }");
echo("  });");
echo("   if($('#id_accesstype').val() == 0) {");
echo("     $('#id_tablenbline').removeAttr('readonly');");
echo("     $('#id_screennbline').removeAttr('readonly');");
echo("   } else {");
echo("     $('#id_tablenbline').attr('readonly','true');");
echo("     $('#id_screennbline').attr('readonly','true');");
echo("   }");
echo("</script>");

// Footer.
echo $OUTPUT->footer();

/* Code for standard settings 
$settings = new admin_settingpage( 'serco', 'Serco configuration' );
// Create 
$ADMIN->add( 'serco', $settings );
$settings->add(new admin_setting_configtextarea('qtype_serco/inputiframe', 'Serco path', 'This key contain the base urls to Serco services and iframes. Separate by carriage return', '', PARAM_RAW, $cols='60', $rows='8'));
$settings->add(new admin_setting_configtext('qtype_serco/tablenbline', 'Serco result', 'This key contain the number of lines displayed in the Serco result table', '', PARAM_INT));
$settings->add(new admin_setting_configtext('qtype_serco/screennbline', 'Serco display', 'This key contain the number of lines displayed on screen if it\'s smaller than tablenbline a scroolbar will appear', '', PARAM_INT));
$settings->add(new admin_setting_configcheckbox('qtype_serco/accessType', 'Access type (service/iframe)', 'uncheck means that the service will be called by default and checked it\'s the iframe', 0));
*/
?>