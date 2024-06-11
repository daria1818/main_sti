<?
namespace Rubyroid\Loyality\Admin;


class Menu
{
	public static function add(&$globalMenu, &$aMenu){
		$menu = [
			"parent_menu" => "global_menu_settings",
			"section" => "rubyroid",
			"sort" => "1100",
			"text" => "RB",
			"title" => "RB",
			"module_id" => "rubyroid.bonusloyalty",
			"items_id" => "rubyroid",
			"url" => "/bitrix/admin/history_transaction_rbcoin.php",
		];

		$aMenu[] = $menu;
	}
}
?>