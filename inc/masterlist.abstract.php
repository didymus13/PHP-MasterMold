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