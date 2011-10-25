<?php
/**
 * PHP-MasterList
 * @author Stephane Doiron
 * @email stephane@stephanedoiron.com
 * @package MasterMold
 * 
 * PHP-MasterList Parent abstract object class
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
 */

abstract class aMasterList implements Countable, SeekableIterator
{
	protected $table = '';
	protected $pkField = '';
	protected $model = ''; // MasterMold model to use
	protected $modelList = array();
	protected $ordering = array(); // key / direction (ASC, DESC)
	private $position = 0;
	
	public function __construct($db, $filter=null, $pattern=null, $limit=null, $offset=null) {
		try {
			if (empty($this->table)) throw new Exception('Model Table must be defined');
			if (empty($this->pkField)) throw new Exception('Model Index Field must be defined');
			if (empty($this->model)) throw new Exception('Model class must be defined');
			$this->checkConnection($db);
			
			$this->position = 0;
			if ($filter && $pattern) {
				$this->fetchFiltered($db, $filter, $pattern, $limit, $offset);
			} else {
				$this->fetchAll($db, $limit, $offset);
			}
			return true;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	protected function checkConnection($db) {
		if (!($db instanceof MDB2_Driver_Common))
			throw new Exception('Database connection must be via a subclass of MDB2_Driver_Common') ;
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
	
	/**
	 * Query the database and repopulate the model list 
	 * @param $db MDB2 connection
	 * @param $sql Query to execute
	 */
	protected function initList($db, $sql, $limit, $offset) {
		try {
			unset($this->modelList);
			$this->checkConnection($db);
			$sql = $this->queryMeta($db, $sql, $limit, $offset);
			$res = $db->query($sql);
			if (PEAR::isERROR($res)) throw new Exception($res->getMessage());
			
			while ($row = $res->fetchRow()) {
				$this->modelList[] = new $this->model($db, $row[$this->pkField]);
			}
			return true;
			
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	protected function queryMeta($db, $sql, $limit, $offset) {
		if (count($this->ordering) > 0) {
			foreach($this->ordering as $field => $dir) {
				$orderSql[] =  $field . ' ' . $dir;
			}
			$sql = $sql . ' ORDER BY ' . join($orderSql, ',') .' ';
		}
		if ($limit) $sql = $sql . ' LIMIT ' . $db->quote($limit, 'integer');
		if ($offset) $sql = $sql . ' OFFSET ' . $db->quote($offset, 'integer');
		return $sql;
	}
	
	protected function fetchAll($db, $limit=null, $offset=null) {
		try {
			$this->checkConnection($db);
			
			$sql = 'select ' . $this->pkField . ' from ' . $this->table;
			$this->initList($db, $sql, $limit, $offset);
			
			return true;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	protected function fetchFiltered($db, $filter, $pattern, $limit=null, $offset=null) {
		try {
			$this->checkConnection($db);
			
			$sql = 'select ' . $this->pkField . ' from ' . $this->table 
				.' WHERE ' . $db->quoteIdentifier($filter) 
				. ' = ' . $db->quote($pattern);
				
			$this->initList($db, $sql, $limit, $offset);
			
			return true;
		} catch (Exception $e) {
			throw $e;
		} 
	}
	

	public function filter($property, $value) {
		try {
			if (!$property) throw new Exception('Filter field must be specified');
			$newList = array();
			foreach($this->modelList as $item) {
				if ($item->$property == $value) $newList[] = $item;
			}
			$this->modelList = $newList;
			$this->rewind();
			return $this;
		} catch (Exception $e) {
			throw $e;
		}
	}
}