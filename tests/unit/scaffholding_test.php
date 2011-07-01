<?php
require_once('simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/../scaffhold.model.php');
require_once('MDB2.php');

class ScaffholdTestCase extends UnitTestCase {
	private $db;
	
	function setUp() {
		global $dsn, $options;
		$this->db =& MDB2::factory($dsn, $options);
		if (PEAR::isError($this->db)) die($this->db->getMessage());
		return True;
		
    	// setup bogus table
    	$this->db->loadModule('Reverse', null, True);
    	$fields = array(
    		'tt_id' => array (
    			'type' => 'integer',
    			'unsigned' => True,
    			'autoincrement' => True),
    		'tt_label' => array(
    			'type' => 'text',
    			'length' => 255), 
    	);
    	$this->db->loadModule('Manager', null, true);
    	$this->db->manager->createTable('test_table', $fields);
	}
	
	function tearDown() {
		$this->db->loadModule('Manager', null, true);
		$this->db->manager->dropTable('test_table');
	}
	
	function testInstantiateBlank() {
		$s = new ScaffholdTest($this->db);
		$this->assertTrue(is_a($s, 'ScaffholdTest'));
		$this->assertEqual('', $s->tt_id);
	}
}
?>