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

/**
 * Parameter form class.
 *
 * This provides an interface to manage admin SERCO configuration.
 *
 * Note that all mandatory fields (non-optional) of your model should be included in the
 * form definition. Mandatory fields which are not editable by the user should be
 * as hidden and constant.
 *
 * @package    qtype
 * @copyright  2018 UCL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_serco_engine_manager {

    protected static $instance = null;

    /**
     * Create a singleton and return it
     *
     * @param  void
     * @return qtype_serco_engine_manager get the engine manager.
     */
    public static function get() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Return configuration of a specific server
     *
     * @param  unique id of the server collection engine
     * @return a qtype_serco_engines DB record.
     */    
    public function load($id) {
        global $DB;
        $engine = $DB->get_record('qtype_serco_engines', array('id' => $id), '*', MUST_EXIST);
        return $engine;
    }

    /**
     * Create or update a specific record of the configuration of a specific server
     *
     * @param  an object of the type qtype_serco_engines DB record.
     * @return unique id of the server collection engine
     */    
    public function save($engine) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();

        if (!empty($engine->id)) {
            $DB->update_record('qtype_serco_engines', $engine);
        } else {
            $engine->id = $DB->insert_record('qtype_serco_engines', $engine);
            //self::create_types($engine);
        }
        $transaction->allow_commit();
        return $engine->id;
    }

    /**
     * Create the general configuration of the SERCO plugin
     *
     * @param  an object with SERCO general configuration.
     * @return void
     */    
    public function saveConfig($config) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records('config_plugins', array('plugin' => 'qtype_sercoc'));
        $config1                  = new stdClass();
        $config1->plugin          = 'qtype_sercoc';
        $config1->name            = 'tablenbline';
        $config1->value           = $config->tablenbline;
        $config2                  = new stdClass();
        $config2->plugin          = 'qtype_sercoc';
        $config2->name            = 'screennbline';
        $config2->value           = $config->screennbline;
        $config3                  = new stdClass();
        $config3->plugin          = 'qtype_sercoc';
        $config3->name            = 'accesstype';
        $config3->value           = $config->accesstype;
        $config4                  = new stdClass();
        $config4->plugin          = 'qtype_sercoc';
        $config4->name            = 'defaultserver';
        $config4->value           = $config->defaultserver;
        
        $DB->insert_record('config_plugins', $config1);
        $DB->insert_record('config_plugins', $config2);
        $DB->insert_record('config_plugins', $config3);
        $DB->insert_record('config_plugins', $config4);

        $transaction->allow_commit();
        cache_helper::purge_all();
        return;
    }
    
    /**
     * Delete a specific record of the configuration of a specific server
     *
     * @param  unique id of the server collection engine
     * @return void
     */    
    public function delete($id) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records('qtype_serco_engines', array('id' => $id));
        //$DB->delete_records('qtype_serco_types', array('serverid' => $id));
        $transaction->allow_commit();
    }

    /**
     * Get an instance of the class initialised
     *
     * @param  an object of the type qtype_serco_engines DB record.
     * @return void
     */    
    public function get_connection($engine) {
        return new qtype_serco_connection($engine);
    }

    /**
     * Get info about a new initialised class 
     *
     * @param  an object of the type qtype_serco_engines DB record.
     * @return info about the instance of qtype_serco_connection 
     */    
    public function get_engine_info($engine) {
        return $this->get_connection($engine)->get_engine_info();
    }

    /** DEPRECATED
     * Create the collection types linked to a distant server 
     *
     * @param  an object of the type qtype_serco_engines DB record.
     * @return void
     */    
    public function create_types($engine) {
      global $DB;
      $transaction = $DB->start_delegated_transaction();
      $DB->delete_records('qtype_serco_types', array('serverid' => $engine->id));

      $jsonurl  = new moodle_url($engine->url.'/service/index.php?info');
      $json     = file_get_contents($jsonurl);
      $obj      = json_decode($json);
      $length   = count($obj->response->docs->Collection_Type);
      for ($i = 0; $i < $length; $i++) {
        $type                   = new stdClass();
        $type->serverid         = $engine->id;
        $type->typeid           = $obj->response->docs->Collection_Type[$i]->id;
        $type->typename         = $obj->response->docs->Collection_Type[$i]->name;
        $type->typeref          = $obj->response->docs->Collection_Type[$i]->ref;
        $type->typedescription  = $obj->response->docs->Collection_Type[$i]->description;
        $typeid = $DB->insert_record('qtype_serco_types', $type);        
      }     
      $transaction->allow_commit();      
    }
    
    /** DEPRECATED
     * Update the collection types linked to a distant server 
     *
     * @param  The id of the server to update
     * @return void
     */    
    public function reload_types($id) {
      global $DB;
      $transaction = $DB->start_delegated_transaction();
      $engine = self::load($id);    
      
      $jsonurl  = $engine->url.'/service/index.php?info';
      
      $json     = file_get_contents($jsonurl);
      $obj      = json_decode($json);
      $length   = count($obj->response->docs->Collection_Type);
      for ($i = 0; $i < $length; $i++) {
        $collectiontype = $DB->get_record('qtype_serco_types', array('serverid' => $engine->id,'typeref' => $obj->response->docs->Collection_Type[$i]->ref));
        if($collectiontype === false) {
          $type                   = new stdClass();
          $type->serverid         = $engine->id;
          $type->typeid           = $obj->response->docs->Collection_Type[$i]->id;
          $type->typename         = $obj->response->docs->Collection_Type[$i]->name;
          $type->typeref          = $obj->response->docs->Collection_Type[$i]->ref;
          $type->typedescription  = $obj->response->docs->Collection_Type[$i]->description;
          $typeid = $DB->insert_record('qtype_serco_types', $type);        
        }
      }     
      $transaction->allow_commit();      
    }   
    
    
    public function find_or_create($engine) {
        global $DB;
        /*
            ob_start();
            var_dump ($engine->url);  
            $result = ob_get_clean();
            file_put_contents ('/var/www/moodle_mbz/log.txt', $result);        
        */
        $result = $DB->get_records_sql("SELECT * FROM mdl_qtype_serco_engines WHERE url = '".$engine->url."'");
        foreach ($result as $server){
          return $server->id;
        }
        throw new Exception('The Serco server is not configured on your moodle. please contact your administrator');
        //return $this->save($engine);
    }
    
}
//$sql = "UPDATE {qtype_serco_engines} SET servername='".$engine->servername."', url='".$engine->url."', tablenbline=".$engine->tablenbline.", screennbline=".$engine->screennbline.", accesstype=".$engine->accesstype." WHERE id=".$engine->id;
//$DB->execute($sql);
