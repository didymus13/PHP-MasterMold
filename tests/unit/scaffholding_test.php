<?php
require_once('simpletest/autorun.php');
require_once('../config.php');
require_once('../scaffhold.model.php');
require_once('MDB2.php');

class ScaffholdTestCase extends UnitTestCase {
	private $db;
	
	function setUp() {
		global $dsn, $options;
		try {
			$this->db =& MDB2::factory($dsn, $options);
			if (PEAR::isError($this->db)) {
				throw $this->db;	
			}
		} catch (Exception $e) {
			throw $e;
		}
		return True;
	}
	
	function tearDown() {
		return True;	
	}
	
	function testInstantiateBlank() {
		$s = new Scaffhold($db);
		$this->assertTrue(is_a($s, 'Scaffhold'));
	}
}
?>