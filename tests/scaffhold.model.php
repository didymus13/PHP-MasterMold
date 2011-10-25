<?
require_once(__DIR__ . '/../inc/mastermold.abstract.php');
require_once(__DIR__ . '/../inc/masterlist.abstract.php');

class ScaffholdTest extends aMasterMold
{
    protected $table = 'test_table';
    protected $pkField = 'tt_id';
    protected $related = array(
    	'related_id' => array('model' => 'ScaffholdRelatedTest',
    						  'name' => 'relatedTest'),
    );
}

class ScaffholdRelatedTest extends aMasterMold
{
	protected $table = 'test_related';
	protected $pkField = 'tr_id';
}

class ScaffholdListTest extends aMasterList
{
	protected $table = 'test_table';
	protected $pkField = 'tt_id';
	protected $model = 'ScaffholdTest';
	protected $ordering = array(
		'tt_text' => 'ASC',
	);
}
?>
