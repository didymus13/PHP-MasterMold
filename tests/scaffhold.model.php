<?
require_once(dirname(__FILE__) . '/../inc/mastermold.abstract.php');

class ScaffholdTest extends aMasterMold
{
    protected $useScaffhold = True;
    protected $table = 'test_table';
    protected $pkField = 'tt_id';
}
?>
