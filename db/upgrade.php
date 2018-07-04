<?php
    function xmldb_qtype_serco_upgrade($oldversion) {

		global $DB;
		$dbman = $DB->get_manager();

		if ($oldversion < 2018061900) {
         // Define field tablenbline to be added to qtype_serco.
        $table = new xmldb_table('qtype_serco');
        $field = new xmldb_field('isquestion', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'emptylist');

        // Conditionally launch add field tablenbline.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Serco savepoint reached.
        upgrade_plugin_savepoint(true, 2018061900, 'qtype', 'serco');        
		}

    /*
		if ($oldversion < 2018041000) {
         // Define field tablenbline to be added to qtype_serco.
        $table = new xmldb_table('qtype_serco');
        $field = new xmldb_field('searchlevel', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'emptylist');

        // Conditionally launch add field tablenbline.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Serco savepoint reached.
		upgrade_plugin_savepoint(true, 2018041000, 'qtype', 'serco');        
		}
    */
  /*  
		if ($oldversion < 2018041200) {
         // Define field tablenbline to be added to qtype_serco.
        $table = new xmldb_table('qtype_serco');
        $field = new xmldb_field('subcat', XMLDB_TYPE_CHAR, '512', null, null, null, null, 'emptylist');
        $field2 = new xmldb_field('subcat2', XMLDB_TYPE_CHAR, '512', null, null, null, null, 'emptylist');

        // Conditionally launch add field tablenbline.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        // Serco savepoint reached.
        upgrade_plugin_savepoint(true, 2018041200, 'qtype', 'serco');        
		}     

*/        
        return true;
	}
?>
