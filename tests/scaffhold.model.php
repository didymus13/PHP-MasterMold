<?
require_once(__DIR__ . '/../inc/mastermold.abstract.php');
require_once(__DIR__ . '/../inc/masterlist.abstract.php');

class ScaffholdTest extends aMasterMold
{
    protected $table = 'test_table';
    protected $pkField = 'tt_id';
}

class ScaffholdListTest extends aMasterList
{
	protected $table = 'test_table';
	protected $pkField = 'tt_id';
	protected $model = 'ScaffholdTest';
}
?>
