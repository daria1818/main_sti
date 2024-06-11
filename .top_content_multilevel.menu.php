<?
global $USER;
$aMenuLinks = Array(

	Array(
		"Главная", 
		"/", 
		Array(), 
		Array("ONLY_MOBILE"=>"Y"), 
		"" 
	),
	Array(
		"Каталог", 
		"/catalog/", 
		Array(), 
		Array("NOT_VISIBLE"=>"Y", "CLASS"=>"wide_menu catalog", "menu_item_class" => "wide_menu", "IS_CATALOG" => 1), 
		"" 
	),
	Array(
		"Производители", 
		"/info/brands/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Акции", 
		"/sale/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Как купить", 
		"/help/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"О нас", 
		"/company/", 
		Array(), 
		Array(), 
		"" 
	),/* 
	Array(
		"Контакты", 
		"/contacts/", 
		Array(), 
		Array(), 
		"" 
	)
	 */
	Array(
        "GBT", 
        "/gbt/sda/", 
        Array(), 
        Array(), 
        'CUser::IsAuthorized()'
    ),
);
?>