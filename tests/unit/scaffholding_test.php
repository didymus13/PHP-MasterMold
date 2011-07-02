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
	}
	
	function tearDown() {
		return True;
	}
	
	function testInstantiateBlank() {
		$s = new ScaffholdTest($this->db);
		$this->assertTrue(is_a($s, 'ScaffholdTest'));
		$this->assertEqual('', $s->tt_id);
	}
}
?>