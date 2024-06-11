<?
namespace Rtop\KPI\Admin;

use \Bitrix\Main\Localization\Loc,
	Rtop\KPI\Premission;
Loc::loadMessages(__FILE__);


class Menu
{
	public static function add(&$globalMenu, &$aMenu){
		$premission = Premission::get();

		if(!$premission)
			return;
		$menu = [
			"parent_menu" => "global_menu_store",
			"section" => "kpi",
			"sort" => "1000",
			"text" => Loc::getMessage("RTKPI_MENU_TITLE"),
			"title" => Loc::getMessage("RTKPI_MENU_TITLE"),
			"module_id" => "rtop.kpi",
			"items_id" => "kpi",
			"url" => "/shop/settings/kpi/",
			"items" => [
				[
					"text" => Loc::getMessage("RTKPI_USER_PAGE"),
					"title" => Loc::getMessage("RTKPI_USER_PAGE"),
					"url" => "rtkp_users.php?lang=".LANGUAGE_ID,
					"more_url" => ["rtkp_users_edit.php?lang=".LANGUAGE_ID],
				],
				[
					"text" => Loc::getMessage("RTKPI_HANDLERS_PAGE"),
					"title" => Loc::getMessage("RTKPI_HANDLERS_PAGE"),
					"url" => "rtkp_events.php?lang=".LANGUAGE_ID,
					"more_url" => ["rtkp_events_edit.php?lang=".LANGUAGE_ID],
				]
			]
		];

		switch ($premission) {
			case 'ADMIN':
				$menu['items'][] = [
					"parent_menu" => "kpi",
					"sort" => 250.1,
					"text" => Loc::getMessage("RTKPI_EVENTS_PAGE"),
					"title" => Loc::getMessage("RTKPI_EVENTS_PAGE"),
					"url" => "/shop/settings/kpi/events",
					"items_id" => "kpi/events",
				];
				$menu['items'][] = [
					"parent_menu" => "kpi",
					"sort" => 252.1,
					"text" => Loc::getMessage("RTKPI_PLANS_PAGE"),
					"title" => Loc::getMessage("RTKPI_PLANS_PAGE"),
					"url" => "/shop/settings/kpi/plans/",
					"items_id" => "kpi/plans",
				];
			case 'SUPERVISOR':
				$menu['items'][] = [
					"parent_menu" => "kpi",
					"sort" => 251.1,
					"text" => Loc::getMessage("RTKPI_CHANGEBALANCE_PAGE"),
					"title" => Loc::getMessage("RTKPI_CHANGEBALANCE_PAGE"),
					"url" => "/shop/settings/kpi/change_balance/",
					"items_id" => "kpi/change_balance",
				];
				$menu['items'][] = [
					"parent_menu" => "kpi",
					"sort" => 253.1,
					"text" => Loc::getMessage("RTKPI_USERS_PAGE"),
					"title" => Loc::getMessage("RTKPI_USERS_PAGE"),
					"url" => "/shop/settings/kpi/plany-i-otdely/",
					"items_id" => "kpi/plany-i-otdely",
				];
				break;
		}

		$aMenu[] = $menu;
	}
}


?>