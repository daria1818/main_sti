<?
namespace Rtop\KPI;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Rtop\KPI\BalanceTable;
Loc::loadMessages(__FILE__);

class Premission
{
	public function get(){
		global $USER;
		$ID = $USER->GetId();
		$user = BalanceTable::getList(['filter' => ['USERID' => $ID], 'select' => ['ROLE']])->fetch();

		return $user['ROLE'];
	}
}
?>