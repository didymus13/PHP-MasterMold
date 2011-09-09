<?php
require_once('simpletest-new/autorun.php');
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../scaffhold.model.php');
require_once('MDB2.php');

class ScaffholdTestCase extends UnitTestCase {
	private $db;
	
	function setUp() {
		global $dsn, $options;
		try {
			$this->db =& MDB2::factory($dsn, $options);
			if (PEAR::isError($this->db)) throw new Exception($this->db->getMessage());
			$this->db->setFetchMode(MDB2_FETCHMODE_ASSOC);
			
			$sql = 'DROP TABLE test_table'; // make sure the table isn't left over from other failed tests
			$this->db->query($sql);
			
			$sql = 'CREATE TABLE test_table (tt_id INTEGER, tt_text TEXT)';
			$res = $this->db->query($sql);
			if (PEAR::isError($res)) throw new Exception($res->getMessage());
			return True;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	function tearDown() {
		$sql = 'DROP TABLE test_table';
		$res = $this->db->query($sql);
		if (PEAR::isError($res)) throw new Exception($res->getMessage());
	}
	
	function testInstantiateBlank() {
		$s = new ScaffholdTest($this->db);
		$this->assertTrue(is_a($s, 'ScaffholdTest'));
		$this->assertEqual('', $s->tt_id);
	}
	
	function testCrud() {
		$s = new ScaffholdTest($this->db);
		$s->tt_id = 1;
		$s->tt_text = 'lorem ipsum';
		$this->assertTrue($s->save($this->db, True, True));
		
		$getS = new ScaffholdTest($this->db, 1);
		$this->assertEqual($s->tt_text, $getS->tt_text);
		
		$getS->tt_text = 'dolor sit amet';
		$this->assertEqual($getS->tt_text, 'dolor sit amet');
		$this->assertTrue($getS->save($this->db));
		
		$updS = new ScaffholdTest($this->db, 1);
		$this->assertNotEqual($s->tt_text, $updS->tt_text);
		$this->assertEqual($updS->tt_text, 'dolor sit amet');
		
		$this->assertTrue($updS->delete($this->db));
		$this->expectException('Exception');
		$delS = new ScaffholdTest($this->db, 1);
	}
}
?>