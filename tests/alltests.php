<?php
require_once('simpletest-new/autorun.php');

class Alltests extends TestSuite {
	function __construct() {
		parent::__construct();
		$this->collect(dirname(__FILE__) . '/unit', 
			new SimplePatternCollector('/_test.php/'));
	}
}
?>