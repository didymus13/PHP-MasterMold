<?php
/**
 * PHP-MasterMold
 * @author Stephane Doiron
 * @email stephane@stephanedoiron.com
 * @package MasterMold
 * 
 * PHP-Mastermold Parent abstract object class
 */

abstract class aMasterMold
{
	protected $useScaffhold = True;
	protected $table = '';
    protected $pkField = '';
	protected $data = array();
	protected $related = array();
	
	public function __construct($db, $id=null)
	{
		try {
			if (!is_a($db, 'MDB2_Driver_Common')) 
				throw new Exception('Database connection must be via a subclass of MDB2_Driver_Common') ;
			if ($this->useScaffhold) $this->initializeData($db);
			if ($id) $this->get($db, $id);
			return true;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	protected function initializeData($db) {
		try {
			$db->loadModule('Reverse', null, true);
			$tableInfo = $db->reverse->tableInfo($this->table);
			
			if (PEAR::isError($tableInfo)) throw new Exception($tableInfo->getMessage());
			if (count($tableInfo) == 0) 
				throw Exception($this->table .' has no fields');
			
			foreach ($tableInfo as $field) {
				$this->data[$field['name']] = array (
					'type'	=> $field['mdb2type'],
					'value'	=> '',
				);
			}
			return true;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function get($db, $id) {
		try {
			if (preg_match('/\D/', $id)) 
				throw InvalidArgumentException('ID must be numeric');
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function __get($property) {
		try {
			if (!array_key_exists($property, $this->data)) 
				throw new InvalidArgumentException('Unknown Property'.print_r($this->data));
			return $this->data[$property]['value'];
		} catch (Exception $e) {
			throw $e;
		}
	}
}

abstract class aMasterList
{
	protected $table = '';
    protected $pkField = '';
    protected $model = '';
}
?>