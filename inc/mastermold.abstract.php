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
}

abstract class aMasterList
{
	protected $table = '';
    protected $pkField = '';
    protected $model = '';
}
?>