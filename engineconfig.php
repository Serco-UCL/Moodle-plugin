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

  $config                  = new stdClass();
  $config->tablenbline     = '';
  $config->screennbline    = '';
  $config->accesstype      = '';
  $config->defaultserver   = '';

  if(ISSET($_POST["tablenbline"]))    $config->tablenbline    = $_POST["tablenbline"];
  if(ISSET($_POST["screennbline"]))   $config->screennbline   = $_POST["screennbline"];
  if(ISSET($_POST["accesstype"]))     $config->accesstype     = $_POST["accesstype"];
  if(ISSET($_POST["defaultserver"]))  $config->defaultserver  = $_POST["defaultserver"];
  
  qtype_serco_engine_manager::get()->saveConfig($config);
  redirect(new moodle_url('/question/type/serco/engines.php'));
?>