<?php
/**
 * PHP-MasterMold
 * @author Stephane Doiron
 * @email stephane@stephanedoiron.com
 * @package MasterMold
 * 
 * PHP-Mastermold Parent abstract object class
 * 
 * Copyright (C) 2011 by Stephane Doiron
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE
 * 
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
			$this->checkConnection($db);
			if ($this->useScaffhold) $this->initializeData($db);
			if ($id) $this->getObject($db, $id);
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
				throw new Exception($this->table .' has no fields');
			
			foreach ($tableInfo as $field) {
				$this->data[$field['name']] = array (
					'type'	=> $field['mdb2type'],
					'value'	=> Null,
				);
			}
			return true;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function getObject($db, $id) {
		try {
			if (preg_match('/\D/', $id)) 
				throw InvalidArgumentException('ID must be numeric');
			$this->checkConnection($db);
			
			foreach ($this->data as $k => $v) {
				$types[$k] = $v['type'];
			}			

			$db->loadModule('Extended'); // Required for AutoExecute
			$res = $db->extended->autoExecute($this->table, null, MDB2_AUTOQUERY_SELECT, 
				$this->pkField . ' = ' . $db->quote($id, 'integer'), null, 
				true, $types);
			if (PEAR::isError($res)) throw new Exception ($res->getMessage());
			if ($res->numRows() == 0) throw new Exception('ID not found');
			$row = $res->fetchRow();

			foreach (array_keys($this->data) as $field) {
				$this->$field = $row[$field];
			}
			
			return True;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function __set($property, $value) {
		try {
			if (!array_key_exists($property, $this->data)) 
				throw new InvalidArgumentException(
					"Unknown Property: $property " . print_r($this->data, true)
				);
			$this->data[$property]['value'] = $value;
			return True;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function __get($property) {
		try {
			if (!array_key_exists($property, $this->data)) 
				throw new InvalidArgumentException(
					"Unknown Property: $property " . print_r($this->data, true)
				);
			return $this->data[$property]['value'];
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	private function checkConnection($db) {
		try {
			if (!is_a($db, 'MDB2_Driver_Common')) {
				throw new Exception('Database connection must be via a subclass of MDB2_Driver_Common') ;
			}
			return true;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	/**
	 * Validates current data state
	 * @TODO validate against MDB2 data types
	 */
	public function validate() {
		return true;
	}
	
	/**
	 * Save model state to the database 
	 * @param MDB2_Driver_Common $db
	 * @param boolean $forceValidate set to false to prevent data validation
	 *
	 */
	public function save($db, $forceValidate = True, $forceInsert = False) {
		try {
			$this->checkConnection($db);
			$db->loadModule('Extended'); // required for autoExecute
			
			if ($forceValidate) $this->validate();
			
			// Reduce the data array to key / value pairs
			$properties = array();
			$types = array();
			foreach ($this->data as $k => $v) {
				$properties[$k] = $v['value'];
				$types[] = $v['type'];
			}			
			// Insert or Update check and execute
			if (empty($this->data[$this->pkField]['value']) || $forceInsert) {
				$res = $db->extended->autoExecute($this->table, $properties,
					MDB2_AUTOQUERY_INSERT, null, $types);
			} else {
				$res = $db->extended->autoExecute($this->table, $properties,
					MDB2_AUTOQUERY_UPDATE,
					$this->pkField . ' = ' . $db->quote($this->data[$this->pkField]['value'], 'integer'), 
					$types);
			}
			if (PEAR::isError($res)) throw new Exception($res->getMessage());
			
			if (empty($this->data[$this->pkField]['value'])) {
				$this->data[$this->pkField]['value'] = $db->lastInsertId($this->table, $this->pkField);
			}
			return True;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function delete($db) {
		try {
			if (preg_match('/\D/', $this->data[$this->pkField]['value'])) 
				throw InvalidArgumentException('ID must be numeric');
			$db->loadModule('Extended');
			$res = $db->extended->autoExecute($this->table, null, MDB2_AUTOQUERY_DELETE, 
				$this->pkField . ' = ' . $db->quote($this->data[$this->pkField]['value'], 'integer'));
			if (PEAR::isError($res)) throw new Exception($res->getMessage());
			return True;
		} catch (Exception $e) {
			throw $e;
		}
	}
}
?>