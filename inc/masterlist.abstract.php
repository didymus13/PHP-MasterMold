<?php
/**
 * PHP-MasterList
 * @author Stephane Doiron
 * @email stephane@stephanedoiron.com
 * @package MasterMold
 * 
 * PHP-MasterList Parent abstract object class
 */

abstract class aMasterList implements Countable, SeekableIterator
{
	protected $table = '';
	protected $pkField = '';
	protected $model = ''; // MasterMold model to use
	protected $modelList = array();
	private $position = 0;
	
	public function __construct($db, $limit=null, $offset=null) {
		try {
			$this->position = 0;
			if (!is_a($db, 'MDB2_Driver_Common')) {
				throw new Exception('Database connection must be via a subclass of MDB2_Driver_Common') ;
			}
			$this->fetchList($db, $limit, $offset);
			return true;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function count() {
		return count($this->modelList);
	}
	
	
	public function rewind() {
		$this->position = 0;
	}
	
	public function seek($position) {
		$this->position = $position;
		if (!$this->valid()) {
			throw new OutOfBoundsException("Invalid seek position ($position)");
		}
	}
	
	public function next() {
		++$this->position;
	}
	
	public function key() {
		return $this->position;
	}
	
	public function current() {
		return $this->modelList[$this->position];
	}
	
	public function valid() {
		return isset($this->modelList[$this->position]);
	}
	
	protected function fetchList($db, $limit=null, $offset=null) {
		try {
			$types = array($this->pkField => 'integer');
			$db->loadModule('Extended');
			$res = $db->extended->autoExecute($this->table, null, MDB2_AUTOQUERY_SELECT,
				null, null, true, $types);
			if (PEAR::isERROR($res)) {
				throw new Exception($res->getMessage(), $res->getCode());	
			}
			while ($row = $res->fetchRow()) {
				$this->modelList[] = new $this->model($db, $row[$this->pkField]); 
			}
			return true;
		} catch (Exception $e) {
			throw $e;
		}
	}
}