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
		} catch (Exception $e) {
			throw $e;
		}

		try {
			$sql = 'DROP TABLE test_table'; // make sure the table isn't left over from other failed tests
			$res = $this->db->query($sql);
			if (PEAR::isError($res)) throw new Exception($res->getMessage() );
			$sql = 'DROP TABLE test_related'; // make sure the table isn't left over from other failed tests
			$res = $this->db->query($sql);
			if (PEAR::isError($res)) throw new Exception($res->getMessage() );
		} catch (Exception $e) {
			// do nothing if tables don't exist
		}
		
		try {
			$sql = 'CREATE TABLE test_table (tt_id INTEGER PRIMARY KEY, tt_text TEXT, related_id INTEGER)';
			$res = $this->db->query($sql);
			if (PEAR::isError($res)) throw new Exception($res->getMessage());
			$sql = 'CREATE TABLE test_related (tr_id INTEGER PRIMARY KEY, tr_text TEXT)';
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
		$sql = 'DROP TABLE test_related';
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
		$s->tt_text = 'lorem ipsum';
		$this->assertTrue($s->save($this->db));
		$this->assertPattern('/\d+/', $s->tt_id);
		
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
	
	function testRelated() {
		$r = new ScaffholdRelatedTest($this->db);
		$r->tr_id = 1;
		$r->tr_text = 'dolor sit amet';
		$this->assertTrue($r->save($this->db, True, True));
		
		$s = new ScaffholdTest($this->db);
		$s->tt_id = 1;
		$s->tt_text = 'lorem ipsum';
		$s->related_id = $r->tr_id;
		$this->assertTrue($s->save($this->db, true, true));
		
		$new = new ScaffholdTest($this->db, 1);
		$this->assertEqual($s->tt_text, $new->tt_text);
		$this->assertEqual($r->tr_text, $new->relatedTest->tr_text);

}

class ScaffholdListTestCase extends UnitTestCase {
	private $db;
	
	public function setUp() {
		global $dsn, $options;
		try {
			$this->db =& MDB2::factory($dsn, $options);
			if (PEAR::isError($this->db)) throw new Exception($this->db->getMessage());
			$this->db->setFetchMode(MDB2_FETCHMODE_ASSOC);
			
			$sql = 'DROP TABLE test_table'; // make sure the table isn't left over from other failed tests
			$this->db->query($sql);
			
			$sql = 'CREATE TABLE test_table (tt_id INTEGER PRIMARY KEY, tt_text TEXT)';
			$res = $this->db->query($sql);
			if (PEAR::isError($res)) throw new Exception($res->getMessage());
			
			$s = new ScaffholdTest($this->db);
			$s->tt_id = 1;
			$s->tt_text = 'lorem ipsum';
			$s->save($this->db, true, true);
			
			$s2 = new ScaffholdTest($this->db);
			$s2->tt_id = 2;
			$s2->tt_text = 'sit amet';
			$s2->save($this->db, true, true);
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
	
	function testList() {
		$s = new ScaffholdTest($this->db, 1);
		
		$list = new ScaffholdListTest($this->db);
		$this->assertEqual(2, $list->count());
		
		$this->assertTrue(is_a($list->current(), 'ScaffholdTest'));
		$this->assertEqual($s->tt_text, $list->current()->tt_text);
		
		$this->expectException('OutOfBoundsException');
		$list->seek(2);
		
		$list->seek(1);
		$this->assertEqual($s2->tt_text, $list->current()->tt_text);
		$this->assertNotEqual($s->tt_text, $list->current()->tt_text);
	}
	
	function testFilterList() {
		$filteredList = new ScaffholdListTest($this->db, 'tt_id', 1);
		$this->assertEqual(1, $filteredList->count());
		$reFiltered = $filteredList->filter($this->db, 'tt_text', 'lorem ipsum');
		$this->assertEqual(1, $reFiltered->count());
		foreach($reFiltered as $test) {
			$this->assertEqual($test->tt_text, 'lorem ipsum');
		}
		$chainFilter = $filteredList->filter('tt_id', 2)
			->filter('tt_text', 'sit amet');
		$this->assertEqual(1, $chainFilter->count());
		
		$chainFilter2 = $filteredList->filter('tt_id', 1)
			->filter('tt_text', 'sit amet');
		$this->assertEqual(0, $chainFilter2->count());
	}
}
?>