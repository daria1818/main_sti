<?
/**
 * Aspro:Next module
 * @copyright 2017 Aspro
 */

if(!defined('ASPRO_NEXT_MODULE_ID')){
	define('ASPRO_NEXT_MODULE_ID', 'aspro.next');
}

use \Bitrix\Main\Application,
	\Bitrix\Main\Type\Collection,
	\Bitrix\Main\Loader,
	\Bitrix\Main\IO\File,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

// initialize module parametrs list and default values
include_once __DIR__.'/../../parametrs.php';
include_once __DIR__.'/../../presets.php';

class CNext{
    const partnerName	= 'aspro';
    const solutionName	= 'next';
    const templateName	= 'aspro_next';
	const moduleID		= ASPRO_NEXT_MODULE_ID;
    const wizardID		= 'aspro:next';
	const devMode 		= false;

	public static $arParametrsList = array();
	public static $arPresetsList = array();
	private static $arMetaParams = array();

	public static function Check(){}

	public static function isPageSpeedTest(){
		return isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Lighthouse') !== false;
	}

	public static function checkIndexBot(){
		static $result;

		if(!isset($result)){
			$result = self::isPageSpeedTest() && Option::get(self::moduleID, 'USE_PAGE_SPEED_OPTIMIZATION', 'Y', SITE_ID) === 'Y';
		}

		return $result;
	}

	public static function ShowPageType($type = 'indexblocks'){
		global $APPLICATION, $arTheme, $bShowHeaderSimple, $bShowFooterSimple;
		$path = $_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/page_blocks/'.$type.'_';
		$file = null;
		if(is_array($arTheme) && $arTheme)
		{
			switch($type):
				case 'page_contacts':
					$path = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].'/'.SITE_DIR.'contacts/'.$type);
					$file = $path.'_'.$arTheme['PAGE_CONTACTS']['VALUE'].'.php';
					break;
				case 'search_title_component':
					$path = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].'/'.SITE_DIR.'include/footer/');
					$file = $path.'site-search.php';
					break;
				case 'basket_component':
					$path = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].'/'.SITE_DIR.'include/footer/');
					$file = $path.'site-basket.php';
					break;
				case 'auth_component':
					$path = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].'/'.SITE_DIR.'include/footer/');
					$file = $path.'site-auth.php';
					break;
				case 'bottom_counter':
					$bIndexBot = self::checkIndexBot(); // is indexed pagespeed bot

					if(!$bIndexBot)
					{
						$path = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].'/'.SITE_DIR.'include/');
						$file = $path.'invis-counter.php';
					}
					break;
				case 'page_width':
					$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/width-'.$arTheme['PAGE_WIDTH']['VALUE'].'.css');
					break;
				case 'h1_style':
					if ($arTheme['H1_STYLE']['VALUE']=='Normal')
						$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/h1-normal.css');
					else
						$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/h1-bold.css');
					break;
				case 'footer':
					if($bShowFooterSimple) {
						$file = $path.'simple_'.$arTheme['SIMPLE_BASKET']['DEPENDENT_PARAMS']['FOOTER_SIMPLE']['VALUE'].'.php';
					} else {
						$file = $path.(isset($arTheme['FOOTER_TYPE']['VALUE']) && $arTheme['FOOTER_TYPE']['VALUE'] ? $arTheme['FOOTER_TYPE']['VALUE'] : $arTheme['FOOTER_TYPE']).'.php';
					}
					break;
				case 'header':
					if($bShowHeaderSimple) {
						$file = $path.'simple_'.$arTheme['SIMPLE_BASKET']['DEPENDENT_PARAMS']['HEADER_SIMPLE']['VALUE'].'.php';
					} else {
						$file = $path.$arTheme['HEADER_TYPE']['VALUE'].'.php';
					}
					?>
					<pre id="debug" style="display:none"><?=$file?></pre>
					<?
					break;
				case 'header_fixed':
					if(!$bShowHeaderSimple) {
						$file = $path.$arTheme['TOP_MENU_FIXED']['DEPENDENT_PARAMS']['HEADER_FIXED']['VALUE'].'.php';
					}
					break;
				case 'header_mobile':
					if(!$bShowHeaderSimple) {
						$file = $path.$arTheme['HEADER_MOBILE']['VALUE'].'.php';
					}
					break;
				case 'header_mobile_menu':
					$file = $path.$arTheme['HEADER_MOBILE_MENU']['VALUE'].'.php';
					break;
				case 'header_mobile_menu':
					$file = $path.$arTheme['HEADER_MOBILE_MENU']['VALUE'].'.php';
					break;
				case 'page_title':
					$file = $path.$arTheme['PAGE_TITLE']['VALUE'].'.php';
					break;
				case 'left_block':
					$file = $path.$arTheme['LEFT_BLOCK']['VALUE'].'.php';
					break;
				default:
					$path = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].'/'.SITE_DIR.$type);
					$file = $path.'_'.$arTheme['INDEX_TYPE']['VALUE'].'.php';
					break;
			endswitch;

			if ($file)
				@include_once $file;
		}
	}

	public static function formatJsName($name = ''){
		return htmlspecialcharsbx($name);
	}

	public static function updateExtendedReviewsProps($commentId, $action = '') {
		if(CModule::IncludeModule('blog') && CModule::IncludeModule('iblock')) {
			$comment = CBlogComment::GetByID($commentId);

			if($comment) {
				$catalogIblockId = \Bitrix\Main\Config\Option::get(self::moduleID, "CATALOG_IBLOCK_ID", CNextCache::$arIBlocks[SITE_ID]['aspro_next_catalog']['aspro_next_catalog'][0], SITE_ID );
				$product = CIBlockElement::GetList(array(), array('PROPERTY_BLOG_POST_ID' => $comment['POST_ID'], "IBLOCK_ID" => $catalogIblockId), false, array('nTopCount' => '1'), array('ID', 'PROPERTY_BLOG_POST_ID', 'IBLOCK_ID'))->Fetch();
				if($product) {
					$productId = $product['ID'];
				}

				$commentsCount = $commentsRating = $commentsCountRaiting = 0;
				$resBlog = CBlogComment::GetList(array("ID"=>"DESC"), array('POST_ID' => $comment['POST_ID'], 'PARENT_ID' => false, 'PUBLISH_STATUS' => 'P'), false, false, array('ID', 'UF_ASPRO_COM_RATING'));
				while( $comment = $resBlog->Fetch() ) {
					if($comment['UF_ASPRO_COM_RATING']) {
						$commentsCountRaiting++;
						$commentsRating += $comment['UF_ASPRO_COM_RATING'];
					}
					$commentsCount++;
				}

				if($action == 'delete'){
					$commentsCount--;
				}

				foreach(GetModuleEvents(ASPRO_NEXT_MODULE_ID, 'OnAsproUpdateExtendedReviewsProps', true) as $arEvent)
					ExecuteModuleEventEx($arEvent, array(&$commentsCount, &$commentsRating, &$commentsCountRaiting));

				$catalogIblockId = $product["IBLOCK_ID"];
				if ($catalogIblockId) {
					CIBlock::clearIblockTagCache($catalogIblockId);
					if($commentsRating) {
						$value = round( $commentsRating/$commentsCountRaiting, 1 );
						CIBlockElement::SetPropertyValuesEx($productId, $catalogIblockId, array('EXTENDED_REVIEWS_COUNT' => $commentsCount, 'EXTENDED_REVIEWS_RAITING' => $value) );
					} else {
						CIBlockElement::SetPropertyValuesEx($productId, $catalogIblockId, array('EXTENDED_REVIEWS_COUNT' => $commentsCount, 'EXTENDED_REVIEWS_RAITING' => 0) );
					}
				}
			}
		}
	}

	public static function PrepareItemProps($arProps){
		if(is_array($arProps) && $arProps)
		{
			foreach($arProps as $PCODE => $arProperty)
			{
				if(in_array($PCODE, array('PERIOD', 'TITLE_BUTTON', 'LINK_BUTTON', 'REDIRECT', 'LINK_PROJECTS', 'LINK_REVIEWS', 'DOCUMENTS', 'FORM_ORDER', 'FORM_QUESTION', 'PHOTOPOS', 'TASK_PROJECT', 'PHOTOS', 'LINK_COMPANY', 'GALLEY_BIG', 'LINK_SERVICES', 'LINK_GOODS', 'LINK_STAFF', 'LINK_SALE', 'SERVICES', 'HIT', 'RECOMMEND', 'NEW', 'STOCK', 'VIDEO', 'VIDEO_YOUTUBE', 'CML2_ARTICLE')))
					unset($arProps[$PCODE]);
				elseif(!$arProperty['VALUE'])
					unset($arProps[$PCODE]);
			}
		}
		else
			$arProps = array();

		return $arProps;
	}

	static function ShowTopDetailBanner($arResult, $arParams){
		$bg = ((isset($arResult['PROPERTIES']['BNR_TOP_BG']) && $arResult['PROPERTIES']['BNR_TOP_BG']['VALUE']) ? CFile::GetPath($arResult['PROPERTIES']['BNR_TOP_BG']['VALUE']) : SITE_TEMPLATE_PATH.'/images/top-bnr.jpg');
		$bShowBG = (isset($arResult['PROPERTIES']['BNR_TOP_IMG']) && $arResult['PROPERTIES']['BNR_TOP_IMG']['VALUE']);
		$title = ($arResult['IPROPERTY_VALUES'] && strlen($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arResult['NAME']);
		$text_color_style = ((isset($arResult['PROPERTIES']['CODE_TEXT']) && $arResult['PROPERTIES']['CODE_TEXT']['VALUE']) ? 'style="color:'.$arResult['PROPERTIES']['CODE_TEXT']['VALUE'].'"' : '');
		$bLanding = (isset($arResult['IS_LANDING']) && $arResult['IS_LANDING'] == 'Y');
		?>
		<div class="banners-content">
			<div class="maxwidth-banner" style="background: url(<?=$bg;?>) 50% 50% no-repeat;">
				<div class="row">
					<div class="maxwidth-theme">
						<div class="col-md-<?=($bShowBG ? 5 : 12);?> text animated delay06 duration08 item_block fadeInUp">
							<h1 <?=$text_color_style;?>><?=((isset($arResult['PROPERTIES']['BANNER_TITLE']['VALUE']) && $arResult['PROPERTIES']['BANNER_TITLE']['VALUE']) ? $arResult['PROPERTIES']['BANNER_TITLE']['VALUE'] : $title);?></h1>
							<div class="intro-text" <?=$text_color_style;?>>
								<?if($bLanding):?>
									<p><?=$arResult['PROPERTIES']['ANONS']['VALUE'];?></p>
								<?else:?>
									<?if($arResult['PROPERTIES']['BANNER_DESCRIPTION']['VALUE']['TEXT']):?>
										<p><?=$arResult['PROPERTIES']['BANNER_DESCRIPTION']['~VALUE']['TEXT'];?></p>
									<?else:?>
										<?if($arResult['PREVIEW_TEXT_TYPE'] == 'text'):?>
											<p><?=$arResult['FIELDS']['PREVIEW_TEXT'];?></p>
										<?else:?>
											<?=$arResult['FIELDS']['PREVIEW_TEXT'];?>
										<?endif;?>
									<?endif;?>
								<?endif;?>
							</div>
							<p>
								<?if($bLanding):?>
									<?if($arResult['PROPERTIES']['BUTTON_TEXT']['VALUE']):?>
										<span>
											<span class="btn btn-default btn-lg scroll_btn"><?=$arResult['PROPERTIES']['BUTTON_TEXT']['VALUE'];?></span>
										</span>
									<?endif;?>
								<?else:?>
									<?if($arResult['DISPLAY_PROPERTIES']['FORM_ORDER']['VALUE_XML_ID'] == 'YES'):?>
										<span>
											<span class="btn btn-default animate-load" data-event="jqm" data-param-form_id="<?=($arParams["FORM_ID_ORDER_SERVISE"] ? $arParams["FORM_ID_ORDER_SERVISE"] : 'SERVICES');?>" data-name="order_services" data-autoload-service="<?=self::formatJsName($arResult['NAME']);?>" data-autoload-project="<?=self::formatJsName($arResult['NAME']);?>" data-autoload-product="<?=self::formatJsName($arResult['NAME']);?>"><span><?=($arParams['S_ORDER_SERVISE'] && strlen($arParams['S_ORDER_SERVISE']) ? $arParams['S_ORDER_SERVISE'] : Loc::getMessage('S_ORDER_SERVISE'))?></span></span>
										</span>
									<?endif;?>

									<?if($arResult['DISPLAY_PROPERTIES']['FORM_QUESTION']['VALUE_XML_ID'] == 'YES'):?>
										<span>
											<span class="btn btn-default white animate-load" data-event="jqm" data-param-form_id="ASK" data-autoload-need_product="<?=self::formatJsName($arResult['NAME']);?>" data-name="question"><span><?=($arParams['S_ASK_QUESTION'] && strlen($arParams['S_ASK_QUESTION']) ? $arParams['S_ASK_QUESTION'] : Loc::getMessage('S_ASK_QUESTION'))?></span></span>
										</span>
									<?endif;?>
								<?endif;?>
							</p>
						</div>
						<?if($bShowBG):?>
							<div class="col-md-7 hidden-xs hidden-sm img animated delay09 duration08 item_block fadeInUp">
								<div class="inner">
									<img src="<?=CFile::GetPath($arResult['PROPERTIES']['BNR_TOP_IMG']['VALUE']);?>" alt="<?=$title;?>" title="<?=$title;?>" draggable="false">
								</div>
							</div>
						<?endif;?>
					</div>
				</div>
			</div>
		</div>
	<?}

	static function utf8_substr_replace($original, $replacement, $position, $length){
		$startString = mb_substr($original, 0, $position, 'UTF-8');
		$endString = mb_substr($original, $position + $length, mb_strlen($original), 'UTF-8');

		$out = $startString.$replacement.$endString;

		return $out;
	}

	public static function GetBackParametrsValues($SITE_ID, $bFromStatic = true, $SITE_DIR = ''){
		static $arCacheValues;

		$SITE_DIR = strlen($SITE_DIR) ? $SITE_DIR : (defined('SITE_DIR') ? SITE_DIR : '');

		if(!isset($arCacheValues)){
			$arCacheValues = array();
		}

		if(!isset($arCacheValues[$SITE_ID])){
			$arCacheValues[$SITE_ID] = array();
		}

		$arValues =& $arCacheValues[$SITE_ID];

		if(!$bFromStatic){
			$arValues = array();
		}

		if(!$arValues){
			$arDefaultValues = $arNestedValues = array();
			$bNestedParams = false;

			// get site template
			$arTemplate = self::GetSiteTemplate($SITE_ID);

			// add custom values for PAGE_CONTACTS
			if(isset(self::$arParametrsList['SECTION']['OPTIONS']['PAGE_CONTACTS']['LIST'])){
				// get site dir
				$arSite = CSite::GetByID($SITE_ID)->Fetch();
				$siteDir = str_replace('//', '/', $arSite['DIR']).'/';
				if($arPageBlocks = self::GetIndexPageBlocks($_SERVER['DOCUMENT_ROOT'].$siteDir.'contacts', 'page_contacts_', '')){
					foreach($arPageBlocks as $page => $value){
						$value_ = str_replace('page_contacts_', '', $value);
						if(!isset(self::$arParametrsList['SECTION']['OPTIONS']['PAGE_CONTACTS']['LIST'][$value_])){
							self::$arParametrsList['SECTION']['OPTIONS']['PAGE_CONTACTS']['LIST'][$value_] = array(
								'TITLE' => $value,
								'HIDE' => 'Y',
								'IS_CUSTOM' => 'Y',
							);
						}
					}
					if(!self::$arParametrsList['SECTION']['OPTIONS']['PAGE_CONTACTS']['DEFAULT']){
						self::$arParametrsList['SECTION']['OPTIONS']['PAGE_CONTACTS']['DEFAULT'] = key(self::$arParametrsList['SECTION']['OPTIONS']['PAGE_CONTACTS']['LIST']);
					}
				}
			}

			if($arTemplate && $arTemplate['PATH']){
				// add custom values for BLOG_PAGE
				if(isset(self::$arParametrsList['SECTION']['OPTIONS']['BLOG_PAGE'])){
					self::Add2OptionCustomComponentTemplatePageBlocks(self::$arParametrsList['SECTION']['OPTIONS']['BLOG_PAGE'], $arTemplate['PATH'].'/components/bitrix/news/blog');
				}

				// add custom values for PROJECTS_PAGE
				if(isset(self::$arParametrsList['SECTION']['OPTIONS']['PROJECTS_PAGE'])){
					self::Add2OptionCustomComponentTemplatePageBlocks(self::$arParametrsList['SECTION']['OPTIONS']['PROJECTS_PAGE'], $arTemplate['PATH'].'/components/bitrix/news/projects');
				}

				// add custom values for NEWS_PAGE
				if(isset(self::$arParametrsList['SECTION']['OPTIONS']['NEWS_PAGE'])){
					self::Add2OptionCustomComponentTemplatePageBlocks(self::$arParametrsList['SECTION']['OPTIONS']['NEWS_PAGE'], $arTemplate['PATH'].'/components/bitrix/news/news');
				}

				// add custom values for STAFF_PAGE
				if(isset(self::$arParametrsList['SECTION']['OPTIONS']['STAFF_PAGE'])){
					self::Add2OptionCustomComponentTemplatePageBlocks(self::$arParametrsList['SECTION']['OPTIONS']['STAFF_PAGE'], $arTemplate['PATH'].'/components/bitrix/news/staff');
				}

				// add custom values for PARTNERS_PAGE
				if(isset(self::$arParametrsList['SECTION']['OPTIONS']['PARTNERS_PAGE'])){
					self::Add2OptionCustomComponentTemplatePageBlocks(self::$arParametrsList['SECTION']['OPTIONS']['PARTNERS_PAGE'], $arTemplate['PATH'].'/components/bitrix/news/partners');
				}

				// add custom values for PARTNERS_PAGE_DETAIL
				if(isset(self::$arParametrsList['SECTION']['OPTIONS']['PARTNERS_PAGE_DETAIL'])){
					self::Add2OptionCustomComponentTemplatePageBlocksElement(self::$arParametrsList['SECTION']['OPTIONS']['PARTNERS_PAGE_DETAIL'], $arTemplate['PATH'].'/components/bitrix/news/partners');
				}

				// add custom values for CATALOG_PAGE_DETAIL
				if(isset(self::$arParametrsList['CATALOG_PAGE']['OPTIONS']['CATALOG_PAGE_DETAIL'])){
					self::Add2OptionCustomComponentTemplatePageBlocksElement(self::$arParametrsList['CATALOG_PAGE']['OPTIONS']['CATALOG_PAGE_DETAIL'], $arTemplate['PATH'].'/components/bitrix/catalog/main');
				}

				// add custom values for USE_FAST_VIEW_PAGE_DETAIL
				if(isset(self::$arParametrsList['CATALOG_PAGE']['OPTIONS']['USE_FAST_VIEW_PAGE_DETAIL'])){
					self::Add2OptionCustomComponentTemplatePageBlocksElement(self::$arParametrsList['CATALOG_PAGE']['OPTIONS']['USE_FAST_VIEW_PAGE_DETAIL'], $arTemplate['PATH'].'/components/bitrix/catalog/main', 'FAST_VIEW_ELEMENT');
				}

				// add custom values for VACANCY_PAGE
				if(isset(self::$arParametrsList['SECTION']['OPTIONS']['VACANCY_PAGE'])){
					self::Add2OptionCustomComponentTemplatePageBlocks(self::$arParametrsList['SECTION']['OPTIONS']['VACANCY_PAGE'], $arTemplate['PATH'].'/components/bitrix/news/vacancy');
				}

				// add custom values for LICENSES_PAGE
				if(isset(self::$arParametrsList['SECTION']['OPTIONS']['LICENSES_PAGE'])){
					self::Add2OptionCustomComponentTemplatePageBlocks(self::$arParametrsList['SECTION']['OPTIONS']['LICENSES_PAGE'], $arTemplate['PATH'].'/components/bitrix/news/licenses');
				}
			}

			if(self::$arParametrsList && is_array(self::$arParametrsList))
			{
				foreach(self::$arParametrsList as $blockCode => $arBlock)
				{
					if($arBlock['OPTIONS'] && is_array($arBlock['OPTIONS']))
					{
						foreach($arBlock['OPTIONS'] as $optionCode => $arOption)
						{
							if($arOption['TYPE'] !== 'note' && $arOption['TYPE'] !== 'includefile')
							{
								if($arOption['TYPE'] === 'array'){
									$itemsKeysCount = Option::get(self::moduleID, $optionCode, '0', $SITE_ID);
									if($arOption['OPTIONS'] && is_array($arOption['OPTIONS']))
									{
										for($itemKey = 0, $cnt = $itemsKeysCount + 1; $itemKey < $cnt; ++$itemKey)
										{
											$_arParameters = array();
											$arOptionsKeys = array_keys($arOption['OPTIONS']);
											foreach($arOptionsKeys as $_optionKey)
											{
												$arrayOptionItemCode = $optionCode.'_array_'.$_optionKey.'_'.$itemKey;
												$arValues[$arrayOptionItemCode] = Option::get(self::moduleID, $arrayOptionItemCode, '', $SITE_ID);
												$arDefaultValues[$arrayOptionItemCode] = $arOption['OPTIONS'][$_optionKey]['DEFAULT'];
											}
										}
									}
									$arValues[$optionCode] = $itemsKeysCount;
									$arDefaultValues[$optionCode] = 0;
								}
								else
								{
									$arDefaultValues[$optionCode] = $arOption['DEFAULT'];
									$arValues[$optionCode] = Option::get(self::moduleID, $optionCode, $arOption['DEFAULT'], $SITE_ID);

									if(isset($arOption['SUB_PARAMS']) && $arOption['SUB_PARAMS']) //get nested params default value
									{
										if($arOption['TYPE'] == 'selectbox' && (isset($arOption['LIST'])) && $arOption['LIST'])
										{
											$bNestedParams = true;
											$arNestedValues[$optionCode] = $arOption['LIST'];
											foreach($arOption['LIST'] as $key => $value)
											{
												if($arOption['SUB_PARAMS'][$key])
												{
													foreach($arOption['SUB_PARAMS'][$key] as $key2 => $arSubOptions)
														$arDefaultValues[$key.'_'.$key2] = $arSubOptions['DEFAULT'];
												}
											}
										}
									}

									if(isset($arOption['DEPENDENT_PARAMS']) && $arOption['DEPENDENT_PARAMS']) //get dependent params default value
									{
										foreach($arOption['DEPENDENT_PARAMS'] as $key => $arSubOption)
										{
											$arDefaultValues[$key] = $arSubOption['DEFAULT'];
											$arValues[$key] = Option::get(self::moduleID, $key, $arSubOption['DEFAULT'], $SITE_ID);
										}
									}

									elseif($optionCode === 'USE_PHONE_AUTH')
									{
										list($bPhoneAuthSupported, $bPhoneAuthShow, $bPhoneAuthRequired, $bPhoneAuthUse) = Aspro\Next\PhoneAuth::getOptions();
										if(!$bPhoneAuthSupported || !$bPhoneAuthShow){
											self::$arParametrsList[$blockCode]['OPTIONS'][$optionCode]['DISABLED'] = 'Y';
										}
									}
								}
							}
						}
					}
				}
			}

			if($arNestedValues && $bNestedParams) //get nested params bd value
			{
				foreach($arNestedValues as $key => $arAllValues)
				{
					$arTmpValues = array();
					foreach($arAllValues as $key2 => $arOptionValue)
					{
						$arTmpValues = unserialize(Option::get(self::moduleID, 'NESTED_OPTIONS_'.$key.'_'.$key2, serialize(array()), $SITE_ID));
						if($arTmpValues)
						{
							foreach($arTmpValues as $key3 => $value)
							{
								$arValues[$key2.'_'.$key3] = $value;
							}
						}
					}

				}
			}

			if($arValues && is_array($arValues))
			{
				foreach($arValues as $optionCode => $arOption)
				{
					if(!isset($arDefaultValues[$optionCode]))
						unset($arValues[$optionCode]);
				}
			}
			if($arDefaultValues && is_array($arDefaultValues))
			{
				foreach($arDefaultValues as $optionCode => $arOption)
				{
					if(!isset($arValues[$optionCode]))
						$arValues[$optionCode] = $arOption;
				}
			}

			foreach($arValues as $key => $value)
			{
				if($key == 'LOGO_IMAGE' || $key == 'FAVICON_IMAGE' || $key == 'APPLE_TOUCH_ICON_IMAGE'){
					$arValue = unserialize(Option::get(self::moduleID, $key, serialize(array()), $SITE_ID));
					$arValue = (array)$arValue;
					$fileID = $arValue ? current($arValue) : false;

					if($key === 'FAVICON_IMAGE'){
						if($fileID){
							$faviconFile = CFIle::GetPath($fileID);
							$file_ext = pathinfo($faviconFile, PATHINFO_EXTENSION);
							$fav_ext = $file_ext ? $file_ext : 'ico';						
							$arValues[$key] = str_replace('//', '/', $SITE_DIR.'/favicon.'.$file_ext);
						} else {
							$arValues[$key] = str_replace('//', '/', $SITE_DIR.'/favicon.ico');
						}						
					}
						

					if($fileID)
					{
						if($key !== 'FAVICON_IMAGE')
							$arValues[$key] = CFIle::GetPath($fileID);
					}
					else
					{
						if($key === 'APPLE_TOUCH_ICON_IMAGE')
							$arValues[$key] = str_replace('//', '/', $SITE_DIR.'/include/apple-touch-icon.png');
						elseif($key === 'LOGO_IMAGE')
						{
							if(file_exists(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].$SITE_DIR.'logo.svg')))
								$arValues[$key] = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].$SITE_DIR.'logo.svg');
							else
								$arValues[$key] = str_replace('//', '/', $SITE_DIR.'/logo.png');
						}
					}

					if(!file_exists(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].$arValues[$key])))
					{
						$arValues[$key] = '';
					}
					else
					{
						//if($key === 'FAVICON_IMAGE')
							//$arValues[$key] .= '?'.filemtime(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].$arValues[$key]));
					}

				}
			}

			// replace #SITE_DIR#
			if(!defined('ADMIN_SECTION'))
			{
				if($arValues && is_array($arValues))
				{
					foreach($arValues as $optionCode => $arOption)
					{
						if(!is_array($arOption) && is_string($arOption))
							$arValues[$optionCode] = str_replace('#SITE_DIR#', $SITE_DIR, $arOption);
					}
				}
			}
		}

		return $arValues;
	}

	public static function GetFrontParametrsValues($SITE_ID){
		if(!$SITE_ID || !strlen($SITE_ID))
			$SITE_ID = SITE_ID;

		$arBackParametrs = self::GetBackParametrsValues($SITE_ID);
		if($arBackParametrs['THEME_SWITCHER'] === 'Y')
			$arValues = array_merge((array)$arBackParametrs, (array)$_SESSION['THEME'][$SITE_ID]);
		else
			$arValues = (array)$arBackParametrs;

		// global flag for OnEndBufferContentHandler
		$GLOBALS['_USE_LAZY_LOAD_ASPRO_NEXT_'] = $arValues['USE_LAZY_LOAD'] === 'Y';

		return $arValues;
	}

	public static function GetFrontParametrValue($optionCode, $SITE_ID=SITE_ID, $bStatic = true){
		static $arFrontParametrs;

		if(!isset($arFrontParametrs) || !$bStatic)
			$arFrontParametrs = self::GetFrontParametrsValues($SITE_ID);

		return $arFrontParametrs[$optionCode];
	}

	public static function CheckColor($strColor){
		$strColor = substr(str_replace('#', '', $strColor), 0, 6);
		$strColor = base_convert(base_convert($strColor, 16, 2), 2, 16);
		for($i = 0, $l = 6 - (function_exists('mb_strlen') ? mb_strlen($strColor) : strlen($strColor)); $i < $l; ++$i)
			$strColor = '0'.$strColor;
		return $strColor;
	}

	public static function GetUserID(){
		static $userID;
		if($userID === NULL)
		{
			global $USER;
			
			$userID = $USER->GetID();
			$userID = ($userID > 0 ? $userID : 0);
		}
		return $userID;
	}

	public static function ShowCabinetLink($icon=true, $text=true, $class_icon='', $show_mess=false, $message=''){
		global $APPLICATION, $arTheme;
		static $hauth_call;

		$iCalledID = ++$hauth_call;

		$type_svg = '';
		if($class_icon)
		{
			$tmp = explode(' ', $class_icon);
			$type_svg = '_'.$tmp[0];
		}
		$html = '<!-- noindex -->';
		$userID = self::GetUserID();
		if(!$message)
			$message = Loc::getMessage('CABINET_LINK');

		if($userID)
		{
			global $USER;

			$html .= '<a rel="nofollow" title="'.Loc::getMessage('CABINET_LINK').'" class="personal-link dark-color'.($text ? /*' with_dropdown'*/ '' : '').'" href="'.$arTheme['PERSONAL_PAGE_URL']['VALUE'].'">';
			if($icon)
				$html .= self::showIconSvg('cabinet', SITE_TEMPLATE_PATH.'/images/svg/User'.$type_svg.'_black.svg', $message, $class_icon);

			if($text)
				$html .= '<span class="wrap">';

				if ($text)
					$html .= '<span class="name">'.$USER->GetFullName().'</span>';
				if($show_mess)
					$html .= '<span class="title">'.$message.'</span>';

			if($text)
				$html .= '</span>';

			$html .= '</a>';
		}
		else
		{
			$url = ((isset($_GET['backurl']) && $_GET['backurl']) ? $_GET['backurl'] : $APPLICATION->GetCurUri());
			$html .= '<a rel="nofollow" title="'.Loc::getMessage('CABINET_LINK').'" class="personal-link dark-color animate-load" data-event="jqm" data-param-type="auth" data-param-backurl="'.htmlspecialcharsbx($url).'" data-name="auth" href="'.$arTheme['PERSONAL_PAGE_URL']['VALUE'].'">';
			if($icon)
				$html .= self::showIconSvg('cabinet', SITE_TEMPLATE_PATH.'/images/svg/Lock'.$type_svg.'_black.svg', $message, $class_icon);
			if($text)
				$html .= '<span class="wrap">';

				if($text)
					$html .= '<span class="name">'.Loc::getMessage('LOGIN').'</span>';
				if($show_mess)
					$html .= '<span class="title">'.$message.'</span>';
			if($text)
				$html .= '</span>';

			$html .= '</a>';
		}
		$html .= '<!-- /noindex -->';?>

		<?Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('header-auth-block'.$iCalledID);?>
			<?=$html;?>
		<?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('header-auth-block'.$iCalledID);?>

	<?}

	public static function showContactPhones($txt = '', $wrapTable = true, $class = '', $icon = 'Phone_black2.svg', $subclass = ''){
		global $arRegion, $APPLICATION;
		$iCalledID = ++$cphones_call;
		$iCountPhones = ($arRegion ? count($arRegion['PHONES']) : self::checkContentFile(SITE_DIR.'include/contacts-site-phone-one.php'));
		$bRegionContact = (\Bitrix\Main\Config\Option::get(self::moduleID, 'SHOW_REGION_CONTACT', 'N') == 'Y');
		?>
		<?if($arRegion):?>
			<?$frame = new \Bitrix\Main\Page\FrameHelper('header-allcphones-block'.$iCalledID);?>
			<?$frame->begin();?>
		<?endif;?>

		<?if($iCountPhones): // count of phones?>
			<?if($wrapTable):?>
				<table>
			<?endif;?>
				<tr>
					<td class="icon"><i class="fa big-icon s45 <?=$subclass;?> fa-phone"></i></td>
					<td>
						<span class="dark_table"><?=($txt ? $txt : Loc::getMessage('SPRAVKA'));?></span>
						<?if($arRegion && $bRegionContact):?>
							<div class="<?=($class ? ' '.$class : '')?>">
								<?for($i = 0; $i < $iCountPhones; ++$i):?>
									<?
									$phone = $arRegion['PHONES'][$i];
									$href = 'tel:'.str_replace(array(' ', '-', '(', ')'), '', $phone);
									$description = '';
									$description = ($arRegion ? $arRegion['PROPERTY_PHONES_DESCRIPTION'][$i] : $arBackParametrs['HEADER_PHONES_array_PHONE_DESCRIPTION_'.$i]);
									$description = (!empty($description)) ? 'title="' . $description . '"' : '';
									?>
									<div itemprop="telephone"><a <?=$description;?> href="<?=$href?>"><?=$phone?></a></div>
								<?endfor;?>
							</div>
						<?else:?>
							<div itemprop="telephone"><?$APPLICATION->IncludeFile(SITE_DIR."include/contacts-site-phone-one.php", Array(), Array("MODE" => "html", "NAME" => "Phone"));?></div>
						<?endif;?>
					</td>
				</tr>
			<?if($wrapTable):?>
				</table>
			<?endif;?>
		<?endif;?>
		<?if($arRegion):?>
			<?$frame->end();?>
		<?endif;?>
		<?
	}

	public static function showContactEmail($txt = '', $wrapTable = true, $class = '', $icon = 'Email.svg', $subclass = ''){
		global $arRegion, $APPLICATION;
		$iCalledID = ++$cemail_call;
		$bEmail = ($arRegion ? $arRegion['PROPERTY_EMAIL_VALUE'] : self::checkContentFile(SITE_DIR.'include/contacts-site-email.php'));
		$bRegionContact = (\Bitrix\Main\Config\Option::get(self::moduleID, 'SHOW_REGION_CONTACT', 'N') == 'Y');
		?>
		<?if($arRegion):?>
			<?$frame = new \Bitrix\Main\Page\FrameHelper('header-allcemail-block'.$iCalledID);?>
			<?$frame->begin();?>
		<?endif;?>
		<?if($bEmail): // count of phones?>
			<?if($wrapTable):?>
				<table>
			<?endif;?>
				<tr>
					<td class="icon"><i class="fa big-icon s45 <?=$subclass;?> fa-envelope"></i></td>
					<td>
						<span class="dark_table"><?=($txt ? $txt : Loc::getMessage('SPRAVKA'));?></span>
						<?if($arRegion && $bRegionContact):?>
							<div class="<?=($class ? ' '.$class : '')?>">
								<?foreach($arRegion['PROPERTY_EMAIL_VALUE'] as $value):?>
									<div itemprop="email">
										<a href="mailto:<?=$value;?>"><?=$value;?></a>
									</div>
								<?endforeach;?>
							</div>
						<?else:?>
							<div itemprop="email"><?$APPLICATION->IncludeFile(SITE_DIR."include/contacts-site-email.php", Array(), Array("MODE" => "html", "NAME" => "email"));?></div>
						<?endif;?>
					</td>
				</tr>
			<?if($wrapTable):?>
				</table>
			<?endif;?>
		<?endif;?>
		<?if($arRegion):?>
			<?$frame->end();?>
		<?endif;?>
		<?
	}

	public static function showContactText(){
		global $arRegion, $APPLICATION;
		$iCalledID = ++$cdesc_call;
		$bDesc = ($arRegion && $arRegion['PROPERTY_REGION_TAG_CONTACT_TEXT_VALUE']['TEXT'] ? $arRegion['PROPERTY_REGION_TAG_CONTACT_TEXT_VALUE']['TEXT'] : self::checkContentFile(SITE_DIR.'include/contacts-about.php'));
		$bRegionContact = (\Bitrix\Main\Config\Option::get(self::moduleID, 'SHOW_REGION_CONTACT', 'N') == 'Y');?>
		<?if($arRegion):?>
			<?$frame = new \Bitrix\Main\Page\FrameHelper('contact-text-block'.$iCalledID);?>
			<?$frame->begin();?>
		<?endif;?>
		<?if($bDesc):?>
			<?if($arRegion && $bRegionContact && $arRegion['PROPERTY_REGION_TAG_CONTACT_TEXT_VALUE']['TEXT']):?>
				<?=$arRegion['PROPERTY_REGION_TAG_CONTACT_TEXT_VALUE']['TEXT'];?>
			<?else:?>
				<?$APPLICATION->IncludeFile(SITE_DIR."include/contacts-about.php", Array(), Array("MODE" => "html", "NAME" => "Contacts about"));?>
			<?endif;?>
		<?endif;?>
		<?if($arRegion):?>
			<?$frame->end();?>
		<?endif;?>
		<?
	}

	public static function showContactAddr($txt = '', $wrapTable = true, $class = '', $icon = 'Addres_black.svg', $subclass = ''){
		global $arRegion, $APPLICATION;
		$iCalledID = ++$caddr_call;
		$bAddr = ($arRegion ? $arRegion['PROPERTY_ADDRESS_VALUE']['TEXT'] : self::checkContentFile(SITE_DIR.'include/contacts-site-address.php'));
		$bRegionContact = (\Bitrix\Main\Config\Option::get(self::moduleID, 'SHOW_REGION_CONTACT', 'N') == 'Y');
		?>
		<?if($arRegion):?>
			<?$frame = new \Bitrix\Main\Page\FrameHelper('header-allcaddr-block'.$iCalledID);?>
			<?$frame->begin();?>
		<?endif;?>
		<?if($bAddr): // count of phones?>
			<?if($wrapTable):?>
				<table>
			<?endif;?>
				<tr>
					<td class="icon"><i class="fa big-icon s45 <?=$subclass;?> fa-map-marker"></i></td>
					<td>
						<span class="dark_table"><?=$txt;?></span>
						<?if($arRegion && $bRegionContact):?>
							<div itemprop="address" class="<?=($class ? ' '.$class : '')?>">
								<?=$arRegion['PROPERTY_ADDRESS_VALUE']['TEXT'];?>
							</div>
						<?else:?>
							<div itemprop="address"><?$APPLICATION->IncludeFile(SITE_DIR."include/contacts-site-address.php", Array(), Array("MODE" => "html", "NAME" => "address"));?></div>
						<?endif;?>
					</td>
				</tr>
			<?if($wrapTable):?>
				</table>
			<?endif;?>
		<?endif;?>
		<?if($arRegion):?>
			<?$frame->end();?>
		<?endif;?>
		<?
	}

	public static function showContactSchedule($txt = '', $wrapTable = true, $class = '', $icon = 'WorkingHours_lg.svg', $subclass = ''){
		global $arRegion, $APPLICATION;
		$iCalledID = ++$cshc_call;
		$bRegionContact = (\Bitrix\Main\Config\Option::get(self::moduleID, 'SHOW_REGION_CONTACT', 'N') == 'Y');
		$bAddr = ($arRegion && $bRegionContact && $arRegion['PROPERTY_REGION_TAG_SHEDULLE_VALUE']['TEXT'] ? $arRegion['PROPERTY_REGION_TAG_SHEDULLE_VALUE']['TEXT'] : self::checkContentFile(SITE_DIR.'include/contacts-site-schedule.php'));
		?>
		<?if($arRegion):?>
			<?$frame = new \Bitrix\Main\Page\FrameHelper('header-allcaddr-block'.$iCalledID);?>
			<?$frame->begin();?>
		<?endif;?>
		<?if($bAddr): // count of phones?>
			<?if($wrapTable):?>
				<table>
			<?endif;?>
				<tr>
					<td class="icon"><i class="fa big-icon s45 <?=$subclass;?> fa-clock-o"></i></td>
					<td>
						<span class="dark_table"><?=$txt;?></span>
						<?if($arRegion && $arRegion['PROPERTY_REGION_TAG_SHEDULLE_VALUE']['TEXT'] && $bRegionContact):?>
							<div itemprop="schedule" class="<?=($class ? ' '.$class : '')?>">
								<?=$arRegion['PROPERTY_REGION_TAG_SHEDULLE_VALUE']['TEXT'];?>
							</div>
						<?else:?>
							<div itemprop="schedule"><?$APPLICATION->IncludeFile(SITE_DIR."include/contacts-site-schedule.php", Array(), Array("MODE" => "html", "NAME" => "schedule"));?></div>
						<?endif;?>
					</td>
				</tr>
			<?if($wrapTable):?>
				</table>
			<?endif;?>
		<?endif;?>
		<?if($arRegion):?>
			<?$frame->end();?>
		<?endif;?>
		<?
	}

	public static function ShowPrintLink($txt=''){
		$html = '';

		$arTheme = self::GetFrontParametrsValues(SITE_ID);
		if($arTheme['PRINT_BUTTON'] == 'Y')
		{
			if(!$txt)
				$txt = $arTheme['EXPRESSION_FOR_PRINT_PAGE'];

			$html = '<div class="print-link"><i class="icon"><svg id="Print.svg" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><path class="cls-1" d="M1553,287h-2v3h-8v-3h-2a2,2,0,0,1-2-2v-5a2,2,0,0,1,2-2h2v-4h8v4h2a2,2,0,0,1,2,2v5A2,2,0,0,1,1553,287Zm-8,1h4v-4h-4v4Zm4-12h-4v2h4v-2Zm4,4h-12v5h2v-3h8v3h2v-5Z" transform="translate(-1539 -274)"/></svg></i>';
			if($txt)
				$html .= '<span class="text">'.$txt.'</span>';
			$html .= '</div>';
		}
		return $html;
	}

	public static function ShowBasketWithCompareLink($class_link='top-btn hover', $class_icon='', $show_price = false, $class_block='', $force_show = false, $bottom = false){?>
		<?global $APPLICATION, $arTheme, $arBasketPrices;
		static $basket_call;
		$type_svg = '';
		if($class_icon)
		{
			$tmp = explode(' ', $class_icon);
			$type_svg = '_'.$tmp[0];
		}


		$iCalledID = ++$basket_call;?>

		<?if(($arTheme['ORDER_BASKET_VIEW']['VALUE'] == 'NORMAL' || ($arTheme['ORDER_BASKET_VIEW']['VALUE'] == 'BOTTOM' && $bottom)) || $force_show):?>
			<?Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('header-basket-with-compare-block'.$iCalledID);?>
				<?if($arTheme['CATALOG_COMPARE']['VALUE'] != 'N'):?>
					<?if($class_block):?>
						<div class="<?=$class_block;?>">
					<?endif;?>
					<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
						array(
							"COMPONENT_TEMPLATE" => ".default",
							"PATH" => SITE_DIR."ajax/show_compare_preview_top.php",
							"AREA_FILE_SHOW" => "file",
							"AREA_FILE_SUFFIX" => "",
							"AREA_FILE_RECURSIVE" => "Y",
							"CLASS_LINK" => $class_link,
							"CLASS_ICON" => $class_icon,
							"FROM_MODULE" => "Y",
							"EDIT_TEMPLATE" => "standard.php"
						),
						false, array('HIDE_ICONS' => 'Y')
					);?>
					<?if($class_block):?>
						</div>
					<?endif;?>
				<?endif;?>
				<?if(self::getShowBasket()):?>
					<!-- noindex -->
					<?if($class_block):?>
						<div class="<?=$class_block;?>">
					<?endif;?>
						<a rel="nofollow" class="basket-link delay <?=$class_link;?> <?=$class_icon;?> <?=($arBasketPrices['DELAY_COUNT'] ? 'basket-count' : '');?>" href="<?=$arTheme['BASKET_PAGE_URL']['VALUE'];?>#delayed" title="<?=$arBasketPrices['DELAY_SUMM_TITLE'];?>">
							<span class="js-basket-block">
								<?=self::showIconSvg("wish ".$class_icon, SITE_TEMPLATE_PATH."/images/svg/Wishlist".$type_svg."_black.svg");?>
								<span class="title dark_link"><?=Loc::getMessage('JS_BASKET_DELAY_TITLE');?></span>
								<span class="count"><?=$arBasketPrices['DELAY_COUNT'];?></span>
							</span>
						</a>
					<?if($class_block):?>
						</div>
					<?endif;?>
					<?if($class_block):?>
						<div class="<?=$class_block;?>">
					<?endif;?>
						<a rel="nofollow" class="basket-link basket <?=($show_price ? 'has_prices' : '');?> <?=$class_link;?> <?=$class_icon;?> <?=($arBasketPrices['BASKET_COUNT'] ? 'basket-count' : '');?>" href="<?=$arTheme['BASKET_PAGE_URL']['VALUE'];?>" title="<?=$arBasketPrices['BASKET_SUMM_TITLE'];?>">
							<span class="js-basket-block">
								<?=self::showIconSvg("basket ".$class_icon, SITE_TEMPLATE_PATH."/images/svg/Basket".$type_svg."_black.svg");?>
								<?if($show_price):?>
									<span class="wrap">
								<?endif;?>
								<span class="title dark_link"><?=Loc::getMessage('JS_BASKET_TITLE');?><span class="count"><?=$arBasketPrices['BASKET_COUNT'];?></span></span>
								<?if($show_price):?>
									<span class="prices"><?=($arBasketPrices['BASKET_COUNT'] ? $arBasketPrices['BASKET_SUMM'] : $arBasketPrices['BASKET_SUMM_TITLE_SMALL'] )?></span>
									</span>
								<?endif;?>
								<span class="count"><?=$arBasketPrices['BASKET_COUNT'];?></span>
							</span>
						</a>
					<?if($class_block):?>
						</div>
					<?endif;?>
					<!-- /noindex -->
				<?endif;?>
			<?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('header-basket-with-compare-block'.$iCalledID, '');?>
		<?endif;?>
	<?}

	public static function ShowMobileMenuCabinet(){
		global $APPLICATION, $arTheme;
		static $mauth_call;

		$iCalledID = ++$mauth_call;?>
		<?Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('mobile-auth-block'.$iCalledID);?>
		<?$APPLICATION->IncludeComponent(
			"bitrix:menu",
			"cabinet_mobile",
			Array(
				"COMPONENT_TEMPLATE" => "cabinet_mobile",
				"MENU_CACHE_TIME" => "3600000",
				"MENU_CACHE_TYPE" => "A",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => array(
				),
				"DELAY" => "N",
				"MAX_LEVEL" => \Bitrix\Main\Config\Option::get(self::moduleID, "MAX_DEPTH_MENU", 2),
				"ALLOW_MULTI_SELECT" => "Y",
				"CACHE_SELECTED_ITEMS" => "N",
				"ROOT_MENU_TYPE" => "cabinet",
				"CHILD_MENU_TYPE" => "left",
				"USE_EXT" => "Y"
			)
		);?>
		<?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('mobile-auth-block'.$iCalledID);?>
	<?}

	public static function ShowMobileMenuBasket(){?>
		<?global $APPLICATION, $arTheme, $arBasketPrices, $compare_items;?>

		<?
		$basketUrl = trim($arTheme['BASKET_PAGE_URL']['VALUE']);
		$compareUrl = trim($arTheme['COMPARE_PAGE_URL']['VALUE']);
		$bShowBasket = ($basketUrl && strlen($basketUrl) && self::getShowBasket());
		static $mbasket_call;

		$iCalledID = ++$mbasket_call;
		$count_compare = 0;
		if($compare_items)
		{
			$count_compare = count($compare_items);
		}
		else
		{
			if(isset($_SESSION["CATALOG_COMPARE_LIST"][$arTheme['CATALOG_IBLOCK_ID']['VALUE']]['ITEMS']))
			{
				$compare_items = array_keys($_SESSION["CATALOG_COMPARE_LIST"][$arTheme['CATALOG_IBLOCK_ID']['VALUE']]['ITEMS']);
				$count_compare = count(array_keys($compare_items));
			}
		}?>
		<?Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('mobile-basket-with-compare-block'.$iCalledID);?>
		<!-- noindex -->
		<?if($bShowBasket):?>
			<div class="menu middle">
				<ul>
					<li class="counters">
						<a rel="nofollow" class="dark-color basket-link basket ready <?=($arBasketPrices['BASKET_COUNT'] ? 'basket-count' : '');?>" href="<?=$basketUrl?>">
							<i class="svg svg-basket"></i>
							<span><?=Loc::getMessage('JS_BASKET_TITLE')?><span class="count<?=(!$arBasketPrices['BASKET_COUNT'] ? ' empted' : '')?>"><?=$arBasketPrices['BASKET_COUNT'];?></span></span>
						</a>
					</li>
					<li class="counters">
						<a rel="nofollow" class="dark-color basket-link delay ready <?=($arBasketPrices['DELAY_COUNT'] ? 'basket-count' : '');?>" href="<?=$basketUrl?>#delayed">
							<i class="svg svg-wish"></i>
							<span><?=Loc::getMessage('JS_BASKET_DELAY_TITLE')?><span class="count<?=(!$arBasketPrices['DELAY_COUNT'] ? ' empted' : '')?>"><?=$arBasketPrices['DELAY_COUNT'];?></span></span>
						</a>
					</li>
				</ul>
			</div>
		<?endif;?>
		<?if($arTheme['CATALOG_COMPARE']['VALUE'] != 'N'):?>
			<div class="menu middle">
				<ul>
					<li class="counters">
						<a rel="nofollow" class="dark-color basket-link compare ready <?=($count_compare ? 'basket-count' : '');?>" href="<?=$compareUrl?>">
							<i class="svg svg-compare"></i>
							<span><?=Loc::getMessage('JS_COMPARE_TITLE')?><span class="count<?=(!$count_compare ? ' empted' : '')?>"><?=$count_compare;?></span></span>
						</a>
					</li>
				</ul>
			</div>
		<?endif;?>
		<!-- /noindex -->
		<?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('mobile-basket-with-compare-block'.$iCalledID);?>
	<?}

	public static function ShowMobileMenuContacts(){
		global $APPLICATION, $arRegion, $arTheme;
		$arBackParametrs = self::GetBackParametrsValues(SITE_ID);
		$iCountPhones = ($arRegion ? count($arRegion['PHONES']) : $arBackParametrs['HEADER_PHONES']);
		$regionId = ($arRegion ? $arRegion['ID'] : '');
		?>
		<?if($iCountPhones): // count of phones?>
			<?
			$phone = ($arRegion ? $arRegion['PHONES'][0] : $arBackParametrs['HEADER_PHONES_array_PHONE_VALUE_0']);
			$href = 'tel:'.str_replace(array(' ', '-', '(', ')'), '', $phone);
			$description = ($arRegion ? $arRegion['PROPERTY_PHONES_DESCRIPTION'][0] : $arBackParametrs['HEADER_PHONES_array_PHONE_DESCRIPTION_0']);
			$description = (!empty($description)) ? '<span class="descr">' . $description . '</span>' : '';
			static $mphones_call;

			$iCalledID = ++$mphones_call;
			?>

			<?if($arRegion):?>
			<?$frame = new \Bitrix\Main\Page\FrameHelper('mobile-phone-block'.$iCalledID);?>
			<?$frame->begin();?>
			<?endif;?>

			<div class="menu middle mobile-menu-contacts">
				<ul>
					<li>
						<a rel="nofollow" href="<?=$href?>" class="dark-color<?=($iCountPhones > 1 ? ' parent' : '')?> <?=(empty($description)?'no-decript':'decript')?> ">
							<i class="svg svg-phone"></i>
							<span><?=$phone?><?=$description?></span>
							<?if($iCountPhones > 1):?>
								<span class="arrow"><i class="svg svg_triangle_right"></i></span>
							<?endif;?>
						</a>
						<?if($iCountPhones > 1): // if more than one?>
							<ul class="dropdown">
								<li class="menu_back"><a href="" class="dark-color" rel="nofollow"><i class="svg svg-arrow-right"></i><?=Loc::getMessage('NEXT_T_MENU_BACK')?></a></li>
								<li class="menu_title"><?=Loc::getMessage('NEXT_T_MENU_CALLBACK')?></li>
								<?for($i = 0; $i < $iCountPhones; ++$i):?>
									<?
									$phone = ($arRegion ? $arRegion['PHONES'][$i] : $arBackParametrs['HEADER_PHONES_array_PHONE_VALUE_'.$i]);
									$href = 'tel:'.str_replace(array(' ', '-', '(', ')'), '', $phone);
									$description = ($arRegion ? $arRegion['PROPERTY_PHONES_DESCRIPTION'][$i] : $arBackParametrs['HEADER_PHONES_array_PHONE_DESCRIPTION_'.$i]);
									$description = (!empty($description)) ? '<span class="descr">' . $description . '</span>' : '';
									?>
									<li><a rel="nofollow" href="<?=$href?>" class="bold dark-color <?=(empty($description)?'no-decript':'decript')?>"><?=$phone?><?=$description?></a></li>
								<?endfor;?>
								<?if($arTheme['SHOW_CALLBACK']['VALUE'] == 'Y'):?>
									<li><a rel="nofollow" class="dark-color" href="" data-event="jqm" data-param-form_id="CALLBACK" data-name="callback"><?=Loc::getMessage('CALLBACK')?></a></li>
								<?endif;?>
							</ul>
						<?endif;?>
					</li>
				</ul>
			</div>

			<?if($arRegion):?>
			<?$frame->end();?>
			<?endif;?>

		<?endif;?>
		<div class="contacts">
			<div class="title"><?=Loc::getMessage('NEXT_T_MENU_CONTACTS_TITLE')?></div>

			<?if($arRegion):?>
			<?$frame = new \Bitrix\Main\Page\FrameHelper('mobile-contact-block');?>
			<?$frame->begin();?>
			<?endif;?>

			<?if($arRegion):?>
				<?if($arRegion['PROPERTY_ADDRESS_VALUE']):?>
					<div class="address">
						<?=self::showIconSvg("address", SITE_TEMPLATE_PATH."/images/svg/Addres_black.svg");?>
						<?=$arRegion['PROPERTY_ADDRESS_VALUE']['TEXT'];?>
					</div>
				<?endif;?>
			<?else:?>
				<div class="address">
					<?=self::showIconSvg("address", SITE_TEMPLATE_PATH."/images/svg/Addres_black.svg");?>
					<?$APPLICATION->IncludeFile(SITE_DIR."include/top_page/site-address.php", array(), array(
							"MODE" => "html",
							"NAME" => "Address",
							"TEMPLATE" => "include_area.php",
						)
					);?>
				</div>
			<?endif;?>
			<?if($arRegion):?>
				<?if($arRegion['PROPERTY_EMAIL_VALUE']):?>
					<div class="email">
						<?=self::showIconSvg("email", SITE_TEMPLATE_PATH."/images/svg/Email.svg");?>
						<?foreach($arRegion['PROPERTY_EMAIL_VALUE'] as $value):?>
							<a href="mailto:<?=$value;?>"><?=$value;?></a>
						<?endforeach;?>
					</div>
				<?endif;?>
			<?else:?>
				<div class="email">
					<?=self::showIconSvg("email", SITE_TEMPLATE_PATH."/images/svg/Email.svg");?>
					<?$APPLICATION->IncludeFile(SITE_DIR."include/footer/site-email.php", array(), array(
							"MODE" => "html",
							"NAME" => "Address",
							"TEMPLATE" => "include_area.php",
						)
					);?>
				</div>
			<?endif;?>

			<?if($arRegion):?>
			<?$frame->end();?>
			<?endif;?>

		</div>
	<?}

	public static function ShowMobileRegions(){
		global $APPLICATION, $arRegion, $arRegions;

		if($arRegion):
			$type_regions = self::GetFrontParametrValue('REGIONALITY_TYPE');
			static $mregions_call;

			$iCalledID = ++$mregions_call;
			$arRegions = CNextRegionality::getRegions();
			$regionId = ($arRegion ? $arRegion['ID'] : '');
			$iCountRegions = count($arRegions);?>
			<?Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('mobile-region-block'.$iCalledID);?>
			<!-- noindex -->
			<div class="menu middle mobile_regions">
				<ul>
					<li>
						<?if(self::GetFrontParametrValue('REGIONALITY_SEARCH_ROW') != 'Y'):?>
							<a rel="nofollow" href="" class="dark-color<?=($iCountRegions > 1 ? ' parent' : '')?>">
						<?else:?>
							<a rel="nofollow" href="" class="js_city_chooser dark-color" data-event="jqm" data-name="city_chooser" data-param-url="<?=urlencode($APPLICATION->GetCurUri());?>" data-param-form_id="city_chooser">
						<?endif;?>
							<i class="svg svg-address black"></i>
							<span><?=$arRegion['NAME'];?></span>
							<?if($iCountRegions > 1):?>
								<span class="arrow"><i class="svg svg_triangle_right"></i></span>
							<?endif;?>
						</a>
						<?if(self::GetFrontParametrValue('REGIONALITY_SEARCH_ROW') != 'Y'):?>
							<?if($iCountRegions > 1): // if more than one?>
								<?$host = (CMain::IsHTTPS() ? 'https://' : 'http://');
								$uri = $APPLICATION->GetCurUri();?>
								<ul class="dropdown">
									<li class="menu_back"><a href="" class="dark-color" rel="nofollow"><i class="svg svg-arrow-right"></i><?=Loc::getMessage('NEXT_T_MENU_BACK')?></a></li>
									<li class="menu_title"><?=Loc::getMessage('NEXT_T_MENU_REGIONS')?></li>
									<?foreach($arRegions as $arItem):?>
										<?$href = $uri;
										if($arItem['PROPERTY_MAIN_DOMAIN_VALUE'] && $type_regions == 'SUBDOMAIN')
											$href = $host.$arItem['PROPERTY_MAIN_DOMAIN_VALUE'].$uri;
										?>
										<li><a rel="nofollow" href="<?=$href?>" class="dark-color city_item" data-id="<?=$arItem['ID'];?>"><?=$arItem['NAME'];?></a></li>
									<?endforeach;?>
								</ul>
							<?endif;?>
						<?endif;?>
					</li>
				</ul>
			</div>
			<!-- /noindex -->
			<?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('mobile-region-block'.$iCalledID);?>
		<?endif;
	}

	public static function getFieldImageData(array &$arItem, array $arKeys, $entity = 'ELEMENT', $ipropertyKey = 'IPROPERTY_VALUES'){
		if (empty($arItem) || empty($arKeys))
            return;

        $entity = (string)$entity;
        $ipropertyKey = (string)$ipropertyKey;

        foreach ($arKeys as $fieldName)
        {
            if(!isset($arItem[$fieldName]) || (!isset($arItem['~'.$fieldName]) || !$arItem['~'.$fieldName]))
                continue;
            $imageData = false;
            $imageId = (int)$arItem['~'.$fieldName];
            if ($imageId > 0)
                $imageData = \CFile::getFileArray($imageId);
            unset($imageId);
            if (is_array($imageData))
            {
                if (isset($imageData['SAFE_SRC']))
                {
                    $imageData['UNSAFE_SRC'] = $imageData['SRC'];
                    $imageData['SRC'] = $imageData['SAFE_SRC'];
                }
                else
                {
                    $imageData['UNSAFE_SRC'] = $imageData['SRC'];
                    $imageData['SRC'] = \CHTTP::urnEncode($imageData['SRC'], 'UTF-8');
                }
                $imageData['ALT'] = '';
                $imageData['TITLE'] = '';

                if ($ipropertyKey != '' && isset($arItem[$ipropertyKey]) && is_array($arItem[$ipropertyKey]))
                {
                    $entityPrefix = $entity.'_'.$fieldName;
                    if (isset($arItem[$ipropertyKey][$entityPrefix.'_FILE_ALT']))
                        $imageData['ALT'] = $arItem[$ipropertyKey][$entityPrefix.'_FILE_ALT'];
                    if (isset($arItem[$ipropertyKey][$entityPrefix.'_FILE_TITLE']))
                        $imageData['TITLE'] = $arItem[$ipropertyKey][$entityPrefix.'_FILE_TITLE'];
                    unset($entityPrefix);
                }
                if ($imageData['ALT'] == '' && isset($arItem['NAME']))
                    $imageData['ALT'] = $arItem['NAME'];
                if ($imageData['TITLE'] == '' && isset($arItem['NAME']))
                    $imageData['TITLE'] = $arItem['NAME'];
            }
            $arItem[$fieldName] = $imageData;
            unset($imageData);
        }

        unset($fieldName);
	}

	public static function GetDirMenuParametrs($dir){
		if($dir && strlen($dir)){
			$file = str_replace('//', '/', $dir.'/.section.php');
			if(file_exists($file)){
				@include($file);
				return $arDirProperties;
			}
		}

		return false;
	}

	public static function FormatNewsUrl($arItem){
    	$url = $arItem['DETAIL_PAGE_URL'];
    	if($arItem['DISPLAY_PROPERTIES']['REDIRECT']['VALUE'] && strlen($arItem['DISPLAY_PROPERTIES']['REDIRECT']['VALUE']))
		{
			$url = $arItem['DISPLAY_PROPERTIES']['REDIRECT']['VALUE'];
			return $url;
		}
    	if($arItem['ACTIVE_FROM'])
    	{
    		if($arDateTime = ParseDateTime($arItem['ACTIVE_FROM'], FORMAT_DATETIME))
    		{
		        $url = str_replace("#YEAR#", $arDateTime['YYYY'], $arItem['DETAIL_PAGE_URL']);
		        return $url;
    		}
    	}
    	return $url;
    }

	public static function GetSections($arItems, $arParams){
		$arSections = array(
			'PARENT_SECTIONS' => array(),
			'CHILD_SECTIONS' => array(),
			'ALL_SECTIONS' => array(),
		);
		if(is_array($arItems) && $arItems)
		{
			$arSectionsIDs = array();
			foreach($arItems as $arItem)
			{
				if($SID = $arItem['IBLOCK_SECTION_ID'])
					$arSectionsIDs[] = $SID;
			}
			if($arSectionsIDs)
			{
				$arSections['ALL_SECTIONS'] = CNextCache::CIBLockSection_GetList(array('SORT' => 'ASC', 'NAME' => 'ASC', 'CACHE' => array('TAG' => CNextCache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 'GROUP' => array('ID'), 'MULTI' => 'N')), array('ID' => $arSectionsIDs));
				$bCheckRoot = false;
				foreach($arSections['ALL_SECTIONS'] as $key => $arSection)
				{
					if($arSection['DEPTH_LEVEL'] > 1)
					{
						$bCheckRoot = true;
						$arSections['CHILD_SECTIONS'][$key] = $arSection;
						unset($arSections['ALL_SECTIONS'][$key]);

						$arFilter = array('IBLOCK_ID'=>$arSection['IBLOCK_ID'], '<=LEFT_BORDER' => $arSection['LEFT_MARGIN'], '>=RIGHT_BORDER' => $arSection['RIGHT_MARGIN'], 'DEPTH_LEVEL' => 1);
						$arSelect = array('ID', 'SORT', 'IBLOCK_ID', 'NAME');
						$arParentSection = CNextCache::CIBLockSection_GetList(array('SORT' => 'ASC', 'NAME' => 'ASC', 'CACHE' => array('TAG' => CNextCache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 'MULTI' => 'N')), $arFilter, false, $arSelect);

						$arSections['ALL_SECTIONS'][$arParentSection['ID']]['SECTION'] = $arParentSection;
						$arSections['ALL_SECTIONS'][$arParentSection['ID']]['CHILD_IDS'][$arSection['ID']] = $arSection['ID'];

						$arSections['PARENT_SECTIONS'][$arParentSection['ID']] = $arParentSection;
					}
					else
					{
						$arSections['ALL_SECTIONS'][$key]['SECTION'] = $arSection;
						$arSections['PARENT_SECTIONS'][$key] = $arSection;
					}
				}

				if($bCheckRoot)
				{
					// get root sections
					$arFilter = array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ACTIVE' => 'Y', 'DEPTH_LEVEL' => 1, 'ID' => array_keys($arSections['ALL_SECTIONS']));
					$arSelect = array('ID', 'SORT', 'IBLOCK_ID', 'NAME');
					$arRootSections = CNextCache::CIBLockSection_GetList(array('SORT' => 'ASC', 'NAME' => 'ASC', 'CACHE' => array('TAG' => CNextCache::GetIBlockCacheTag($arParams['IBLOCK_ID']))), $arFilter, false, $arSelect);
					foreach($arRootSections as $arSection)
					{
						$arSections['ALL_SECTIONS']['SORTED'][$arSection['ID']] = $arSections['ALL_SECTIONS'][$arSection['ID']];
						unset($arSections['ALL_SECTIONS'][$arSection['ID']]);
					}
					foreach($arSections['ALL_SECTIONS']['SORTED'] as $key => $arSection)
					{
						$arSections['ALL_SECTIONS'][$key] = $arSection;
					}
					unset($arSections['ALL_SECTIONS']['SORTED']);
				}
			}
		}
		return $arSections;
	}

	public static function ShowAdminRow($optionCode, $arOption, $arTab, $arControllerOption){
		$optionName = $arOption['TITLE'];
		$optionType = $arOption['TYPE'];
		$optionList = $arOption['LIST'];
		$optionDefault = $arOption['DEFAULT'];
		$optionVal = $arTab['OPTIONS'][$optionCode];
		$optionSize = $arOption['SIZE'];
		$optionCols = $arOption['COLS'];
		$optionRows = $arOption['ROWS'];
		$optionChecked = $optionVal == 'Y' ? 'checked' : '';
		$optionDisabled = isset($arControllerOption[$optionCode]) || array_key_exists('DISABLED', $arOption) && $arOption['DISABLED'] == 'Y' ? 'disabled' : '';
		$optionSup_text = array_key_exists('SUP', $arOption) ? $arOption['SUP'] : '';
		$optionController = isset($arControllerOption[$optionCode]) ? "title='".GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT")."'" : "";
		$optionsSiteID = $arTab['SITE_ID'];
		$isArrayItem = strpos($optionCode, '_array_') !== false;
		?>

		<?if($optionType == 'dynamic_iblock'):?>
			<?if(Loader::IncludeModule('iblock')):?>
				<td colspan="2">
					<div class="title"  align="center"><b><?=$optionName;?></b></div>
					<?
					$arIblocks = array();
					$arSort = array(
						"SORT" => "ASC",
						"ID" => "ASC"
					);
					$arFilter = array(
						"ACTIVE" => "Y",
						"SITE_ID" => $optionsSiteID,
						"TYPE" => "aspro_next_form"
					);
					$rsItems = CIBlock::GetList($arSort, $arFilter);
					while($arItem = $rsItems->Fetch()){
						if($arItem["CODE"] != "aspro_next_example" && $arItem["CODE"] != "aspro_next_order_page")
						{
							$arItem['THEME_VALUE'] = Option::get(self::moduleID, htmlspecialcharsbx($optionCode)."_".htmlspecialcharsbx(strtoupper($arItem['CODE'])), '', $optionsSiteID);
							$arIblocks[] = $arItem;
						}
					}
					if($arIblocks):?>
						<table width="100%">
							<?foreach($arIblocks as $arIblock):?>
								<tr>
									<td class="adm-detail-content-cell-l" width="50%">
										<?=GetMessage("SUCCESS_SEND_FORM", array("#IBLOCK_CODE#" => $arIblock["NAME"]));?>
									</td>
									<td class="adm-detail-content-cell-r" width="50%">
										<input type="text" <?=((isset($arOption['PARAMS']) && isset($arOption['PARAMS']['WIDTH'])) ? 'style="width:'.$arOption['PARAMS']['WIDTH'].'"' : '');?> <?=$optionController?> size="<?=$optionSize?>" maxlength="255" value="<?=htmlspecialcharsbx($arIblock['THEME_VALUE'])?>" name="<?=htmlspecialcharsbx($optionCode)."_".htmlspecialcharsbx($arIblock['CODE'])."_".$optionsSiteID?>" <?=$optionDisabled?>>
									</td>
								</tr>
							<?endforeach;?>
						</table>
					<?endif;?>
				</td>
			<?endif;?>
		<?elseif($optionType == "note"):?>
			<?if($optionCode == 'GOALS_NOTE'){
				$FORMS_GOALS_LIST = '';
				if(\Bitrix\Main\Loader::includeModule('form'))
				{
					if($optionsSiteID)
					{
						$by = array('by' => 's_id', 'CACHE' => array('TAG' => 'forms'));
						$order = 'asc';
						if($arForms = CNextCache::CForm_GetList($by, $order, array('SITE' => $optionsSiteID, 'SITE_EXACT_MATCH' => 'Y'), $is_filtered))
						{
							foreach($arForms as $arForm)
								$FORMS_GOALS_LIST .= $arForm['NAME'].' - <i>goal_webform_success_'.$arForm['ID'].'</i><br />';
						}
					}
				}
				$arOption["NOTE"] = str_replace('#FORMS_GOALS_LIST#', $FORMS_GOALS_LIST, $arOption["NOTE"]);
			}
			?>
			<td colspan="2" align="center">
				<?=BeginNote('align="center"');?>
				<?=$arOption["NOTE"]?>
				<?=EndNote();?>
			</td>
		<?else:?>
			<?if(!$isArrayItem):?>
				<td class="<?=(in_array($optionType, array("multiselectbox", "textarea", "statictext", "statichtml")) ? "adm-detail-valign-top" : "")?>" width="50%">
					<?if($optionType == "checkbox"):?>
						<label for="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>"><?=$optionName?></label>
					<?else:?>
						<?=$optionName.($optionCode == "BASE_COLOR_CUSTOM" ? ' #' : '')?>
					<?endif;?>
					<?if($optionSup_text && strlen($optionSup_text)):?>
						<span class="required"><sup><?=$optionSup_text?></sup></span>
					<?endif;?>
				</td>
			<?endif;?>
			<td<?=(!$isArrayItem ? ' width="50%"' : '')?>>
				<?
				if($optionCode == 'PAGE_CONTACTS')
				{
					$siteDir = str_replace('//', '/', $arTab['SITE_DIR']).'/';
					if($arPageBlocks = self::GetIndexPageBlocks($_SERVER['DOCUMENT_ROOT'].$siteDir.'contacts', 'page_contacts_', '')){
						$arTmp = array();
						foreach($arPageBlocks as $page => $value)
						{
							$value_ = str_replace('page_contacts_', '', $value);
							$arTmp[$value_] = $value;
						}
						foreach($arOption['LIST'] as $key_list => $arValue)
						{
							if(isset($arTmp[$key_list]))
								;
							else
								unset($arOption['LIST'][$key_list]);
						}
					}
					$optionList = $arOption['LIST'];
				}
				elseif($optionCode == 'BLOG_PAGE')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/blog');
				}
				elseif($optionCode == 'NEWS_PAGE')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/news');
				}
				elseif($optionCode == 'PROJECTS_PAGE')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/projects');
				}
				elseif($optionCode == 'STAFF_PAGE')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/staff');
				}
				elseif($optionCode == 'PARTNERS_PAGE')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/partners');
				}
				elseif($optionCode == 'PARTNERS_PAGE_DETAIL')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/partners', 'ELEMENT');
				}
				elseif($optionCode == 'CATALOG_PAGE_DETAIL')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/catalog/main', 'ELEMENT');
				}
				elseif($optionCode == 'USE_FAST_VIEW_PAGE_DETAIL')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/catalog/main', 'FAST_VIEW_ELEMENT');
				}
				elseif($optionCode == 'VACANCY_PAGE')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/vacancy');
				}
				elseif($optionCode == 'LICENSES_PAGE')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/licenses');
				}
				elseif($optionCode == 'GRUPPER_PROPS')
				{
					// redsign.grupper
					$optionList['GRUPPER']['TITLE'] = Loc::getMessage('GRUPPER_PROPS_GRUPPER');
					if(!\Bitrix\Main\Loader::includeModule('redsign.grupper'))
					{
						$optionList['GRUPPER']['DISABLED'] = 'Y';
						$optionList['GRUPPER']['TITLE'] .= Loc::getMessage('NOT_INSTALLED', array('#MODULE_NAME#' => 'redsign.grupper'));
					}

					// webdebug.utilities
					$optionList['WEBDEBUG']['TITLE'] = Loc::getMessage('GRUPPER_PROPS_WEBDEBUG');
					if(!\Bitrix\Main\Loader::includeModule('webdebug.utilities'))
					{
						$optionList['WEBDEBUG']['DISABLED'] = 'Y';
						$optionList['WEBDEBUG']['TITLE'] .= Loc::getMessage('NOT_INSTALLED', array('#MODULE_NAME#' => 'webdebug.utilities'));
					}

					// yenisite.infoblockpropsplus
					$optionList['YENISITE_GRUPPER']['TITLE'] = Loc::getMessage('GRUPPER_PROPS_YENISITE_GRUPPER');
					if(!\Bitrix\Main\Loader::includeModule('yenisite.infoblockpropsplus'))
					{
						$optionList['YENISITE_GRUPPER']['DISABLED'] = 'Y';
						$optionList['YENISITE_GRUPPER']['TITLE'] .= Loc::getMessage('NOT_INSTALLED', array('#MODULE_NAME#' => 'yenisite.infoblockpropsplus'));
					}
				}
				elseif($optionCode === 'PRIORITY_SECTION_DESCRIPTION_SOURCE')
				{
					// sotbit.seometa
					$optionList['SOTBIT_SEOMETA']['TITLE'] = Loc::getMessage('PRIORITY_SECTION_DESCRIPTION_SOURCE_SOTBIT_SEOMETA');
					if(!\Bitrix\Main\Loader::includeModule('sotbit.seometa'))
					{
						$optionList['SOTBIT_SEOMETA']['DISABLED'] = 'Y';
						$optionList['SOTBIT_SEOMETA']['TITLE'] .= Loc::getMessage('NOT_INSTALLED', array('#MODULE_NAME#' => 'sotbit.seometa'));
					}
				}
				?>

				<?if($optionType == "checkbox"):?>
					<input type="checkbox" <?=((isset($arOption['DEPENDENT_PARAMS']) && $arOption['DEPENDENT_PARAMS']) ? "class='depend-check'" : "");?> <?=$optionController?> id="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" value="Y" <?=$optionChecked?> <?=$optionDisabled?> <?=($optionDefault && strlen($optionDefault) ? $optionDefault : "")?>>
				<?elseif($optionType == "text" || $optionType == "password"):?>
					<input type="<?=$optionType?>" <?=((isset($arOption['PARAMS']) && isset($arOption['PARAMS']['WIDTH'])) ? 'style="width:'.$arOption['PARAMS']['WIDTH'].'"' : '');?> <?=$optionController?> size="<?=$optionSize?>" maxlength="255" value="<?=htmlspecialcharsbx($optionVal)?>" name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" <?=$optionDisabled?> <?=($optionCode == "password" ? "autocomplete='off'" : "")?>>
				<?elseif($optionType == "selectbox"):?>
					<?
					if(isset($arOption['TYPE_SELECT']))
					{
						if($arOption['TYPE_SELECT'] == 'STORES')
						{
							static $bStores;
							if ($bStores === null){
								$bStores = false;
								if(\Bitrix\Main\Loader::includeModule('catalog')){
									if(class_exists('CCatalogStore')){
										$dbRes = CCatalogStore::GetList(array(), array(), false, false, array());
										if($c = $dbRes->SelectedRowsCount()){
											$bStores = true;
										}
									}
								}
							}
							if(!$bStores)
								unset($optionList['STORES']);
						}
						elseif($arOption['TYPE_SELECT'] == 'IBLOCK')
						{
							$bIBlocks = false;
							\Bitrix\Main\Loader::includeModule('iblock');
							$rsIBlock=CIBlock::GetList(array("SORT" => "ASC", "ID" => "DESC"), array("LID" => $optionsSiteID));
							$arIBlocks=array();
							while($arIBlock=$rsIBlock->Fetch()){
								$arIBlocks[$arIBlock["ID"]]["NAME"]="(".$arIBlock["ID"].") ".$arIBlock["NAME"]."[".$arIBlock["CODE"]."]";
								$arIBlocks[$arIBlock["ID"]]["CODE"]=$arIBlock["CODE"];
							}
							if($arIBlocks)
							{
								$bIBlocks = true;
							}
						}
						elseif($arOption['TYPE_SELECT'] == 'GROUP')
						{
							static $arUserGroups;
							if($arUserGroups === null){
								$DefaultGroupID = 0;
								$rsGroups = CGroup::GetList($by = "id", $order = "asc", array("ACTIVE" => "Y"));
								while($arItem = $rsGroups->Fetch()){
									$arUserGroups[$arItem["ID"]] = $arItem["NAME"];
									if($arItem["ANONYMOUS"] == "Y"){
										$DefaultGroupID = $arItem["ID"];
									}
								}
							}
							$optionList = $arUserGroups;
						}
					}
					if(!is_array($optionList)) $optionList = (array)$optionList;
					$arr_keys = array_keys($optionList);
					?>
					<select name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" <?=$optionController?> <?=$optionDisabled?>>
						<?if($bIBlocks)
						{
							foreach($arIBlocks as $key => $arValue) {
								$selected = "";
								if(!$optionVal && $arValue["CODE"] === "aspro_next_catalog"){
									$selected = "selected";
								}
								elseif($optionVal && $optionVal==$key){
									$selected = "selected";
								}
								?><option value="<?=$key?>" <?=$selected?>><?=htmlspecialcharsbx($arValue["NAME"])?></option><?
							}
						}
						elseif($optionCode === 'BASE_COLOR'){
							foreach($optionList as $key => $arValue){
								$selected = "";
								if($optionVal && $optionVal == $key){
									$selected = "selected";
								}
								?><option value="<?=$key?>" <?=$selected?> <?=($arValue['DISABLED'] === 'Y' ? 'disabled' : '')?> style="background-color:<?=$arValue["COLOR"]?>;"><?=htmlspecialcharsbx($arValue["TITLE"].' ('.$arValue["COLOR"].')')?></option><?
							}
						}
						elseif($optionCode === 'GRUPPER_PROPS' || $optionCode === 'PRIORITY_SECTION_DESCRIPTION_SOURCE' || $optionCode === 'AUTH_TYPE')
						{
							foreach($optionList as $key => $arValue){
								$selected = "";
								if($optionVal && $optionVal == $key){
									$selected = "selected";
								}
								?><option value="<?=$key?>" <?=$selected?> <?=($arValue['DISABLED'] === 'Y' ? 'disabled' : '')?>><?=htmlspecialcharsbx($arValue["TITLE"])?></option><?
							}
						}
						else
						{
							for($j = 0, $c = count($arr_keys); $j < $c; ++$j){
								?><option value="<?=$arr_keys[$j]?>" <?if($optionVal == $arr_keys[$j]) echo "selected"?>><?=htmlspecialcharsbx((is_array($optionList[$arr_keys[$j]]) ? $optionList[$arr_keys[$j]]["TITLE"] : $optionList[$arr_keys[$j]]))?></option><?
							}
						}?>
					</select>
				<?elseif($optionType == "multiselectbox"):?>
					<?
					if(isset($arOption['TYPE_SELECT']))
					{
						if($arOption['TYPE_SELECT'] == 'STORES')
						{
							$arStores = [];
							if (\Bitrix\Main\Loader::includeModule('catalog')) {
								if (class_exists('CCatalogStore')) {
									$dbRes = CCatalogStore::GetList(array(), array(), false, false, array('ID', 'TITLE'));
									while ($arStore = $dbRes->Fetch()) {
										$arStores[$arStore['ID']] = '['.$arStore['ID'].'] '.$arStore['TITLE'];
									}
								}
								if ($arStores) {
									$optionList = $arStores;
								}
							}
						}
						if($arOption['TYPE_SELECT'] == 'PRICES')
						{
							\Bitrix\Main\Loader::includeModule('catalog');
							$arPrices = array();
							$rsPrice = CCatalogGroup::GetList(array("SORT" => "ASC"), array());
							while($arPrice = $rsPrice->GetNext())
							{
								$name = ($arPrice["NAME_LANG"] ? $arPrice["NAME_LANG"] : $arPrice["NAME"]);
								$arPrices[$arPrice["ID"]]["TITLE"] = "(".$arPrice["ID"].") ".$name." [".$arPrice["XML_ID"]."]";
							}
							$optionList = $arPrices;
						}
						elseif($arOption['TYPE_SELECT'] == 'IBLOCK')
						{
							static $bIBlocks;
							if ($bIBlocks === null){
								$bIBlocks = false;
								\Bitrix\Main\Loader::includeModule('iblock');
								$rsIBlock=CIBlock::GetList(array("SORT" => "ASC", "ID" => "DESC"), array("LID" => $optionsSiteID));
								$arIBlocks=array();
								while($arIBlock=$rsIBlock->Fetch()){
									$arIBlocks[$arIBlock["ID"]]["NAME"]="(".$arIBlock["ID"].") ".$arIBlock["NAME"]."[".$arIBlock["CODE"]."]";
									$arIBlocks[$arIBlock["ID"]]["CODE"]=$arIBlock["CODE"];
								}
								if($arIBlocks)
								{
									$bIBlocks = true;
								}
							}
						}
						elseif($arOption['TYPE_SELECT'] == 'GROUP')
						{
							static $arUserGroups;
							if($arUserGroups === null){
								$DefaultGroupID = 0;
								$rsGroups = CGroup::GetList($by = "id", $order = "asc", array("ACTIVE" => "Y"));
								while($arItem = $rsGroups->Fetch()){
									$arUserGroups[$arItem["ID"]] = $arItem["NAME"];
									if($arItem["ANONYMOUS"] == "Y"){
										$DefaultGroupID = $arItem["ID"];
									}
								}
							}
							$optionList = $arUserGroups;
						}
					}
					if(!is_array($optionList)) $optionList = (array)$optionList;
					$arr_keys = array_keys($optionList);
					$optionVal = explode(",", $optionVal);
					if(!is_array($optionVal)) $optionVal = (array)$optionVal;
					?>
					<select size="<?=$optionSize?>" <?=$optionController?> <?=$optionDisabled?> multiple name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>[]" >
						<?for($j = 0, $c = count($arr_keys); $j < $c; ++$j):?>
							<option value="<?=$arr_keys[$j]?>" <?if(in_array($arr_keys[$j], $optionVal)) echo "selected"?>><?=htmlspecialcharsbx((is_array($optionList[$arr_keys[$j]]) ? $optionList[$arr_keys[$j]]["TITLE"] : $optionList[$arr_keys[$j]]))?></option>
						<?endfor;?>
					</select>
				<?elseif($optionType == "textarea"):?>
					<textarea <?=$optionController?> <?=$optionDisabled?> rows="<?=$optionRows?>" cols="<?=$optionCols?>" name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>"><?=htmlspecialcharsbx($optionVal)?></textarea>
				<?elseif($optionType == "statictext"):?>
					<?=htmlspecialcharsbx($optionVal)?>
				<?elseif($optionType == "statichtml"):?>
					<?=$optionVal?>
				<?elseif($optionType == "file"):?>
					<?$val = unserialize(Option::get(self::moduleID, $optionCode, serialize(array()), $optionsSiteID));

					$arOption['MULTIPLE'] = 'N';
					if($optionCode == 'LOGO_IMAGE'){
						$arOption['WIDTH'] = 394;
						$arOption['HEIGHT'] = 140;
					}
					elseif($optionCode == 'FAVICON_IMAGE'){
						$arOption['WIDTH'] = 16;
						$arOption['HEIGHT'] = 16;
					}
					elseif($optionCode == 'APPLE_TOUCH_ICON_IMAGE'){
						$arOption['WIDTH'] = 180;
						$arOption['HEIGHT'] = 180;
					}
					self::__ShowFilePropertyField($optionCode."_".$optionsSiteID, $arOption, $val);?>
				<?elseif($optionType === 'includefile'):?>
					<?
					if(!is_array($arOption['INCLUDEFILE'])){
						$arOption['INCLUDEFILE'] = array($arOption['INCLUDEFILE']);
					}
					foreach($arOption['INCLUDEFILE'] as $includefile){
						$includefile = str_replace('//', '/', str_replace('#SITE_DIR#', $arTab['SITE_DIR'].'/', $includefile));
						$includefile = str_replace('//', '/', str_replace('#TEMPLATE_DIR#', $arTab['TEMPLATE']['DIR'].'/', $includefile));
						if(strpos($includefile, '#') === false){
							$template = (isset($arOption['TEMPLATE']) && strlen($arOption['TEMPLATE']) ? 'include_area.php' : $arOption['TEMPLATE']);
							$href = (!strlen($includefile) ? "javascript:;" : "javascript: new BX.CAdminDialog({'content_url':'/bitrix/admin/public_file_edit.php?site=".$arTab['SITE_ID']."&bxpublic=Y&from=includefile&noeditor=Y&templateID=".$arTab['TEMPLATE']['ID']."&path=".$includefile."&lang=".LANGUAGE_ID."&template=".$template."&subdialog=Y&siteTemplateId=".$arTab['TEMPLATE']['ID']."','width':'1009','height':'503'}).Show();");
							?><a class="adm-btn" href="<?=$href?>" name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" title="<?=GetMessage('OPTIONS_EDIT_BUTTON_TITLE')?>"><?=GetMessage('OPTIONS_EDIT_BUTTON_TITLE')?></a>&nbsp;<?
						}
					}
					?>
				<?endif;?>
			</td>
		<?endif;?>
	<?}

	public static function getActualParamsValue($arTab, $arOption, $path, $field = 'ELEMENTS'){
		$optionList = $arOption['LIST'];
		// get site template
		$arTemplate = self::GetSiteTemplate($arTab['SITE_ID']);
		if($arTemplate && $arTemplate['PATH'])
		{
			if($arPageBlocks = self::GetComponentTemplatePageBlocks($arTemplate['PATH'].$path))
			{
				foreach($arOption['LIST'] as $key_list => $arValue)
				{
					if(isset($arPageBlocks[$field][$key_list]))
						;
					elseif($key_list != 'NO')
						unset($arOption['LIST'][$key_list]);
				}
			}
			$optionList = $arOption['LIST'];
		}
		return $optionList;
	}

	public static function UpdateFrontParametrsValues(){
		$arBackParametrs = self::GetBackParametrsValues(SITE_ID);
		if($arBackParametrs['THEME_SWITCHER'] === 'Y'){
			if($_REQUEST && isset($_REQUEST['BASE_COLOR']))
			{
				if($_REQUEST['THEME'] === 'default')
				{
					if(self::$arParametrsList && is_array(self::$arParametrsList))
					{
						foreach(self::$arParametrsList as $blockCode => $arBlock)
						{
							unset($_SESSION['THEME'][SITE_ID]);
							$_SESSION['THEME'][SITE_ID] = null;

							if(isset($_SESSION['THEME_ACTION']))
							{
								unset($_SESSION['THEME_ACTION'][SITE_ID]);
								$_SESSION['THEME_ACTION'][SITE_ID] = null;
							}
						}
					}
					Option::set(self::moduleID, 'NeedGenerateCustomTheme', 'Y', SITE_ID);
					Option::set(self::moduleID, 'NeedGenerateCustomThemeBG', 'Y', SITE_ID);
				}
				else{
					if(self::$arParametrsList && is_array(self::$arParametrsList)){
						foreach(self::$arParametrsList as $blockCode => $arBlock){
							if($arBlock['OPTIONS'] && is_array($arBlock['OPTIONS'])){
								foreach($arBlock['OPTIONS'] as $optionCode => $arOption){
									if($arOption['THEME'] === 'Y'){
										if(isset($_REQUEST[$optionCode])){
											if($optionCode == 'BASE_COLOR_CUSTOM' || $optionCode == 'CUSTOM_BGCOLOR_THEME')
												$_REQUEST[$optionCode] = self::CheckColor($_REQUEST[$optionCode]);

											if($optionCode == 'BASE_COLOR' && $_REQUEST[$optionCode] === 'CUSTOM')
												Option::set(self::moduleID, "NeedGenerateCustomTheme", 'Y', SITE_ID);

											if($optionCode == 'CUSTOM_BGCOLOR_THEME' && $_REQUEST[$optionCode] === 'CUSTOM')
												Option::set(self::moduleID, "NeedGenerateCustomThemeBG", 'Y', SITE_ID);

											if(isset($arOption['LIST']))
											{
												if(isset($arOption['LIST'][$_REQUEST[$optionCode]]))
													$_SESSION['THEME'][SITE_ID][$optionCode] = $_REQUEST[$optionCode];

												else
													$_SESSION['THEME'][SITE_ID][$optionCode] = $arOption['DEFAULT'];

											}
											else
											{
												$_SESSION['THEME'][SITE_ID][$optionCode] = $_REQUEST[$optionCode];
											}
											/*if($optionCode == 'ORDER_VIEW')
												self::ClearSomeComponentsCache(SITE_ID);*/

											if(isset($arOption['SUB_PARAMS']) && $arOption['SUB_PARAMS']) //nested params
											{

												if($arOption['TYPE'] == 'selectbox' && isset($arOption['LIST']))
												{
													$propValue = $_SESSION['THEME'][SITE_ID][$optionCode];
													if($arOption['SUB_PARAMS'][$propValue])
													{
														foreach($arOption['SUB_PARAMS'][$propValue] as $subkey => $arSubvalue)
														{
															if($_REQUEST[$propValue.'_'.$subkey])
															{
																$_SESSION['THEME'][SITE_ID][$propValue.'_'.$subkey] = $_REQUEST[$propValue.'_'.$subkey];
															}
															else
															{
																if($arSubvalue['TYPE'] == 'checkbox'  && !isset($arSubvalue["VISIBLE"]))
																	$_SESSION['THEME'][SITE_ID][$propValue.'_'.$subkey] = 'N';
																else
																	$_SESSION['THEME'][SITE_ID][$propValue.'_'.$subkey] = $arSubvalue['DEFAULT'];
															}
														}
													}
												}
											}

											if(isset($arOption['DEPENDENT_PARAMS']) && $arOption['DEPENDENT_PARAMS']) //dependent params
											{
												foreach($arOption['DEPENDENT_PARAMS'] as $key => $arSubOptions)
												{
													if($arSubOptions['THEME'] == 'Y')
													{
														if($_REQUEST[$key])
															$_SESSION['THEME'][SITE_ID][$key] = $_REQUEST[$key];
														else
														{
															if($arSubOptions['TYPE'] == 'checkbox')
															{
																if(isset($_SESSION['THEME_ACTION']) && (isset($_SESSION['THEME_ACTION'][SITE_ID][$key]) && $_SESSION['THEME_ACTION'][SITE_ID][$key]))
																{
																	$_SESSION['THEME'][SITE_ID][$key] = $_SESSION['THEME_ACTION'][SITE_ID][$key];
																	unset($_SESSION['THEME_ACTION'][SITE_ID][$key]);
																}
																else
																	$_SESSION['THEME'][SITE_ID][$key] = 'N';
															}
															else
															{
																if(isset($_SESSION['THEME_ACTION']) && (isset($_SESSION['THEME_ACTION'][SITE_ID][$key]) && $_SESSION['THEME_ACTION'][SITE_ID][$key]))
																{
																	$_SESSION['THEME'][SITE_ID][$key] = $_SESSION['THEME_ACTION'][SITE_ID][$key];
																	unset($_SESSION['THEME_ACTION'][SITE_ID][$key]);
																}
																else
																	$_SESSION['THEME'][SITE_ID][$key] = $arSubOptions['DEFAULT'];
															}
														}
													}
												}
											}

											$bChanged = true;
										}
										else
										{
											if($arOption['TYPE'] == 'checkbox' && !$_REQUEST[$optionCode])
											{
												$_SESSION['THEME'][SITE_ID][$optionCode] = 'N';
												if(isset($arOption['DEPENDENT_PARAMS']) && $arOption['DEPENDENT_PARAMS']) //dependent params save
												{
													foreach($arOption['DEPENDENT_PARAMS'] as $key => $arSubOptions)
													{
														if($arSubOptions['THEME'] == 'Y')
														{
															if(isset($_SESSION['THEME'][SITE_ID][$key]))
																$_SESSION['THEME_ACTION'][SITE_ID][$key] = $_SESSION['THEME'][SITE_ID][$key];
															else
																$_SESSION['THEME_ACTION'][SITE_ID][$key] = $arBackParametrs[$key];
														}
													}
												}
											}

											if(isset($arOption['SUB_PARAMS']) && $arOption['SUB_PARAMS']) //nested params
											{

												if($arOption['TYPE'] == 'selectbox' && isset($arOption['LIST']))
												{
													$propValue = $_SESSION['THEME'][SITE_ID][$optionCode];
													if($arOption['SUB_PARAMS'][$propValue])
													{
														foreach($arOption['SUB_PARAMS'][$propValue] as $subkey => $arSubvalue)
														{
															if($_REQUEST[$propValue.'_'.$subkey])
																$_SESSION['THEME'][SITE_ID][$propValue.'_'.$subkey] = $_REQUEST[$propValue.'_'.$subkey];
															else
															{
																if(!isset($arSubvalue["VISIBLE"]))
																	$_SESSION['THEME'][SITE_ID][$propValue.'_'.$subkey] = 'N';
															}
														}
													}
												}
											}

										}
									}
								}
							}
						}
					}

					if(isset($_REQUEST["backurl"]) && $_REQUEST["backurl"])
						LocalRedirect($_REQUEST["backurl"]);
				}
				if(isset($_REQUEST["BASE_COLOR"]) && $_REQUEST["BASE_COLOR"])
					LocalRedirect($_SERVER["HTTP_REFERER"]);
			}
		}
		else
		{
			unset($_SESSION['THEME'][SITE_ID]);
			if(isset($_SESSION['THEME_ACTION'][SITE_ID]))
				unset($_SESSION['THEME_ACTION'][SITE_ID]);
		}
	}

	public static function Start($siteID){
		global  $APPLICATION, $STARTTIME, $arRegion;
		$STARTTIME = time() * 1000;

		if(isset($_REQUEST['color_theme']) && $_REQUEST['color_theme'])
			LocalRedirect($_SERVER['HTTP_REFERER']);

		if(CModule::IncludeModuleEx(self::moduleID) == 1)
		{
			if(!defined('_USE_AJAX_HANDLER_NEXT_'))
				define('_USE_AJAX_HANDLER_NEXT_', true); // global flag for onAfterAjaxResponseHandler

			$bIndexBot = self::checkIndexBot(); // is indexed yandex/google bot

			if(!$bIndexBot)
			{
				self::UpdateFrontParametrsValues(); //update theme values

				self::GenerateThemes($siteID); //generate theme.css and bgtheme.css
			}

			$arTheme = self::GetFrontParametrsValues($siteID); //get site options

			if($arTheme['USE_REGIONALITY'] == 'Y')
				$arRegion = CNextRegionality::getCurrentRegion(); //get current region from regionality module

			if(!$arTheme['FONT_STYLE'] || !self::$arParametrsList['MAIN']['OPTIONS']['FONT_STYLE']['LIST'][$arTheme['FONT_STYLE']])
				$font_family = 'Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,500,600,700,800&subset=latin,cyrillic-ext';
			else
				$font_family = self::$arParametrsList['MAIN']['OPTIONS']['FONT_STYLE']['LIST'][$arTheme['FONT_STYLE']]['LINK'];

			if(!$bIndexBot)
			{
				if(!$arTheme['CUSTOM_FONT'])
					$APPLICATION->SetAdditionalCSS((CMain::IsHTTPS() ? 'https' : 'http').'://fonts.googleapis.com/css?family='.$font_family);
				else
					$APPLICATION->AddHeadString('<'.$arTheme['CUSTOM_FONT'].'>');
			}

			$APPLICATION->SetPageProperty('viewport', 'initial-scale=1.0, width=device-width');
			$APPLICATION->SetPageProperty('HandheldFriendly', 'true');
			$APPLICATION->SetPageProperty('apple-mobile-web-app-capable', 'yes');
			$APPLICATION->SetPageProperty('apple-mobile-web-app-status-bar-style', 'black');
			$APPLICATION->SetPageProperty('SKYPE_TOOLBAR', 'SKYPE_TOOLBAR_PARSER_COMPATIBLE');

			\Aspro\Next\PWA::showMeta(SITE_ID);

			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/vendor/css/bootstrap.css');

			if(!$bIndexBot)
				$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/jquery.fancybox.css');

			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/styles.css');

			if(!$bIndexBot)
			{
				if($arTheme['DETAIL_PICTURE_MODE'] == 'MAGNIFIER')
					$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/xzoom.css');
			}

			$APPLICATION->SetAdditionalCSS(((Option::get('main', 'use_minified_assets', 'N', $siteID) === 'Y') && file_exists($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/css/media.css')) ? SITE_TEMPLATE_PATH.'/css/media.css' : SITE_TEMPLATE_PATH.'/css/media.css', true);

			if(!$bIndexBot)
			{
				$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/vendor/fonts/font-awesome/css/font-awesome.min.css', true);
				$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/print.css', true);
				$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/animation/animation_ext.css');
				$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/vendor/css/footable.standalone.min.css');
				$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/blocks/blocks.css');
			}

			if($arTheme['H1_STYLE'] == '2') // 2 - Normal
				$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/h1-normal.css');
			else // 1 - Bold
				$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/h1-bold.css');

			if(!$bIndexBot)
			{
				if(
					$arTheme['NLO_MENU'] === 'Y' ||
					(isset($_REQUEST['observer']) && $_REQUEST['observer'])
				){
					$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/observer.js');
				}

				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.actual.min.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/vendor/js/jquery.bxslider.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jqModal.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/vendor/js/bootstrap.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/vendor/js/jquery.appear.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/browser.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.fancybox.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.flexslider.js');

				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/vendor/js/moment.min.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/vendor/js/footable.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/vendor/js/jquery.menu-aim.js');

				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/vendor/js/velocity/velocity.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/vendor/js/velocity/velocity.ui.js');

				if($arTheme['USE_LAZY_LOAD'] === 'Y' && !self::checkMask(Option::get('aspro.next', 'LAZY_LOAD_EXCEPTIONS', ''))){
					$APPLICATION->AddHeadString('<script>window.lazySizesConfig = window.lazySizesConfig || {};lazySizesConfig.loadMode = 2;lazySizesConfig.expand = 100;lazySizesConfig.expFactor = 1;lazySizesConfig.hFac = 0.1;</script>');
					$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/vendor/js/lazysizes.min.js');
				}

				if((!isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest')) && !isset($_REQUEST['ajax']))
					$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.validate.min.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.inputmask.bundle.min.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.uniform.min.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.easing.1.3.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/equalize.min.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.alphanumeric.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.cookie.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.plugin.min.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.countdown.min.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.countdown-ru.js');
				
				$BLOCK_IKSELECT = $APPLICATION->GetProperty("BLOCK_IKSELECT");

				if ($BLOCK_IKSELECT != "Y") {
					$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.ikSelect.js');
				}

				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.mobile.custom.touch.min.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.dotdotdot.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/rating_likes.js');
				$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/blocks/blocks.js');

				// neeed for rating composite fix
				if($arTheme['REVIEWS_VIEW'] === 'STANDART'){
					IncludeAJAX();
				}

				if($arTheme['DETAIL_PICTURE_MODE'] == 'MAGNIFIER')
					$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/xzoom.js');
				if($arTheme['VIEWED_TYPE'] == 'LOCAL')
					CJSCore::Init(array('ls'));

				if((!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest')) && (!isset($_REQUEST['ajax']) || (isset($_REQUEST['ajax']) && strtolower($_REQUEST['ajax']) != 'y')))
				{
					$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/video_banner.js');
					$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/main.js');
					$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/custom.js', true);
				}
			}

			if($arTheme['FAVICON_IMAGE'] && strlen($arTheme['FAVICON_IMAGE'])){
				$file_ext = pathinfo($arTheme['FAVICON_IMAGE'], PATHINFO_EXTENSION);
				$fav_ext = $file_ext ? $file_ext : 'ico';
				$fav_type = '';

				switch ($fav_ext) {
					case 'ico': 
						$fav_type = 'image/x-icon';
						break;
					case 'svg': 
						$fav_type = 'image/svg+xml';
						break;
					case 'png': 
						$fav_type = 'image/png';
						break;
					case 'jpg': 
						$fav_type = 'image/jpeg';
						break;
					case 'gif': 
						$fav_type = 'image/gif';
						break;
					case 'bmp': 
						$fav_type = 'image/bmp';
						break;
				}

				$APPLICATION->AddHeadString('<link rel="shortcut icon" href="'.$arTheme['FAVICON_IMAGE'].'" type="'.$fav_type.'" />', true);
			}

			if($arTheme['APPLE_TOUCH_ICON_IMAGE'] && strlen($arTheme['APPLE_TOUCH_ICON_IMAGE']))
				$APPLICATION->AddHeadString('<link rel="apple-touch-icon" sizes="180x180" href="'.$arTheme['APPLE_TOUCH_ICON_IMAGE'].'" />', true);

			CJSCore::Init(array('jquery2'));

			if(!$bIndexBot)
			{
				CAjax::Init();

				if(self::IsBasketPage())
				{
					CJSCore::Init(array('fx'));?>

					<?//clear basket session counter in basket page?>
					<?if($_COOKIE['click_basket'] && $_COOKIE['click_basket'] == 'Y'):?>
						<?CNext::clearBasketCounters();?>
						<?unset($_COOKIE['click_basket'])?>
						<script>
							$.removeCookie('click_basket', {path: '/'});
						</script>
					<?endif;?>

				<?}

				\Bitrix\Main\Loader::includeModule('iblock');
				\Bitrix\Main\Loader::includeModule('sale');
				\Bitrix\Main\Loader::includeModule('catalog');
			}

			return true;
		}
		else
		{
			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/styles.css');
			$APPLICATION->SetTitle(GetMessage('ERROR_INCLUDE_MODULE'));
			$APPLICATION->IncludeFile(SITE_DIR.'include/error_include_module.php', Array(), Array()); die();
		}
	}

	public static function checkBgImage($siteID){
		global $APPLICATION, $arRegion, $arTheme;
		static $arBanner;
		if($arBanner === NULL)
		{
			$arFilterBanner = array('IBLOCK_ID' => CNextCache::$arIBlocks[$siteID]['aspro_next_adv']['aspro_next_bg_images'][0], 'ACTIVE'=>'Y');

			if($arRegion && isset($arTheme['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_FILTER_ITEM']) && $arTheme['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_FILTER_ITEM']['VALUE'] == 'Y')
				$arFilterBanner['PROPERTY_LINK_REGION'] = $arRegion['ID'];

			$arItems = CNextCache::CIBLockElement_GetList(array('SORT' => 'ASC', 'CACHE' => array('TAG' => CNextCache::GetIBlockCacheTag(CNextCache::$arIBlocks[$siteID]['aspro_next_adv']['aspro_next_bg_images'][0]))), $arFilterBanner, false, false, array('ID', 'NAME', 'PREVIEW_PICTURE', 'PROPERTY_URL', 'PROPERTY_FIXED_BANNER', 'PROPERTY_URL_NOT_SHOW'));
			$arBanner = array();
			if($arItems)
			{
				$curPage = $APPLICATION->GetCurPage();
				foreach($arItems as $arItem)
				{
					if(isset($arItem['PROPERTY_URL_VALUE']) && $arItem['PREVIEW_PICTURE'])
					{
						if(!is_array($arItem['PROPERTY_URL_VALUE']))
							$arItem['PROPERTY_URL_VALUE'] = array($arItem['PROPERTY_URL_VALUE']);
						if($arItem['PROPERTY_URL_VALUE'])
						{
							foreach($arItem['PROPERTY_URL_VALUE'] as $url)
							{
								$url=str_replace('SITE_DIR', SITE_DIR, $url);
								if($arItem['PROPERTY_URL_NOT_SHOW_VALUE'])
								{
									if(!is_array($arItem['PROPERTY_URL_NOT_SHOW_VALUE']))
										$arItem['PROPERTY_URL_NOT_SHOW_VALUE'] = array($arItem['PROPERTY_URL_NOT_SHOW_VALUE']);
									foreach($arItem['PROPERTY_URL_NOT_SHOW_VALUE'] as $url_not_show)
									{
										$url_not_show=str_replace('SITE_DIR', SITE_DIR, $url_not_show);
										if(CSite::InDir($url_not_show))
											break 2;
									}
									foreach($arItem['PROPERTY_URL_NOT_SHOW_VALUE'] as $url_not_show)
									{
										$url_not_show = str_replace('SITE_DIR', SITE_DIR, $url_not_show);
										if(CSite::InDir($url_not_show))
										{
											// continue;
											break 2;
										}
										else
										{
											if(CSite::InDir($url))
											{
												$arBanner = $arItem;
												break;
											}
										}
									}
								}
								else
								{
									if(CSite::InDir($url))
									{
										$arBanner = $arItem;
										break;
									}
								}
							}
						}
					}
				}
			}
		}
		return $arBanner;
	}

	public static function isChildsSelected($arChilds){
		if($arChilds && is_array($arChilds)){
			foreach($arChilds as $arChild){
				if($arChild['SELECTED']){
					return $arChild;
				}
			}
		}
		return false;
	}

	public static function showBgImage($siteID, $arTheme){
		global $APPLICATION;
		if($arTheme['SHOW_BG_BLOCK']['VALUE'] == 'Y')
		{
			$arBanner = self::checkBgImage($siteID);
			if($arBanner)
			{
				$image = CFile::GetFileArray($arBanner['PREVIEW_PICTURE']);
				$class = 'bg_image_site opacity1';
				if($arBanner['PROPERTY_FIXED_BANNER_VALUE'] == 'Y')
					$class .= ' fixed';
				if(self::IsMainPage())
					$class .= ' opacity';
				echo '<span class=\''.$class.'\' style=\'background-image:url('.$image["SRC"].');\'></span>';
			}
		}
		return true;
	}

	/*static function GetBannerStyle($bannerwidth, $topmenu){
        $style = '';

        if($bannerwidth == 'WIDE'){
            $style = '.maxwidth-banner{max-width: 1550px;}';
        }
        elseif($bannerwidth == 'MIDDLE'){
            $style = '.maxwidth-banner{max-width: 1450px;}';
        }
        elseif($bannerwidth == 'NARROW'){
            $style = '.maxwidth-banner{max-width: 1343px; padding: 0 16px;}';
			if($topmenu !== 'LIGHT'){
				$style .= '.banners-big{margin-top:20px;}';
			}
        }
        else{
            $style = '.maxwidth-banner{max-width: auto;}';
        }

        return '<style>'.$style.'</style>';
    }*/

    public static function GetIndexPageBlocks($pageAbsPath, $pageBlocksPrefix, $pageBlocksDirName = 'page_blocks'){
    	$arResult = array();

    	if($pageAbsPath && $pageBlocksPrefix){
    		$pageAbsPath = str_replace('//', '//', $pageAbsPath).'/';
    		if(is_dir($pageBlocksAbsPath = str_replace('', '', $pageAbsPath.(strlen($pageBlocksDirName) ? $pageBlocksDirName : '')))){
    			if($arPageBlocks = glob($pageBlocksAbsPath.'/*.php')){
		    		foreach($arPageBlocks as $file){
						$file = str_replace('.php', '', basename($file));
						if(strpos($file, $pageBlocksPrefix) !== false){
							$arResult[$file] = $file;
						}
					}
    			}
    		}
    	}

    	return $arResult;
    }

    public static function GetComponentTemplatePageBlocks($templateAbsPath, $pageBlocksDirName = 'page_blocks'){
    	$arResult = array('SECTIONS' => array(), 'SUBSECTIONS' => array(), 'ELEMENTS' => array(), 'ELEMENT' => array(), 'LANDING' => array());

    	if($templateAbsPath){
    		$templateAbsPath = str_replace('//', '//', $templateAbsPath).'/';
    		if(is_dir($pageBlocksAbsPath = str_replace('//', '/', $templateAbsPath.(strlen($pageBlocksDirName) ? $pageBlocksDirName : '')))){
    			if($arPageBlocks = glob($pageBlocksAbsPath.'/*.php')){
		    		foreach($arPageBlocks as $file){
						$file = str_replace('.php', '', basename($file));
						if(strpos($file, 'sections_') !== false){
							$arResult['SECTIONS'][$file] = $file;
						}
						elseif(strpos($file, 'section_') !== false){
							$arResult['SUBSECTIONS'][$file] = $file;
						}
						elseif(strpos($file, 'list_elements_') !== false){
							$arResult['ELEMENTS'][$file] = $file;
						}
						elseif(strpos($file, 'element_') !== false){
							$arResult['ELEMENT'][$file] = $file;
						}
						elseif(strpos($file, 'fast_view_') !== false){
							$arResult['FAST_VIEW_ELEMENT'][$file] = $file;
						}
						elseif(strpos($file, 'bigdata_') !== false){
							$arResult['BIGDATA'][$file] = $file;
						}
						elseif(strpos($file, 'landing_') !== false){
							$arResult['LANDING'][$file] = $file;
						}
					}
    			}
    		}
    	}

    	return $arResult;
    }

    public static function GetComponentTemplatePageBlocksParams($arPageBlocks){
    	$arResult = array();

    	if($arPageBlocks && is_array($arPageBlocks)){
    		if(isset($arPageBlocks['SECTIONS']) && $arPageBlocks['SECTIONS'] && is_array($arPageBlocks['SECTIONS'])){
    			$arResult['SECTIONS_TYPE_VIEW'] = array(
					'PARENT' => 'BASE',
					'SORT' => 1,
					'NAME' => GetMessage('M_SECTIONS_TYPE_VIEW'),
					'TYPE' => 'LIST',
					'VALUES' => $arPageBlocks['SECTIONS'],
					'DEFAULT' => key($arPageBlocks['SECTIONS']),
				);
    		}
    		if(isset($arPageBlocks['SUBSECTIONS']) && $arPageBlocks['SUBSECTIONS'] && is_array($arPageBlocks['SUBSECTIONS'])){
    			$arResult['SECTION_TYPE_VIEW'] = array(
					'PARENT' => 'BASE',
					'SORT' => 1,
					'NAME' => GetMessage('M_SECTION_TYPE_VIEW'),
					'TYPE' => 'LIST',
					'VALUES' => $arPageBlocks['SUBSECTIONS'],
					'DEFAULT' => key($arPageBlocks['SUBSECTIONS']),
				);
    		}
    		if(isset($arPageBlocks['ELEMENTS']) && $arPageBlocks['ELEMENTS'] && is_array($arPageBlocks['ELEMENTS'])){
    			$arResult['SECTION_ELEMENTS_TYPE_VIEW'] = array(
					'PARENT' => 'BASE',
					'SORT' => 1,
					'NAME' => GetMessage('M_SECTION_ELEMENTS_TYPE_VIEW'),
					'TYPE' => 'LIST',
					'VALUES' => $arPageBlocks['ELEMENTS'],
					'DEFAULT' => key($arPageBlocks['ELEMENTS']),
				);
    		}
    		if(isset($arPageBlocks['ELEMENT']) && $arPageBlocks['ELEMENT'] && is_array($arPageBlocks['ELEMENT'])){
    			$arResult['ELEMENT_TYPE_VIEW'] = array(
					'PARENT' => 'BASE',
					'SORT' => 1,
					'NAME' => GetMessage('M_ELEMENT_TYPE_VIEW'),
					'TYPE' => 'LIST',
					'VALUES' => $arPageBlocks['ELEMENT'],
					'DEFAULT' => key($arPageBlocks['ELEMENT']),
				);
    		}
		if(isset($arPageBlocks['LANDING']) && $arPageBlocks['LANDING'] && is_array($arPageBlocks['LANDING'])){
    			$arResult['LANDING_TYPE_VIEW'] = array(
					'PARENT' => 'BASE',
					'SORT' => 1,
					'NAME' => GetMessage('M_LANDING_TYPE_VIEW'),
					'TYPE' => 'LIST',
					'VALUES' => $arPageBlocks['LANDING'],
					'DEFAULT' => key($arPageBlocks['LANDING']),
				);
    		}
    	}

    	return $arResult;
    }

    protected static function IsComponentTemplateHasModuleElementsPageBlocksParam($templateName, $arExtParams = array()){
    	$section_param = ((isset($arExtParams['SECTION']) && $arExtParams['SECTION']) ? $arExtParams['SECTION'] : 'SECTION');
    	$template_param = ((isset($arExtParams['OPTION']) && $arExtParams['OPTION']) ? $arExtParams['OPTION'] : strtoupper($templateName));
	    return $templateName && isset(self::$arParametrsList[$section_param]['OPTIONS'][$template_param.'_PAGE']);
    }

    protected static function IsComponentTemplateHasModuleElementPageBlocksParam($templateName, $arExtParams = array()){
    	$section_param = ((isset($arExtParams['SECTION']) && $arExtParams['SECTION']) ? $arExtParams['SECTION'] : 'SECTION');
    	$template_param = ((isset($arExtParams['OPTION']) && $arExtParams['OPTION']) ? $arExtParams['OPTION'] : strtoupper($templateName));
	    return $templateName && isset(self::$arParametrsList[$section_param]['OPTIONS'][$template_param.'_PAGE_DETAIL']);
    }

    public static function AddComponentTemplateModulePageBlocksParams($templateAbsPath, &$arParams, $arExtParams = array()){
    	if($templateAbsPath && $arParams && is_array($arParams)){
    		$templateAbsPath = str_replace('//', '//', $templateAbsPath).'/';
    		$templateName = basename($templateAbsPath);
    		if(self::IsComponentTemplateHasModuleElementsPageBlocksParam($templateName, $arExtParams)){
    			$arParams['SECTION_ELEMENTS_TYPE_VIEW']['VALUES'] = array_merge(array('FROM_MODULE' => GetMessage('M_FROM_MODULE_PARAMS')), $arParams['SECTION_ELEMENTS_TYPE_VIEW']['VALUES']);
    			$arParams['SECTION_ELEMENTS_TYPE_VIEW']['DEFAULT'] = 'FROM_MODULE';
    		}
    		if(self::IsComponentTemplateHasModuleElementPageBlocksParam($templateName, $arExtParams)){
    			$arParams['ELEMENT_TYPE_VIEW']['VALUES'] = array_merge(array('FROM_MODULE' => GetMessage('M_FROM_MODULE_PARAMS')), $arParams['ELEMENT_TYPE_VIEW']['VALUES']);
    			$arParams['ELEMENT_TYPE_VIEW']['DEFAULT'] = 'FROM_MODULE';
    		}
    	}
    }

    public static function CheckComponentTemplatePageBlocksParams(&$arParams, $templateAbsPath, $pageBlocksDirName = 'page_blocks'){
    	$arPageBlocks = self::GetComponentTemplatePageBlocks($templateAbsPath, $pageBlocksDirName);

    	if(!isset($arParams['SECTIONS_TYPE_VIEW']) || !$arParams['SECTIONS_TYPE_VIEW'] || (!isset($arPageBlocks['SECTIONS'][$arParams['SECTIONS_TYPE_VIEW']]) && $arParams['SECTIONS_TYPE_VIEW'] !== 'FROM_MODULE')){
    		$arParams['SECTIONS_TYPE_VIEW'] = key($arPageBlocks['SECTIONS']);
    	}
    	if(!isset($arParams['SECTION_TYPE_VIEW']) || !$arParams['SECTION_TYPE_VIEW'] || (!isset($arPageBlocks['SUBSECTIONS'][$arParams['SECTION_TYPE_VIEW']]) && $arParams['SECTION_TYPE_VIEW'] !== 'FROM_MODULE')){
    		$arParams['SECTION_TYPE_VIEW'] = key($arPageBlocks['SUBSECTIONS']);
    	}
    	if(!isset($arParams['SECTION_ELEMENTS_TYPE_VIEW']) || !$arParams['SECTION_ELEMENTS_TYPE_VIEW'] || (!isset($arPageBlocks['ELEMENTS'][$arParams['SECTION_ELEMENTS_TYPE_VIEW']]) && $arParams['SECTION_ELEMENTS_TYPE_VIEW'] !== 'FROM_MODULE')){
    		$arParams['SECTION_ELEMENTS_TYPE_VIEW'] = key($arPageBlocks['ELEMENTS']);
    	}
    	if(!isset($arParams['ELEMENT_TYPE_VIEW']) || !$arParams['ELEMENT_TYPE_VIEW'] || (!isset($arPageBlocks['ELEMENT'][$arParams['ELEMENT_TYPE_VIEW']]) && $arParams['ELEMENT_TYPE_VIEW'] !== 'FROM_MODULE')){
    		$arParams['ELEMENT_TYPE_VIEW'] = key($arPageBlocks['ELEMENT']);
    	}
    }

    public static function Add2OptionCustomComponentTemplatePageBlocks(&$arOption, $templateAbsPath){
		if($arOption && isset($arOption['LIST'])){
			if($arPageBlocks = self::GetComponentTemplatePageBlocks($templateAbsPath)){
				foreach($arPageBlocks['ELEMENTS'] as $page => $value){
					if(!isset($arOption['LIST'][$page])){
						$arOption['LIST'][$page] = array(
							'TITLE' => $value,
							'HIDE' => 'Y',
							'IS_CUSTOM' => 'Y',
						);
					}
				}
				if(!$arOption['DEFAULT'] && $arOption['LIST']){
					$arOption['DEFAULT'] = key($arOption['LIST']);
				}
			}
		}
    }

    public static function Add2OptionCustomComponentTemplatePageBlocksElement(&$arOption, $templateAbsPath, $field = 'ELEMENT'){
		if($arOption && isset($arOption['LIST'])){
			if($arPageBlocks = self::GetComponentTemplatePageBlocks($templateAbsPath)){
				foreach($arPageBlocks[$field] as $page => $value){
					if(!isset($arOption['LIST'][$page])){
						$arOption['LIST'][$page] = array(
							'TITLE' => $value,
							'HIDE' => 'Y',
							'IS_CUSTOM' => 'Y',
						);
					}
				}
				if(!$arOption['DEFAULT'] && $arOption['LIST']){
					$arOption['DEFAULT'] = key($arOption['LIST']);
				}
			}
		}
    }

    public static function GetCurrentElementFilter(&$arVariables, &$arParams){
        $arFilter = array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'INCLUDE_SUBSECTIONS' => 'Y');
        if($arParams['CHECK_DATES'] == 'Y'){
            $arFilter = array_merge($arFilter, array('ACTIVE' => 'Y', 'SECTION_GLOBAL_ACTIVE' => 'Y', 'ACTIVE_DATE' => 'Y'));
        }
        if($arVariables['ELEMENT_ID']){
            $arFilter['ID'] = $arVariables['ELEMENT_ID'];
        }
        elseif($arVariables['ELEMENT_CODE'] && strlen($arVariables['ELEMENT_CODE'])){
            $arFilter['CODE'] = $arVariables['ELEMENT_CODE'];
        }
		if($arVariables['SECTION_ID']){
			$arFilter['SECTION_ID'] = ($arVariables['SECTION_ID'] ? $arVariables['SECTION_ID'] : false);
		}
		if($arVariables['SECTION_CODE']){
			$arFilter['SECTION_CODE'] = ($arVariables['SECTION_CODE'] ? $arVariables['SECTION_CODE'] : false);
		}
        if(!$arFilter['SECTION_ID'] && !$arFilter['SECTION_CODE']){
            unset($arFilter['SECTION_GLOBAL_ACTIVE']);
        }
        if($arParams['FILTER_NAME'] && strlen($arParams['FILTER_NAME'])){
        	if($GLOBALS[$arParams['FILTER_NAME']]){
				$arFilter = array_merge($arFilter, $GLOBALS[$arParams['FILTER_NAME']]);
			}
        }
        return $arFilter;
    }

	public static function GetCurrentSectionFilter(&$arVariables, &$arParams){
		$arFilter = array('IBLOCK_ID' => $arParams['IBLOCK_ID']);
		if($arParams['CHECK_DATES'] == 'Y'){
			$arFilter = array_merge($arFilter, array('ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y', 'ACTIVE_DATE' => 'Y'));
		}
		if($arVariables['SECTION_ID']){
			$arFilter['ID'] = $arVariables['SECTION_ID'];
		}
		if($arVariables['SECTION_CODE'] && strlen($arVariables['SECTION_CODE'])){
			$arFilter['CODE'] = $arVariables['SECTION_CODE'];
		}
		if(!$arVariables['SECTION_ID'] && !strlen($arFilter['CODE'])){
			$arFilter['ID'] = 0; // if section not found
		}
		return $arFilter;
	}

	public static function GetCurrentSectionElementFilter(&$arVariables, &$arParams, $CurrentSectionID = false){
		$arFilter = array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'INCLUDE_SUBSECTIONS' => 'N');
		if($arParams['CHECK_DATES'] == 'Y'){
			$arFilter = array_merge($arFilter, array('ACTIVE' => 'Y', 'SECTION_GLOBAL_ACTIVE' => 'Y', 'ACTIVE_DATE' => 'Y'));
		}
		if(!$arFilter['SECTION_ID'] = ($CurrentSectionID !== false ? $CurrentSectionID : ($arVariables['SECTION_ID'] ? $arVariables['SECTION_ID'] : false))){
			unset($arFilter['SECTION_GLOBAL_ACTIVE']);
		}
		if($arParams['FILTER_NAME'] && strlen($arParams['FILTER_NAME'])){
			$GLOBALS[$arParams['FILTER_NAME']] = (array)$GLOBALS[$arParams['FILTER_NAME']];
			foreach($arUnsetFilterFields = array('SECTION_ID', 'SECTION_CODE', 'SECTION_ACTIVE', 'SECTION_GLOBAL_ACTIVE') as $filterUnsetField){
				foreach($GLOBALS[$arParams['FILTER_NAME']] as $filterField => $filterValue){
					if(($p = strpos($filterUnsetField, $filterField)) !== false && $p < 2){
						unset($GLOBALS[$arParams['FILTER_NAME']][$filterField]);
					}
				}
			}
			if($GLOBALS[$arParams['FILTER_NAME']]){
				$arFilter = array_merge($arFilter, $GLOBALS[$arParams['FILTER_NAME']]);
			}
		}
		return $arFilter;
	}

	public static function ShowRSSIcon($href){?>
		<script type="text/javascript">
		$(document).ready(function () {
			$('h1').before('<a class="rss" href="<?=$href?>" title="rss" target="_blank">RSS</a>');
		});
		</script>
		<?
		$GLOBALS['APPLICATION']->AddHeadString('<link rel="alternate" type="application/rss+xml" title="rss" href="'.$href.'" />');
	}

	public static function ShowLogo(){
		global $arSite;
		$arTheme = self::GetFrontParametrsValues(SITE_ID);
		$text = '<a href="'.SITE_DIR.'">';
		if($arImg = unserialize(Option::get(self::moduleID, "LOGO_IMAGE", serialize(array()))))
			$text .= '<img src="'.CFile::GetPath($arImg[0]).'" alt="'.$arSite["SITE_NAME"].'" title="'.$arSite["SITE_NAME"].'" data-src="" />';
		elseif(self::checkContentFile(SITE_DIR.'/include/logo_svg.php'))
			$text .= File::getFileContents($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'/include/logo_svg.php');
		else
			$text .= '<img src="'.$arTheme["LOGO_IMAGE"].'" alt="'.$arSite["SITE_NAME"].'" title="'.$arSite["SITE_NAME"].'" data-src="" />';
		$text .= '</a>';

		return $text;
	}

	public static function ShowLogoFixed(){
		global $arSite;
		$arTheme = self::GetFrontParametrsValues(SITE_ID);
		$text = '<a href="'.SITE_DIR.'">';
		if($arImg = unserialize(Option::get(self::moduleID, "LOGO_IMAGE_FIXED", serialize(array()))))
			$text .= '<img src="'.CFile::GetPath($arImg[0]).'" alt="'.$arSite["SITE_NAME"].'" title="'.$arSite["SITE_NAME"].'" data-src="" />';
		elseif($arImg = unserialize(Option::get(self::moduleID, "LOGO_IMAGE", serialize(array()))))
			$text .= '<img src="'.CFile::GetPath($arImg[0]).'" alt="'.$arSite["SITE_NAME"].'" title="'.$arSite["SITE_NAME"].'" data-src="" />';
		elseif(self::checkContentFile(SITE_DIR.'/include/logo_svg.php'))
			$text .= File::getFileContents($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'/include/logo_svg.php');
		else
			$text .= '<img src="'.$arTheme["LOGO_IMAGE"].'" alt="'.$arSite["SITE_NAME"].'" title="'.$arSite["SITE_NAME"].'" data-src="" />';
		$text .= '</a>';

		return $text;
	}

	public static function showIconSvg($class = 'phone', $path, $title = '', $class_icon = '', $show_wrapper = true, $bInline = true){
		$text ='';
		if(self::checkContentFile($path))
		{
			static $svg_call;
			$iSvgID = ++$svg_call;
			if($show_wrapper)
				$text = '<i class="svg '.($bInline ? 'inline' : '').' '.$class_icon.' svg-inline-'.$class.'" aria-hidden="true" '.($title ? 'title="'.$title.'"' : '').'>';

				$text .= str_replace('markID', $iSvgID, File::getFileContents($_SERVER['DOCUMENT_ROOT'].$path));
			if($show_wrapper)
				$text .= '</i>';
		}
		return $text;
	}

	public static function checkContentFile($path){
		if(File::isFileExists($_SERVER['DOCUMENT_ROOT'].$path))
			$content = File::getFileContents($_SERVER['DOCUMENT_ROOT'].$path);
		return (!empty($content));
	}

	public static function ShowPageProps($prop){
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		$APPLICATION->AddBufferContent(array("CNext", "GetPageProps"), $prop);
	}

	public static function GetPageProps($prop){
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		if($prop == 'ERROR_404')
		{
			return (defined($prop) ? 'with_error' : '');
		}
		else
		{
			$val = $APPLICATION->GetProperty($prop);
			if(!empty($val))
				return $val;
		}
		return '';
	}

	public static function getCurrentThemeClasses(){
		global $arTheme, $bShowHeaderSimple, $APPLICATION;
		$basket_class = ($arTheme['ORDER_BASKET_VIEW']['VALUE'] == 'FLY2' ? 'fly fly2' : strToLower($arTheme['ORDER_BASKET_VIEW']['VALUE']));
		$delayClass = ($arTheme['SHOW_DELAY_BUTTON']['VALUE'] == 'N' ? ' without-delay ' : '');

		return 'basket_'.$basket_class.' basket_fill_'.$arTheme['ORDER_BASKET_COLOR']['VALUE'].$delayClass.' side_'.$arTheme['SIDE_MENU']['VALUE'].' catalog_icons_'.$arTheme['LEFT_BLOCK_CATALOG_ICONS']['VALUE'].' banner_auto '.($arTheme['USE_FAST_VIEW_PAGE_DETAIL']['VALUE'] != 'NO' ? 'with_fast_view' : '').' mheader-v'.$arTheme['HEADER_MOBILE']['VALUE'].' header-v'.$arTheme['HEADER_TYPE']['VALUE'].' regions_'.$arTheme['USE_REGIONALITY']['VALUE'].' fill_'.$arTheme['SHOW_BG_BLOCK']['VALUE'].' footer-v'.$arTheme['FOOTER_TYPE']['VALUE'].' front-v'.$arTheme['INDEX_TYPE']['VALUE'].' mfixed_'.$arTheme['HEADER_MOBILE_FIXED']['VALUE'].' mfixed_view_'.strtolower($arTheme['HEADER_MOBILE_FIXED']['DEPENDENT_PARAMS']['HEADER_MOBILE_SHOW']['VALUE']).' title-v'.$arTheme['PAGE_TITLE']['VALUE'].((int)($arTheme['HEADER_PHONES']) > 0 ? ' with_phones' : '').($arTheme['MOBILE_CATALOG_LIST_ELEMENTS_COMPACT']['VALUE'] === 'Y' ? ' ce_cmp' : '').($arTheme['USE_LAZY_LOAD']['VALUE'] === 'Y' ? ' with_lazy' : '').' store_'.$arTheme['STORE_AMOUNT_VIEW']['VALUE'].($bShowHeaderSimple ? ' simple_basket_mode' : '');
	}

	public static function getCurrentPageClass(){
		static $result;
		global $arTheme;

		if(!isset($result))
		{
			if(self::IsMainPage())
				$result = 'front';
			if(self::IsAuthSection())
				$result = 'auth';
			if(self::IsBasketPage())
				$result = 'basket';
			if(self::IsCatalogPage())
				$result = 'catalog';
			if(self::IsPersonalPage())
				$result = 'personal';
			if(self::IsOrderPage())
				$result = 'order';
			if(self::IsFormPage())
				$result = 'form';
			if($result)
				$result.='_page';
		}
		return $result;
	}

	public static function IsMainPage(){
		static $result;

		if(!isset($result))
			$result = CSite::InDir(SITE_DIR.'index.php');

		return $result;
	}

	public static function IsAuthSection(){
		static $result;

		if(!isset($result))
			$result = CSite::InDir(SITE_DIR.'auth/');

		return $result;
	}

	public static function IsBasketPage($page = ''){
		static $result;

		if(!isset($result))
		{
			if(!$page)
			{
				$arOptions = self::GetBackParametrsValues(SITE_ID);
				if(!$arOptions['BASKET_PAGE_URL'] || !strlen($arOptions['BASKET_PAGE_URL']))
					$arOptions['BASKET_PAGE_URL'] = SITE_DIR.'basket/';
				$page = $arOptions['BASKET_PAGE_URL'];
			}
			$result = CSite::InDir($page);
		}

		return $result;
	}

	public static function IsCatalogPage($page = ''){
		static $result;

		if(!isset($result))
		{
			if(!$page)
			{
				$arOptions = self::GetBackParametrsValues(SITE_ID);
				if(!$arOptions['CATALOG_PAGE_URL'] && !strlen($arOptions['CATALOG_PAGE_URL']))
					$arOptions['CATALOG_PAGE_URL'] = SITE_DIR.'catalog/';
				$page = $arOptions['CATALOG_PAGE_URL'];
			}
			$result = CSite::InDir($page);
		}

		return $result;
	}

	public static function IsOrderPage($page = ''){
		static $result;

		if(!isset($result))
		{
			if(!$page)
			{
				$arOptions = self::GetBackParametrsValues(SITE_ID);
				if(!$arOptions['ORDER_PAGE_URL'] || !strlen($arOptions['ORDER_PAGE_URL']))
					$arOptions['ORDER_PAGE_URL'] = SITE_DIR.'order/';
				$page = $arOptions['ORDER_PAGE_URL'];
			}
			$result = CSite::InDir($page);
		}

		return $result;
	}

	public static function IsPersonalPage($page = ''){
		static $result;

		if(!isset($result))
		{
			if(!$page)
			{
				$arOptions = self::GetBackParametrsValues(SITE_ID);
				if(!$arOptions['PERSONAL_PAGE_URL'] || !strlen($arOptions['PERSONAL_PAGE_URL']))
					$arOptions['PERSONAL_PAGE_URL'] = SITE_DIR.'personal/';
				$page = $arOptions['PERSONAL_PAGE_URL'];
			}
			$result = CSite::InDir($page);
		}

		return $result;
	}

	public static function IsFormPage(){
		static $result;

		if(!isset($result))
			$result = CSite::InDir(SITE_DIR.'form/');

		return $result;
	}

	public static function GenerateMinCss($file){
		if(file_exists($file))
		{
			$content = @file_get_contents($file);
			if($content !== false)
			{
				$content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
				$content = str_replace(array("\r\n", "\r", "\n", "\t"), '', $content);
				$content = preg_replace('/ {2,}/', ' ', $content);
				$content = str_replace(array(' : ', ': ', ' :',), ':', $content);
				$content = str_replace(array(' ; ', '; ', ' ;'), ';', $content);
				$content = str_replace(array(' > ', '> ', ' >'), '>', $content);
				$content = str_replace(array(' + ', '+ ', ' +'), '+', $content);
				$content = str_replace(array(' { ', '{ ', ' {'), '{', $content);
				$content = str_replace(array(' } ', '} ', ' }'), '}', $content);
				$content = str_replace(array(' ( ', '( ', ' ('), '(', $content);
				$content = str_replace(array(' ) ', ') ', ' )'), ')', $content);
				$content = str_replace('and(', 'and (', $content);
				$content = str_replace(')li', ') li', $content);
				$content = str_replace(').', ') .', $content);
				@file_put_contents(dirname($file).'/'.basename($file, '.css').'.min.css', $content);
			}
		}
		return false;
	}

	public static function GenerateThemes($siteID){
		$arBackParametrs = self::GetBackParametrsValues(SITE_ID);
		$arBaseColors = self::$arParametrsList['MAIN']['OPTIONS']['BASE_COLOR']['LIST'];
		$arBaseBgColors = self::$arParametrsList['MAIN']['OPTIONS']['BGCOLOR_THEME']['LIST'];
		$isCustomTheme = $_SESSION['THEME'][SITE_ID]['BASE_COLOR'] === 'CUSTOM';
		$isCustomThemeBG = $_SESSION['THEME'][SITE_ID]['BGCOLOR_THEME'] === 'CUSTOM';

		$bNeedGenerateAllThemes = Option::get(self::moduleID, 'NeedGenerateThemes', 'N', $siteID) === 'Y';
		$bNeedGenerateCustomTheme = Option::get(self::moduleID, 'NeedGenerateCustomTheme', 'N', $siteID) === 'Y';
		$bNeedGenerateCustomThemeBG = Option::get(self::moduleID, 'NeedGenerateCustomThemeBG', 'N', $siteID) === 'Y';

		$baseColorCustom = $baseColorBGCustom = '';

		$lastGeneratedBaseColorCustom = Option::get(self::moduleID, 'LastGeneratedBaseColorCustom', '', $siteID);
		if(isset(self::$arParametrsList['MAIN']['OPTIONS']['BASE_COLOR_CUSTOM']))
		{
			$baseColorCustom = $arBackParametrs['BASE_COLOR_CUSTOM'] = str_replace('#', '', $arBackParametrs['BASE_COLOR_CUSTOM']);
			if($arBackParametrs['THEME_SWITCHER'] === 'Y' && $_SESSION['THEME'][SITE_ID]['BASE_COLOR_CUSTOM'] && strlen($_SESSION['THEME'][SITE_ID]['BASE_COLOR_CUSTOM']))
				$baseColorCustom = $_SESSION['THEME'][SITE_ID]['BASE_COLOR_CUSTOM'] = str_replace('#', '', $_SESSION['THEME'][SITE_ID]['BASE_COLOR_CUSTOM']);
		}

		$lastGeneratedBaseColorBGCustom = Option::get(self::moduleID, 'LastGeneratedBaseColorBGCustom', '', $siteID);
		if(isset(self::$arParametrsList['MAIN']['OPTIONS']['CUSTOM_BGCOLOR_THEME']))
		{
			$baseColorBGCustom = $arBackParametrs['CUSTOM_BGCOLOR_THEME'] = str_replace('#', '', $arBackParametrs['CUSTOM_BGCOLOR_THEME']);
			if($arBackParametrs['THEME_SWITCHER'] === 'Y' && strlen($_SESSION['THEME'][SITE_ID]['CUSTOM_BGCOLOR_THEME']))
				$baseColorBGCustom = $_SESSION['THEME'][SITE_ID]['CUSTOM_BGCOLOR_THEME'] = str_replace('#', '', $_SESSION['THEME'][SITE_ID]['CUSTOM_BGCOLOR_THEME']);
		}

		$bGenerateAll = self::devMode || $bNeedGenerateAllThemes;
		$bGenerateCustom = $bGenerateAll || $bNeedGenerateCustomTheme || ($arBackParametrs['THEME_SWITCHER'] === 'Y' && $isCustomTheme && strlen($baseColorCustom) && $baseColorCustom != $lastGeneratedBaseColorCustom);
		$bGenerateCustomBG = $bGenerateAll || $bNeedGenerateCustomThemeBG || ($arBackParametrs['THEME_SWITCHER'] === 'Y' && $isCustomThemeBG && strlen($baseColorBGCustom) && $baseColorBGCustom != $lastGeneratedBaseColorBGCustom);

		if($arBaseColors && is_array($arBaseColors) && ($bGenerateAll || $bGenerateCustom || $bGenerateCustomBG))
		{
			if(!class_exists('lessc'))
				include_once 'lessc.inc.php';

			$less = new lessc;
			try
			{
				if(defined('SITE_TEMPLATE_PATH'))
				{
					$templateName = array_pop(explode('/', SITE_TEMPLATE_PATH));
				}

				foreach($arBaseColors as $colorCode => $arColor)
				{
					if(($bCustom = ($colorCode == 'CUSTOM')) && $bGenerateCustom)
					{
						$less->setVariables(array('bcolor' => (strlen($baseColorCustom) ? '#'.$baseColorCustom : $arBaseColors[self::$arParametrsList['MAIN']['OPTIONS']['BASE_COLOR']['DEFAULT']]['COLOR'])));
					}
					elseif($bGenerateAll)
					{
						$less->setVariables(array('bcolor' => $arColor['COLOR']));
					}

					if($bGenerateAll || ($bCustom && $bGenerateCustom))
					{
						if(defined('SITE_TEMPLATE_PATH'))
						{
							$themeDirPath = $_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/themes/'.strToLower($colorCode.($colorCode !== 'CUSTOM' ? '' : '_'.$siteID)).'/';
							if(!is_dir($themeDirPath))
								mkdir($themeDirPath, 0755, true);
							$output = $less->compileFile(__DIR__.'/../../css/theme.less', $themeDirPath.'theme.css');
							if($output)
							{
								if($bCustom)
									Option::set(self::moduleID, 'LastGeneratedBaseColorCustom', $baseColorCustom, $siteID);

								self::GenerateMinCss($themeDirPath.'theme.css');
							}

							if($templateName && $templateName != 'aspro_next') {

								$themeDirPath = $_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/aspro_next'.'/themes/'.strToLower($colorCode.($colorCode !== 'CUSTOM' ? '' : '_'.$siteID)).'/';
								if(!is_dir($themeDirPath))
									mkdir($themeDirPath, 0755, true);
								$output = $less->compileFile(__DIR__.'/../../css/theme.less', $themeDirPath.'theme.css');
								if($output)
								{
									self::GenerateMinCss($themeDirPath.'theme.css');
								}

							}

						}
					}
				}
				foreach($arBaseBgColors as $colorCode => $arColor)
				{
					if(($bCustom = ($colorCode == 'CUSTOM')) && $bGenerateCustomBG)
					{
						if(strlen($baseColorBGCustom))
						{
							$footerBgColor = $baseColorBGCustom === "FFFFFF" ? "F6F6F7" : $baseColorBGCustom;
							$less->setVariables(array('bcolor' => (strlen($baseColorBGCustom) ? '#'.$baseColorBGCustom : $arBaseBgColors[self::$arParametrsList['MAIN']['OPTIONS']['BGCOLOR_THEME']['DEFAULT']]['COLOR']), 'fcolor' => '#'.$footerBgColor));
						}
					}
					elseif($bGenerateAll)
					{
						$less->setVariables(array('bcolor' => $arColor['COLOR'], 'fcolor' => $arColor['COLOR']));
					}

					if($bGenerateAll || ($bCustom && $bGenerateCustomBG))
					{
						if(defined('SITE_TEMPLATE_PATH'))
						{
							$themeDirPath = $_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/bg_color/'.strToLower($colorCode.($colorCode !== 'CUSTOM' ? '' : '_'.$siteID)).'/';
							if(!is_dir($themeDirPath))
								mkdir($themeDirPath, 0755, true);
							$output = $less->compileFile(__DIR__.'/../../css/bgtheme.less', $themeDirPath.'bgcolors.css');
							if($output)
							{
								if($bCustom)
									Option::set(self::moduleID, 'LastGeneratedBaseColorBGCustom', $baseColorBGCustom, $siteID);

								self::GenerateMinCss($themeDirPath.'bgcolors.css');
							}

							if($templateName && $templateName != 'aspro_next') {

								$themeDirPath = $_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/aspro_next'.'/bg_color/'.strToLower($colorCode.($colorCode !== 'CUSTOM' ? '' : '_'.$siteID)).'/';
								if(!is_dir($themeDirPath))
									mkdir($themeDirPath, 0755, true);
								$output = $less->compileFile(__DIR__.'/../../css/bgtheme.less', $themeDirPath.'bgcolors.css');
								if($output)
								{
									self::GenerateMinCss($themeDirPath.'bgcolors.css');
								}

							}

						}
					}
				}
			}
			catch(exception $e)
			{
				echo 'Fatal error: '.$e->getMessage();
				die();
			}

			if($bNeedGenerateAllThemes)
				Option::set(self::moduleID, "NeedGenerateThemes", 'N', $siteID);
			if($bNeedGenerateCustomTheme)
				Option::set(self::moduleID, "NeedGenerateCustomTheme", 'N', $siteID);
			if($bNeedGenerateCustomThemeBG)
				Option::set(self::moduleID, "NeedGenerateCustomThemeBG", 'N', $siteID);
		}
	}

	public static function get_banners_position($position, $show_all = 'N') {
		$arTheme = self::GetFrontParametrsValues(SITE_ID);
		if ($arTheme["ADV_".$position] == 'Y') {
			global $APPLICATION;
			$APPLICATION->IncludeComponent(
				"bitrix:news.list",
				"banners",
				array(
					"IBLOCK_TYPE" => "aspro_next_adv",
					"IBLOCK_ID" => CNextCache::$arIBlocks[SITE_ID]["aspro_next_adv"]["aspro_next_banners_inner"][0],
					"POSITION"	=> $position,
					"PAGE"		=> $APPLICATION->GetCurPage(),
					"NEWS_COUNT" => "100",
					"SHOW_ALL_ELEMENTS" => $show_all,
					"SORT_BY1" => "SORT",
					"SORT_ORDER1" => "ASC",
					"SORT_BY2" => "ID",
					"SORT_ORDER2" => "ASC",
					"FIELD_CODE" => array(
						0 => "NAME",
						2 => "PREVIEW_PICTURE",
					),
					"PROPERTY_CODE" => array(
						0 => "LINK",
						1 => "TARGET",
						2 => "BGCOLOR",
						3 => "SHOW_SECTION",
						4 => "SHOW_PAGE",
						5 => "HIDDEN_XS",
						6 => "HIDDEN_SM",
						7 => "POSITION",
						8 => "SIZING",
					),
					"CHECK_DATES" => "Y",
					"FILTER_NAME" => "arRegionLink",
					"DETAIL_URL" => "",
					"AJAX_MODE" => "N",
					"AJAX_OPTION_JUMP" => "N",
					"AJAX_OPTION_STYLE" => "Y",
					"AJAX_OPTION_HISTORY" => "N",
					"CACHE_TYPE" => "A",
					"CACHE_TIME" => "3600000",
					"CACHE_FILTER" => "Y",
					"CACHE_GROUPS" => "N",
					"PREVIEW_TRUNCATE_LEN" => "150",
					"ACTIVE_DATE_FORMAT" => "d.m.Y",
					"SET_TITLE" => "N",
					"SET_STATUS_404" => "N",
					"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
					"ADD_SECTIONS_CHAIN" => "N",
					"HIDE_LINK_WHEN_NO_DETAIL" => "N",
					"PARENT_SECTION" => "",
					"PARENT_SECTION_CODE" => "",
					"INCLUDE_SUBSECTIONS" => "Y",
					"PAGER_TEMPLATE" => ".default",
					"DISPLAY_TOP_PAGER" => "N",
					"DISPLAY_BOTTOM_PAGER" => "N",
					"PAGER_TITLE" => "",
					"PAGER_SHOW_ALWAYS" => "N",
					"PAGER_DESC_NUMBERING" => "N",
					"PAGER_DESC_NUMBERING_CACHE_TIME" => "3600000",
					"PAGER_SHOW_ALL" => "N",
					"AJAX_OPTION_ADDITIONAL" => "",
					"SHOW_DETAIL_LINK" => "N",
					"SET_BROWSER_TITLE" => "N",
					"SET_META_KEYWORDS" => "N",
					"SET_META_DESCRIPTION" => "N",
					"COMPONENT_TEMPLATE" => "banners",
					"SET_LAST_MODIFIED" => "N",
					"COMPOSITE_FRAME_MODE" => "A",
					"COMPOSITE_FRAME_TYPE" => "AUTO",
					"PAGER_BASE_LINK_ENABLE" => "N",
					"SHOW_404" => "N",
					"MESSAGE_404" => ""
				),
				false, array('ACTIVE_COMPONENT' => 'Y', 'HIDE_ICONS' => 'Y')
			);
		}
	}

	public static function formatPriceMatrix($arItem = array()){
		if(isset($arItem['PRICE_MATRIX']) && $arItem['PRICE_MATRIX'])
		{
			$result = false;
			$minPrice = 0;
			foreach($arItem['PRICE_MATRIX']['MATRIX'] as $key => $arPriceGroup)
			{
				foreach($arPriceGroup as $key2 => $arPrice)
				{
					if($arPrice['PRICE'])
					{
						if($arItem['PRICE_MATRIX']['CAN_BUY'] && in_array($key, $arItem['PRICE_MATRIX']['CAN_BUY']) && ($arItem['CATALOG_TYPE'] == 1 || $arItem['CATALOG_TYPE'] == 2))
						{
							if (empty($result))
							{
								$minPrice = ($arPrice['DISCOUNT_PRICE'] != $arPrice['PRICE'] ? $arPrice['DISCOUNT_PRICE'] : $arPrice['PRICE']);
								$result = $minPrice;
							}
							else
							{
								$comparePrice = ($arPrice['DISCOUNT_PRICE'] != $arPrice['PRICE'] ? $arPrice['DISCOUNT_PRICE'] : $arPrice['PRICE']);
								if ($minPrice > $comparePrice)
								{
									$minPrice = $comparePrice;
									$result = $minPrice;
								}
							}
							$arItem['MIN_PRICE']['VALUE'] = $result;
							$arItem['MIN_PRICE']['DISCOUNT_VALUE'] = $result;
							$arItem['MIN_PRICE']['PRINT_DISCOUNT_VALUE'] = CCurrencyLang::CurrencyFormat($result, $arPrice['CURRENCY'], true);
							$arItem['MIN_PRICE']['CURRENCY'] = $arPrice['CURRENCY'];
							$arItem['MIN_PRICE']['CAN_BUY'] = 'Y';
						}
						$arItem['PRICE_MATRIX']['MATRIX'][$key][$key2]['PRINT_PRICE'] =  CCurrencyLang::CurrencyFormat($arPrice['PRICE'], $arPrice['CURRENCY'], true);
					}
					if($arPrice['DISCOUNT_PRICE'])
						$arItem['PRICE_MATRIX']['MATRIX'][$key][$key2]['PRINT_DISCOUNT_PRICE'] =  CCurrencyLang::CurrencyFormat($arPrice['DISCOUNT_PRICE'], $arPrice['CURRENCY'], true);
				}
			}
		}
		return $arItem;
	}

	public static function showPriceMatrix($arItem = array(), $arParams, $strMeasure = '', $arAddToBasketData = array()){
		$html = '';
		if(isset($arItem['PRICE_MATRIX']) && $arItem['PRICE_MATRIX'])
		{
			ob_start();?>
				<div class="price_matrix_block">
					<?
					$sDiscountPrices = \Bitrix\Main\Config\Option::get(ASPRO_NEXT_MODULE_ID, 'DISCOUNT_PRICE');
					$arDiscountPrices = array();
					if($sDiscountPrices)
						$arDiscountPrices = array_flip(explode(',', $sDiscountPrices));

					\Bitrix\Main\Type\Collection::sortByColumn($arItem['PRICE_MATRIX']['COLS'], array('SORT' => SORT_ASC));

					$arTmpPrice = (isset($arItem['ITEM_PRICES']) ? current($arItem['ITEM_PRICES']) : array());

					$iCountPriceGroup = count($arItem['PRICE_MATRIX']['COLS']);
					$bPriceRows = (count($arItem['PRICE_MATRIX']['ROWS']) > 1);?>
					<?foreach($arItem['PRICE_MATRIX']['COLS'] as $arPriceGroup):?>
						<?if($iCountPriceGroup > 1):?>
							<?
							$class = '';
							if($arTmpPrice)
							{
								if($arItem['PRICE_MATRIX']['MATRIX'][$arPriceGroup['ID']][$arTmpPrice['QUANTITY_HASH']]['ID'] == $arTmpPrice['ID'])
									$class = 'min';
							}?>
							<div class="price_group <?=$class;?>  <?=$arPriceGroup['XML_ID']?>"><div class="price_name"><?=$arPriceGroup["NAME_LANG"];?></div>
						<?endif;?>
						<div class="price_matrix_wrapper <?=($arDiscountPrices ? (isset($arDiscountPrices[$arPriceGroup['ID']]) ? 'strike_block' : '') : '');?>">
						<?$iCountPriceInterval = count($arItem['PRICE_MATRIX']['MATRIX'][$arPriceGroup['ID']]);?>
						<?foreach($arItem['PRICE_MATRIX']['MATRIX'][$arPriceGroup['ID']] as $key => $arPrice):?>
							<?if($iCountPriceInterval > 1):?>
								<div class="price_wrapper_block">
									<div class="price_interval">
										<?
										$quantity_from = $arItem['PRICE_MATRIX']['ROWS'][$key]['QUANTITY_FROM'];
										$quantity_to = $arItem['PRICE_MATRIX']['ROWS'][$key]['QUANTITY_TO'];
										$text = ($quantity_to ? ($quantity_from ? $quantity_from.'-'.$quantity_to : '&lt;'.$quantity_to ) : '&gt;'.$quantity_from );
										?>
										<?=$text?><?if(($arParams["SHOW_MEASURE"]=="Y") && $strMeasure):?> <?=$strMeasure?><?endif;?>
									</div>
								<?endif;?>
								<?if($arPrice["PRICE"] > $arPrice["DISCOUNT_PRICE"]){?>
									<div class="price" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["DISCOUNT_PRICE"];?>">
										<?if(strlen($arPrice["DISCOUNT_PRICE"])):?>
											<span class="values_wrapper"><?=\Aspro\Functions\CAsproItem::getCurrentPrice("DISCOUNT_PRICE", $arPrice);?></span><?if(($arParams["SHOW_MEASURE"]=="Y") && $strMeasure && $arPrice["DISCOUNT_PRICE"]):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?>
										<?endif;?>
									</div>
									<?if($arParams["SHOW_OLD_PRICE"]=="Y"):?>
										<div class="price discount" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["PRICE"];?>">
											<span class="values_wrapper"><?=\Aspro\Functions\CAsproItem::getCurrentPrice("PRICE", $arPrice);?></span>
										</div>
									<?endif;?>
								<?}else{?>
									<div class="price" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["DISCOUNT_PRICE"];?>">
										<span><span class="values_wrapper"><?=\Aspro\Functions\CAsproItem::getCurrentPrice("PRICE", $arPrice);?></span><?if(($arParams["SHOW_MEASURE"]=="Y") && $strMeasure && $arPrice["PRICE"]):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?></span>
									</div>
								<?}?>
							<?if($iCountPriceInterval > 1):?>
								</div>
							<?else:
								if($arParams['SHOW_DISCOUNT_PERCENT'] == 'Y' && $arPrice["PRICE"] > $arPrice["DISCOUNT_PRICE"]):?>
									<?$ratio = (!$bPriceRows ? $arAddToBasketData["MIN_QUANTITY_BUY"] : 1);?>
									<div class="sale_block">
										<?$diff = ($arPrice["PRICE"] - $arPrice["DISCOUNT_PRICE"]);?>
										<?if($arParams['SHOW_DISCOUNT_PERCENT_NUMBER'] != 'Y'):?>
											<span class="title"><?=GetMessage("CATALOG_ECONOMY");?></span> <div class="text"><span class="values_wrapper" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=($diff*$ratio);?>"><?=\Aspro\Functions\CAsproItem::getCurrentPrice($diff, $arPrice, false)?></span></div>
										<?else:?>
											<?$percent=round(($diff/$arPrice["PRICE"])*100);?>
											<?if($percent && $percent<100){?>
												<div class="value">-<span><?=$percent;?></span>%</div>
											<?}?>
											<div class="text"><?=GetMessage("CATALOG_ECONOMY");?> <span class="values_wrapper"><?=\Aspro\Functions\CAsproItem::getCurrentPrice($diff, $arPrice, false);?></span></div>
										<?endif;?>
									</div>
								<?endif;?>
							<?endif;?>
						<?endforeach;?>
						</div>
						<?if($iCountPriceGroup > 1):?>
							</div>
						<?endif;?>
					<?endforeach;?>
				</div>
			<?$html = ob_get_contents();
			ob_end_clean();

			foreach(GetModuleEvents(ASPRO_NEXT_MODULE_ID, 'OnAsproShowPriceMatrix', true) as $arEvent) // event for manipulation price matrix
				ExecuteModuleEventEx($arEvent, array($arItem, $arParams, $strMeasure, $arAddToBasketData, &$html));
		}
		return $html;
	}

	public static function showPriceRangeTop($arItem, $arParams, $mess = ''){
		$html = '';
		if($arItem)
		{
			if(isset($arItem['PRICE_MATRIX']) && $arItem['PRICE_MATRIX'])
			{
				ob_start();
					$rangSelected = $arItem['ITEM_QUANTITY_RANGE_SELECTED'];
					$priceSelected = $arItem['ITEM_PRICE_SELECTED'];
					if(isset($arItem['FIX_PRICE_MATRIX']) && $arItem['FIX_PRICE_MATRIX'])
					{
						$rangSelected = $arItem['FIX_PRICE_MATRIX']['RANGE_SELECT'];
						$priceSelected = $arItem['FIX_PRICE_MATRIX']['PRICE_SELECT'];
					}
					?>
					<div class="with_matrix">
						<div class="price">
							<div class="price_value_block"><?=\Aspro\Functions\CAsproItem::getCurrentPrice("PRICE", $arItem['ITEM_PRICES'][$priceSelected]);?></div><?if (($arParams['SHOW_MEASURE'] == 'Y') && $arItem['CATALOG_MEASURE_NAME'] && $arItem['ITEM_PRICES'][$priceSelected]["PRICE"]):?><span class="price_measure">/<?=$arItem['CATALOG_MEASURE_NAME']?></span><?endif;?>
						</div>
						<?if($arParams['SHOW_OLD_PRICE'] == 'Y' && $arItem['ITEM_PRICES'][$priceSelected]['BASE_PRICE'] > $arItem['ITEM_PRICES'][$priceSelected]['PRICE']):?>
							<div class="price discount"><?=\Aspro\Functions\CAsproItem::getCurrentPrice("BASE_PRICE", $arItem['ITEM_PRICES'][$priceSelected]);?></div>
						<?endif;?>
						<?if($arParams['SHOW_DISCOUNT_PERCENT'] == 'Y' && $arItem['ITEM_PRICES'][$priceSelected]['DISCOUNT']):?>
							<div class="sale_block matrix">
								<div class="sale_wrapper">
									<?if($arParams['SHOW_DISCOUNT_PERCENT_NUMBER'] != 'Y'):?>
										<span class="title"><?=$mess;?></span>
										<div class="text">
										<span class="values_wrapper"><?=\Aspro\Functions\CAsproItem::getCurrentPrice("DISCOUNT", $arItem['ITEM_PRICES'][$priceSelected]);?></span></div>
									<?else:?>
										<?$percent=$arItem['ITEM_PRICES'][$priceSelected]["PERCENT"];?>
										<?if($percent && $percent<100){?>
											<div class="value">-<span><?=$percent;?></span>%</div>
										<?}?>
										<div class="text">
										<span class="title"><?=$mess;?></span>
										<span class="values_wrapper"><?=\Aspro\Functions\CAsproItem::getCurrentPrice("DISCOUNT", $arItem['ITEM_PRICES'][$priceSelected]);?></span></div>
									<?endif;?>
									<div class="clearfix"></div>
								</div>
							</div>
						<?endif;?>
					</div>
				<?$html = ob_get_contents();
				ob_end_clean();
				foreach(GetModuleEvents(ASPRO_NEXT_MODULE_ID, 'OnAsproShowPriceRangeTop', true) as $arEvent) // event for manipulation price matrix top
					ExecuteModuleEventEx($arEvent, array($arItem, $arParams, $mess, &$html));
			}
		}
		return $html;
	}

	public static function checkPriceRangeExt($arResult = array()){
		$arData = array();
		if($arResult)
		{
			if(isset($arResult['ITEM_PRICE_MODE']) && $arResult['ITEM_PRICE_MODE'] == 'Q')
			{
				$arRang = array();
				$bFound = false;
				$quantity = (int)$arResult['CATALOG_MEASURE_RATIO'];

				$rangSelected = $arResult['ITEM_QUANTITY_RANGE_SELECTED'];
				$priceSelected = $arResult['ITEM_PRICE_SELECTED'];

				foreach($arResult['ITEM_QUANTITY_RANGES'] as $key => $arItemRang)
				{
					$arRang = $arItemRang;
					if($quantity >= (int)$arRang['SORT_FROM'] && (strpos($arRang['SORT_TO'], 'INF') !== false || $quantity <= (int)$arRang['SORT_TO']))
					{
						$bFound = true;
						$rangSelected = $arRang['HASH'];
						break;
					}
				}
				if(!$bFound && ($arRang = self::getMinPriceRangeExt($arResult['ITEM_QUANTITY_RANGES'])))
				{
					$rangSelected = $arRang['HASH'];
				}

				foreach($arResult['ITEM_PRICES'] as $key => $arPrice)
				{
					if ($arPrice['QUANTITY_HASH'] == $rangSelected)
					{
						$priceSelected = $key;
						break;
					}
				}

				$arData = array(
					'RANGE_SELECT' => $rangSelected,
					'PRICE_SELECT' => $priceSelected,
				);
			}
		}
		return $arData;
	}

	public static function getMinPriceRangeExt($arPriceRange = array()){
		$arRang = array();
		if($arPriceRange)
		{
			foreach($arPriceRange as $key => $arItemRang)
			{
				if(!$arRang || ((int)$arItemRang['SORT_FROM'] < (int)$arRang['SORT_FROM']))
				{
					$arRang = $arItemRang;
				}
			}
		}
		return $arRang;
	}

	public static function getChilds($input, &$start = 0, $level = 0){
		$childs = array();

		if(!$level){
			$lastDepthLevel = 1;
			if(is_array($input)){
				foreach($input as $i => $arItem){
					if($arItem["DEPTH_LEVEL"] > $lastDepthLevel){
						if($i > 0){
							$input[$i - 1]["IS_PARENT"] = 1;
						}
					}
					$lastDepthLevel = $arItem["DEPTH_LEVEL"];
				}
			}
		}

		for($i = $start, $count = count($input); $i < $count; ++$i){
			$item = $input[$i];
			if($level > $item['DEPTH_LEVEL'] - 1){
				break;
			}
			elseif(!empty($item['IS_PARENT'])){
				++$i;
				$item['CHILD'] = self::getChilds($input, $i, $level + 1);
				--$i;
			}
			$childs[] = $item;
		}

		$start = $i;

		if($GLOBALS['arTheme']['USE_REGIONALITY']['VALUE'] === 'Y' && $GLOBALS['arTheme']['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_FILTER_ITEM']['VALUE'] === 'Y' && $GLOBALS['arRegion']){
			if(is_array($childs)){
				foreach($childs as $i => $item){
					if($item['PARAMS'] && isset($item['PARAMS']['LINK_REGION'])){
						if($item['PARAMS']['LINK_REGION']){
							if(!in_array($GLOBALS['arRegion']['ID'], $item['PARAMS']['LINK_REGION'])){
								unset($childs[$i]);
							}
						}
						else{
							unset($childs[$i]);
						}
					}
				}
			}
		}

		return $childs;
	}

	public static function unique_multidim_array($array, $key) {
	    $temp_array = array();
	    $i = 0;
	    $key_array = array();

	    foreach($array as $val) {
	        if (!in_array($val[$key], $key_array)) {
	            $key_array[$i] = $val[$key];
	            $temp_array[$i] = $val;
	        }
	        $i++;
	    }
	    return $temp_array;
	}

	public static function convertArray($array, $charset){
		global $APPLICATION;
	    if(is_array($array) && $array){
		    foreach($array as $key=>$arVal) {
		    	foreach($arVal as $key2=>$value){
					$array[$key][$key2]=$APPLICATION->ConvertCharset($value, 'UTF-8', $charset);
		    	}
		    }
		}else{
			$array=array();
		}
	    return $array;
	}

	public static function getChilds2($input, &$start = 0, $level = 0){
		static $arIblockItemsMD5 = array();

		if(!$level){
			$lastDepthLevel = 1;
			if($input && is_array($input)){
				foreach($input as $i => $arItem){
					if($arItem['DEPTH_LEVEL'] > $lastDepthLevel){
						if($i > 0){
							$input[$i - 1]['IS_PARENT'] = 1;
						}
					}
					$lastDepthLevel = $arItem['DEPTH_LEVEL'];
				}
			}
		}

		$childs = array();
		$count = count($input);
		for($i = $start; $i < $count; ++$i){
			$item = $input[$i];
			if(!isset($item)){
				continue;
			}
			if($level > $item['DEPTH_LEVEL'] - 1){
				break;
			}
			else{
				if(!empty($item['IS_PARENT'])){
					$i++;
					$item['CHILD'] = self::getChilds($input, $i, $level+1);
					$i--;
				}

				$childs[] = $item;
			}
		}

		$start = $i;

		if(is_array($childs)){
			foreach($childs as $i => $item){
				if($item['PARAMS']){
					$md5 = md5($item['TEXT'].$item['LINK'].$item['SELECTED'].$item['PERMISSION'].$item['ITEM_TYPE'].$item['IS_PARENT'].serialize($item['ADDITIONAL_LINKS']).serialize($item['PARAMS']));
					if(isset($arIblockItemsMD5[$md5][$item['PARAMS']['DEPTH_LEVEL']])){
						if(isset($arIblockItemsMD5[$md5][$item['PARAMS']['DEPTH_LEVEL']][$level]) || ($item['DEPTH_LEVEL'] === 1 && !$level)){
							unset($childs[$i]);
							continue;
						}
					}
					if(!isset($arIblockItemsMD5[$md5])){
						$arIblockItemsMD5[$md5] = array($item['PARAMS']['DEPTH_LEVEL'] => array($level => true));
					}
					else{
						$arIblockItemsMD5[$md5][$item['PARAMS']['DEPTH_LEVEL']][$level] = true;
					}
				}
			}
		}

		if($GLOBALS['arTheme']['USE_REGIONALITY']['VALUE'] === 'Y' && $GLOBALS['arTheme']['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_FILTER_ITEM']['VALUE'] === 'Y' && $GLOBALS['arRegion']){
			if(is_array($childs)){
				foreach($childs as $i => $item){
					if($item['PARAMS'] && isset($item['PARAMS']['LINK_REGION'])){
						if($item['PARAMS']['LINK_REGION']){
							if(!in_array($GLOBALS['arRegion']['ID'], $item['PARAMS']['LINK_REGION'])){
								unset($childs[$i]);
							}
						}
						else{
							unset($childs[$i]);
						}
					}
				}
			}
		}

		if(!$level){
			$arIblockItemsMD5 = array();
		}

		return $childs;
	}

	public static function getSectionChilds($PSID, &$arSections, &$arSectionsByParentSectionID, &$arItemsBySectionID, &$aMenuLinksExt){
		if($arSections && is_array($arSections)){
			foreach($arSections as $arSection){
				if($arSection['IBLOCK_SECTION_ID'] == $PSID){
					$arItem = array($arSection['NAME'], $arSection['SECTION_PAGE_URL'], array(), array('FROM_IBLOCK' => 1, 'DEPTH_LEVEL' => $arSection['DEPTH_LEVEL'], 'IBLOCK_ID' => $arSection['IBLOCK_ID']));
					$arItem[3]['IS_PARENT'] = (isset($arItemsBySectionID[$arSection['ID']]) || isset($arSectionsByParentSectionID[$arSection['ID']]) ? 1 : 0);
					if($arSection["PICTURE"]){
						$arItem[3]["PICTURE"]=$arSection["PICTURE"];
					}
					if($arSection["UF_CATALOG_ICON"]){
						$arItem[3]["UF_CATALOG_ICON"]=$arSection["UF_CATALOG_ICON"];
					}
					if($arSection["UF_REGION"]){
						$arItem[3]["LINK_REGION"]=$arSection["UF_REGION"];
					}
					$aMenuLinksExt[] = $arItem;
					if($arItem[3]['IS_PARENT']){
						// subsections
						self::getSectionChilds($arSection['ID'], $arSections, $arSectionsByParentSectionID, $arItemsBySectionID, $aMenuLinksExt);
						// section elements
						if($arItemsBySectionID[$arSection['ID']] && is_array($arItemsBySectionID[$arSection['ID']])){
							foreach($arItemsBySectionID[$arSection['ID']] as $arItem){
								if(is_array($arItem['DETAIL_PAGE_URL'])){
									if(isset($arItem['CANONICAL_PAGE_URL'])){
										$arItem['DETAIL_PAGE_URL'] = $arItem['CANONICAL_PAGE_URL'];
									}
									else{
										$arItem['DETAIL_PAGE_URL'] = $arItem['DETAIL_PAGE_URL'][key($arItem['DETAIL_PAGE_URL'])];
									}
								}
								$aMenuLinksExt[] = array($arItem['NAME'], $arItem['DETAIL_PAGE_URL'], array(), array('FROM_IBLOCK' => 1, 'DEPTH_LEVEL' => ($arSection['DEPTH_LEVEL'] + 1), 'IS_ITEM' => 1));
							}
						}
					}
				}
			}
		}
	}

	public static function replaceMenuChilds(&$arResult, $arParams) {
		$arMegaLinks = $arMegaItems = array();

		$menuIblockId = CNextCache::$arIBlocks[SITE_ID]['aspro_next_catalog']['aspro_next_megamenu'][0];
		if($menuIblockId){
			$arMenuSections = CNextCache::CIblockSection_GetList(
				array(
					'SORT' => 'ASC',
					'ID' => 'ASC',
					'CACHE' => array(
						'TAG' => CNextCache::GetIBlockCacheTag($menuIblockId),
						'GROUP' => array('DEPTH_LEVEL'),
						'MULTI' => 'Y',
					)
				),
				array(
					'ACTIVE' => 'Y',
					'GLOBAL_ACTIVE' => 'Y',
					'IBLOCK_ID' => $menuIblockId,
					'<=DEPTH_LEVEL' => $arParams['MAX_LEVEL'],
				),
				false,
				array(
					'ID',
					'NAME',
					'IBLOCK_SECTION_ID',
					'DEPTH_LEVEL',
					'PICTURE',
					'UF_MEGA_MENU_LINK',
					'UF_CATALOG_ICON',
				)
			);

			ksort($arMenuSections);

			if($arMenuSections){
				$cur_page = $GLOBALS['APPLICATION']->GetCurPage(true);
				$cur_page_no_index = $GLOBALS['APPLICATION']->GetCurPage(false);
				$some_selected = false;
				$bMultiSelect = $arParams['ALLOW_MULTI_SELECT'] === 'Y';

				foreach($arMenuSections as $depth => $arLinks){
					foreach($arLinks as $arLink){
						$url = trim($arLink['UF_MEGA_MENU_LINK']);
						if(
							(
								$depth == 1 &&
								strlen($url)
							) ||
							$depth > 1
						){
							$arPicture['PICTURE'] = $arLink['PICTURE'];
							if ($arLink['UF_CATALOG_ICON']) {
								$arPicture['UF_CATALOG_ICON'] = $arLink['UF_CATALOG_ICON'];
							}

							$arMegaItem = array(
								'TEXT' => htmlspecialcharsbx($arLink['NAME']),
								'LINK' => strlen($url) ? $url : 'javascript:;',
								'SELECTED' => false,
								// 'PARAMS' => $arPicture,
								'PARAMS' => array(
									'PICTURE' => $arLink['PICTURE'],
									'UF_CATALOG_ICON' => $arLink['UF_CATALOG_ICON'],
								),
								'CHILD' => array(),
							);
							$arMegaItems[$arLink['ID']] =& $arMegaItem;

							if($depth > 1){
								if(
									strlen($url) &&
									($bMultiSelect || !$some_selected)
								){
									$arMegaItem['SELECTED'] = CMenu::IsItemSelected($url, $cur_page, $cur_page_no_index);
								}

								if($arMegaItems[$arLink['IBLOCK_SECTION_ID']]){
									$arMegaItems[$arLink['IBLOCK_SECTION_ID']]['IS_PARENT'] = 1;
									$arMegaItems[$arLink['IBLOCK_SECTION_ID']]['CHILD'][] =& $arMegaItems[$arLink['ID']];
								}
							}
							else{
								$arMegaLinks[] =& $arMegaItems[$arLink['ID']];
							}

							unset($arMegaItem);
						}
					}
				}
			}
		}

		if($arMegaLinks){
			foreach($arResult as $i => $arItem){
				foreach($arMegaLinks as $arLink){
					if($arItem['LINK'] == $arLink['LINK']){
						if($arResult[$i]['PARAMS']['MEGA_MENU_CHILDS']){
							array_splice($arResult, $i, 1, $arLink['CHILD']);
						}
						else{
							$arResult[$i]['CHILD'] =& $arLink['CHILD'];
							$arResult[$i]['IS_PARENT'] = boolval($arLink['CHILD']);
						}
					}
				}
			}
		}
	}

	public static function cmpByID($a, $b){
		return ($b['ID'] - $a['ID']);
	}

	public static function cmpBySort($a, $b){
		return ($a['SORT'] - $b['SORT']);
	}

	public static function cmpByIDFilter($a, $b){
		global $IDFilter;
		$ak = array_search($a['ID'], $IDFilter);
		$bk = array_search($b['ID'], $IDFilter);
		if($ak === $bk){
			return 0;
		}
		else{
			return ($ak > $bk ? 1 : -1);
		}
	}

	public static function getChainNeighbors($curSectionID, $chainPath){
		static $arSections, $arSectionsIDs, $arSubSections;

		$arResult = array();

		if($arSections === NULL){
			$arSections = $arSectionsIDs = $arSubSections = array();
			$IBLOCK_ID = false;
			$nav = CIBlockSection::GetNavChain(false, $curSectionID, array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "SECTION_PAGE_URL"));
			while($ar = $nav->GetNext()){
				$arSections[] = $ar;
				$arSectionsIDs[] = ($ar["IBLOCK_SECTION_ID"] ? $ar["IBLOCK_SECTION_ID"] : 0);
				$IBLOCK_ID = $ar["IBLOCK_ID"];
			}

			if($arSectionsIDs){
				$arSubSectionsFilter = array("ACTIVE" => "Y", "GLOBAL_ACTIVE" => "Y", "IBLOCK_ID" => $IBLOCK_ID, "SECTION_ID" => $arSectionsIDs);
				$resSubSection = CIBlockSection::GetList(array('SORT' => 'ASC'), self::makeSectionFilterInRegion($arSubSectionsFilter), false, array("ID", "NAME", "IBLOCK_SECTION_ID", "SECTION_PAGE_URL"));
				while($arSubSection = $resSubSection->GetNext()){
					$arSubSection["IBLOCK_SECTION_ID"] = ($arSubSection["IBLOCK_SECTION_ID"] ? $arSubSection["IBLOCK_SECTION_ID"] : 0);
					$arSubSections[$arSubSection["IBLOCK_SECTION_ID"]][] = $arSubSection;
				}

				if(in_array(0, $arSectionsIDs)){
					$arSubSectionsFilter = array("ACTIVE" => "Y", "GLOBAL_ACTIVE" => "Y", "IBLOCK_ID" => $IBLOCK_ID, "SECTION_ID" => false);
					$resSubSection = CIBlockSection::GetList(array('SORT' => 'ASC'), self::makeSectionFilterInRegion($arSubSectionsFilter), false, array("ID", "NAME", "IBLOCK_SECTION_ID", "SECTION_PAGE_URL"));
					while($arSubSection = $resSubSection->GetNext()){
						$arSubSections[$arSubSection["IBLOCK_SECTION_ID"]][] = $arSubSection;
					}
				}
			}
		}

		if($arSections && strlen($chainPath)){
			foreach($arSections as $arSection){
				if($arSection["SECTION_PAGE_URL"] == $chainPath){
					if($arSubSections[$arSection["IBLOCK_SECTION_ID"]]){
						foreach($arSubSections[$arSection["IBLOCK_SECTION_ID"]] as $arSubSection){
							if($curSectionID !== $arSubSection["ID"]){
								$arResult[] = array("NAME" => $arSubSection["NAME"], "LINK" => $arSubSection["SECTION_PAGE_URL"]);
							}
						}
					}
					break;
				}
			}
		}

		return $arResult;
	}

	public static function getSectionsIds_NotInRegion($iblockId = false, $regionId = false){
		static $arCache, $arIblockHasUFRegion;

		$arSectionsIds = array();

		if(!$iblockId){
			$iblockId = CNextCache::$arIBlocks[SITE_ID]['aspro_next_catalog']['aspro_next_catalog'][0];
		}

		if($iblockId){
			if(!isset($arIblockHasUFRegion)){
				$arIblockHasUFRegion = array();
			}

			if(!isset($arIblockHasUFRegion[$iblockId])){
				$arIblockHasUFRegion[$iblockId] = false;

				$rsData = \CUserTypeEntity::GetList(array('ID' => 'ASC'), array('ENTITY_ID' => 'IBLOCK_'.$iblockId.'_SECTION', 'FIELD_NAME' => 'UF_REGION'));
				if($arRes = $rsData->Fetch()){
					$arIblockHasUFRegion[$iblockId] = true;
				}
			}

			if($arIblockHasUFRegion[$iblockId]){
				if(!$regionId && $GLOBALS['arRegion']){
					$regionId = $GLOBALS['arRegion']['ID'];
				}

				if($regionId){
					if(!isset($arCache)){
						$arCache = array();
					}

					if(!isset($arCache[$iblockId])){
						$arCache[$iblockId] = array();
					}

					if(!isset($arCache[$iblockId][$regionId])){
						if($arSections = CNextCache::CIBLockSection_GetList(
							array(
								'CACHE' => array(
									'TAG' => CNextCache::GetIBlockCacheTag($iblockId),
									'MULTI' => 'Y'
								)
							),
							array(
								'IBLOCK_ID' => $iblockId,
								'!UF_REGION' => $regionId,
							),
							false,
							array(
								'ID',
								'RIGHT_MARGIN',
								'LEFT_MARGIN',
							),
							false
						)){
							$arSectionsIds = array_column($arSections, 'ID');

							if($arSectionsIds){
								if($arSectionsIds_ = CNextCache::CIBLockSection_GetList(
									array(
										'CACHE' => array(
											'TAG' => CNextCache::GetIBlockCacheTag($iblockId),
											'MULTI' => 'Y',
											'RESULT' => array('ID'),
										)
									),
									array(
										'IBLOCK_ID' => $iblockId,
										'ID' => $arSectionsIds,
										'UF_REGION' => $regionId,
									),
									false,
									array('ID'),
									false
								)){
									$arSectionsIds = array_diff($arSectionsIds, $arSectionsIds_);
								}
							}

							$arSubSectionsIds = array();
							foreach($arSections as $arSection){
								if(in_array($arSection['ID'], $arSectionsIds)){
									if(($arSection['LEFT_MARGIN'] + 1) < $arSection['RIGHT_MARGIN']){
										$arSubSectionsIds[] = $arSection['ID'];
									}
								}
							}

							while($arSubSectionsIds){
								if($arSections = CNextCache::CIBLockSection_GetList(
									array(
										'CACHE' => array(
											'TAG' => CNextCache::GetIBlockCacheTag($iblockId),
											'MULTI' => 'Y'
										)
									),
									array(
										'IBLOCK_ID' => $iblockId,
										'SECTION_ID' => $arSubSectionsIds,
									),
									false,
									array(
										'ID',
										'RIGHT_MARGIN',
										'LEFT_MARGIN',
									),
									false
								)){
									$arSubSectionsIds = array_column($arSections, 'ID');
									if($arSubSectionsIds){
										if($arSectionsIds_ = CNextCache::CIBLockSection_GetList(
											array(
												'CACHE' => array(
													'TAG' => CNextCache::GetIBlockCacheTag($iblockId),
													'MULTI' => 'Y',
													'RESULT' => array('ID'),
												)
											),
											array(
												'IBLOCK_ID' => $iblockId,
												'ID' => $arSubSectionsIds,
												'UF_REGION' => $regionId,
											),
											false,
											array('ID'),
											false
										)){
											$arSubSectionsIds = array_diff($arSubSectionsIds, $arSectionsIds_);
										}
									}

									if($arSubSectionsIds){
										$arSectionsIds = array_merge($arSectionsIds, $arSubSectionsIds);
									}

									$arSubSubSectionsIds = array();
									foreach($arSections as $arSection){
										if(in_array($arSection['ID'], $arSubSectionsIds)){
											if(($arSection['LEFT_MARGIN'] + 1) < $arSection['RIGHT_MARGIN']){
												$arSubSubSectionsIds[] = $arSection['ID'];
											}
										}
									}
									$arSubSectionsIds = $arSubSubSectionsIds;
								}
								else{
									$arSubSectionsIds = array();
								}
							}
						}

						$arCache[$iblockId][$regionId] = $arSectionsIds;
					}
					else{
						$arSectionsIds = $arCache[$iblockId][$regionId];
					}
				}
			}
		}

		return $arSectionsIds;
	}

	public static function makeSectionFilterInRegion(&$arFilter, $regionId = false){
		if(
			$GLOBALS['arTheme']['USE_REGIONALITY']['VALUE'] === 'Y' &&
			$GLOBALS['arTheme']['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_FILTER_ITEM']['VALUE'] === 'Y'
		){
			$iblockId = $arFilter['IBLOCK_ID'];
			if(!$iblockId){
				$iblockId = CNextCache::$arIBlocks[SITE_ID]['aspro_next_catalog']['aspro_next_catalog'][0];
			}

			if($iblockId){
				if(!$regionId && $GLOBALS['arRegion']){
					$regionId = $GLOBALS['arRegion']['ID'];
				}

				if($regionId){
					if($arSectionsIds = self::getSectionsIds_NotInRegion($arFilter['IBLOCK_ID'], $regionId)){
						$arFilter['!ID'] = $arSectionsIds;
					}
				}
			}
		}

		return $arFilter;
	}

	public static function makeElementFilterInRegion(&$arFilter, $regionId = false, $bSetLinkRegionFilter = false){
		if(
			$GLOBALS['arTheme']['USE_REGIONALITY']['VALUE'] === 'Y' &&
			$GLOBALS['arTheme']['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_FILTER_ITEM']['VALUE'] === 'Y'
		){
			$iblockId = $arFilter['IBLOCK_ID'];
			if(!$iblockId){
				$iblockId = CNextCache::$arIBlocks[SITE_ID]['aspro_next_catalog']['aspro_next_catalog'][0];
			}

			if($iblockId){
				if(!$regionId && $GLOBALS['arRegion']){
					$regionId = $GLOBALS['arRegion']['ID'];
				}

				if($regionId){
					if($arSectionsIds = self::getSectionsIds_NotInRegion($arFilter['IBLOCK_ID'], $regionId)){
						$arFilter['!IBLOCK_SECTION_ID'] = $arSectionsIds;
					}

					if($bSetLinkRegionFilter){
						if(self::isIblockHasPropertyLinkRegion($iblockId)){
							$arFilter['PROPERTY_LINK_REGION'] = $regionId;
						}
					}
				}
			}
		}
		return $arFilter;
	}

	public static function checkElementsIdsInRegion(&$arIds, $iblockId = false, $regionId = false){
		if(
			$GLOBALS['arTheme']['USE_REGIONALITY']['VALUE'] === 'Y' &&
			$GLOBALS['arTheme']['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_FILTER_ITEM']['VALUE'] === 'Y' &&
			$arIds
		){
			if(!$iblockId){
				$iblockId = CNextCache::$arIBlocks[SITE_ID]['aspro_next_catalog']['aspro_next_catalog'][0];
			}

			if($iblockId){
				if(!$regionId && $GLOBALS['arRegion']){
					$regionId = $GLOBALS['arRegion']['ID'];
				}

				if($regionId){
					if($arSectionsIds = self::getSectionsIds_NotInRegion($arFilter['IBLOCK_ID'], $regionId)){
						$arIds = CNextCache::CIBLockElement_GetList(
							array(
								'CACHE' => array(
									'TAG' => CNextCache::GetIBlockCacheTag($iblockId),
									'RESULT' => array('ID'),
									'MULTI' => 'Y',
								)
							),
							array(
								'ID' => $arIds,
								'IBLOCK_ID' => $iblockId,
								'!IBLOCK_SECTION_ID' => $arSectionsIds,
							),
							false,
							false,
							array('ID')
						);
					}
				}
			}
		}

		return $arIds;
	}

	public static function isIblockHasPropertyLinkRegion($iblockId){
		static $arIblockHasLinkRegion;

		if($iblockId){
			if(!isset($arIblockHasLinkRegion)){
				$arIblockHasLinkRegion = array();
			}

			if(!isset($arIblockHasLinkRegion[$iblockId])){
				$arIblockHasLinkRegion[$iblockId] = false;

				$rsData = \CIBlockProperty::GetList(array(), array('CODE' => 'LINK_REGION', 'IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'));
				if($arRes = $rsData->Fetch()){
					$arIblockHasLinkRegion[$iblockId] = true;
				}
			}

			return $arIblockHasLinkRegion[$iblockId];
		}

		return false;
	}

	public static function drawFormField($FIELD_SID, $arQuestion){
		?>
		<?$arQuestion["HTML_CODE"] = str_replace('name=', 'data-sid="'.$FIELD_SID.'" name=', $arQuestion["HTML_CODE"]);?>
		<?$arQuestion["HTML_CODE"] = str_replace('left', '', $arQuestion["HTML_CODE"]);?>
		<?$arQuestion["HTML_CODE"] = str_replace('size="0"', '', $arQuestion["HTML_CODE"]);?>
		<?if($arQuestion['STRUCTURE'][0]['FIELD_TYPE'] == 'hidden'):?>
			<?=$arQuestion["HTML_CODE"];?>
		<?else:?>
			<div class="form-control">
				<label><span><?=$arQuestion["CAPTION"]?><?=($arQuestion["REQUIRED"] == "Y" ? '&nbsp;<span class="star">*</span>' : '')?></span></label>
				<?
				if(strpos($arQuestion["HTML_CODE"], "class=") === false)
					$arQuestion["HTML_CODE"] = str_replace('input', 'input class=""', $arQuestion["HTML_CODE"]);

				if(is_array($arResult["FORM_ERRORS"]) && array_key_exists($FIELD_SID, $arResult['FORM_ERRORS']))
					$arQuestion["HTML_CODE"] = str_replace('class="', 'class="error ', $arQuestion["HTML_CODE"]);

				if($arQuestion["REQUIRED"] == "Y")
					$arQuestion["HTML_CODE"] = str_replace('name=', 'required name=', $arQuestion["HTML_CODE"]);

				if($arQuestion["STRUCTURE"][0]["FIELD_TYPE"] == "email")
					$arQuestion["HTML_CODE"] = str_replace('type="text"', 'type="email" placeholder="mail@domen.com"', $arQuestion["HTML_CODE"]);

				if((strpos($arQuestion["HTML_CODE"], "phone") !== false) || (strpos(strToLower($FIELD_SID), "phone") !== false))
					$arQuestion["HTML_CODE"] = str_replace('type="text"', 'type="tel"', $arQuestion["HTML_CODE"]);
				?>
				<?if($FIELD_SID == 'RATING'):?>
					<div class="votes_block nstar big with-text votes_block_review">
						<div class="ratings">
							<div class="inner_rating">
								<?for($i=1;$i<=5;$i++):?>
									<div class="item-rating" data-message="<?=GetMessage('RATING_MESSAGE_'.$i)?>"><?=CNext::showIconSvg("star", SITE_TEMPLATE_PATH."/images/svg/star_round.svg");?></div>
								<?endfor;?>
							</div>
						</div>
						<div class="rating_message muted" data-message="<?=GetMessage('RATING_MESSAGE_0')?>"><?=GetMessage('RATING_MESSAGE_0')?></div>
						<?=str_replace('type="text"', 'type="hidden"', $arQuestion["HTML_CODE"])?>
					</div>
				<?else:?>
					<?=$arQuestion["HTML_CODE"]?>
				<?endif;?>
			</div>
		<?endif;?>
		<?
	}

	public static function GetValidFormIDForSite(&$form_id){
		if(!is_numeric($form_id) && !in_array($form_id, array('auth', 'one_click_buy')))
		{
			\Bitrix\Main\Loader::includeModule('form');
			$rsForm = CForm::GetList($by = 'id', $order = 'asc', array('ACTIVE' => 'Y', 'SID' => $form_id, 'SID_EXACT_MATCH' => 'N', 'SITE' => array(SITE_ID)), $is_filtered);
			if($item = $rsForm->Fetch())
				$form_id = $item['ID'];
		}

		return $form_id;
	}

	public static function CheckTypeCount($totalCount){
		if(is_float($totalCount))
			return floatval($totalCount);
		else
			return intval($totalCount);
	}

	public static function GetTotalCount($arItem, $arParams = array()){
		$totalCount = 0;

		if(
			($arParams['USE_REGION'] == 'Y' || intval($arParams['USE_REGION']) > 0) &&
			$arParams['STORES']
		){
			$arSelect = array('ID', 'PRODUCT_AMOUNT');
			$arFilter = array('ACTIVE' => 'Y', 'ID' => $arParams['STORES']);

			if($arItem['OFFERS']){
				$arOffers = array_column($arItem['OFFERS'], 'ID');

				if($arOffers){
					$quantity = 0;

					$rsStore = CCatalogStore::GetList(array(), array_merge($arFilter, array('PRODUCT_ID' => $arOffers)), false, false, $arSelect);
					while($arStore = $rsStore->Fetch()){
						$quantity += $arStore['PRODUCT_AMOUNT'];
					}

					$totalCount = $quantity;
				}
			}
			elseif(
				isset($arItem['PRODUCT']['TYPE']) &&
				$arItem['PRODUCT']['TYPE'] == 2
			){
				if(!$arItem['SET_ITEMS']){
					$arItem['SET_ITEMS'] = array();

					if($arSets = CCatalogProductSet::getAllSetsByProduct($arItem['ID'], 1)){
						$arSets = reset($arSets);

						foreach($arSets['ITEMS'] as $v){
							$v['ID'] = $v['ITEM_ID'];
							unset($v['ITEM_ID']);
							$arItem['SET_ITEMS'][] = $v;
						}
					}
				}

			    $arProductSet = $arItem['SET_ITEMS'] ? array_column($arItem['SET_ITEMS'], 'ID') : array();

			    if($arProductSet){
					$arSelect[] = 'ELEMENT_ID';
					$quantity = array();

					$rsStore = CCatalogStore::GetList(array(), array_merge($arFilter, array('PRODUCT_ID' => $arProductSet)), false, false, $arSelect);
					while($arStore = $rsStore->Fetch()){
					    $quantity[$arStore['ELEMENT_ID']] += $arStore['PRODUCT_AMOUNT'];
					}

					if($quantity){
					    foreach($arItem['SET_ITEMS'] as $v) {
							$quantity[$v['ID']] /= $v['QUANTITY'];
							$quantity[$v['ID']] = floor($quantity[$v['ID']]);
					    }
					}
					$totalCount = min($quantity);
			    }
			}
			else{
				$rsStore = CCatalogStore::GetList(array(), array_merge($arFilter, array('PRODUCT_ID' => $arItem['ID'])), false, false, $arSelect);
				while($arStore = $rsStore->Fetch()){
					$quantity += $arStore['PRODUCT_AMOUNT'];
				}

				$totalCount = $quantity;
			}
		}
		else{
			if($arItem['OFFERS']){
				foreach($arItem['OFFERS'] as $arOffer)
					$totalCount += $arOffer['CATALOG_QUANTITY'];
			}
			else
				$totalCount += ($arItem['~CATALOG_QUANTITY'] != $arItem['CATALOG_QUANTITY'] ? $arItem['~CATALOG_QUANTITY'] : $arItem['CATALOG_QUANTITY']);
		}

		foreach(GetModuleEvents(ASPRO_NEXT_MODULE_ID, 'OnAsproGetTotalQuantity', true) as $arEvent) // event for manipulation total quantity
			ExecuteModuleEventEx($arEvent, array($arItem, $arParams, &$totalCount));

		return self::CheckTypeCount($totalCount);
	}

	public static function GetQuantityArray($totalCount, $arItemIDs = array(), $useStoreClick="N", $productType=1, $bShowAjaxItems = false, $dopClass = ''){
		if($productType==2){
		    return;
		}
		static $arQuantityOptions, $arQuantityRights;
		if($arQuantityOptions === NULL){
			$arQuantityOptions = array(
				"USE_WORD_EXPRESSION" => Option::get(self::moduleID, "USE_WORD_EXPRESSION", "Y", SITE_ID),
				"MAX_AMOUNT" => Option::get(self::moduleID, "MAX_AMOUNT", "10", SITE_ID),
				"MIN_AMOUNT" => Option::get(self::moduleID, "MIN_AMOUNT", "2", SITE_ID),
				"EXPRESSION_FOR_MIN" => Option::get(self::moduleID, "EXPRESSION_FOR_MIN", GetMessage("EXPRESSION_FOR_MIN_DEFAULT"), SITE_ID),
				"EXPRESSION_FOR_MID" => Option::get(self::moduleID, "EXPRESSION_FOR_MID", GetMessage("EXPRESSION_FOR_MID_DEFAULT"), SITE_ID),
				"EXPRESSION_FOR_MAX" => Option::get(self::moduleID, "EXPRESSION_FOR_MAX", GetMessage("EXPRESSION_FOR_MAX_DEFAULT"), SITE_ID),
				"EXPRESSION_FOR_EXISTS" => Option::get(self::moduleID, "EXPRESSION_FOR_EXISTS", GetMessage("EXPRESSION_FOR_EXISTS_DEFAULT"), SITE_ID),
				"EXPRESSION_FOR_NOTEXISTS" => Option::get(self::moduleID, "EXPRESSION_FOR_NOTEXISTS", GetMessage("EXPRESSION_FOR_NOTEXISTS_DEFAULT"), SITE_ID),
				"SHOW_QUANTITY_FOR_GROUPS" => (($tmp = Option::get(self::moduleID, "SHOW_QUANTITY_FOR_GROUPS", "", SITE_ID)) ? explode(",", $tmp) : array()),
				"SHOW_QUANTITY_COUNT_FOR_GROUPS" => (($tmp = Option::get(self::moduleID, "SHOW_QUANTITY_COUNT_FOR_GROUPS", "", SITE_ID)) ? explode(",", $tmp) : array()),
			);

			$arQuantityRights = array(
				"SHOW_QUANTITY" => false,
				"SHOW_QUANTITY_COUNT" => false,
			);

			global $USER;
			$res = CUser::GetUserGroupList(self::GetUserID());
			while ($arGroup = $res->Fetch()){
				if(in_array($arGroup["GROUP_ID"], $arQuantityOptions["SHOW_QUANTITY_FOR_GROUPS"])){
					$arQuantityRights["SHOW_QUANTITY"] = true;
				}
				if(in_array($arGroup["GROUP_ID"], $arQuantityOptions["SHOW_QUANTITY_COUNT_FOR_GROUPS"])){
					$arQuantityRights["SHOW_QUANTITY_COUNT"] = true;
				}
			}
		}

		$indicators = 0;
		$totalAmount = $totalText = $totalHTML = $totalHTMLs = '';

		if($arQuantityRights["SHOW_QUANTITY"]){
			if($totalCount > $arQuantityOptions["MAX_AMOUNT"]){
				$indicators = 3;
				$totalAmount = $arQuantityOptions["EXPRESSION_FOR_MAX"];
			}
			elseif($totalCount < $arQuantityOptions["MIN_AMOUNT"] && $totalCount > 0){
				$indicators = 1;
				$totalAmount = $arQuantityOptions["EXPRESSION_FOR_MIN"];
			}
			else{
				$indicators = 2;
				$totalAmount = $arQuantityOptions["EXPRESSION_FOR_MID"];
			}

			if($totalCount > 0){
				$totalText = $arQuantityOptions["EXPRESSION_FOR_EXISTS"];
			}else{
				if($useStoreClick=="Y"){
					$totalText = "<span class='store_view'>".$arQuantityOptions["EXPRESSION_FOR_NOTEXISTS"]."</span>";
				}else{
					$totalText = $arQuantityOptions["EXPRESSION_FOR_NOTEXISTS"];
				}
			}

			if($arQuantityRights["SHOW_QUANTITY_COUNT"] && $totalCount > 0){
				if($arQuantityOptions["USE_WORD_EXPRESSION"] == "Y"){
					if(strlen($totalAmount)){
						if($useStoreClick=="Y"){
							$totalText = "<span class='store_view'>".$totalAmount."</span>";
						}else{
							$totalText = $totalAmount;
						}
					}
				}
				else{
					if($useStoreClick=="Y"){
						$totalText .= (strlen($totalText) ? " <span class='store_view'>(".$totalCount.")</span>" : "<span class='store_view'>".$totalCount."</span>");
					}else{
						$totalText .= (strlen($totalText) ? " (".$totalCount.")" : $totalCount);
					}
				}
			}
			$totalHTMLs ='<div class="item-stock '.($bShowAjaxItems ? ' js-show-stores js-show-info-block ' : '').' '.$dopClass.'" '.($arItemIDs["ID"] ? 'data-id="'.$arItemIDs["ID"].'"' : '').' '.($arItemIDs["STORE_QUANTITY"] ? "id=".$arItemIDs["STORE_QUANTITY"] : "").'>';
			$totalHTMLs .= '<span class="icon '.$arClass[1].($totalCount > 0 ? 'stock stock_range_'.$indicators : ' order').'"></span><span class="value">'.$totalText.'</span>';
			$totalHTMLs .='</div>';
		}

		$arOptions = array("OPTIONS" => $arQuantityOptions, "RIGHTS" => $arQuantityRights, "TEXT" => $totalText, "INDICATORS" => $indicators, "HTML" => $totalHTMLs);

		foreach(GetModuleEvents(ASPRO_NEXT_MODULE_ID, 'OnAsproGetTotalQuantityBlock', true) as $arEvent) // event for manipulation store quantity block
			ExecuteModuleEventEx($arEvent, array($totalCount, &$arOptions));

		return $arOptions;
	}

	public static function GetAvailiableStore($totalCount = 0, $arItemIDs=array(), $detail=false){
		static $arQuantityOptions;
		if($arQuantityOptions === NULL){
			$arQuantityOptions = array(
				"EXPRESSION_FOR_EXISTS" => Option::get(self::moduleID, "EXPRESSION_FOR_EXISTS", GetMessage("EXPRESSION_FOR_EXISTS_DEFAULT"), SITE_ID),
				"EXPRESSION_FOR_NOTEXISTS" => Option::get(self::moduleID, "EXPRESSION_FOR_NOTEXISTS", GetMessage("EXPRESSION_FOR_NOTEXISTS_DEFAULT"), SITE_ID),
			);
		}
		$totalHTML='<div class="item-stock" '.($arItemIDs["STORE_QUANTITY"] ? "id=".$arItemIDs["STORE_QUANTITY"] : "").'>';
		if($totalCount){
			$totalHTML.='<span class="icon stock"></span><span>'.$arQuantityOptions["EXPRESSION_FOR_EXISTS"];
			if($detail=="Y"){
				$totalHTML.='<span class="store_link"> ('.$totalCount.')</span>';
			}else{
				$totalHTML.=' ('.$totalCount.')';
			}
			$totalHTML.='</span>';
		}else{
			$totalHTML.='<span class="icon order"></span><span>'.$arQuantityOptions["EXPRESSION_FOR_NOTEXISTS"].'</span>';
		}
		$totalHTML.='</div>';

		return array( "OPTIONS" => $arQuantityOptions, "HTML" => $totalHTML );
	}

	public static function GetPropertyViewType($IBLOCK_ID){
		global $DB;
		$IBLOCK_ID = intval($IBLOCK_ID);
		$SECTION_ID=64;
		// $IBLOCK_ID = 15;
        $result = array();
		/*$rs = $DB->Query($s = "
			SELECT
				B.SECTION_PROPERTY,
				BP.ID PROPERTY_ID,
				BSP.SECTION_ID LINK_ID,
				BSP.SMART_FILTER,
				BSP.DISPLAY_TYPE,
				BSP.DISPLAY_EXPANDED,
				BSP.FILTER_HINT,
				BP.SORT,
				BP.PROPERTY_TYPE,
				BP.USER_TYPE
			FROM
				b_iblock B
				INNER JOIN b_iblock_property BP ON BP.IBLOCK_ID = B.ID
				INNER JOIN b_iblock_section_property BSP ON  BSP.PROPERTY_ID = BP.ID
			WHERE
				B.ID = ".$IBLOCK_ID."
			ORDER BY
				BP.SORT ASC, BP.ID ASC
		");*/
		$rs = $DB->Query($s = "
			SELECT
                    B.SECTION_PROPERTY,
                    BP.ID PROPERTY_ID,
                    BSP.SECTION_ID LINK_ID,
                    BSP.SMART_FILTER,
                    BSP.DISPLAY_TYPE,
                    BSP.DISPLAY_EXPANDED,
                    BSP.FILTER_HINT,
                    BP.SORT,
                    BS.LEFT_MARGIN,
                    BS.NAME LINK_TITLE,
                    BP.PROPERTY_TYPE,
                    BP.USER_TYPE
                FROM
                    b_iblock B
                    INNER JOIN b_iblock_property BP ON BP.IBLOCK_ID = B.ID
                    INNER JOIN b_iblock_section M ON M.ID = ".$SECTION_ID."
                    INNER JOIN b_iblock_section BS ON BS.IBLOCK_ID = M.IBLOCK_ID
                        AND M.LEFT_MARGIN >= BS.LEFT_MARGIN
                        AND M.RIGHT_MARGIN <= BS.RIGHT_MARGIN
                    INNER JOIN b_iblock_section_property BSP ON BSP.IBLOCK_ID = BS.IBLOCK_ID AND BSP.SECTION_ID = BS.ID AND BSP.PROPERTY_ID = BP.ID
                WHERE
                    B.ID = ".$IBLOCK_ID."
                ORDER BY
                    BP.SORT ASC, BP.ID ASC, BS.LEFT_MARGIN DESC
		");
		while ($ar = $rs->Fetch()){
			$result[$ar["PROPERTY_ID"]] = array(
				"PROPERTY_ID" => $ar["PROPERTY_ID"],
				"SMART_FILTER" => $ar["SMART_FILTER"],
				"DISPLAY_TYPE" => $ar["DISPLAY_TYPE"],
				"DISPLAY_EXPANDED" => $ar["DISPLAY_EXPANDED"],
				"FILTER_HINT" => $ar["FILTER_HINT"],
				"INHERITED_FROM" => $ar["LINK_ID"],
				"SORT" => $ar["SORT"],
				"PROPERTY_TYPE" => $ar["PROPERTY_TYPE"],
			);
		}
		return $result;
	}

	public static function GetSKUPropsArray(&$arSkuProps, $iblock_id=0, $type_view="list", $hide_title_props="N", $group_iblock_id="N", $arItem = array(), $offerShowPreviewPictureProps = array()){
		$arSkuTemplate = array();
		$class_title=($hide_title_props=="Y" ? "hide_class" : "show_class");
		$class_title.=' bx_item_section_name';
		if($iblock_id){
			//$arPropsSku=CNext::GetPropertyViewType($iblock_id);
			$arPropsSku=CIBlockSectionPropertyLink::GetArray($iblock_id);
			if($arPropsSku){
				foreach ($arSkuProps as $key=>$arProp){
					if($arPropsSku[$arProp["ID"]]){
						$arSkuProps[$key]["DISPLAY_TYPE"]=$arPropsSku[$arProp["ID"]]["DISPLAY_TYPE"];
					}
				}
			}
		}?>

		<?
		$bTextViewProp = (Option::get(self::moduleID, "VIEW_TYPE_HIGHLOAD_PROP", "N", SITE_ID) == "Y");

		$arCurrentOffer = $arItem['OFFERS'][$arItem['OFFERS_SELECTED']];
		$j = 0;
		$arFilter = $arShowValues = array();

		/*get correct values*/
		foreach ($arSkuProps as $key => $arProp){
			$strName = 'PROP_'.$arProp['ID'];
			$arShowValues = self::GetRowValues($arFilter, $strName, $arItem);

			if(in_array($arCurrentOffer['TREE'][$strName], $arShowValues))
			{
				$arFilter[$strName] = $arCurrentOffer['TREE'][$strName];
			}
			else
			{
				$arFilter[$strName] = $arShowValues[0];
			}

			/*if($arParams['SHOW_ABSENT'])
			{*/
				$arCanBuyValues = $tmpFilter = array();
				$tmpFilter = $arFilter;
				foreach($arShowValues as $value)
				{
					$tmpFilter[$strName] = $value;
					if(self::GetCanBuy($tmpFilter, $arItem))
					{
						$arCanBuyValues[] = $value;
					}
				}
			/*}
			else
			{
				$arCanBuyValues = $arShowValues;
			}*/

			$arSkuProps[$key] = self::UpdateRow($arFilter[$strName], $arShowValues, $arCanBuyValues, $arProp, $type_view);
		}
		/**/

		if($group_iblock_id=="Y"){
			foreach ($arSkuProps as $iblockId => $skuProps){
				$arSkuTemplate[$iblockId] = array();
				$j = 0;
				foreach ($skuProps as $key=>&$arProp){
					$templateRow = '';
					$class_title.= (($arProp["HINT"] && $arProp["SHOW_HINTS"] == "Y") ? ' whint char_name' : '');
					$hint_block = (($arProp["HINT"] && $arProp["SHOW_HINTS"]=="Y") ? '<div class="hint"><span class="icon"><i>?</i></span><div class="tooltip">'.$arProp["HINT"].'</div></div>' : '');
					if(($arProp["DISPLAY_TYPE"]=="P" || $arProp["DISPLAY_TYPE"]=="R" ) && $type_view!= 'block' ){
						$templateRow .= '<div class="bx_item_detail_size" '.$arProp['STYLE'].' id="#ITEM#_prop_'.$arProp['ID'].'_cont" data-display_type="SELECT" data-id="'.$arProp['ID'].'">'.
		'<span class="'.$class_title.'">'.$hint_block.'<span>'.htmlspecialcharsex($arProp['NAME']).'</span></span>'.
		'<div class="bx_size_scroller_container form-control bg"><div class="bx_size"><select id="#ITEM#_prop_'.$arProp['ID'].'_list" class="list_values_wrapper">';
						foreach ($arProp['VALUES'] as $arOneValue){
							//if($arOneValue['ID']>0){
								$arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
								$templateRow .= '<option '.$arOneValue['SELECTED'].' '.$arOneValue['DISABLED'].' data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-showtype="select" data-onevalue="'.$arOneValue['ID'].'" ';
								if($arProp["DISPLAY_TYPE"]=="R"){
									$templateRow .= 'data-img_src="'.$arOneValue["PICT"]["SRC"].'" ';
								}
								$templateRow .= 'title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'">';
								$templateRow .= '<span class="cnt">'.$arOneValue['NAME'].'</span>';
								$templateRow .= '</option>';
							//}
						}
						$templateRow .= '</select></div>'.
		'</div></div>';
					}elseif ('TEXT' == $arProp['SHOW_MODE']){
						$templateRow .= '<div class="bx_item_detail_size" '.$arProp['STYLE'].' id="#ITEM#_prop_'.$arProp['ID'].'_cont" data-display_type="LI" data-id="'.$arProp['ID'].'">'.
		'<span class="'.$class_title.'">'.$hint_block.'<span>'.htmlspecialcharsex($arProp['NAME']).'</span></span>'.
		'<div class="bx_size_scroller_container"><div class="bx_size"><ul id="#ITEM#_prop_'.$arProp['ID'].'_list" class="list_values_wrapper">';
						foreach ($arProp['VALUES'] as $arOneValue){
							//if($arOneValue['ID']>0){
								$arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
								$templateRow .= '<li class="item '.$arOneValue['CLASS'].'" '.$arOneValue['STYLE'].' data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-showtype="li" data-onevalue="'.$arOneValue['ID'].'" title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'"><i></i><span class="cnt">'.$arOneValue['NAME'].'</span></li>';
							//}
						}
						$templateRow .= '</ul></div>'.
		'</div></div>';
					}elseif ('PICT' == $arProp['SHOW_MODE']){
						$arCurrentTree = array();
						if($offerShowPreviewPictureProps && is_array($offerShowPreviewPictureProps)){
							if(in_array($arProp['CODE'], $offerShowPreviewPictureProps)){
								if($arCurrentOffer && $arCurrentOffer['TREE']){
									$arCurrentTree = $arCurrentOffer['TREE'];
								}
							}
						}

						$isHasPicture = true;
						foreach($arProp['VALUES'] as &$arOneValue){
							$boolOneSearch = false;
							if($arCurrentTree && $arOneValue['ID'] != 0){
								$arRowTree = $arCurrentTree;
								$arRowTree['PROP_'.$arProp['ID']] = $arOneValue['ID'];

								foreach($arItem['OFFERS'] as &$arOffer){
									$boolOneSearch = true;
									foreach($arRowTree as $rkey => $rval){
										if($rval !== $arOffer['TREE'][$rkey]){
											$boolOneSearch = false;
											break;
										}
									}
									if($boolOneSearch){
										if($arOffer['PREVIEW_PICTURE_FIELD'] && is_array($arOffer['PREVIEW_PICTURE_FIELD']) && $arOffer['PREVIEW_PICTURE_FIELD']['SRC']){
											$arOneValue['NEW_PICT'] = $arOffer['PREVIEW_PICTURE_FIELD'];
										}
										else{
											$boolOneSearch = false;
										}
										break;
									}
								}
								unset($arOffer);
							}

							if(!$boolOneSearch){
								//if($arOneValue['ID']>0){
									if(!isset($arOneValue['PICT']['SRC']) || !$arOneValue['PICT']['SRC'])
									{
										if(!$bTextViewProp)
										{
											$arOneValue['PICT']['SRC'] = SITE_TEMPLATE_PATH.'/images/no_photo_small.png';
											$arOneValue['NO_PHOTO'] = 'Y';
										}
										else
										{
											$isHasPicture = false;
										}
									}
								//}
							}
						}
						unset($arOneValue);

						if($isHasPicture)
						{
							$templateRow .= '<div class="bx_item_detail_scu" '.$arProp['STYLE'].' id="#ITEM#_prop_'.$arProp['ID'].'_cont" data-display_type="LI" data-id="'.$arProp['ID'].'">'.
		'<span class="'.$class_title.'">'.$hint_block.'<span>'.htmlspecialcharsex($arProp['NAME']).'</span></span>'.
		'<div class="bx_scu_scroller_container"><div class="bx_scu"><ul id="#ITEM#_prop_'.$arProp['ID'].'_list" class="list_values_wrapper">';
						}
						else
						{
							$templateRow .= '<div class="bx_item_detail_size" '.$arProp['STYLE'].' id="#ITEM#_prop_'.$arProp['ID'].'_cont" data-display_type="LI" data-id="'.$arProp['ID'].'">'.
		'<span class="'.$class_title.'">'.htmlspecialcharsex($arProp['NAME']).'</span>'.
		'<div class="bx_size_scroller_container"><div class="bx_size"><ul id="#ITEM#_prop_'.$arProp['ID'].'_list" class="list_values_wrapper">';
						}
						foreach ($arProp['VALUES'] as $arOneValue){
							//if($arOneValue['ID']>0){
								$arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
								if($arOneValue['NAME'] == "Базовый") $arOneValue['NAME'] = "Более 14 мес";
								// if($isHasPicture && ($arOneValue['NEW_PICT'] || (isset($arOneValue['PICT']['SRC']) && $arOneValue['PICT']['SRC'])))
								// {
								// 	$str = '<span class="cnt1"><span class="cnt_item'.($arOneValue['NEW_PICT'] ? ' pp' : '').'" style="background-image:url(\''.($arOneValue['NEW_PICT'] ? $arOneValue['NEW_PICT']['SRC'] : $arOneValue['PICT']['SRC']).'\');" data-obgi="url(\''.$arOneValue['PICT']['SRC'].'\')" title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'"></span></span>';
								// 	if(isset($arOneValue['NO_PHOTO']) && $arOneValue['NO_PHOTO'] == 'Y')
								// 		$str = '<span class="cnt1 nf"><span class="cnt_item" title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'"><span class="bg" style="background-image:url(\''.$arOneValue['PICT']['SRC'].'\');"></span></span></span>';
								// 	$templateRow .= '<li class="item '.$arOneValue['CLASS'].'" '.$arOneValue['STYLE'].' data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-showtype="li" data-onevalue="'.$arOneValue['ID'].'"><i title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'"></i>'.$str.'</li>';
								// }
								// else
								// {
									$templateRow .= '<li class="item '.$arOneValue['CLASS'].'" '.$arOneValue['STYLE'].' data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-showtype="li" data-onevalue="'.$arOneValue['ID'].'" title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'"><i></i><span class="cnt">'.$arOneValue['NAME'].'</span></li>';
								// }
							//}
						}
						$templateRow .= '</ul></div>'.
		'</div></div>';
					}
					$arSkuTemplate[$iblockId][$arProp['CODE']] = $templateRow;
				}
			}
		}else{
			foreach ($arSkuProps as $key=>&$arProp){
				$templateRow = '';
				$class_title.= (($arProp["HINT"] && $arProp["SHOW_HINTS"] == "Y") ? ' whint char_name' : '');
				$hint_block = (($arProp["HINT"] && $arProp["SHOW_HINTS"]=="Y") ? '<div class="hint"><span class="icon"><i>?</i></span><div class="tooltip">'.$arProp["HINT"].'</div></div>' : '');
				if(($arProp["DISPLAY_TYPE"]=="P" || $arProp["DISPLAY_TYPE"]=="R" ) && $type_view!= 'block' ){
					$templateRow .= '<div class="bx_item_detail_size" '.$arProp['STYLE'].' id="#ITEM#_prop_'.$arProp['ID'].'_cont" data-display_type="SELECT" data-id="'.$arProp['ID'].'">'.
	'<span class="'.$class_title.'">'.$hint_block.'<span>'.htmlspecialcharsex($arProp['NAME']).'</span></span>'.
	'<div class="bx_size_scroller_container form-control bg"><div class="bx_size"><select id="#ITEM#_prop_'.$arProp['ID'].'_list" class="list_values_wrapper">';
					foreach ($arProp['VALUES'] as $arOneValue){
						//if($arOneValue['ID']>0){
							$arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
							$templateRow .= '<option '.$arOneValue['SELECTED'].' '.$arOneValue['DISABLED'].' data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-showtype="select" data-onevalue="'.$arOneValue['ID'].'" ';
							if($arProp["DISPLAY_TYPE"]=="R"){
								$templateRow .= 'data-img_src="'.$arOneValue["PICT"]["SRC"].'" ';
							}
							$templateRow .= 'title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'">';
							$templateRow .= '<span class="cnt">'.$arOneValue['NAME'].'</span>';
							$templateRow .= '</option>';
						//}
					}
					$templateRow .= '</select></div>'.
	'</div></div>';
				}elseif ('TEXT' == $arProp['SHOW_MODE']){
					$templateRow .= '<div class="bx_item_detail_size" '.$arProp['STYLE'].' id="#ITEM#_prop_'.$arProp['ID'].'_cont" data-display_type="LI" data-id="'.$arProp['ID'].'">'.
	'<span class="'.$class_title.'">'.$hint_block.'<span>'.htmlspecialcharsex($arProp['NAME']).'</span></span>'.
	'<div class="bx_size_scroller_container"><div class="bx_size"><ul id="#ITEM#_prop_'.$arProp['ID'].'_list" class="list_values_wrapper">';
					foreach ($arProp['VALUES'] as $arOneValue){
						//if($arOneValue['ID']>0){
							$arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
							$templateRow .= '<li class="item '.$arOneValue['CLASS'].'" '.$arOneValue['STYLE'].' data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-showtype="li" data-onevalue="'.$arOneValue['ID'].'" title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'"><i></i><span class="cnt">'.$arOneValue['NAME'].'</span></li>';
						//}
					}
					$templateRow .= '</ul></div>'.
	'</div></div>';
				}elseif ('PICT' == $arProp['SHOW_MODE']){
					$arCurrentTree = array();
					if($offerShowPreviewPictureProps && is_array($offerShowPreviewPictureProps)){
						if(in_array($arProp['CODE'], $offerShowPreviewPictureProps)){
							if($arCurrentOffer && $arCurrentOffer['TREE']){
								$arCurrentTree = $arCurrentOffer['TREE'];
							}
						}
					}

					$isHasPicture = true;
					foreach($arProp['VALUES'] as &$arOneValue){
						$boolOneSearch = false;
						if($arCurrentTree && $arOneValue['ID'] != 0){
							$arRowTree = $arCurrentTree;
							$arRowTree['PROP_'.$arProp['ID']] = $arOneValue['ID'];

							foreach($arItem['OFFERS'] as &$arOffer){
								$boolOneSearch = true;
								foreach($arRowTree as $rkey => $rval){
									if($rval !== $arOffer['TREE'][$rkey]){
										$boolOneSearch = false;
										break;
									}
								}
								if($boolOneSearch){
									if($arOffer['PREVIEW_PICTURE_FIELD'] && is_array($arOffer['PREVIEW_PICTURE_FIELD']) && $arOffer['PREVIEW_PICTURE_FIELD']['SRC']){
										$arOneValue['NEW_PICT'] = $arOffer['PREVIEW_PICTURE_FIELD'];
									}
									else{
										$boolOneSearch = false;
									}
									break;
								}
							}
							unset($arOffer);
						}

						if(!$boolOneSearch){
							//if($arOneValue['ID']>0){
								if(!isset($arOneValue['PICT']['SRC']) || !$arOneValue['PICT']['SRC'])
								{
									if(!$bTextViewProp)
									{
										$arOneValue['PICT']['SRC'] = SITE_TEMPLATE_PATH.'/images/no_photo_small.png';
										$arOneValue['NO_PHOTO'] = 'Y';
									}
									else
									{
										$isHasPicture = false;
									}
								}
							//}
						}

						foreach($arItem['OFFERS'] as &$arOffer)
						{
							if($arRowTree['PROP_'.$arProp['ID']] == $arOffer['TREE']['PROP_'.$arProp['ID']] && !$boolOneSearch)
							{
								if($arOffer['PREVIEW_PICTURE_FIELD'] && is_array($arOffer['PREVIEW_PICTURE_FIELD']) && $arOffer['PREVIEW_PICTURE_FIELD']['SRC'])
									$arOneValue['NEW_PICT'] = $arOffer['PREVIEW_PICTURE_FIELD'];
								break;
							}
						}
					}
					unset($arOneValue);

					if($isHasPicture)
					{
						$templateRow .= '<div class="bx_item_detail_scu" '.$arProp['STYLE'].' id="#ITEM#_prop_'.$arProp['ID'].'_cont" data-display_type="LI" data-id="'.$arProp['ID'].'">'.
	'<span class="'.$class_title.'">'.$hint_block.'<span>'.htmlspecialcharsex($arProp['NAME']).'</span></span>'.
	'<div class="bx_scu_scroller_container"><div class="bx_scu"><ul id="#ITEM#_prop_'.$arProp['ID'].'_list" class="list_values_wrapper">';
					}
					else
					{
						$templateRow .= '<div class="bx_item_detail_size" '.$arProp['STYLE'].' id="#ITEM#_prop_'.$arProp['ID'].'_cont" data-display_type="LI" data-id="'.$arProp['ID'].'">'.
	'<span class="'.$class_title.'">'.htmlspecialcharsex($arProp['NAME']).'</span>'.
	'<div class="bx_size_scroller_container"><div class="bx_size"><ul id="#ITEM#_prop_'.$arProp['ID'].'_list" class="list_values_wrapper">';

					}
					foreach ($arProp['VALUES'] as $arOneValue){
						//if($arOneValue['ID']>0){
							$arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
							if($arOneValue['NAME'] == "Базовый") $arOneValue['NAME'] = "Более 14 мес";
							// if($isHasPicture && ($arOneValue['NEW_PICT'] || (isset($arOneValue['PICT']['SRC']) && $arOneValue['PICT']['SRC'])))
							// {
							// 	$str = '<span class="cnt1"><span class="cnt_item'.($arOneValue['NEW_PICT'] ? ' pp' : '').'" style="background-image:url(\''.($arOneValue['NEW_PICT'] ? $arOneValue['NEW_PICT']['SRC'] : $arOneValue['PICT']['SRC']).'\');" data-obgi="url(\''.$arOneValue['PICT']['SRC'].'\')" title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'"></span></span>';
							// 	if(isset($arOneValue['NO_PHOTO']) && $arOneValue['NO_PHOTO'] == 'Y')
							// 		$str = '<span class="cnt1 nf"><span class="cnt_item" title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'"><span class="bg" style="background-image:url(\''.$arOneValue['PICT']['SRC'].'\');"></span></span></span>';
							// 	$templateRow .= '<li class="item '.$arOneValue['CLASS'].'" '.$arOneValue['STYLE'].' data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-showtype="li" data-onevalue="'.$arOneValue['ID'].'"><i title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'"></i>'.$str.'</li>';
							// }
							// else
							// {
								$templateRow .= '<li class="item '.$arOneValue['CLASS'].'" '.$arOneValue['STYLE'].' data-treevalue="'.$arProp['ID'].'_'.$arOneValue['ID'].'" data-showtype="li" data-onevalue="'.$arOneValue['ID'].'" title="'.$arProp['NAME'].': '.$arOneValue['NAME'].'"><i></i><span class="cnt">'.$arOneValue['NAME'].'</span></li>';
							// }
						//}
					}
					$templateRow .= '</ul></div>'.
	'</div></div>';
				}

				$arSkuTemplate[$arProp['CODE']] = $templateRow;
			}
		}
		unset($templateRow, $arProp);
		return $arSkuTemplate;
	}

	public static function UpdateRow($arFilter, $arShowValues, $arCanBuyValues, $arProp, $type_view){
		$isCurrent = false;
		$showI = 0;

		if($arProp['VALUES']){
			foreach($arProp['VALUES'] as $key => $arValue)
			{
				$value = $arValue['ID'];
				$isCurrent = ($value === $arFilter);
				$selectMode = (($arProp["DISPLAY_TYPE"] == "P" || $arProp["DISPLAY_TYPE"] == "R" ) && $type_view != 'block' );

				if(in_array($value, $arCanBuyValues))
				{
					$arProp['VALUES'][$key]['CLASS'] = ($isCurrent ? 'active' : '');
				}
				else
				{
					$arProp['VALUES'][$key]['CLASS'] = ($isCurrent ? 'active missing' : 'missing');
					// $arProp['VALUES'][$key]['CLASS'] = ($isCurrent ? 'active' : '');
				}
				if($selectMode)
				{
					$arProp['VALUES'][$key]['DISABLED'] = 'disabled';
					$arProp['VALUES'][$key]['SELECTED'] = ($isCurrent ? 'selected' : '');
				}
				else
				{
					$arProp['VALUES'][$key]['STYLE'] = 'style="display: none"';
				}

				if(in_array($value, $arShowValues))
				{
					if($selectMode)
					{
						$arProp['VALUES'][$key]['DISABLED'] = '';
					}
					else
					{
						$arProp['VALUES'][$key]['STYLE'] = '';
					}

					if($value != 0)
						++$showI;
				}
			}

			if(!$showI){
				$arProp['STYLE'] = 'style="display: none"';
			}
			else{
				$arProp['STYLE'] = 'style=""';
			}
		}

		return $arProp;
	}

	public static function GetRowValues($arFilter, $index, $arItem){
		$i = 0;
		$arValues = array();
		$boolSearch = false;
		$boolOneSearch = true;

		if(!$arFilter)
		{
			if($arItem['OFFERS']){
				foreach($arItem['OFFERS'] as $arOffer)
				{
					if(!in_array($arOffer['TREE'][$index], $arValues))
					{
						$arValues[] = $arOffer['TREE'][$index];
					}
				}
			}
			$boolSearch = true;
		}
		else
		{
			if($arItem['OFFERS']){
				foreach($arItem['OFFERS'] as $arOffer)
				{
					$boolOneSearch = true;
					foreach($arFilter as $propName => $filter)
					{
						if ($filter !== $arOffer['TREE'][$propName])
						{
							$boolOneSearch = false;
							break;
						}
					}
					if ($boolOneSearch)
					{
						if(!in_array($arOffer['TREE'][$index], $arValues))
						{
							$arValues[] = $arOffer['TREE'][$index];
						}
						$boolSearch = true;
					}
				}
			}
		}
		return ($boolSearch ? $arValues : false);
	}

	public static function GetCanBuy($arFilter, $arItem){
		$i = 0;
		$boolSearch = false;
		$boolOneSearch = true;

		foreach($arItem['OFFERS'] as $arOffer)
		{
			$boolOneSearch = true;
			foreach($arFilter as $propName => $filter)
			{
				if ($filter !== $arOffer['TREE'][$propName])
				{
					$boolOneSearch = false;
					break;
				}
			}
			if($boolOneSearch)
			{
				if($arOffer['CAN_BUY'])
				{
					$boolSearch = true;
					break;
				}
			}
		}
		return $boolSearch;
	}

	public static function GetItemsIDs($arItem, $detail="N"){
		$arAllIDs=array();
		$arAllIDs["strMainID"] = $arItem['strMainID'];
		$arAllIDs["strObName"] = 'ob'.preg_replace("/[^a-zA-Z0-9_]/", "x", $arAllIDs["strMainID"]);

		if($detail=="Y"){
			$arAllIDs["ALL_ITEM_IDS"] = array(
				'ID' => $arAllIDs["strMainID"],
				'PICT' => $arAllIDs["strMainID"].'_pict',
				'DISCOUNT_PICT_ID' => $arAllIDs["strMainID"].'_dsc_pict',
				'STICKER_ID' => $arAllIDs["strMainID"].'_sticker',
				'BIG_SLIDER_ID' => $arAllIDs["strMainID"].'_big_slider',
				'BIG_IMG_CONT_ID' => $arAllIDs["strMainID"].'_bigimg_cont',
				'SLIDER_CONT_ID' => $arAllIDs["strMainID"].'_slider_cont',
				'SLIDER_LIST' => $arAllIDs["strMainID"].'_slider_list',
				'SLIDER_LEFT' => $arAllIDs["strMainID"].'_slider_left',
				'SLIDER_RIGHT' => $arAllIDs["strMainID"].'_slider_right',
				'OLD_PRICE' => $arAllIDs["strMainID"].'_old_price',
				'PRICE' => $arAllIDs["strMainID"].'_price',
				'DISCOUNT_PRICE' => $arAllIDs["strMainID"].'_price_discount',
				'SLIDER_CONT_OF_ID' => $arAllIDs["strMainID"].'_slider_cont_',
				'SLIDER_LIST_OF_ID' => $arAllIDs["strMainID"].'_slider_list_',
				'SLIDER_LEFT_OF_ID' => $arAllIDs["strMainID"].'_slider_left_',
				'SLIDER_RIGHT_OF_ID' => $arAllIDs["strMainID"].'_slider_right_',
				'SLIDER_CONT_OFM_ID' => $arAllIDs["strMainID"].'_sliderm_cont_',
				'SLIDER_LIST_OFM_ID' => $arAllIDs["strMainID"].'_sliderm_list_',
				'SLIDER_LEFT_OFM_ID' => $arAllIDs["strMainID"].'_sliderm_left_',
				'SLIDER_RIGHT_OFM_ID' => $arAllIDs["strMainID"].'_sliderm_right_',
				'QUANTITY' => $arAllIDs["strMainID"].'_quantity',
				'QUANTITY_DOWN' => $arAllIDs["strMainID"].'_quant_down',
				'QUANTITY_UP' => $arAllIDs["strMainID"].'_quant_up',
				'QUANTITY_MEASURE' => $arAllIDs["strMainID"].'_quant_measure',
				'QUANTITY_LIMIT' => $arAllIDs["strMainID"].'_quant_limit',
				'BASIS_PRICE' => $arAllIDs["strMainID"].'_basis_price',
				'BUY_LINK' => $arAllIDs["strMainID"].'_buy_link',
				'BASKET_LINK' => $arAllIDs["strMainID"].'_basket_link',
				'ADD_BASKET_LINK' => $arAllIDs["strMainID"].'_add_basket_link',
				'BASKET_ACTIONS' => $arAllIDs["strMainID"].'_basket_actions',
				'NOT_AVAILABLE_MESS' => $arAllIDs["strMainID"].'_not_avail',
				'COMPARE_LINK' => $arAllIDs["strMainID"].'_compare_link',
				'PROP' => $arAllIDs["strMainID"].'_prop_',
				'PROP_DIV' => $arAllIDs["strMainID"].'_skudiv',
				'DISPLAY_PROP_DIV' => $arAllIDs["strMainID"].'_sku_prop',
				'DISPLAY_PROP_ARTICLE_DIV' => $arAllIDs["strMainID"].'_sku_article_prop',
				'OFFER_GROUP' => $arAllIDs["strMainID"].'_set_group_',
				'BASKET_PROP_DIV' => $arAllIDs["strMainID"].'_basket_prop',
				'SUBSCRIBE_DIV' => $arAllIDs["strMainID"].'_subscribe_div',
				'SUBSCRIBED_DIV' => $arAllIDs["strMainID"].'_subscribed_div',
				'STORE_QUANTITY' => $arAllIDs["strMainID"].'_store_quantity',
			);
		}else{
			$arAllIDs["ALL_ITEM_IDS"] = array(
				'ID' => $arAllIDs["strMainID"],
				'PICT' => $arAllIDs["strMainID"].'_pict',
				'SECOND_PICT' => $arAllIDs["strMainID"].'_secondpict',
				'STICKER_ID' => $arAllIDs["strMainID"].'_sticker',
				'SECOND_STICKER_ID' => $arAllIDs["strMainID"].'_secondsticker',
				'QUANTITY' => $arAllIDs["strMainID"].'_quantity',
				'QUANTITY_DOWN' => $arAllIDs["strMainID"].'_quant_down',
				'QUANTITY_UP' => $arAllIDs["strMainID"].'_quant_up',
				'QUANTITY_MEASURE' => $arAllIDs["strMainID"].'_quant_measure',
				'BUY_LINK' => $arAllIDs["strMainID"].'_buy_link',
				'BASKET_LINK' => $arAllIDs["strMainID"].'_basket_link',
				'BASKET_ACTIONS' => $arAllIDs["strMainID"].'_basket_actions',
				'NOT_AVAILABLE_MESS' => $arAllIDs["strMainID"].'_not_avail',
				'SUBSCRIBE_LINK' => $arAllIDs["strMainID"].'_subscribe',
				'COMPARE_LINK' => $arAllIDs["strMainID"].'_compare_link',
				'STORE_QUANTITY' => $arAllIDs["strMainID"].'_store_quantity',
				'PRICE' => $arAllIDs["strMainID"].'_price',
				'PRICE_OLD' => $arAllIDs["strMainID"].'_price_old',
				'DSC_PERC' => $arAllIDs["strMainID"].'_dsc_perc',
				'SECOND_DSC_PERC' => $arAllIDs["strMainID"].'_second_dsc_perc',
				'PROP_DIV' => $arAllIDs["strMainID"].'_sku_tree',
				'PROP' => $arAllIDs["strMainID"].'_prop_',
				'DISPLAY_PROP_DIV' => $arAllIDs["strMainID"].'_sku_prop',
				'BASKET_PROP_DIV' => $arAllIDs["strMainID"].'_basket_prop',
				'SUBSCRIBE_DIV' => $arAllIDs["strMainID"].'subscribe_div',
				'SUBSCRIBED_DIV' => $arAllIDs["strMainID"].'subscribed_div',
			);
		}

		$arAllIDs["TITLE_ITEM"] = (
			isset($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])&& $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] != ''
			? $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
			: $arItem['NAME']
		);
		return $arAllIDs;
	}

	public static function GetSKUJSParams($arResult, $arParams, $arItem, $detail="N", $group_iblock_id="N"){
		$arSkuProps = array();

		if($group_iblock_id=="Y"){
			$arResult['SKU_PROPS']=reset($arResult['SKU_PROPS']);
		}

		foreach ($arResult['SKU_PROPS'] as $arOneProp){
			if (!isset($arItem['OFFERS_PROP'][$arOneProp['CODE']]))
				continue;
			if($detail == "Y")
				$display_type = ((($arOneProp['DISPLAY_TYPE'] == "P" || $arOneProp['DISPLAY_TYPE'] == "R") && $arParams["DISPLAY_TYPE"] != 'block' ) ? "SELECT" : "LI" );
			else
				$display_type = ((($arItem['OFFERS_PROPS_JS'][$arOneProp['CODE']]['DISPLAY_TYPE'] == "P" || $arItem['OFFERS_PROPS_JS'][$arOneProp['CODE']]['DISPLAY_TYPE'] == "R") && $arParams["DISPLAY_TYPE"] != 'block' ) ? "SELECT" : "LI" );
			$arSkuProps[] = array(
				'ID' => $arOneProp['ID'],
				'CODE' => $arOneProp['CODE'],
				'SHOW_MODE' => $arOneProp['SHOW_MODE'],
				'VALUES_COUNT' => $arOneProp['VALUES_COUNT'],
				'DISPLAY_TYPE' => $display_type,
			);
		}

		foreach ($arItem['JS_OFFERS'] as &$arOneJs){
			if (0 < $arOneJs['PRICE']['DISCOUNT_DIFF_PERCENT'])
			{
				$arOneJs['PRICE']['DISCOUNT_DIFF_PERCENT'] = '-'.$arOneJs['PRICE']['DISCOUNT_DIFF_PERCENT'].'%';
				$arOneJs['BASIS_PRICE']['DISCOUNT_DIFF_PERCENT'] = '-'.$arOneJs['BASIS_PRICE']['DISCOUNT_DIFF_PERCENT'].'%';
			}
		}
		unset($arOneJs);
		if ($arItem['OFFERS_PROPS_DISPLAY']){
			foreach ($arItem['JS_OFFERS'] as $keyOffer => $arJSOffer){
				$strProps = '';
				$arArticle=array();
				if (!empty($arJSOffer['DISPLAY_PROPERTIES'])){
					foreach ($arJSOffer['DISPLAY_PROPERTIES'] as $arOneProp){
						if($arOneProp['CODE']=='ARTICLE'){
							$arArticle=$arOneProp;
							continue;
						}
						$strProps .= '<tr itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue"><td><span itemprop="name">'.$arOneProp['NAME'].'</span></td><td><span itemprop="value">'.(
							is_array($arOneProp['VALUE'])
							? implode(' / ', $arOneProp['VALUE'])
							: $arOneProp['VALUE']
						).'</span></td></tr>';

					}
				}
				if($arArticle){
					$strArticle = '';
					$strArticle .= $arArticle['NAME'].': '.(
							is_array($arArticle['VALUE'])
							? implode(' / ', $arArticle['VALUE'])
							: $arArticle['VALUE']
						);
					$arItem['JS_OFFERS'][$keyOffer]['ARTICLE'] = $strArticle;
				}

				$arItem['JS_OFFERS'][$keyOffer]['DISPLAY_PROPERTIES'] = $strProps;

			}
		}
		if ($arItem['SHOW_OFFERS_PROPS']){
			foreach ($arItem['JS_OFFERS'] as $keyOffer => $arJSOffer){
				$strProps = '';
				if (!empty($arJSOffer['DISPLAY_PROPERTIES'])){
					foreach ($arJSOffer['DISPLAY_PROPERTIES'] as $arOneProp){
						if($arOneProp['VALUE']){
							$arOneProp['VALUE_FORMAT']='<span class="block_title" itemprop="name">'.$arOneProp['NAME'].': </span><span class="value" itemprop="value">'.$arOneProp['VALUE'].'</span>';
							if($arOneProp['CODE']!='ARTICLE'){
								$strProps .='<tr itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue"><td class="char_name"><div class="props_item"><span itemprop="name">'.$arOneProp['NAME'].'</span></div></td><td class="char_value"><span itemprop="value">'.$arOneProp['VALUE'].'</span></td></tr>';
							}
						}
						$arItem['JS_OFFERS'][$keyOffer]['DISPLAY_PROPERTIES_CODE'][$arOneProp["CODE"]] = $arOneProp;
					}
				}
				$arItem['JS_OFFERS'][$keyOffer]['TABLE_PROP']=$strProps;
			}
			foreach ($arItem['JS_OFFERS'] as $keyOffer => $arJSOffer){
				if (!empty($arJSOffer['DISPLAY_PROPERTIES'])){
					foreach ($arJSOffer['DISPLAY_PROPERTIES'] as $keyProp => $arOneProp){
						if($arOneProp['VALUE']){
							if($arOneProp['CODE']=='ARTICLE')
								unset($arItem['JS_OFFERS'][$keyOffer]['DISPLAY_PROPERTIES'][$keyProp]);
						}
					}
				}
			}
		}

		$arItemIDs=self::GetItemsIDs($arItem);
		if($detail=="Y"){
			$arJSParams = array(
				'CONFIG' => array(
					'USE_CATALOG' => $arResult['CATALOG'],
					'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
					'SHOW_PRICE' => true,
					'SHOW_DISCOUNT_PERCENT' => ($arParams['SHOW_DISCOUNT_PERCENT'] == 'Y'),
					'SHOW_OLD_PRICE' => ($arParams['SHOW_OLD_PRICE'] == 'Y'),
					'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
					'SHOW_SKU_PROPS' => $arItem['SHOW_OFFERS_PROPS'],
					'OFFER_GROUP' => $arItem['OFFER_GROUP'],
					'MAIN_PICTURE_MODE' => $arParams['DETAIL_PICTURE_MODE'],
					'SHOW_BASIS_PRICE' => ($arParams['SHOW_BASIS_PRICE'] == 'Y'),
					'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
					'SHOW_CLOSE_POPUP' => ($arParams['SHOW_CLOSE_POPUP'] == 'Y')
				),
				'SHOW_UNABLE_SKU_PROPS' => $arParams['SHOW_UNABLE_SKU_PROPS'],
				'PRODUCT_TYPE' => $arResult['CATALOG_TYPE'],
				'VISUAL' => array(
					'ID' => $arItemIDs["ALL_ITEM_IDS"]['ID'],
				),
				'DEFAULT_COUNT' => $arParams['DEFAULT_COUNT'],
				'DEFAULT_PICTURE' => array(
					'PREVIEW_PICTURE' => $arResult['DEFAULT_PICTURE'],
					'DETAIL_PICTURE' => $arResult['DEFAULT_PICTURE']
				),
				'STORE_QUANTITY' => $arItemIDs["ALL_ITEM_IDS"]['STORE_QUANTITY'],
				'PRODUCT' => array(
					'ID' => $arResult['ID'],
					'NAME' => $arResult['~NAME'],
				),
				'BASKET' => array(
					'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
					'BASKET_URL' => $arParams['BASKET_URL'],
					'SKU_PROPS' => $arItem['OFFERS_PROP_CODES'],
					'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
					'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
				),
				'OFFERS' => $arItem['JS_OFFERS'],
				'OFFER_SELECTED' => $arItem['OFFERS_SELECTED'],
				'SKU_DETAIL_ID' => $arParams['SKU_DETAIL_ID'],
				'TREE_PROPS' => $arSkuProps
			);
		}else{
			$arJSParams = array(
				'SHOW_UNABLE_SKU_PROPS' => $arParams['SHOW_UNABLE_SKU_PROPS'],
				'PRODUCT_TYPE' => $arItem['CATALOG_TYPE'],
				'SHOW_QUANTITY' => ($arParams['USE_PRODUCT_QUANTITY'] == 'Y'),
				'DEFAULT_COUNT' => $arParams['DEFAULT_COUNT'],
				'SHOW_ADD_BASKET_BTN' => false,
				'SHOW_BUY_BTN' => true,
				'SHOW_ABSENT' => true,
				'SHOW_SKU_PROPS' => $arItem['OFFERS_PROPS_DISPLAY'],
				'SECOND_PICT' => $arItem['SECOND_PICT'],
				'SHOW_OLD_PRICE' => ('Y' == $arParams['SHOW_OLD_PRICE']),
				'SHOW_DISCOUNT_PERCENT' => ('Y' == $arParams['SHOW_DISCOUNT_PERCENT']),
				'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
				'SHOW_CLOSE_POPUP' => ($arParams['SHOW_CLOSE_POPUP'] == 'Y'),
				'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
				'BASKET_URL' => $arParams['BASKET_URL'],
				'DEFAULT_PICTURE' => array(
					'PICTURE' => $arItem['PRODUCT_PREVIEW'],
					'PICTURE_SECOND' => $arItem['PRODUCT_PREVIEW_SECOND']
				),
				'VISUAL' => array(
					'ID' => $arItemIDs["ALL_ITEM_IDS"]['ID'],
					'PICT_ID' => $arItemIDs["ALL_ITEM_IDS"]['PICT'],
					'SECOND_PICT_ID' => $arItemIDs["ALL_ITEM_IDS"]['SECOND_PICT'],
					'QUANTITY_ID' => $arItemIDs["ALL_ITEM_IDS"]['QUANTITY'],
					'QUANTITY_UP_ID' => $arItemIDs["ALL_ITEM_IDS"]['QUANTITY_UP'],
					'QUANTITY_DOWN_ID' => $arItemIDs["ALL_ITEM_IDS"]['QUANTITY_DOWN'],
					'QUANTITY_MEASURE' => $arItemIDs["ALL_ITEM_IDS"]['QUANTITY_MEASURE'],
					'STORE_QUANTITY' => $arItemIDs["ALL_ITEM_IDS"]['STORE_QUANTITY'],
					'PRICE_ID' => $arItemIDs["ALL_ITEM_IDS"]['PRICE'],
					'PRICE_OLD_ID' => $arItemIDs["ALL_ITEM_IDS"]['PRICE_OLD'],
					'TREE_ID' => $arItemIDs["ALL_ITEM_IDS"]['PROP_DIV'],
					'TREE_ITEM_ID' => $arItemIDs["ALL_ITEM_IDS"]['PROP'],
					'BUY_ID' => $arItemIDs["ALL_ITEM_IDS"]['BUY_LINK'],
					'BASKET_LINK' => $arItemIDs["ALL_ITEM_IDS"]['BASKET_LINK'],
					'ADD_BASKET_ID' => $arItemIDs["ALL_ITEM_IDS"]['ADD_BASKET_ID'],
					'DSC_PERC' => $arItemIDs["ALL_ITEM_IDS"]['DSC_PERC'],
					'SECOND_DSC_PERC' => $arItemIDs["ALL_ITEM_IDS"]['SECOND_DSC_PERC'],
					'DISPLAY_PROP_DIV' => $arItemIDs["ALL_ITEM_IDS"]['DISPLAY_PROP_DIV'],
					'BASKET_ACTIONS_ID' => $arItemIDs["ALL_ITEM_IDS"]['BASKET_ACTIONS'],
					'NOT_AVAILABLE_MESS' => $arItemIDs["ALL_ITEM_IDS"]['NOT_AVAILABLE_MESS'],
					'COMPARE_LINK_ID' => $arItemIDs["ALL_ITEM_IDS"]['COMPARE_LINK'],
					'SUBSCRIBE_ID' => $arItemIDs["ALL_ITEM_IDS"]['SUBSCRIBE_DIV'],
					'SUBSCRIBED_ID' => $arItemIDs["ALL_ITEM_IDS"]['SUBSCRIBED_DIV'],
				),
				'BASKET' => array(
					'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
					'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
					'SKU_PROPS' => $arItem['OFFERS_PROP_CODES'],
					'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
					'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
				),
				'PRODUCT' => array(
					'ID' => $arItem['ID'],
					'NAME' => $arItemIDs["TITLE_ITEM"]
				),
				'OFFERS' => $arItem['JS_OFFERS'],
				'OFFER_SELECTED' => $arItem['OFFERS_SELECTED'],
				'TREE_PROPS' => $arSkuProps,
				'LAST_ELEMENT' => $arItem['LAST_ELEMENT']
			);
		}
		$arJSParams['SHOW_DISCOUNT_PERCENT_NUMBER'] = $arParams['SHOW_DISCOUNT_PERCENT_NUMBER'];
		$arJSParams['OFFER_SHOW_PREVIEW_PICTURE_PROPS'] = $arParams['OFFER_SHOW_PREVIEW_PICTURE_PROPS'];
		if ($arParams['DISPLAY_COMPARE']){
			$arJSParams['COMPARE'] = array(
				'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
				'COMPARE_URL_TEMPLATE_DEL' => str_replace("ADD_TO_COMPARE_LIST", "DELETE_FROM_COMPARE_LIST", $arResult['~COMPARE_URL_TEMPLATE']),
				'COMPARE_PATH' => $arParams['COMPARE_PATH']
			);
		}

		// fix google`s links indexing (google parse js). Templates values must be not empty!
		$arJSParams['BASKET']['ADD_URL_TEMPLATE'] = $arJSParams['BASKET']['BUY_URL_TEMPLATE'] = ' ';
		if ($arParams['DISPLAY_COMPARE']){
			$arJSParams['COMPARE']['COMPARE_URL_TEMPLATE'] = $arJSParams['COMPARE']['COMPARE_URL_TEMPLATE_DEL'] = ' ';
		}

		return $arJSParams;
	}

	public static function GetAddToBasketArray(&$arItem, $totalCount = 0, $defaultCount = 1, $basketUrl = '', $bDetail = false, $arItemIDs = array(), $class_btn = "small", $arParams=array()){
		static $arAddToBasketOptions, $bUserAuthorized;
		if($arAddToBasketOptions === NULL){
			$arAddToBasketOptions = array(
				"SHOW_BASKET_ONADDTOCART" => Option::get(self::moduleID, "SHOW_BASKET_ONADDTOCART", "Y", SITE_ID) == "Y",
				"USE_PRODUCT_QUANTITY_LIST" => Option::get(self::moduleID, "USE_PRODUCT_QUANTITY_LIST", "Y", SITE_ID) == "Y",
				"USE_PRODUCT_QUANTITY_DETAIL" => Option::get(self::moduleID, "USE_PRODUCT_QUANTITY_DETAIL", "Y", SITE_ID) == "Y",
				"BUYNOPRICEGGOODS" => Option::get(self::moduleID, "BUYNOPRICEGGOODS", "NOTHING", SITE_ID),
				"BUYMISSINGGOODS" => Option::get(self::moduleID, "BUYMISSINGGOODS", "ADD", SITE_ID),
				"EXPRESSION_ORDER_BUTTON" => Option::get(self::moduleID, "EXPRESSION_ORDER_BUTTON", GetMessage("EXPRESSION_ORDER_BUTTON_DEFAULT"), SITE_ID),
				"EXPRESSION_ORDER_TEXT" => Option::get(self::moduleID, "EXPRESSION_ORDER_TEXT", GetMessage("EXPRESSION_ORDER_TEXT_DEFAULT"), SITE_ID),
				"EXPRESSION_SUBSCRIBE_BUTTON" => Option::get(self::moduleID, "EXPRESSION_SUBSCRIBE_BUTTON", GetMessage("EXPRESSION_SUBSCRIBE_BUTTON_DEFAULT"), SITE_ID),
				"EXPRESSION_SUBSCRIBED_BUTTON" => Option::get(self::moduleID, "EXPRESSION_SUBSCRIBED_BUTTON", GetMessage("EXPRESSION_SUBSCRIBED_BUTTON_DEFAULT"), SITE_ID),
				"EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT" => Option::get(self::moduleID, "EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT", GetMessage("EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT"), SITE_ID),
				"EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT" => Option::get(self::moduleID, "EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT", GetMessage("EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT"), SITE_ID),
				"EXPRESSION_READ_MORE_OFFERS_DEFAULT" => Option::get(self::moduleID, "EXPRESSION_READ_MORE_OFFERS_DEFAULT", GetMessage("EXPRESSION_READ_MORE_OFFERS_DEFAULT"), SITE_ID),
			);

			global $USER;
			$bUserAuthorized = $USER->IsAuthorized();
		}
		$buttonText = $buttonHTML = $buttonACTION = '';
		$quantity=$ratio=1;
		$max_quantity=0;
		$float_ratio=is_double($arItem["CATALOG_MEASURE_RATIO"]);

		if($arItem["CATALOG_MEASURE_RATIO"]){
			$quantity=$arItem["CATALOG_MEASURE_RATIO"];
			$ratio=$arItem["CATALOG_MEASURE_RATIO"];
		}else{
			$quantity=$defaultCount;
		}
		if($arItem["CATALOG_QUANTITY_TRACE"]=="Y"){
			if($totalCount < $quantity){
				$quantity=($totalCount>$arItem["CATALOG_MEASURE_RATIO"] ? $totalCount : $arItem["CATALOG_MEASURE_RATIO"] );
			}
			$max_quantity=$totalCount;
		}

		$canBuy = $arItem["CAN_BUY"];
		if($arParams['USE_REGION'] == 'Y' && $arParams['STORES'])
		{
			$canBuy = ($totalCount || ((!$totalCount && $arItem["CATALOG_QUANTITY_TRACE"] == "N") || (!$totalCount && $arItem["CATALOG_QUANTITY_TRACE"] == "Y" && $arItem["CATALOG_CAN_BUY_ZERO"] == "Y")));
		}
		$arItem["CAN_BUY"] = $canBuy;

		$arItemProps = $arItem['IS_OFFER'] === 'Y' ? ($arParams['OFFERS_CART_PROPERTIES'] ? implode(';', $arParams['OFFERS_CART_PROPERTIES']) : "") : ($arParams['PRODUCT_PROPERTIES'] ? implode(';', $arParams['PRODUCT_PROPERTIES']) : "");
		$partProp=($arParams["PARTIAL_PRODUCT_PROPERTIES"] ? $arParams["PARTIAL_PRODUCT_PROPERTIES"] : "" );
		$addProp=($arParams["ADD_PROPERTIES_TO_BASKET"] ? $arParams["ADD_PROPERTIES_TO_BASKET"] : "" );
		$emptyProp=$arItem["EMPTY_PROPS_JS"];
		if($arItem["OFFERS"]){
			global $arTheme;
			$type_sku = is_array($arTheme) ? (isset($arTheme["TYPE_SKU"]["VALUE"]) ? $arTheme["TYPE_SKU"]["VALUE"] : $arTheme["TYPE_SKU"]) : 'TYPE_1';
			if(!$bDetail && $arItem["OFFERS_MORE"] != "Y" && $type_sku != "TYPE_2"){
				$buttonACTION = 'ADD';
				$buttonText = array($arAddToBasketOptions['EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT'], $arAddToBasketOptions['EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT']);
				$buttonHTML = '<span class="btn btn-default transition_bg '.$class_btn.' read_more1 to-cart animate-load" id="'.$arItemIDs['BUY_LINK'].'" data-offers="N" data-iblockID="'.$arItem["IBLOCK_ID"].'" data-item="'.$arItem["ID"].'"><i></i><span>'.$buttonText[0].'</span></span><a rel="nofollow" href="'.$basketUrl.'" id="'.$arItemIDs['BASKET_LINK'].'" class="'.$class_btn.' in-cart btn btn-default transition_bg" data-item="'.$arItem["ID"].'"  style="display:none;"><i></i><span>'.$buttonText[1].'</span></a>';

			}
			elseif(($bDetail && $arItem["FRONT_CATALOG"] == "Y") || $arItem["OFFERS_MORE"]=="Y" || $type_sku == "TYPE_2"){
				$buttonACTION = 'MORE';
				$buttonText = array($arAddToBasketOptions['EXPRESSION_READ_MORE_OFFERS_DEFAULT']);
				$buttonHTML = '<a class="btn btn-default basket read_more" rel="nofollow" href="'.$arItem["DETAIL_PAGE_URL"].'" data-item="'.$arItem["ID"].'">'.$buttonText[0].'</a>';
			}

		}
		// elseif(isset($arItem["PRODUCT"]["TYPE"]) && $arItem["PRODUCT"]["TYPE"] == 2 && !$bDetail) {
		// 	// ONLY MORE BUTTON FOR PRODUCTS WITH TYPE 2
		// 	$buttonACTION = 'MORE';
		// 	$buttonText = array($arAddToBasketOptions['EXPRESSION_READ_MORE_OFFERS_DEFAULT']);
		// 	$buttonHTML = '<a class="btn btn-default basket read_more" rel="nofollow" href="'.$arItem["DETAIL_PAGE_URL"].'" data-item="'.$arItem["ID"].'">'.$buttonText[0].'</a>';
		// }
		else{
			if($bPriceExists = isset($arItem["MIN_PRICE"]) && $arItem["MIN_PRICE"]["VALUE"] > 0){
				// price exists

				if($totalCount > 0){
					// rest exists
					if((isset($arItem["CAN_BUY"]) && $arItem["CAN_BUY"]) || (isset($arItem["MIN_PRICE"]) && $arItem["MIN_PRICE"]["CAN_BUY"] == "Y")){
						if($bDetail && $arItem["FRONT_CATALOG"] == "Y"){
							$buttonACTION = 'MORE';
							$buttonText = array($arAddToBasketOptions['EXPRESSION_READ_MORE_OFFERS_DEFAULT']);
							$rid=($arItem["RID"] ? "?RID=".$arItem["RID"] : "");
							$buttonHTML = '<a class="btn btn-default transition_bg basket read_more" rel="nofollow" href="'.$arItem["DETAIL_PAGE_URL"].$rid.'" data-item="'.$arItem["ID"].'">'.$buttonText[0].'</a>';
						}
						else{

							$arItem["CAN_BUY"] = 1;
							$buttonACTION = 'ADD';
							$buttonText = array($arAddToBasketOptions['EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT'], $arAddToBasketOptions['EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT']);
							$buttonHTML = '<span data-value="'.$arItem["MIN_PRICE"]["DISCOUNT_VALUE"].'" data-currency="'.$arItem["MIN_PRICE"]["CURRENCY"].'" class="'.$class_btn.' to-cart btn btn-default transition_bg animate-load" data-item="'.$arItem["ID"].'" data-float_ratio="'.$float_ratio.'" data-ratio="'.$ratio.'" data-bakset_div="bx_basket_div_'.$arItem["ID"].'" data-props="'.$arItemProps.'" data-part_props="'.$partProp.'" data-add_props="'.$addProp.'"  data-empty_props="'.$emptyProp.'" data-offers="'.$arItem["IS_OFFER"].'" data-iblockID="'.$arItem["IBLOCK_ID"].'"  data-quantity="'.$quantity.'"><i></i><span>'.$buttonText[0].'</span></span><a rel="nofollow" href="'.$basketUrl.'" class="'.$class_btn.' in-cart btn btn-default transition_bg" data-item="'.$arItem["ID"].'"  style="display:none;"><i></i><span>'.$buttonText[1].'</span></a>';
						}
					}
					elseif($arItem["CATALOG_SUBSCRIBE"] == "Y"){
						$buttonACTION = 'SUBSCRIBE';
						$buttonText = array($arAddToBasketOptions['EXPRESSION_SUBSCRIBE_BUTTON'], $arAddToBasketOptions['EXPRESSION_SUBSCRIBED_BUTTON']);
						$buttonHTML = '<span class="'.$class_btn.' ss to-subscribe'.(!$bUserAuthorized ? ' auth' : '').(self::checkVersionModule('16.5.3', 'catalog') ? ' nsubsc' : '').' btn btn-default transition_bg" rel="nofollow" data-param-form_id="subscribe" data-name="subscribe" data-param-id="'.$arItem["ID"].'" data-item="'.$arItem["ID"].'"><i></i><span>'.$buttonText[0].'</span></span><span class="'.$class_btn.' ss in-subscribe btn btn-default transition_bg" rel="nofollow" style="display:none;" data-item="'.$arItem["ID"].'"><i></i><span>'.$buttonText[1].'</span></span>';
					}
				}
				else{
					if(!strlen($arAddToBasketOptions['EXPRESSION_ORDER_BUTTON'])){
						$arAddToBasketOptions['EXPRESSION_ORDER_BUTTON']=GetMessage("EXPRESSION_ORDER_BUTTON_DEFAULT");
					}
					// no rest
					if($bDetail && $arItem["FRONT_CATALOG"] == "Y"){
						$buttonACTION = 'MORE';
						$buttonText = array($arAddToBasketOptions['EXPRESSION_READ_MORE_OFFERS_DEFAULT']);
						$rid=($arItem["RID"] ? "?RID=".$arItem["RID"] : "");
						$buttonHTML = '<a class="btn btn-default basket read_more" rel="nofollow" href="'.$arItem["DETAIL_PAGE_URL"].$rid.'" data-item="'.$arItem["ID"].'">'.$buttonText[0].'</a>';
					}
					else{
						$buttonACTION = $arAddToBasketOptions["BUYMISSINGGOODS"];
						if($arAddToBasketOptions["BUYMISSINGGOODS"] == "ADD" /*|| $arItem["CAN_BUY"]*/){
							if($arItem["CAN_BUY"]){
								$buttonText = array($arAddToBasketOptions['EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT'], $arAddToBasketOptions['EXPRESSION_ADDEDTOBASKET_BUTTON_DEFAULT']);
								$buttonHTML = '<span data-value="'.$arItem["MIN_PRICE"]["DISCOUNT_VALUE"].'" data-currency="'.$arItem["MIN_PRICE"]["CURRENCY"].'" class="'.$class_btn.' to-cart btn btn-default transition_bg animate-load" data-item="'.$arItem["ID"].'" data-float_ratio="'.$float_ratio.'" data-ratio="'.$ratio.'" data-bakset_div="bx_basket_div_'.$arItem["ID"].'" data-props="'.$arItemProps.'" data-part_props="'.$partProp.'" data-add_props="'.$addProp.'"  data-empty_props="'.$emptyProp.'" data-offers="'.$arItem["IS_OFFER"].'" data-iblockID="'.$arItem["IBLOCK_ID"].'" data-quantity="'.$quantity.'"><i></i><span>'.$buttonText[0].'</span></span><a rel="nofollow" href="'.$basketUrl.'" class="'.$class_btn.' in-cart btn btn-default transition_bg" data-item="'.$arItem["ID"].'"  style="display:none;"><i></i><span>'.$buttonText[1].'</span></a>';
							}else{
								if($arAddToBasketOptions["BUYMISSINGGOODS"] == "SUBSCRIBE" && $arItem["CATALOG_SUBSCRIBE"] == "Y"){
									$buttonText = array($arAddToBasketOptions['EXPRESSION_SUBSCRIBE_BUTTON'], $arAddToBasketOptions['EXPRESSION_SUBSCRIBED_BUTTON']);
									$buttonHTML = '<span class="'.$class_btn.' ss to-subscribe'.(!$bUserAuthorized ? ' auth' : '').(self::checkVersionModule('16.5.3', 'catalog') ? ' nsubsc' : '').' btn btn-default transition_bg" rel="nofollow" data-name="subscribe" data-param-form_id="subscribe" data-param-id="'.$arItem["ID"].'"  data-item="'.$arItem["ID"].'"><i></i><span>'.$buttonText[0].'</span></span><span class="'.$class_btn.' ss in-subscribe btn btn-default transition_bg" rel="nofollow" style="display:none;" data-item="'.$arItem["ID"].'"><i></i><span>'.$buttonText[1].'</span></span>';
								}else{
									$buttonText = array($arAddToBasketOptions['EXPRESSION_ORDER_BUTTON']);
									$buttonHTML = '<span class="'.$class_btn.' to-order btn btn-default white grey transition_bg transparent animate-load" data-event="jqm" data-param-form_id="TOORDER" data-name="toorder" data-autoload-product_name="'.self::formatJsName($arItem["NAME"]).'" data-autoload-product_id="'.$arItem["ID"].'"><i></i><span>'.$buttonText[0].'</span></span>';
									if($arAddToBasketOptions['EXPRESSION_ORDER_TEXT']){
										$buttonHTML .='<div class="more_text">'.$arAddToBasketOptions['EXPRESSION_ORDER_TEXT'].'</div>';
									}
								}
							}

						}
						elseif($arAddToBasketOptions["BUYMISSINGGOODS"] == "SUBSCRIBE" && $arItem["CATALOG_SUBSCRIBE"] == "Y"){
							$buttonText = array($arAddToBasketOptions['EXPRESSION_SUBSCRIBE_BUTTON'], $arAddToBasketOptions['EXPRESSION_SUBSCRIBED_BUTTON']);
							$buttonHTML = '<span class="'.$class_btn.' ss to-subscribe'.(!$bUserAuthorized ? ' auth' : '').(self::checkVersionModule('16.5.3', 'catalog') ? ' nsubsc' : '').' btn btn-default transition_bg" data-name="subscribe" data-param-form_id="subscribe" data-param-id="'.$arItem["ID"].'"  rel="nofollow" data-item="'.$arItem["ID"].'"><i></i><span>'.$buttonText[0].'</span></span><span class="'.$class_btn.' ss in-subscribe btn btn-default transition_bg" rel="nofollow" style="display:none;" data-item="'.$arItem["ID"].'"><i></i><span>'.$buttonText[1].'</span></span>';
						}
						elseif($arAddToBasketOptions["BUYMISSINGGOODS"] == "ORDER"){
							$buttonText = array($arAddToBasketOptions['EXPRESSION_ORDER_BUTTON']);
							$buttonHTML = '<span class="'.$class_btn.' to-order btn btn-default white grey transition_bg transparent animate-load" data-event="jqm" data-param-form_id="TOORDER" data-name="toorder" data-autoload-product_name="'.self::formatJsName($arItem["NAME"]).'" data-autoload-product_id="'.$arItem["ID"].'"><i></i><span>'.$buttonText[0].'</span></span>';
							if($arAddToBasketOptions['EXPRESSION_ORDER_TEXT']){
								$buttonHTML .='<div class="more_text">'.$arAddToBasketOptions['EXPRESSION_ORDER_TEXT'].'</div>';
							}
						}
					}
				}
			}
			else{
				// no price or price <= 0
				if($bDetail && $arItem["FRONT_CATALOG"] == "Y"){
					$buttonACTION = 'MORE';
					$buttonText = array($arAddToBasketOptions['EXPRESSION_READ_MORE_OFFERS_DEFAULT']);
					$buttonHTML = '<a class="btn btn-default transition_bg basket read_more" rel="nofollow" href="'.$arItem["DETAIL_PAGE_URL"].'" data-item="'.$arItem["ID"].'">'.$buttonText[0].'</a>';
				}
				else{
					$buttonACTION = $arAddToBasketOptions["BUYNOPRICEGGOODS"];
					if($arAddToBasketOptions["BUYNOPRICEGGOODS"] == "ORDER"){
						$buttonText = array($arAddToBasketOptions['EXPRESSION_ORDER_BUTTON']);
						$buttonHTML = '<span class="'.$class_btn.' to-order btn btn-default white grey transition_bg transparent animate-load" data-event="jqm" data-param-form_id="TOORDER" data-name="toorder" data-autoload-product_name="'.self::formatJsName($arItem["NAME"]).'" data-autoload-product_id="'.$arItem["ID"].'"><i></i><span>'.$buttonText[0].'</span></span>';
						if($arAddToBasketOptions['EXPRESSION_ORDER_TEXT']){
							$buttonHTML .='<div class="more_text">'.$arAddToBasketOptions['EXPRESSION_ORDER_TEXT'].'</div>';
						}
					}
				}
			}
		}

		$arOptions = array("OPTIONS" => $arAddToBasketOptions, "TEXT" => $buttonText, "HTML" => $buttonHTML, "ACTION" => $buttonACTION, "RATIO_ITEM" => $ratio, "MIN_QUANTITY_BUY" => $quantity, "MAX_QUANTITY_BUY" => $max_quantity, "CAN_BUY" => $canBuy);

		foreach(GetModuleEvents(ASPRO_NEXT_MODULE_ID, 'OnAsproGetBuyBlockElement', true) as $arEvent) // event for manipulation with buy block element
			ExecuteModuleEventEx($arEvent, array($arItem, $totalCount, $arParams, &$arOptions));

		return $arOptions;
	}

	public static function checkShowDelay($arParams, $quantity, $arItem = array()){
		$bShowBlock = true;
		if($arParams['USE_REGION'] == 'Y')
		{
			if($arItem)
			{
				$canBuy = $arItem["CAN_BUY"];
				if($arParams['STORES'])
					$canBuy = ($quantity || ((!$quantity && $arItem["CATALOG_QUANTITY_TRACE"] == "N") || (!$quantity && $arItem["CATALOG_QUANTITY_TRACE"] == "Y" && $arItem["CATALOG_CAN_BUY_ZERO"] == "Y")));
				if(!$canBuy)
					$bShowBlock = false;
			}
			elseif(!$quantity)
				$bShowBlock = false;
		}
		return $bShowBlock;
	}

	public static function checkVersionExt($template="main", $module="catalog"){
		if($info = CModule::CreateModuleObject($module)){
			$testVersion = '16.0.14';
			if(CheckVersion($testVersion, $info->MODULE_VERSION)){
				$templateInclude=$template;
			}
			else{
				$templateInclude=$template."_new";
			}
		}
		return $templateInclude;
	}

	public static function checkVersionModule($version = '1.0.0', $module="catalog"){
		if($info = CModule::CreateModuleObject($module))
		{
			if(!CheckVersion($version, $info->MODULE_VERSION))
				return true;
		}
		return false;
	}

	public static function GetFileInfo($arItem){
		$arTmpItem = CFile::GetFileArray($arItem);
		switch($arTmpItem["CONTENT_TYPE"]){
			case 'application/pdf': $type="pdf"; break;
			case 'application/vnd.ms-excel': $type="excel"; break;
			case 'application/vnd.ms-office': $type="excel"; break;
			case 'application/xls': $type="excel"; break;
			case 'application/octet-stream': $type="word"; break;
			case 'application/msword': $type="word"; break;
			case 'image/jpeg': $type="jpg"; break;
			case 'image/tiff': $type="tiff"; break;
			case 'image/png': $type="png"; break;
			default: $type="default"; break;
		}
		if($type == "default")
		{
			$frm = explode('.', $arTmpItem['FILE_NAME']);
			$frm = $frm[1];
			if($frm == 'doc' || $frm == 'docx')
				$type = 'doc';
			elseif($frm == 'xls' || $frm == 'xlsx')
				$type = 'xls';
			elseif($frm == 'jpg' || $frm == 'jpeg')
				$type = 'jpg';
			elseif($frm == 'png')
				$type = 'png';
			elseif($frm == 'ppt' || $frm == 'pptx')
				$type = 'ppt';
			elseif($frm == 'tif')
				$type = 'tif';
			elseif($frm == 'pdf')
				$type = 'pdf';
			else
				$type = 'txt';
		}

		$filesize = $arTmpItem["FILE_SIZE"];
		if($filesize > 1024){
			$filesize = ($filesize / 1024);
			if($filesize > 1024){
				$filesize = ($filesize / 1024);
				if($filesize > 1024){
					$filesize = ($filesize / 1024);
					$filesize = round($filesize, 1);
					$filesize_format=str_replace(".", ",", $filesize).GetMessage('CT_NAME_GB');
				}
				else{
					$filesize = round($filesize, 1);
					$filesize_format=str_replace(".", ",", $filesize).GetMessage('CT_NAME_MB');
				}
			}
			else{
				$filesize = round($filesize, 1);
				$filesize_format=str_replace(".", ",", $filesize).GetMessage('CT_NAME_KB');
			}
		}
		else{
			$filesize = round($filesize, 1);
			$filesize_format=str_replace(".", ",", $filesize).GetMessage('CT_NAME_b');
		}
		$fileName = substr($arTmpItem["ORIGINAL_NAME"], 0, strrpos($arTmpItem["ORIGINAL_NAME"], '.'));
		return array("TYPE" => $type, "FILE_SIZE" => $filesize, "FILE_SIZE_FORMAT" => $filesize_format, "DESCRIPTION" => ( $arTmpItem["DESCRIPTION"] ? $arTmpItem["DESCRIPTION"] : $fileName), "SRC" => $arTmpItem["SRC"]);
	}

	public static function get_file_info($fileID){
		$file = CFile::GetFileArray($fileID);
		$pos = strrpos($file['FILE_NAME'], '.');
		$file['FILE_NAME'] = substr($file['FILE_NAME'], $pos);
		if(!$file['FILE_SIZE']){
			// bx bug in some version
			$file['FILE_SIZE'] = filesize($_SERVER['DOCUMENT_ROOT'].$file['SRC']);
		}
		$frm = explode('.', $file['FILE_NAME']);
		$frm = $frm[1];
		if($frm == 'doc' || $frm == 'docx'){
			$type = 'doc';
		}
		elseif($frm == 'xls' || $frm == 'xlsx'){
			$type = 'xls';
		}
		elseif($frm == 'jpg' || $frm == 'jpeg'){
			$type = 'jpg';
		}
		elseif($frm == 'png'){
			$type = 'png';
		}
		elseif($frm == 'ppt'){
			$type = 'ppt';
		}
		elseif($frm == 'tif'){
			$type = 'tif';
		}
		elseif($frm == 'txt'){
			$type = 'txt';
		}
		else{
			$type = 'pdf';
		}
		return $arr = array('TYPE' => $type, 'FILE_SIZE' => $file['FILE_SIZE'], 'SRC' => $file['SRC'], 'DESCRIPTION' => $file['DESCRIPTION'], 'ORIGINAL_NAME' => $file['ORIGINAL_NAME']);
	}

	public static function filesize_format($filesize){
		$formats = array(GetMessage('CT_NAME_b'), GetMessage('CT_NAME_KB'), GetMessage('CT_NAME_MB'), GetMessage('CT_NAME_GB'), GetMessage('CT_NAME_TB'));
		$format = 0;
		while($filesize > 1024 && count($formats) != ++$format){
			$filesize = round($filesize / 1024, 1);
		}
		$formats[] = GetMessage('CT_NAME_TB');
		return $filesize.' '.$formats[$format];
	}

	public static function getMinPriceFromOffersExt(&$offers, $currency, $replaceMinPrice = true){
		$replaceMinPrice = ($replaceMinPrice === true);
		$result = false;
		$minPrice = 0;
		if (!empty($offers) && is_array($offers))
		{
			$doubles = array();
			foreach ($offers as $oneOffer)
			{
				if(!$oneOffer["MIN_PRICE"])
					continue;
				$oneOffer['ID'] = (int)$oneOffer['ID'];
				if (isset($doubles[$oneOffer['ID']]))
					continue;
				/*if (!$oneOffer['CAN_BUY'])
					continue;*/

				CIBlockPriceTools::setRatioMinPrice($oneOffer, $replaceMinPrice);

				$oneOffer['MIN_PRICE']['CATALOG_MEASURE_RATIO'] = $oneOffer['CATALOG_MEASURE_RATIO'];
				$oneOffer['MIN_PRICE']['CATALOG_MEASURE'] = $oneOffer['CATALOG_MEASURE'];
				$oneOffer['MIN_PRICE']['CATALOG_MEASURE_NAME'] = $oneOffer['CATALOG_MEASURE_NAME'];
				$oneOffer['MIN_PRICE']['~CATALOG_MEASURE_NAME'] = $oneOffer['~CATALOG_MEASURE_NAME'];

				if (empty($result))
				{
					$minPrice = ($oneOffer['MIN_PRICE']['CURRENCY'] == $currency
						? $oneOffer['MIN_PRICE']['DISCOUNT_VALUE']
						: CCurrencyRates::ConvertCurrency($oneOffer['MIN_PRICE']['DISCOUNT_VALUE'], $oneOffer['MIN_PRICE']['CURRENCY'], $currency)
					);
					$result = $oneOffer['MIN_PRICE'];
				}
				else
				{
					$comparePrice = ($oneOffer['MIN_PRICE']['CURRENCY'] == $currency
						? $oneOffer['MIN_PRICE']['DISCOUNT_VALUE']
						: CCurrencyRates::ConvertCurrency($oneOffer['MIN_PRICE']['DISCOUNT_VALUE'], $oneOffer['MIN_PRICE']['CURRENCY'], $currency)
					);
					if ($minPrice > $comparePrice)
					{
						$minPrice = $comparePrice;
						$result = $oneOffer['MIN_PRICE'];
					}
				}
				$doubles[$oneOffer['ID']] = true;
			}
		}
		return $result;
	}

	public static function getMaxPriceFromOffersExt(&$offers, $currency, $replaceMaxPrice = true){
		$replaceMaxPrice = ($replaceMaxPrice === true);
		$result = false;
		$maxPrice = 0;
		if (!empty($offers) && is_array($offers))
		{
			$doubles = array();
			foreach ($offers as $oneOffer)
			{
				if(!$oneOffer["MIN_PRICE"])
					continue;
				$oneOffer['ID'] = (int)$oneOffer['ID'];
				if (isset($doubles[$oneOffer['ID']]))
					continue;
				/*if (!$oneOffer['CAN_BUY'])
					continue;*/

				CIBlockPriceTools::setRatioMinPrice($oneOffer, $replaceMaxPrice);

				$oneOffer['MIN_PRICE']['CATALOG_MEASURE_RATIO'] = $oneOffer['CATALOG_MEASURE_RATIO'];
				$oneOffer['MIN_PRICE']['CATALOG_MEASURE'] = $oneOffer['CATALOG_MEASURE'];
				$oneOffer['MIN_PRICE']['CATALOG_MEASURE_NAME'] = $oneOffer['CATALOG_MEASURE_NAME'];
				$oneOffer['MIN_PRICE']['~CATALOG_MEASURE_NAME'] = $oneOffer['~CATALOG_MEASURE_NAME'];

				if (empty($result))
				{
					$maxPrice = ($oneOffer['MIN_PRICE']['CURRENCY'] == $currency
						? $oneOffer['MIN_PRICE']['DISCOUNT_VALUE']
						: CCurrencyRates::ConvertCurrency($oneOffer['MIN_PRICE']['DISCOUNT_VALUE'], $oneOffer['MIN_PRICE']['CURRENCY'], $currency)
					);
					$result = $oneOffer['MIN_PRICE'];
				}
				else
				{
					$comparePrice = ($oneOffer['MIN_PRICE']['CURRENCY'] == $currency
						? $oneOffer['MIN_PRICE']['DISCOUNT_VALUE']
						: CCurrencyRates::ConvertCurrency($oneOffer['MIN_PRICE']['DISCOUNT_VALUE'], $oneOffer['MIN_PRICE']['CURRENCY'], $currency)
					);
					if ($maxPrice < $comparePrice)
					{
						$maxPrice = $comparePrice;
						$result = $oneOffer['MIN_PRICE'];
					}
				}
				$doubles[$oneOffer['ID']] = true;
			}
		}
		return $result;
	}

	public static function getSliderForItemExt(&$item, $propertyCode, $addDetailToSlider, $encode = true, $notAddDetail = false)
    {
        $encode = ($encode === true);
        $result = array();

        if (!empty($item) && is_array($item))
        {
            if (
                '' != $propertyCode &&
                isset($item['PROPERTIES'][$propertyCode]) &&
                'F' == $item['PROPERTIES'][$propertyCode]['PROPERTY_TYPE']
            )
            {
                if ('MORE_PHOTO' == $propertyCode && isset($item['MORE_PHOTO']) && !empty($item['MORE_PHOTO']))
                {
					
                    foreach ($item['MORE_PHOTO'] as &$onePhoto)
                    {
                    	$alt = ($onePhoto["DESCRIPTION"] ? $onePhoto["DESCRIPTION"] : ($item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'] ? $item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'] : $item["NAME"]));
                    	$title = ($onePhoto["DESCRIPTION"] ? $onePhoto["DESCRIPTION"] : ($item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] ? $item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] : $item["NAME"]));
                    	if($item['ALT_TITLE_GET'] == 'SEO')
                    	{
                    		$alt = ($item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'] ? $item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'] : $item["NAME"]);
                    		$title = ($item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] ? $item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] : $item["NAME"]);
                    	}
                        $result[] = array(
                            'ID' => (int)$onePhoto['ID'],
                            'SRC' => ($encode ? CHTTP::urnEncode($onePhoto['SRC'], 'utf-8') : $onePhoto['SRC']),
                            'WIDTH' => (int)$onePhoto['WIDTH'],
                            'HEIGHT' => (int)$onePhoto['HEIGHT'],
                            'ALT' => $alt,
                            'TITLE' => $title
                        );
                    }
                    unset($onePhoto);
                }
                else
                {
                    if (
                        isset($item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']) &&
                        !empty($item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE'])
                    )
                    {
                        $fileValues = (
                        isset($item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']['ID']) ?
                            array(0 => $item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']) :
                            $item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']
                        );
                        foreach ($fileValues as &$oneFileValue)
                        {
                        	$alt = ($oneFileValue["DESCRIPTION"] ? $oneFileValue["DESCRIPTION"] : ($item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'] ? $item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'] : $item["NAME"]));
	                    	$title = ($oneFileValue["DESCRIPTION"] ? $oneFileValue["DESCRIPTION"] : ($item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] ? $item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] : $item["NAME"]));
	                    	if($item['ALT_TITLE_GET'] == 'SEO')
	                    	{
	                    		$alt = ($item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'] ? $item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'] : $item["NAME"]);
	                    		$title = ($item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] ? $item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] : $item["NAME"]);
	                    	}
                            $result[] = array(
                                'ID' => (int)$oneFileValue['ID'],
                                'SRC' => ($encode ? CHTTP::urnEncode($oneFileValue['SRC'], 'utf-8') : $oneFileValue['SRC']),
                                'WIDTH' => (int)$oneFileValue['WIDTH'],
                                'HEIGHT' => (int)$oneFileValue['HEIGHT'],
                                'ALT' => $alt,
                          		'TITLE' => $title
                            );
                        }
                        if (isset($oneFileValue))
                            unset($oneFileValue);
                    }
                    else
                    {
                        $propValues = $item['PROPERTIES'][$propertyCode]['VALUE'];
                        if (!is_array($propValues))
                            $propValues = array($propValues);
                        foreach ($propValues as &$oneValue)
                        {
                            $oneFileValue = CFile::GetFileArray($oneValue);
                            if (isset($oneFileValue['ID']))
                            {
                            	$alt = ($oneFileValue["DESCRIPTION"] ? $oneFileValue["DESCRIPTION"] : ($item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'] ? $item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'] : $item["NAME"]));
		                    	$title = ($oneFileValue["DESCRIPTION"] ? $oneFileValue["DESCRIPTION"] : ($item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] ? $item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] : $item["NAME"]));
		                    	if($item['ALT_TITLE_GET'] == 'SEO')
		                    	{
		                    		$alt = ($item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'] ? $item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'] : $item["NAME"]);
		                    		$title = ($item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] ? $item['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] : $item["NAME"]);
		                    	}
                                $result[] = array(
                                    'ID' => (int)$oneFileValue['ID'],
                                    'SRC' => ($encode ? CHTTP::urnEncode($oneFileValue['SRC'], 'utf-8') : $oneFileValue['SRC']),
                                    'WIDTH' => (int)$oneFileValue['WIDTH'],
                                    'HEIGHT' => (int)$oneFileValue['HEIGHT'],
                                    'ALT' => $alt,
                          			'TITLE' => $title
                                );
                            }
                        }
                        if (isset($oneValue))
							unset($oneValue);
                    }
                }
            }
            if(isset($item['OFFERS']) && $item['OFFERS'] && !$addDetailToSlider){
            	if(empty($result))
            		unset($item['DETAIL_PICTURE']);
            }

            if ($addDetailToSlider || empty($result))
            {
            	if (empty($result) && !$addDetailToSlider && $notAddDetail) {
            		return [];
            	}
                if (!empty($item['DETAIL_PICTURE']))
                {
                    if (!is_array($item['DETAIL_PICTURE']))
                        $item['DETAIL_PICTURE'] = CFile::GetFileArray($item['DETAIL_PICTURE']);
                    if (isset($item['DETAIL_PICTURE']['ID']))
                    {
                    	$alt = ($item['DETAIL_PICTURE']['DESCRIPTION'] ? $item['DETAIL_PICTURE']['DESCRIPTION'] : ($item['DETAIL_PICTURE']['ALT'] ? $item['DETAIL_PICTURE']['ALT'] : $item['NAME'] ));
                    	$title = ($item['DETAIL_PICTURE']['DESCRIPTION'] ? $item['DETAIL_PICTURE']['DESCRIPTION'] : ($item['DETAIL_PICTURE']['TITLE'] ? $item['DETAIL_PICTURE']['TITLE'] : $item['NAME'] ));
                    	if($item['ALT_TITLE_GET'] == 'SEO')
                    	{
                    		$alt = ($item['DETAIL_PICTURE']['ALT'] ? $item['DETAIL_PICTURE']['ALT'] : $item['NAME'] );
                    		$title = ($item['DETAIL_PICTURE']['TITLE'] ? $item['DETAIL_PICTURE']['TITLE'] : $item['NAME'] );
						}
						$detailPictIds = array_column($result, 'ID');
						if(!in_array((int)$item['DETAIL_PICTURE']['ID'], $detailPictIds)){ 
							array_unshift(
								$result,
								array(
									'ID' => (int)$item['DETAIL_PICTURE']['ID'],
									'SRC' => ($encode ? CHTTP::urnEncode($item['DETAIL_PICTURE']['SRC'], 'utf-8') : $item['DETAIL_PICTURE']['SRC']),
									'WIDTH' => (int)$item['DETAIL_PICTURE']['WIDTH'],
									'HEIGHT' => (int)$item['DETAIL_PICTURE']['HEIGHT'],
									'ALT' => $alt,
									'TITLE' => $title
								)
							);
						}
                    }
                }
            }
		}		
        return $result;
    }

	public static function checkBreadcrumbsChain(&$arParams, $arSection = array(), $arElement = array()){
		global $APPLICATION;

		if(Option::get(self::moduleID, "SHOW_BREADCRUMBS_CATALOG_CHAIN", "H1", SITE_ID) == "NAME"){
			$APPLICATION->arAdditionalChain = false;
			if($arParams['INCLUDE_IBLOCK_INTO_CHAIN'] == 'Y' && isset(CNextCache::$arIBlocksInfo[$arParams['IBLOCK_ID']]['NAME'])){
				$APPLICATION->AddChainItem(CNextCache::$arIBlocksInfo[$arParams['IBLOCK_ID']]['NAME'], $arElement['~LIST_PAGE_URL']);
			}
			if($arParams['ADD_SECTIONS_CHAIN'] == 'Y' && $arSection){
				$rsPath = CIBlockSection::GetNavChain($arParams['IBLOCK_ID'], $arSection['ID']);
				$rsPath->SetUrlTemplates('', $arParams['SECTION_URL']);
				while($arPath = $rsPath->GetNext()){
					$APPLICATION->AddChainItem($arPath['NAME'], $arPath['~SECTION_PAGE_URL']);
				}
			}
			if($arParams['ADD_ELEMENT_CHAIN'] == 'Y' && $arElement){
				$APPLICATION->AddChainItem($arElement['NAME']);
			}
		}
	}

	public static function getShowBasket(){
		static $bShowBasket;
		if($bShowBasket === NULL)
		{
			$arFrontParametrs = self::GetFrontParametrsValues(SITE_ID);
			$bShowBasket = ($arFrontParametrs['SHOW_BASKET_ON_PAGES'] == 'Y' || ($arFrontParametrs['SHOW_BASKET_ON_PAGES'] == 'N' && (!self::IsBasketPage() && !self::IsOrderPage())));
		}
		return $bShowBasket;
	}

	public static function SetJSOptions(){
		global $APPLICATION, $STARTTIME, $arSite, $arTheme;

		$MESS['MIN_ORDER_PRICE_TEXT']=Option::get(self::moduleID, 'MIN_ORDER_PRICE_TEXT', GetMessage('MIN_ORDER_PRICE_TEXT_EXAMPLE'), SITE_ID);

		self::showBgImage(SITE_ID, $arTheme);


		$arFrontParametrs = self::GetFrontParametrsValues(SITE_ID);
		$tmp = $arFrontParametrs['DATE_FORMAT'];
		$DATE_MASK = ($tmp == 'DOT' ? 'd.m.y' : ($tmp == 'HYPHEN' ? 'd-m-y' : ($tmp == 'SPACE' ? 'd m y' : ($tmp == 'SLASH' ? 'd/m/y' : 'd:m:y'))));
		$VALIDATE_DATE_MASK = ($tmp == 'DOT' ? '^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}$' : ($tmp == 'HYPHEN' ? '^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$' : ($tmp == 'SPACE' ? '^[0-9]{1,2} [0-9]{1,2} [0-9]{4}$' : ($tmp == 'SLASH' ? '^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$' : '^[0-9]{1,2}\:[0-9]{1,2}\:[0-9]{4}$'))));
		$DATE_PLACEHOLDER = ($tmp == 'DOT' ? GetMessage('DATE_FORMAT_DOT') : ($tmp == 'HYPHEN' ? GetMessage('DATE_FORMAT_HYPHEN') : ($tmp == 'SPACE' ? GetMessage('DATE_FORMAT_SPACE') : ($tmp == 'SLASH' ? GetMessage('DATE_FORMAT_SLASH') : GetMessage('DATE_FORMAT_COLON')))));
		$DATETIME_MASK = ($tmp == 'DOT' ? 'd.m.y' : ($tmp == 'HYPHEN' ? 'd-m-y' : ($tmp == 'SPACE' ? 'd m y' : ($tmp == 'SLASH' ? 'd/m/y' : 'd:m:y')))).' h:s';
		$DATETIME_PLACEHOLDER = ($tmp == 'DOT' ? GetMessage('DATE_FORMAT_DOT') : ($tmp == 'HYPHEN' ? GetMessage('DATE_FORMAT_HYPHEN') : ($tmp == 'SPACE' ? GetMessage('DATE_FORMAT_SPACE') : ($tmp == 'SLASH' ? GetMessage('DATE_FORMAT_SLASH') : GetMessage('DATE_FORMAT_COLON'))))).' '.GetMessage('TIME_FORMAT_COLON');
		$VALIDATE_DATETIME_MASK = ($tmp == 'DOT' ? '^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4} [0-9]{1,2}\:[0-9]{1,2}$' : ($tmp == 'HYPHEN' ? '^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4} [0-9]{1,2}\:[0-9]{1,2}$' : ($tmp == 'SPACE' ? '^[0-9]{1,2} [0-9]{1,2} [0-9]{4} [0-9]{1,2}\:[0-9]{1,2}$' : ($tmp == 'SLASH' ? '^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4} [0-9]{1,2}\:[0-9]{1,2}$' : '^[0-9]{1,2}\:[0-9]{1,2}\:[0-9]{4} [0-9]{1,2}\:[0-9]{1,2}$'))));

		list($bPhoneAuthSupported, $bPhoneAuthShow, $bPhoneAuthRequired, $bPhoneAuthUse) = Aspro\Next\PhoneAuth::getOptions();
		?>
		<?Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('basketitems-component-block');?>
			<?if(self::getShowBasket()):?>
				<?if($arFrontParametrs['USE_REGIONALITY'] == 'Y')
					CSaleBasket::UpdateBasketPrices(CSaleBasket::GetBasketUserID(), SITE_ID);?>
				<?$APPLICATION->IncludeComponent( "bitrix:sale.basket.basket.line", "actual", Array(
					"PATH_TO_BASKET" => SITE_DIR."basket/",
					"PATH_TO_ORDER" => SITE_DIR."order/",
					"PATH_TO_PERSONAL" => SITE_DIR."personal/",
					"PATH_TO_PROFILE" => SITE_DIR."personal/",
					"SHOW_DELAY" => "Y",
					"SHOW_PRODUCTS"=>"N",
					"SHOW_EMPTY_VALUES" => "Y",
					"SHOW_NOTAVAIL" => "N",
					"SHOW_SUBSCRIBE" => "N",
					"SHOW_IMAGE" => "Y",
					"SHOW_PRICE" => "Y",
					"SHOW_SUMMARY" => "Y",
					"SHOW_NUM_PRODUCTS" => "Y",
					"SHOW_TOTAL_PRICE" => "Y",
					"HIDE_ON_BASKET_PAGES" => "N"
				) );?>
			<?endif;?>
		<?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('basketitems-component-block', '');?>
		<?if($arFrontParametrs['SHOW_LICENCE'] == 'Y')
		{
			if(function_exists('file_get_contents'))
			{
				$license_text = file_get_contents(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/licenses_text.php'));
			}
			else
			{
				ob_start();
					include_once(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/licenses_text.php'));
				$license_text = ob_get_contents();
				ob_end_clean();
			}
			$MESS['LICENSES_TEXT'] = $license_text;
		}?>
		<?if($arFrontParametrs['SHOW_OFFER'] == 'Y')
		{
			if(function_exists('file_get_contents'))
			{
				$offer_text = file_get_contents(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/offer_text.php'));
			}
			else
			{
				ob_start();
					include_once(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/offer_text.php'));
				$offer_text = ob_get_contents();
				ob_end_clean();
			}
			$MESS['OFFER_TEXT'] = $offer_text;
		}?>

		<?/*<script src="<?=SITE_TEMPLATE_PATH.'/js/setTheme.php?site_id='.SITE_ID.'&site_dir='.SITE_DIR?>" data-skip-moving="true"></script>*/?>

		<script type="text/javascript">
		<?if($arFrontParametrs['YANDEX_ECOMERCE'] == 'Y' || $arFrontParametrs['GOOGLE_ECOMERCE'] == 'Y'):?>
			window.onload=function(){
				window.dataLayer = window.dataLayer || [];
			}
		<?endif;?>
		BX.message(<?=CUtil::PhpToJSObject( $MESS, false )?>);

		var arNextOptions = ({
			"SITE_DIR" : "<?=SITE_DIR?>",
			"SITE_ID" : "<?=SITE_ID?>",
			"SITE_ADDRESS" : "<?=$arSite['SERVER_NAME'];?>",
			"FORM" : ({
				"ASK_FORM_ID" : "ASK",
				"SERVICES_FORM_ID" : "SERVICES",
				"FEEDBACK_FORM_ID" : "FEEDBACK",
				"CALLBACK_FORM_ID" : "CALLBACK",
				"RESUME_FORM_ID" : "RESUME",
				"TOORDER_FORM_ID" : "TOORDER"
			}),
			"PAGES" : ({
				"FRONT_PAGE" : "<?=self::IsMainPage()?>",
				"BASKET_PAGE" : "<?=self::IsBasketPage()?>",
				"ORDER_PAGE" : "<?=self::IsOrderPage()?>",
				"PERSONAL_PAGE" : "<?=self::IsPersonalPage()?>",
				"CATALOG_PAGE" : "<?=self::IsCatalogPage()?>",
				"CATALOG_PAGE_URL" : "<?=$arFrontParametrs['CATALOG_PAGE_URL']?>",
				"BASKET_PAGE_URL" : "<?=$arFrontParametrs['BASKET_PAGE_URL']?>",
			}),
			"PRICES" : ({
				"MIN_PRICE" : "<?=trim(Option::get(self::moduleID, "MIN_ORDER_PRICE", "1000", SITE_ID));?>",
			}),
			"THEME" : ({
				'THEME_SWITCHER' : '<?=$arFrontParametrs['THEME_SWITCHER']?>',
				'BASE_COLOR' : '<?=$arFrontParametrs['BASE_COLOR']?>',
				'BASE_COLOR_CUSTOM' : '<?=$arFrontParametrs['BASE_COLOR_CUSTOM']?>',
				'TOP_MENU' : '<?=$arFrontParametrs['TOP_MENU']?>',
				'TOP_MENU_FIXED' : '<?=$arFrontParametrs['TOP_MENU_FIXED']?>',
				'COLORED_LOGO' : '<?=$arFrontParametrs['COLORED_LOGO']?>',
				'SIDE_MENU' : '<?=$arFrontParametrs['SIDE_MENU']?>',
				'SCROLLTOTOP_TYPE' : '<?=$arFrontParametrs['SCROLLTOTOP_TYPE']?>',
				'SCROLLTOTOP_POSITION' : '<?=$arFrontParametrs['SCROLLTOTOP_POSITION']?>',
				'USE_LAZY_LOAD' : '<?=$arFrontParametrs['USE_LAZY_LOAD']?>',
				'CAPTCHA_FORM_TYPE' : '<?=$arFrontParametrs['CAPTCHA_FORM_TYPE']?>',
				'ONE_CLICK_BUY_CAPTCHA' : '<?=$arFrontParametrs['ONE_CLICK_BUY_CAPTCHA']?>',
				'PHONE_MASK' : '<?=$arFrontParametrs['PHONE_MASK']?>',
				'VALIDATE_PHONE_MASK' : '<?=$arFrontParametrs['VALIDATE_PHONE_MASK']?>',
				'DATE_MASK' : '<?=$DATE_MASK?>',
				'DATE_PLACEHOLDER' : '<?=$DATE_PLACEHOLDER?>',
				'VALIDATE_DATE_MASK' : '<?=($VALIDATE_DATE_MASK)?>',
				'DATETIME_MASK' : '<?=$DATETIME_MASK?>',
				'DATETIME_PLACEHOLDER' : '<?=$DATETIME_PLACEHOLDER?>',
				'VALIDATE_DATETIME_MASK' : '<?=($VALIDATE_DATETIME_MASK)?>',
				'VALIDATE_FILE_EXT' : '<?=$arFrontParametrs['VALIDATE_FILE_EXT']?>',
				'BANNER_WIDTH' : '<?=$arFrontParametrs['BANNER_WIDTH']?>',
				'BIGBANNER_ANIMATIONTYPE' : '<?=$arFrontParametrs['BIGBANNER_ANIMATIONTYPE']?>',
				'BIGBANNER_SLIDESSHOWSPEED' : '<?=$arFrontParametrs['BIGBANNER_SLIDESSHOWSPEED']?>',
				'BIGBANNER_ANIMATIONSPEED' : '<?=$arFrontParametrs['BIGBANNER_ANIMATIONSPEED']?>',
				'PARTNERSBANNER_SLIDESSHOWSPEED' : '<?=$arFrontParametrs['PARTNERSBANNER_SLIDESSHOWSPEED']?>',
				'PARTNERSBANNER_ANIMATIONSPEED' : '<?=$arFrontParametrs['PARTNERSBANNER_ANIMATIONSPEED']?>',
				'ORDER_BASKET_VIEW' : '<?=$arFrontParametrs['ORDER_BASKET_VIEW']?>',
				'SHOW_BASKET_ONADDTOCART' : '<?=$arFrontParametrs['SHOW_BASKET_ONADDTOCART']?>',
				'SHOW_BASKET_PRINT' : '<?=$arFrontParametrs['SHOW_BASKET_PRINT']?>',
				"SHOW_ONECLICKBUY_ON_BASKET_PAGE" : '<?=$arFrontParametrs['SHOW_ONECLICKBUY_ON_BASKET_PAGE'];?>',
				'SHOW_LICENCE' : '<?=$arFrontParametrs['SHOW_LICENCE'];?>',				
				'LICENCE_CHECKED' : '<?=$arFrontParametrs['LICENCE_CHECKED'];?>',
				'SHOW_OFFER' : '<?=$arFrontParametrs['SHOW_OFFER'];?>',
				'OFFER_CHECKED' : '<?=$arFrontParametrs['OFFER_CHECKED'];?>',
				'LOGIN_EQUAL_EMAIL' : '<?=$arFrontParametrs['LOGIN_EQUAL_EMAIL'];?>',
				'PERSONAL_ONEFIO' : '<?=$arFrontParametrs['PERSONAL_ONEFIO'];?>',
				'SHOW_TOTAL_SUMM' : '<?=$arFrontParametrs['SHOW_TOTAL_SUMM'];?>',
				'SHOW_TOTAL_SUMM_TYPE' : '<?=$arFrontParametrs['SHOW_TOTAL_SUMM_TYPE'];?>',
				'CHANGE_TITLE_ITEM' : '<?=$arFrontParametrs['CHANGE_TITLE_ITEM'];?>',
				'DISCOUNT_PRICE' : '<?=$arFrontParametrs['DISCOUNT_PRICE'];?>',
				'STORES' : '<?=$arFrontParametrs['STORES'];?>',
				'STORES_SOURCE' : '<?=$arFrontParametrs['STORES_SOURCE'];?>',
				'TYPE_SKU' : '<?=$arFrontParametrs['TYPE_SKU']?>',
				'MENU_POSITION' : '<?=$arFrontParametrs['MENU_POSITION']?>',
				'MENU_TYPE_VIEW' : '<?=$arFrontParametrs['MENU_TYPE_VIEW']?>',
				'DETAIL_PICTURE_MODE' : '<?=$arFrontParametrs['DETAIL_PICTURE_MODE']?>',
				'PAGE_WIDTH' : '<?=$arFrontParametrs['PAGE_WIDTH']?>',
				'PAGE_CONTACTS' : '<?=$arFrontParametrs['PAGE_CONTACTS']?>',
				'HEADER_TYPE' : '<?=$arFrontParametrs['HEADER_TYPE']?>',
				'REGIONALITY_SEARCH_ROW' : '<?=$arFrontParametrs['REGIONALITY_SEARCH_ROW']?>',
				'HEADER_TOP_LINE' : '<?=$arFrontParametrs['HEADER_TOP_LINE']?>',
				'HEADER_FIXED' : '<?=$arFrontParametrs['HEADER_FIXED']?>',
				'HEADER_MOBILE' : '<?=$arFrontParametrs['HEADER_MOBILE']?>',
				'HEADER_MOBILE_MENU' : '<?=$arFrontParametrs['HEADER_MOBILE_MENU']?>',
				'HEADER_MOBILE_MENU_SHOW_TYPE' : '<?=$arFrontParametrs['HEADER_MOBILE_MENU_SHOW_TYPE']?>',
				'TYPE_SEARCH' : '<?=$arFrontParametrs['TYPE_SEARCH']?>',
				'PAGE_TITLE' : '<?=$arFrontParametrs['PAGE_TITLE']?>',
				'INDEX_TYPE' : '<?=$arFrontParametrs['INDEX_TYPE']?>',
				'FOOTER_TYPE' : '<?=$arFrontParametrs['FOOTER_TYPE']?>',
				'PRINT_BUTTON' : '<?=$arFrontParametrs['PRINT_BUTTON']?>',
				'EXPRESSION_FOR_PRINT_PAGE' : '<?=$arFrontParametrs['EXPRESSION_FOR_PRINT_PAGE']?>',
				'EXPRESSION_FOR_FAST_VIEW' : '<?=$arFrontParametrs['EXPRESSION_FOR_FAST_VIEW']?>',
				'FILTER_VIEW' : '<?=$arFrontParametrs['FILTER_VIEW']?>',
				'YA_GOALS' : '<?=$arFrontParametrs['YA_GOALS']?>',
				'YA_COUNTER_ID' : '<?=$arFrontParametrs['YA_COUNTER_ID']?>',
				'USE_FORMS_GOALS' : '<?=$arFrontParametrs['USE_FORMS_GOALS']?>',
				'USE_SALE_GOALS' : '<?=$arFrontParametrs['USE_SALE_GOALS']?>',
				'USE_DEBUG_GOALS' : '<?=$arFrontParametrs['USE_DEBUG_GOALS']?>',
				'SHOW_HEADER_GOODS' : '<?=$arFrontParametrs['SHOW_HEADER_GOODS']?>',
				'INSTAGRAMM_INDEX' : '<?=(isset($arFrontParametrs[$arFrontParametrs['INDEX_TYPE'].'_INSTAGRAMM']) ? $arFrontParametrs[$arFrontParametrs['INDEX_TYPE'].'_INSTAGRAMM'] : 'Y')?>',
				'USE_PHONE_AUTH': '<?=($bPhoneAuthUse ? 'Y' : 'N')?>',
				'MOBILE_CATALOG_LIST_ELEMENTS_COMPACT': '<?=$arFrontParametrs['MOBILE_CATALOG_LIST_ELEMENTS_COMPACT']?>',
				'NLO_MENU': '<?=$arFrontParametrs['NLO_MENU']?>',
			}),
			"PRESETS": <?=CUtil::PhpToJSObject(self::$arPresetsList)?>,
			"REGIONALITY":({
				'USE_REGIONALITY' : '<?=$arFrontParametrs['USE_REGIONALITY']?>',
				'REGIONALITY_VIEW' : '<?=$arFrontParametrs['REGIONALITY_VIEW']?>',
			}),
			"COUNTERS":({
				"YANDEX_COUNTER" : 1,
				"GOOGLE_COUNTER" : 1,
				"YANDEX_ECOMERCE" : "<?=Option::get(self::moduleID, "YANDEX_ECOMERCE", false, SITE_ID)?>",
				"GOOGLE_ECOMERCE" : "<?=Option::get(self::moduleID, "GOOGLE_ECOMERCE", false, SITE_ID)?>",
				"TYPE":{
					"ONE_CLICK":"<?=GetMessage("ONE_CLICK_BUY");?>",
					"QUICK_ORDER":"<?=GetMessage("QUICK_ORDER");?>",
				},
				"GOOGLE_EVENTS":{
					"ADD2BASKET": "<?=trim(Option::get(self::moduleID, "BASKET_ADD_EVENT", "addToCart", SITE_ID))?>",
					"REMOVE_BASKET": "<?=trim(Option::get(self::moduleID, "BASKET_REMOVE_EVENT", "removeFromCart", SITE_ID))?>",
					"CHECKOUT_ORDER": "<?=trim(Option::get(self::moduleID, "CHECKOUT_ORDER_EVENT", "checkout", SITE_ID))?>",
					"PURCHASE": "<?=trim(Option::get(self::moduleID, "PURCHASE_ORDER_EVENT", "gtm.dom", SITE_ID))?>",
				}
			}),
			"JS_ITEM_CLICK":({
				"precision" : 6,
				"precisionFactor" : Math.pow(10,6)
			})
		});
		</script>
		<?/*fix reset POST*/
		if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['color_theme']){
			LocalRedirect($_SERVER['HTTP_REFERER']);
		}?>
	<?}

	public static function showAddress($class = ''){
		global $arRegion, $APPLICATION;
		static $addr_call;
		$iCalledID = ++$addr_call;
		$regionId = ($arRegion ? $arRegion['ID'] : '');?>

		<?if($arRegion):?>
		<?$frame = new \Bitrix\Main\Page\FrameHelper('address-block'.$iCalledID);?>
		<?$frame->begin();?>
		<?endif;?>

			<?if($arRegion):?>
				<?if($arRegion['PROPERTY_ADDRESS_VALUE']):?>
					<div <?=($class ? 'class="'.$class.'"' : '')?>>
						<?=$arRegion['PROPERTY_ADDRESS_VALUE']['TEXT'];?>
					</div>
				<?endif;?>
			<?else:?>
				<div <?=($class ? 'class="'.$class.'"' : '')?>>
					<?$APPLICATION->IncludeFile(SITE_DIR."include/top_page/site-address.php", array(), array(
							"MODE" => "html",
							"NAME" => "Address",
							"TEMPLATE" => "include_area.php",
						)
					);?>
				</div>
			<?endif;?>

		<?if($arRegion):?>
		<?$frame->end();?>
		<?endif;?>

	<?}

	public static function showEmail($class = ''){
		static $email_call;
		global $arRegion, $APPLICATION;

		$iCalledID = ++$email_call;
		$regionId = ($arRegion ? $arRegion['ID'] : '');

		if($arRegion){
			$frame = new \Bitrix\Main\Page\FrameHelper('email-block'.$iCalledID);
			$frame->begin();
		}
		?>
		<?if($arRegion):?>
			<?if($arRegion['PROPERTY_EMAIL_VALUE']):?>
				<div <?=($class ? 'class="'.$class.'"' : '')?>>
					<?foreach($arRegion['PROPERTY_EMAIL_VALUE'] as $value):?>
						<a href="mailto:<?=$value;?>" target="_blank"><?=$value?></a>
					<?endforeach;?>
				</div>
			<?endif;?>
		<?else:?>
			<div <?=($class ? 'class="'.$class.'"' : '')?>>
				<?$APPLICATION->IncludeFile(SITE_DIR."include/footer/site-email.php", array(), array(
						"MODE" => "html",
						"NAME" => "Address",
						"TEMPLATE" => "include_area.php",
					)
				);?>
			</div>
		<?endif;?>
		<?
		if($arRegion){
			$frame->end();
		}
	}

	public static function ShowHeaderMobilePhones($class = ''){
		static $hphones_call_m;
		global $arRegion, $arTheme;

		$iCalledID = ++$hphones_call_m;
		$arBackParametrs = self::GetBackParametrsValues(SITE_ID);

		$iCountPhones = ($arRegion ? count($arRegion['PHONES']) : $arBackParametrs['HEADER_PHONES']);
		$regionId = ($arRegion ? $arRegion['ID'] : '');

		if($arRegion){
			$frame = new \Bitrix\Main\Page\FrameHelper('header-allphones-block'.$iCalledID);
			$frame->begin();
		}
		?>
		<?if($iCountPhones):?>
			<?=self::showIconSvg('phone', SITE_TEMPLATE_PATH.'/images/svg/phone.svg', '', $class);?>
			<div id="mobilePhone" class="dropdown-mobile-phone">
				<div class="wrap">
					<div class="more_phone title"><span class="no-decript dark-color "><?=Loc::getMessage('NEXT_T_MENU_CALLBACK')?> <?=CNext::showIconSvg("close dark dark-i", SITE_TEMPLATE_PATH."/images/svg/Close.svg");?></span></div>
					<?for($i = 0; $i < $iCountPhones; ++$i):?>
						<?
						$phone = ($arRegion ? $arRegion['PHONES'][$i] : $arBackParametrs['HEADER_PHONES_array_PHONE_VALUE_'.$i]);
						$href = 'tel:'.str_replace(array(' ', '-', '(', ')'), '', $phone);
						$description = ($arRegion ? $arRegion['PROPERTY_PHONES_DESCRIPTION'][$i] : $arBackParametrs['HEADER_PHONES_array_PHONE_DESCRIPTION_'.$i]);
						$description = (strlen($description) ? '<span>'.$description.'</span>' : '');
						?>
						<div class="more_phone">
						    <a class="dark-color <?=(strlen($description) ? '' : 'no-decript')?>" rel="nofollow" href="<?=$href?>"><?=$phone?><?=$description?></a>
						</div>
					<?endfor;?>
					<?if($arTheme['SHOW_CALLBACK']['VALUE'] == 'Y'):?>
						<div class="more_phone"><a rel="nofollow" class="dark-color no-decript callback" href="" data-event="jqm" data-param-form_id="CALLBACK" data-name="callback"><?=Loc::getMessage('CALLBACK')?></a></div>
					<?endif;?>
				</div>
			</div>
		<?endif;?>
		<?
		if($arRegion){
			$frame->end();
		}
	}

	public static function ShowHeaderPhones($class = '', $bFooter = false){
		static $hphones_call;
		global $arRegion;

		$iCalledID = ++$hphones_call;
		$arBackParametrs = self::GetBackParametrsValues(SITE_ID);
		$iCountPhones = ($arRegion ? count($arRegion['PHONES']) : $arBackParametrs['HEADER_PHONES']);
		$regionId = ($arRegion ? $arRegion['ID'] : '');

		if($arRegion){
			$frame = new \Bitrix\Main\Page\FrameHelper('header-allphones-block'.$iCalledID);
			$frame->begin();
		}
		?>
		<?if($iCountPhones):?>
			<?
			$phone = ($arRegion ? $arRegion['PHONES'][0] : $arBackParametrs['HEADER_PHONES_array_PHONE_VALUE_0']);
			$href = 'tel:'.str_replace(array(' ', '-', '(', ')'), '', $phone);
			?>
			<?if($bFooter):?>
				<div class="phone blocks">
			<?endif;?>
			<div class="phone<?=($iCountPhones > 1 ? ' with_dropdown' : '')?><?=($class ? ' '.$class : '')?>">
				<i class="svg svg-phone"></i>
				<a rel="nofollow" href="<?=$href?>"><?=$phone?></a>
				<?if($iCountPhones > 1):?>
					<div class="dropdown scrollbar">
						<div class="wrap">
							<?for($i = 1; $i < $iCountPhones; ++$i):?>
								<?
								$phone = ($arRegion ? $arRegion['PHONES'][$i] : $arBackParametrs['HEADER_PHONES_array_PHONE_VALUE_'.$i]);
								$href = 'tel:'.str_replace(array(' ', '-', '(', ')'), '', $phone);
								$description = ($arRegion ? $arRegion['PROPERTY_PHONES_DESCRIPTION'][$i] : $arBackParametrs['HEADER_PHONES_array_PHONE_DESCRIPTION_'.$i]);
								$description = (strlen($description) ? '<span>'.$description.'</span>' : '');
								?>
								<div class="more_phone">
								    <a <?=(strlen($description) ? '' : 'class="no-decript"')?> rel="nofollow" href="<?=$href?>"><?=$phone?><?=$description?></a>
								</div>
							<?endfor;?>
						</div>
					</div>
				<?endif;?>
			</div>
			<?if($bFooter):?>
				</div>
			<?endif;?>
		<?endif;?>
		<?
		if($arRegion){
			$frame->end();
		}
	}

	public static function showFooterPhone(){
		static $fphones_call;
		global $arRegion;

		$iCalledID = ++$fphones_call;
		$arBackParametrs = self::GetBackParametrsValues(SITE_ID);
		$iCountPhones = ($arRegion ? count($arRegion['PHONES']) : $arBackParametrs['HEADER_PHONES']);
		$regionId = ($arRegion ? $arRegion['ID'] : '');

		if($arRegion){
			$frame = new \Bitrix\Main\Page\FrameHelper('footer-allphones-block'.$iCalledID);
			$frame->begin();
		}
		?>
		<?if($iCountPhones):?>
			<?
			$phone = ($arRegion ? $arRegion['PHONES'][0] : $arBackParametrs['HEADER_PHONES_array_PHONE_VALUE_0']);
			$href = 'tel:'.str_replace(array(' ', '-', '(', ')'), '', $phone);
			?>
			<span class="phone_wrap">
				<span>
					<a href="<?=$href?>" rel="nofollow"><?=$phone?></a>
				</span>
			</span>
		<?endif;?>
		<?
		if($arRegion){
			$frame->end();
		}
	}

	public static function goto404Page(){
		global $APPLICATION;

		if($_SESSION['SESS_INCLUDE_AREAS']){
			echo '</div>';
		}
		echo '</div>';
		$APPLICATION->IncludeFile(SITE_DIR.'404.php', array(), array('MODE' => 'html'));
		die();
	}

	public static function checkAjaxRequest(){
		return ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || (strtolower($_REQUEST['ajax']) == 'y'));
	}

	public static function checkRestartBuffer(){
		global $APPLICATION;
		static $bRestarted;

		if($bRestarted)
			die();

		if((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || (isset($_REQUEST['ajax']) && strtolower($_REQUEST['ajax']) == 'y'))
		{
			$APPLICATION->RestartBuffer();
			$bRestarted = true;
		}
	}

	public static function checkAllowDelivery($summ, $currency){
		$ERROR = false;
		$min_price = \Bitrix\Main\Config\Option::get(self::moduleID, 'MIN_ORDER_PRICE', 1000, SITE_ID);
		$error_text = '';
		if( $summ < $min_price ){
			$ERROR = true;
			$error_text = \Bitrix\Main\Config\Option::get(self::moduleID, 'MIN_ORDER_PRICE_TEXT', GetMessage('MIN_ORDER_PRICE_TEXT_EXAMPLE'));
			$error_text = str_replace( '#PRICE#', SaleFormatCurrency($min_price,$currency), $error_text );
			if($currency)
				$error_text = str_replace( '#PRICE#', SaleFormatCurrency($min_price,$currency), $error_text );
			else
				$error_text = str_replace( '#PRICE#', $min_price, $error_text );
		}
		return $arError=array('ERROR' => $ERROR, 'TEXT' => $error_text);
	}

	public static function showMoreText($text){
		$arText = explode('#MORE_TEXT#', $text);
		if($arText[1])
		{
			$str = $arText[0];
			$str .= '<div class="wrap_more_item">';
				$str .= '<div class="more_text_item">';
				$str .= $arText[1];
				$str .= '</div>';
				$str .= '<div class="open_more"><span class="text"><i class="arrow"></i><span class="pseudo">'.GetMessage("EXPAND_BLOCK").'</span></span></div>';
			$str .= '</div>';
		}
		else
		{
			$str = $text;
		}
		return $str;
	}

	public static function GetCompositeOptions(){
		if(class_exists('CHTMLPagesCache')){
			if(method_exists('CHTMLPagesCache', 'GetOptions')){
				return CHTMLPagesCache::GetOptions();
			}
		}
	
		return array();
	}

	public static function IsCompositeEnabled(){
		if($arHTMLCacheOptions = self::GetCompositeOptions()){
			if(method_exists('CHTMLPagesCache', 'isOn')){
				if (CHTMLPagesCache::isOn()){
					if(isset($arHTMLCacheOptions['AUTO_COMPOSITE']) && $arHTMLCacheOptions['AUTO_COMPOSITE'] === 'Y'){
						return 'AUTO_COMPOSITE';
					}
					else{
						return 'COMPOSITE';
					}
				}
			}
			else{
				if($arHTMLCacheOptions['COMPOSITE'] === 'Y'){
					return 'COMPOSITE';
				}
			}
		}
	
		return false;
	}
	
	public static function EnableComposite($auto = false, $arHTMLCacheOptions = array()){
		if(class_exists('CHTMLPagesCache')){
			if(method_exists('CHTMLPagesCache', 'GetOptions')){
				$arHTMLCacheOptions = is_array($arHTMLCacheOptions) ? $arHTMLCacheOptions : array();
				$arHTMLCacheOptions = array_merge(CHTMLPagesCache::GetOptions(), $arHTMLCacheOptions);
	
				$arHTMLCacheOptions['COMPOSITE'] = $arHTMLCacheOptions['COMPOSITE'] ?? 'Y';
				$arHTMLCacheOptions['AUTO_UPDATE'] = $arHTMLCacheOptions['AUTO_UPDATE'] ?? 'Y'; // standart mode
				$arHTMLCacheOptions['AUTO_UPDATE_TTL'] = $arHTMLCacheOptions['AUTO_UPDATE_TTL'] ?? '0'; // no ttl delay
				$arHTMLCacheOptions['AUTO_COMPOSITE'] = ($auto ? 'Y' : 'N'); // auto composite mode
	
				CHTMLPagesCache::SetEnabled(true);
				CHTMLPagesCache::SetOptions($arHTMLCacheOptions);
				bx_accelerator_reset();
			}
		}
	}

	public static function CopyFaviconToSiteDir($arValue, $siteID = ''){
		if(($siteID)){
			if(is_string($arValue) && $arValue){
				$arValue = unserialize($arValue);
			}

			if(isset($arValue[0]) && $arValue[0]){
				$imageSrc = $_SERVER['DOCUMENT_ROOT'].CFile::GetPath($arValue[0]);
			}
			else{
				if($arTemplate = self::GetSiteTemplate($siteID)){
					$imageSrc = preg_replace('@/+@', '/', $arTemplate['PATH'].'/images/favicon.ico');
				}
			}

			$arSite = CSite::GetByID($siteID)->Fetch();

			if(!file_exists($imageSrc)){
				$imageSrc = preg_replace('@/+@', '/', $arSite['ABS_DOC_ROOT'].'/'.$arSite['DIR'].'/include/favicon.ico');
			}

			if(file_exists($imageSrc)){
				$file_ext = pathinfo($imageSrc, PATHINFO_EXTENSION);
				$fav_ext = $file_ext ? $file_ext : 'ico';				

				$imageDest = preg_replace('@/+@', '/', $arSite['ABS_DOC_ROOT'].'/'.$arSite['DIR'].'/favicon.'.$fav_ext);

				if(file_exists($imageDest)){
					if(sha1_file($imageSrc) == sha1_file($imageDest)){
						return;
					}
				}

				$arFavExtFiles = array('ico', 'png', 'gif', 'bmp', 'jpg', 'svg');
				foreach( $arFavExtFiles as $unlinkExt){
					$imageUnlink = preg_replace('@/+@', '/', $arSite['ABS_DOC_ROOT'].'/'.$arSite['DIR'].'/favicon.'.$unlinkExt);
					
					if(file_exists($imageUnlink)){
						@unlink($imageUnlink);
					}
				}

				//@unlink($imageDest);				
				@copy($imageSrc, $imageDest);
			}
		}
	}

	public static function GetSiteTemplate($siteID = ''){
		static $arCache;
		$arTemplate = array();

		if(strlen($siteID)){
			if(!isset($arCache)){
				$arCache = array();
			}

			if(!isset($arCache[$siteID])){
				$dbRes = CSite::GetTemplateList($siteID);
				while($arTemplate = $dbRes->Fetch()){
					if(!strlen($arTemplate['CONDITION'])){
						if(file_exists(($arTemplate['PATH'] = $_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/'.$arTemplate['TEMPLATE']))){
							$arTemplate['DIR'] = '/bitrix/templates/'.$arTemplate['TEMPLATE'];
							break;
						}
						elseif(file_exists(($arTemplate['PATH'] = $_SERVER['DOCUMENT_ROOT'].'/local/templates/'.$arTemplate['TEMPLATE']))){
							$arTemplate['DIR'] = '/local/templates/'.$arTemplate['TEMPLATE'];
							break;
						}
					}
				}

				if($arTemplate){
					$arCache[$siteID] = $arTemplate;
				}
			}
			else{
				$arTemplate = $arCache[$siteID];
			}
		}

		return $arTemplate;
	}

	public static function __AdmSettingsDrawCustomRow($html){
		echo '<tr><td colspan="2">'.$html.'</td></tr>';
	}

	protected static function __ShowFilePropertyField($name, $arOption, $values){
		global $bCopy, $historyId;

		if(!is_array($values)){
			$values = array($values);
		}

		if($bCopy || empty($values)){
			$values = array('n0' => 0);
		}

		$optionWidth = $arOption['WIDTH'] ? $arOption['WIDTH'] : 200;
		$optionHeight = $arOption['HEIGHT'] ? $arOption['HEIGHT'] : 100;

		if($arOption['MULTIPLE'] == 'N'){
			foreach($values as $key => $val){
				if(is_array($val)){
					$file_id = $val['VALUE'];
				}
				else{
					$file_id = $val;
				}
				if($historyId > 0){
					echo CFileInput::Show($name.'['.$key.']', $file_id,
						array(
							'IMAGE' => $arOption['IMAGE'],
							'PATH' => 'Y',
							'FILE_SIZE' => 'Y',
							'DIMENSIONS' => 'Y',
							'IMAGE_POPUP' => 'Y',
							'MAX_SIZE' => array(
								'W' => $optionWidth,
								'H' => $optionHeight,
							),
						)
					);
				}
				else{
					echo CFileInput::Show($name.'['.$key.']', $file_id,
						array(
							'IMAGE' => $arOption['IMAGE'],
							'PATH' => 'Y',
							'FILE_SIZE' => 'Y',
							'DIMENSIONS' => 'Y',
							'IMAGE_POPUP' => 'Y',
							'MAX_SIZE' => array(
							'W' => $optionWidth,
							'H' => $optionHeight,
							),
						),
						array(
							'upload' => true,
							'medialib' => true,
							'file_dialog' => true,
							'cloud' => true,
							'del' => true,
							'description' => $arOption['WITH_DESCRIPTION'] == 'Y',
						)
					);
				}
				break;
			}
		}
		else{
			$inputName = array();
			foreach($values as $key => $val){
				if(is_array($val)){
					$inputName[$name.'['.$key.']'] = $val['VALUE'];
				}
				else{
					$inputName[$name.'['.$key.']'] = $val;
				}
			}
			if($historyId > 0){
				echo CFileInput::ShowMultiple($inputName, $name.'[n#IND#]',
					array(
						'IMAGE' => $arOption['IMAGE'],
						'PATH' => 'Y',
						'FILE_SIZE' => 'Y',
						'DIMENSIONS' => 'Y',
						'IMAGE_POPUP' => 'Y',
						'MAX_SIZE' => array(
							'W' => $optionWidth,
							'H' => $optionHeight,
						),
					),
				false);
			}
			else{
				echo CFileInput::ShowMultiple($inputName, $name.'[n#IND#]',
					array(
						'IMAGE' => $arOption['IMAGE'],
						'PATH' => 'Y',
						'FILE_SIZE' => 'Y',
						'DIMENSIONS' => 'Y',
						'IMAGE_POPUP' => 'Y',
						'MAX_SIZE' => array(
							'W' => $optionWidth,
							'H' => $optionHeight,
						),
					),
				false,
					array(
						'upload' => true,
						'medialib' => true,
						'file_dialog' => true,
						'cloud' => true,
						'del' => true,
						'description' => $arOption['WITH_DESCRIPTION'] == 'Y',
					)
				);
			}
		}
	}

	public static function GetItemsYear($arParams){
    	$arResult = array();
    	$arItems = CNextCache::CIBLockElement_GetList(array('SORT' => 'ASC', 'NAME' => 'ASC', 'CACHE' => array('TAG' => CNextCache::GetIBlockCacheTag($arParams['IBLOCK_ID']))), array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ACTIVE' => 'Y'), false, false, array('ID', 'NAME', 'ACTIVE_FROM'));
		if($arItems)
		{
			foreach($arItems as $arItem)
			{
				if($arItem['ACTIVE_FROM'])
				{
					if($arDateTime = ParseDateTime($arItem['ACTIVE_FROM'], FORMAT_DATETIME))
						$arResult[$arDateTime['YYYY']] = $arDateTime['YYYY'];
				}
			}
		}
		return $arResult;
    }

	public static function GetYearsItems($iblock_id){
		$arYears=array();
		$rsItems=CIBlockElement::GetList(array(), array('IBLOCK_ID' => $iblock_id, 'ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y'), false, false, array('ID', 'DATE_ACTIVE_FROM'));
		while($arItem=$rsItems->Fetch()){
			if($arItem['DATE_ACTIVE_FROM']){
				$date = explode(' ', $arItem['DATE_ACTIVE_FROM']);
				$date = $date[0];
				$date = explode('.', $date);
				$arYears[$date[2]] = $date[2];
			}
		}
		return $arYears;
	}

	public static function GetItemStickers($arItemStickerProperty = array(), $siteID = false){
		static $type;

		if(!isset($type)){
			$type = Option::get(self::moduleID, 'ITEM_STICKER_CLASS_SOURCE', 'PROPERTY_VALUE', ($siteID ? $siteID : SITE_ID));
		}

		$arStickers = array();
		if($arItemStickerProperty && is_array($arItemStickerProperty) && array_key_exists('VALUE', $arItemStickerProperty) && $arItemStickerProperty['VALUE']){
			if(!is_array($arItemStickerProperty['VALUE'])){
				$arItemStickerProperty['VALUE'] = array($arItemStickerProperty['VALUE']);
				$arItemStickerProperty['VALUE_XML_ID'] = array($arItemStickerProperty['VALUE_XML_ID']);
			}

			foreach($arItemStickerProperty['VALUE'] as $i => $value){
				$arStickers[] = array(
					'VALUE' => $value,
					'CLASS' => 'sticker_'.($type === 'PROPERTY_VALUE' ? CUtil::translit($value, 'ru') : strtolower($arItemStickerProperty['VALUE_XML_ID'][$i])),
				);
			}
		}

		return $arStickers;
	}

	public static function removeDirectory($dir){
		if($objs = glob($dir."/*")){
			foreach($objs as $obj){
				if(is_dir($obj)){
					self::removeDirectory($obj);
				}
				else{
					if(!unlink($obj)){
						if(chmod($obj, 0777)){
							unlink($obj);
						}
					}
				}
			}
		}
		if(!rmdir($dir)){
			if(chmod($dir, 0777)){
				rmdir($dir);
			}
		}
	}

    public static function inputClean($input, $sql = false){
        return $input;
    }

    public static function getBasketCounters(){
    	global $USER, $arTheme;
    	$USER_ID = ($USER_ID = self::GetUserID()) ? $USER_ID : 0;
    	$arResult = false;

    	if(isset($_SESSION['ASPRO_BASKET_COUNTERS'][SITE_ID]))
    	{
    		if(!is_array($_SESSION['ASPRO_BASKET_COUNTERS'][SITE_ID]) || (is_array($_SESSION['ASPRO_BASKET_COUNTERS'][SITE_ID]) && count($_SESSION['ASPRO_BASKET_COUNTERS'][SITE_ID]) && !isset($_SESSION['ASPRO_BASKET_COUNTERS'][$USER_ID][SITE_ID])))
    		{
    			unset($_SESSION['ASPRO_BASKET_COUNTERS'][SITE_ID]);
    		}
    		else
    		{
		    	$arResult = $_SESSION['ASPRO_BASKET_COUNTERS'][SITE_ID][$USER_ID];
    		}
    	}

    	if(!$arResult || !is_array($arResult))
    	{
    		// set default value
    		$arResult = array('READY' => array('COUNT' => 0, 'TITLE' => '', 'HREF' => $arTheme['BASKET_PAGE_URL']), 'DELAY' => array('COUNT' => 0, 'TITLE' => '', 'HREF' => $arTheme['BASKET_PAGE_URL']), 'COMPARE' => array('COUNT' => 0, 'TITLE' => Loc::getMessage('COMPARE_BLOCK'), 'HREF' => $arTheme['COMPARE_PAGE_URL']), 'DEFAULT' => true);
    	}

    	$_SESSION['ASPRO_BASKET_COUNTERS'][SITE_ID] = array($USER_ID => $arResult);
    	return $arResult;
    }

    public static function clearFormatPrice($price){
    	$strPrice = '';
    	if($price)
    	{
    		$arPrice = array();
	    	preg_match('/<span class=\'price_value\'>(.+?)<\/span>/is', $price, $arVals);
			if($arVals[1])
				$arPrice[] = $arVals[1];
			preg_match('/<span class=\'price_currency\'>(.+?)<\/span>/is', $price, $arVals);

			if($arVals[1])
				$arPrice[] = $arVals[1];
			if($arPrice)
				$strPrice = implode('', $arPrice);
			else
				$strPrice = $price;
    	}
    	return $strPrice;
    }

    public static function updateBasketCounters($arValue){
    	global $USER;
    	$USER_ID = ($USER_ID = self::GetUserID()) ? $USER_ID : 0;

    	$arResult = self::getBasketCounters();
    	if($arValue && is_array($arValue)){
    		$arResult = array_replace_recursive($arResult, $arValue);
    	}
    	$arResult['DEFAULT'] = false;

    	$_SESSION['ASPRO_BASKET_COUNTERS'][SITE_ID] = array($USER_ID => $arResult);
    	return $arResult;
    }

    public static function clearBasketCounters(){
    	unset($_SESSION['ASPRO_BASKET_COUNTERS'][SITE_ID]);
    }

	public static function newAction($action = "unknown"){
		$socket = fsockopen('bi.aspro.ru', 80, $errno, $errstr, 10);
		if($socket)
		{
			if(CModule::IncludeModule("main"))
			{
				global $APPLICATION;
				require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");
				$errorMessage = "";
				$serverIP = ($_SERVER["HTTP_X_REAL_IP"] ? $_SERVER["HTTP_X_REAL_IP"] : $_SERVER["SERVER_ADDR"]);
				$arUpdateList = CUpdateClient::GetUpdatesList($errorMessage, "ru", "Y");
				if(array_key_exists("CLIENT", $arUpdateList) && $arUpdateList["CLIENT"][0]["@"]["LICENSE"])
				{
					$edition = $arUpdateList["CLIENT"][0]["@"]["LICENSE"];
				}
				else
				{
					$edition = "UNKNOWN";
				}
				$data = json_encode(
					array(
						"client" => "aspro",
						"install_date" => date("Y-m-d H:i:s"),
						"solution_code" => self::moduleID,
						"ip" => $serverIP,
						"http_host" => $_SERVER["HTTP_HOST"],
						"bitrix_version" => SM_VERSION,
						"bitrix_edition" => $APPLICATION->ConvertCharset($edition, SITE_CHARSET, "utf-8"),
						"bitrix_key_hash" => md5(CUpdateClient::GetLicenseKey()),
						"site_name" => $APPLICATION->ConvertCharset(Option::get("main", "site_name"), SITE_CHARSET, "utf-8"),
						"site_url" => $APPLICATION->ConvertCharset(Option::get("main", "server_name"), SITE_CHARSET, "utf-8"),
						"email_default" => $APPLICATION->ConvertCharset(Option::get("main", "email_from"), SITE_CHARSET, "utf-8"),
						"action" => $action,
					)
				);
				fwrite($socket, "POST /rest/bitrix/installs HTTP/1.1\r\n");
				fwrite($socket, "Host: bi.aspro.ru\r\n");
				fwrite($socket, "Content-type: application/x-www-form-urlencoded\r\n");
				fwrite($socket, "Content-length:".strlen($data)."\r\n");
				fwrite($socket, "Accept:*/*\r\n");
				fwrite($socket, "User-agent:Bitrix Installer\r\n");
				fwrite($socket, "Connection:Close\r\n");
				fwrite($socket, "\r\n");
				fwrite($socket, "$data\r\n");
				fwrite($socket, "\r\n");
				$answer = '';
				while(!feof($socket)){
					$answer.= fgets($socket, 4096);
				}
				fclose($socket);
			}
		}
	}

	public static function AddMeta($arParams = array()){
		self::$arMetaParams = array_merge((array)self::$arMetaParams, (array)$arParams);
	}

	public static function SetMeta(){
		global $APPLICATION, $arSite, $arRegion;

		$PageH1 = $APPLICATION->GetTitle();
		$PageMetaTitleBrowser = $APPLICATION->GetPageProperty('title');
		$DirMetaTitleBrowser = $APPLICATION->GetDirProperty('title');
		$PageMetaDescription = $APPLICATION->GetPageProperty('description');
		$DirMetaDescription = $APPLICATION->GetDirProperty('description');

		// set title
		if(!CNext::IsMainPage())
		{
			if(!strlen($PageMetaTitleBrowser))
			{
				if(!strlen($DirMetaTitleBrowser))
					$PageMetaTitleBrowser = $PageH1.((strlen($PageH1) && strlen($arSite['SITE_NAME'])) ? ' - ' : '' ).$arSite['SITE_NAME'];
			}
		}
		else
		{
			if(!strlen($PageMetaTitleBrowser))
			{
				if(!strlen($DirMetaTitleBrowser))
					$PageMetaTitleBrowser = $arSite['SITE_NAME'].((strlen($arSite['SITE_NAME']) && strlen($PageH1)) ? ' - ' : '' ).$PageH1;
			}
		}

		// check Open Graph required meta properties
		$addr = (CMain::IsHTTPS() ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
		if(self::$arMetaParams['og:title'] && !strlen(self::$arMetaParams['og:title']))
			self::$arMetaParams['og:title'] = $PageMetaTitleBrowser;
		if(self::$arMetaParams['og:type'] && !strlen(self::$arMetaParams['og:type']))
			self::$arMetaParams['og:type'] = 'website';
		if(self::$arMetaParams['og:image'] && !strlen(self::$arMetaParams['og:image']))
		{
			$logo = self::GetFrontParametrValue("LOGO_IMAGE", SITE_ID, false);
			if($logo)
				self::$arMetaParams['og:image'] = $logo;
			elseif(file_exists(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR.'logo.svg')))
				self::$arMetaParams['og:image'] = SITE_DIR.'logo.svg';// site logo
			else
				self::$arMetaParams['og:image'] = SITE_DIR.'logo.png'; // site logo
		}
		if(self::$arMetaParams['og:url'] && !strlen(self::$arMetaParams['og:url']))
			self::$arMetaParams['og:url'] = $_SERVER['REQUEST_URI'];
		if(self::$arMetaParams['og:description'] && !strlen(self::$arMetaParams['og:description']))
			self::$arMetaParams['og:description'] = (strlen($PageMetaDescription) ? $PageMetaDescription : $DirMetaDescription);

		if(self::$arMetaParams['og:description'])
		{
			$obParser = new CTextParser;
			self::$arMetaParams['og:description'] = $obParser->html_cut(self::$arMetaParams['og:description'], 300);
		}

		foreach(self::$arMetaParams as $metaName => $metaValue)
		{
			if(strlen($metaValue = strip_tags($metaValue)))
			{
				$metaValue = str_replace('//', '/', $metaValue);
				if($metaName === 'og:image' || $metaName === 'og:url'){
					$metaValue = $addr.$metaValue;
				}
				$metaValue = str_replace('#REGION_TAG_', '#REGION_STRIP_TAG_', $metaValue);
				$APPLICATION->AddHeadString('<meta property="'.$metaName.'" content="'.$metaValue.'" />', true);
				if($metaName === 'og:image'){
					$APPLICATION->AddHeadString('<link rel="image_src" href="'.$metaValue.'"  />', true);
				}
			}
		}

		self::setRegionSeoMarks();
	}

	public static function setRegionSeoMarks(){
		global $arRegion;
		if($arRegion)
		{
			$arTagSeoMarks = array();
			foreach($arRegion as $key => $value)
			{
				if(strpos($key, 'PROPERTY_REGION_TAG') !== false && strpos($key, '_VALUE_ID') === false)
				{
					$tag_name = str_replace(array('PROPERTY_', '_VALUE'), '', $key);
					$arTagSeoMarks['#'.$tag_name.'#'] = $key;
				}
			}

			if($arTagSeoMarks)
				CNextRegionality::addSeoMarks($arTagSeoMarks);
		}
	}

	public static function getViewedProducts($userID=false, $siteID=false){
		global $arTheme, $STARTTIME;
		$arResult = array();
		$siteID = $siteID ? $siteID : SITE_ID;
		$localKey = 'NEXT_VIEWED_ITEMS_'.$siteID;

		if($IsViewedTypeLocal = ($arTheme['VIEWED_TYPE']['VALUE'] === 'LOCAL'))
		{
			$arViewed = (isset($_COOKIE[$localKey]) && strlen($_COOKIE[$localKey])) ? json_decode($_COOKIE[$localKey], true) : array();

			if($arViewed && is_array($arViewed))
			{
				$viewedDays = Option::get("sale", "viewed_time", "5");
				$viewedCntMax = Option::get("sale", "viewed_count", "10");
				$DIETIME = $STARTTIME - $viewedDays * 86400000;

				// delete old items
				foreach($arViewed as $ID => $arItem)
				{
					if($arItem[0] < $DIETIME)
					{
						unset($arViewed[$ID]);
						continue;
					}

					$arResult[$ID] = $arItem[0];
				}

				// sort by ACTIVE_FROM
				arsort($arResult);

				// make IDs array
				$arResult = array_keys($arResult);

				// only $viewedCntMax items
				$arResult = array_slice($arResult, 0, $viewedCntMax);
			}
		}
		else
		{
			\Bitrix\Main\Loader::includeModule('sale');
			\Bitrix\Main\Loader::includeModule('catalog');
			$userID = $userID ? $userID : (int)CSaleBasket::GetBasketUserID(false);

			$viewedIterator = \Bitrix\Catalog\CatalogViewedProductTable::GetList(array(
				'select' => array('PRODUCT_ID', 'ELEMENT_ID'),
				'filter' => array('=FUSER_ID' => $userID, '=SITE_ID' => $siteID),
				'order' => array('DATE_VISIT' => 'DESC'),
				'limit' => 8
			));
			while($viewedProduct = $viewedIterator->fetch())
			{
				$viewedProduct['ELEMENT_ID'] = (int)$viewedProduct['ELEMENT_ID'];
				$viewedProduct['PRODUCT_ID'] = (int)$viewedProduct['PRODUCT_ID'];
				$arResult[$viewedProduct['PRODUCT_ID']] = $viewedProduct['ELEMENT_ID'];
			}
		}

		return $arResult;
	}

	public static function setFooterTitle(){
		global $APPLICATION, $arSite, $arRegion;

		$bShowSiteName = (\Bitrix\Main\Config\Option::get(self::moduleID, "HIDE_SITE_NAME_TITLE", "N") == "N");
		$sPostfix = ($bShowSiteName ? ' - '.$arSite['SITE_NAME'] : '');

		if($arRegion)
		{
			CNextRegionality::addSeoMarks(array('#REGION_ID#' => 'ID'));
			CNextRegionality::replaceSeoMarks();
		}
		else
		{
			if(strlen($APPLICATION->GetPageProperty('title')) > 1)
				$title = $APPLICATION->GetPageProperty('title');
			else
				$title = $APPLICATION->GetTitle();

			if(!CNext::IsMainPage())
			{
				$APPLICATION->SetPageProperty("title", $title.$sPostfix);
			}
			else
			{
				if(!empty($title))
					$APPLICATION->SetPageProperty("title", $title);
				else
					$APPLICATION->SetPageProperty("title", $arSite['SITE_NAME']);
			}
		}
		self::SetMeta();

		if(!defined('ADMIN_SECTION') && isset($_REQUEST['auth_service_id']) && $_REQUEST['auth_service_id'])
		{
			if($_REQUEST['auth_service_id']):
				global $APPLICATION, $CACHE_MANAGER;?>
				<?$APPLICATION->IncludeComponent(
					"bitrix:system.auth.form",
					"popup",
					array(
						"PROFILE_URL" => "",
						"SHOW_ERRORS" => "Y",
						"POPUP_AUTH" => "Y"
					)
				);?>
			<?endif;?>
		<?}
	}

	public static function getBasketItems($iblockId=0, $field="PRODUCT_ID"){
		$basket_items = $delay_items = $subscribe_items = $not_available_items = array();
		$arItems = array();
		// static $arItems;
		if(self::IsMainPage())
			$arSubscribeList = false;
		// if($arItems === NULL)
		// {
			$bUseSubscribeManager = ($arSubscribeList = self::getUserSubscribeList()) !== false;
			if(\Bitrix\Main\Loader::includeModule("sale"))
			{
				$arBasketItems=array();
				$dbRes = CSaleBasket::GetList(array("NAME" => "ASC", "ID" => "ASC"), array("FUSER_ID" => CSaleBasket::GetBasketUserID(), "LID" => SITE_ID, "ORDER_ID" => NULL), false, false, array("ID", "PRODUCT_ID", "DELAY", "SUBSCRIBE", "CAN_BUY", "TYPE", "SET_PARENT_ID"));
				while($item = $dbRes->Fetch())
					$arBasketItems[] = $item;

				global $compare_items;
				if(!is_array($compare_items))
				{
					$compare_items = array();
					$iblockId=((isset($iblockId) && $iblockId) ? $iblockId : \Bitrix\Main\Config\Option::get(self::moduleID, "CATALOG_IBLOCK_ID", CNextCache::$arIBlocks[SITE_ID]['aspro_next_catalog']['aspro_next_catalog'][0], SITE_ID ));
					if($iblockId && isset($_SESSION["CATALOG_COMPARE_LIST"][$iblockId]["ITEMS"]))
						$compare_items = array_keys($_SESSION["CATALOG_COMPARE_LIST"][$iblockId]["ITEMS"]);

				}
				if($arBasketItems)
				{
					foreach($arBasketItems as $arBasketItem)
					{
						if(CSaleBasketHelper::isSetItem($arBasketItem)) // set item
							continue;
						if($arBasketItem["DELAY"]=="N" && $arBasketItem["CAN_BUY"] == "Y" && $arBasketItem["SUBSCRIBE"] == "N")
							$basket_items[] = $arBasketItem[$field];
						elseif($arBasketItem["DELAY"]=="Y" && $arBasketItem["CAN_BUY"] == "Y" && $arBasketItem["SUBSCRIBE"] == "N")
							$delay_items[] = $arBasketItem[$field];
						elseif($arBasketItem["SUBSCRIBE"]=="Y")
							$subscribe_items[] = $arBasketItem[$field];
						else
							$not_available_items[] = $arBasketItem[$field];
					}
				}
				$arItems["BASKET"]=array_combine($basket_items, $basket_items);
				$arItems["DELAY"]=array_combine($delay_items, $delay_items);
				$arItems["SUBSCRIBE"]=array_combine($subscribe_items, $subscribe_items);
				$arItems["NOT_AVAILABLE"]=array_combine($not_available_items, $not_available_items);
				$arItems["COMPARE"]=array_combine($compare_items, $compare_items);
			}

			if($bUseSubscribeManager && $arSubscribeList)
			{
				foreach($arSubscribeList as $PRODUCT_ID => $arIDs)
					$arItems['SUBSCRIBE'][$PRODUCT_ID] = $PRODUCT_ID;
			}
		// }

		return $arItems;
	}

	public static function getUserSubscribeList($userId = false){
		if(\Bitrix\Main\Loader::includeModule('catalog'))
		{
			if(class_exists('\Bitrix\Catalog\Product\SubscribeManager'))
			{
				global $USER, $DB;
				$userId = $userId ? intval($userId) : (($USER && is_object($USER) && $USER->isAuthorized()) ? self::GetUserID() : false);
				$bSubscribeProducts = (isset($_SESSION['SUBSCRIBE_PRODUCT']['LIST_PRODUCT_ID']) && $_SESSION['SUBSCRIBE_PRODUCT']['LIST_PRODUCT_ID']);

				if($userId || $bSubscribeProducts)
				{
					$arSubscribeList = array();
					$subscribeManager = new \Bitrix\Catalog\Product\SubscribeManager;

					$filter = array(
						'USER_ID' => $userId,
						'=SITE_ID' => SITE_ID,
						array(
							'LOGIC' => 'OR',
							array('=DATE_TO' => false),
							array('>DATE_TO' => date($DB->dateFormatToPHP(\CLang::getDateFormat('FULL')), time()))
						),
					);

					$resultObject = \Bitrix\Catalog\SubscribeTable::getList(
						array(
							'select' => array(
								'ID',
								'ITEM_ID',
							),
							'filter' => $filter,
						)
					);

					while($arItem = $resultObject->fetch())
					{
						$arSubscribeList[$arItem['ITEM_ID']][] = $arItem['ID'];
					}
					if(!$userId && $bSubscribeProducts)
					{
						foreach($arSubscribeList as $key => $id)
						{
							if(!$_SESSION['SUBSCRIBE_PRODUCT']['LIST_PRODUCT_ID'][$key])
								unset($arSubscribeList[$key]);
						}
					}

					return $arSubscribeList;
				}
			}
		}

		return false;
	}

	public static function showFooterBasket(){
		global $arTheme, $APPLICATION, $arRegion;
		if($arRegion)
		{
			CSaleBasket::UpdateBasketPrices(CSaleBasket::GetBasketUserID(), SITE_ID);
		}
		Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('basketitems-block');

		$arItems=self::getBasketItems();?>

		<?//if(self::IsBasketPage()):?>
			<?if(\Bitrix\Main\Loader::includeModule('currency'))
			{
				CJSCore::Init(array('currency'));
				$currencyFormat = CCurrencyLang::GetFormatDescription(CSaleLang::GetLangCurrency(SITE_ID));
			}
			?>
			<script type="text/javascript">
				<?if(is_array($currencyFormat)):?>
					function jsPriceFormat(_number){
						BX.Currency.setCurrencyFormat('<?=CSaleLang::GetLangCurrency(SITE_ID);?>', <? echo CUtil::PhpToJSObject($currencyFormat, false, true); ?>);
						return BX.Currency.currencyFormat(_number, '<?=CSaleLang::GetLangCurrency(SITE_ID);?>', true);
					}
				<?endif;?>
			</script>
		<?//endif;?>
		<script type="text/javascript">
			var arBasketAspro = <? echo CUtil::PhpToJSObject($arItems, false, true); ?>;
			$(document).ready(function(){
				setBasketStatusBtn();
			});
		</script>
		<?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('basketitems-block', '');?>
		<?Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('all-basketitems-block');?>
		<?$basketType = (isset($arTheme['ORDER_BASKET_VIEW']['VALUE']) ? $arTheme['ORDER_BASKET_VIEW']['VALUE'] : $arTheme['ORDER_BASKET_VIEW']);?>
		<?if($basketType == 'BOTTOM'):?>
			<div class="basket_bottom_block basket_fill_<?=$arTheme['ORDER_BASKET_COLOR']['VALUE'];?>">
				<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
					array(
						"COMPONENT_TEMPLATE" => ".default",
						"PATH" => SITE_DIR."include/footer/comp_basket_bottom.php",
						"AREA_FILE_SHOW" => "file",
						"AREA_FILE_SUFFIX" => "",
						"AREA_FILE_RECURSIVE" => "Y",
						"EDIT_TEMPLATE" => "standard.php"
					),
					false
				);?>
			</div>
		<?elseif($basketType != 'NORMAL'):?>
			<div class="basket_wrapp <?=(self::IsBasketPage() ? 'basket_page' : '');?> <?=strtolower($basketType);?> basket_fill_<?=$arTheme['ORDER_BASKET_COLOR']['VALUE'];?>">
				<div class="header-cart fly" id="basket_line">
					<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
						array(
							"COMPONENT_TEMPLATE" => ".default",
							"PATH" => SITE_DIR."include/top_page/comp_basket_top.php",
							"AREA_FILE_SHOW" => "file",
							"AREA_FILE_SUFFIX" => "",
							"AREA_FILE_RECURSIVE" => "Y",
							"EDIT_TEMPLATE" => "standard.php"
						),
						false
					);?>
				</div>
			</div>
		<?endif;?>
		<?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('all-basketitems-block', '');?>
	<?}

	public static function GetCurrentSectionSubSectionFilter(&$arVariables, &$arParams, $CurrentSectionID = false){
		$arFilter = array('IBLOCK_ID' => $arParams['IBLOCK_ID']);
		if($arParams['CHECK_DATES'] == 'Y')
		{
			$arFilter = array_merge($arFilter, array('ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y', 'ACTIVE_DATE' => 'Y'));
		}
		if(!$arFilter['SECTION_ID'] = ($CurrentSectionID !== false ? $CurrentSectionID : ($arVariables['SECTION_ID'] ? $arVariables['SECTION_ID'] : false)))
		{
			$arFilter['INCLUDE_SUBSECTIONS'] = 'N';array_merge($arFilter, array('INCLUDE_SUBSECTIONS' => 'N', 'DEPTH_LEVEL' => '1'));
			$arFilter['DEPTH_LEVEL'] = '1';
			unset($arFilter['GLOBAL_ACTIVE']);
		}
		return $arFilter;
	}

	public static function GetIBlockAllElementsFilter(&$arParams){
		global $arRegion;
		$arFilter = array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'INCLUDE_SUBSECTIONS' => 'Y');
		if(isset($arParams['CHECK_DATES']) && $arParams['CHECK_DATES'] == 'Y')
		{
			$arFilter = array_merge($arFilter, array('ACTIVE' => 'Y', 'ACTIVE_DATE' => 'Y'));
		}
		if(isset($arParams['SHOW_DEACTIVATED']) && $arParams['SHOW_DEACTIVATED'] === 'N')
		{ // for catalog component
			$arFilter = array_merge($arFilter, array('ACTIVE' => 'Y'));
		}
		if(strlen($arParams['FILTER_NAME']) && (array)$GLOBALS[$arParams['FILTER_NAME']])
		{
			$arFilter = array_merge($arFilter, (array)$GLOBALS[$arParams['FILTER_NAME']]);
		}
		/*if($arRegion)
		{
			if(!isset($arFilter['PROPERTY_LINK_REGION']))
				$arFilter['PROPERTY_LINK_REGION'] = $arRegion['ID'];
		}*/
		return $arFilter;
	}

	public static function prepareItemMapHtml($arShop, $bStore = "N", $arParams = array(), $bBigBlock = "N"){
		$html = '<div class="map_info_store">';

		if($arParams['TITLE_BLOCK_DETAIL_NAME'])
			$html .= '<div class="subtitle font_upper muted">'.$arParams['TITLE_BLOCK_DETAIL_NAME'].'</div>';

		$html .= '<div class="title option-font-bold '.($bBigBlock != 'Y' ? 'font_mlg' : 'font_exlg').'">'.(strlen($arShop["URL"]) ? '<a class="dark_link" href="'.$arShop["URL"].'">' : '').$arShop["ADDRESS"].(strlen($arShop["URL"]) ? '</a>' : '').'</div>';
		if($arShop['METRO'] || $arShop['SCHEDULE'] || $arShop['EMAIL'] || $arShop['PHONE'] || isset($arShop['QUANTITY'])){
			if(isset($arShop['QUANTITY']))
			{
				$html .= $arShop['QUANTITY'];
			}
			$html .= '<div class="properties">';


				$html .= ($arShop['METRO'] ? '<div class="property schedule"><div class="title-prop font_upper muted">'.($bStore == 'Y' ? Loc::getMessage('CONTACT_METRO') : $arShop['DISPLAY_PROPERTIES']['METRO']['NAME']).'</div><div class="value">'.$arShop['METRO_PLACEMARK_HTML'].'</div></div>' : '');
				$html .= (strlen($arShop['SCHEDULE']) ? '<div class="property schedule"><div class="title-prop font_upper muted">'.($bStore == 'Y' ? Loc::getMessage('CONTACT_SCHEDULE') : $arShop['DISPLAY_PROPERTIES']['SCHEDULE']['NAME']).'</div><div class="value">'.$arShop['SCHEDULE'].'</div></div>' : '');

				if($arShop['PHONE']){
					$phone = '';
					if(is_array($arShop['PHONE'])){
						foreach($arShop['PHONE'] as $value){
							$phone .= '<div class="value"><a class="dark_link" rel= "nofollow" href="tel:'.str_replace(array(' ', ',', '-', '(', ')'), '', $value).'">'.$value.'</a></div>';
						}
					}
					else{
						$phone = '<div class="value"><a class="dark_link" rel= "nofollow" href="tel:'.str_replace(array(' ', ',', '-', '(', ')'), '', $arShop['PHONE']).'">'.$arShop['PHONE'].'</a></div>';


					}
					$html .= '<div class="property phone"><div class="title-prop font_upper muted">'.($bStore == 'Y' ? Loc::getMessage('CONTACT_PHONE') : $arShop['DISPLAY_PROPERTIES']['PHONE']['NAME']).'</div>'.$phone.'</div>';
				}

				$html .= (strlen($arShop['EMAIL']) ? '<div class="property email"><div class="title-prop font_upper muted">'.($bStore == 'Y' ? Loc::getMessage('CONTACT_EMAIL') : $arShop['DISPLAY_PROPERTIES']['EMAIL']['NAME']).'</div><div class="value"><a class="dark_link" href="mailto:'.$arShop['EMAIL'].'">'.$arShop['EMAIL'].'</a></div></div>' : '');
			$html .= '</div>';
		}
		$html .= '</div>';
		return $html;
	}

	public static function prepareShopListArray($arShops){
		$arFormatShops=array();

		$arPlacemarks = array();

		if(is_array($arShops)){
			foreach($arShops as $i => $arShop){
				if(isset($arShop['IBLOCK_ID'])){
					$arShop['TITLE'] = ($arShop['FIELDS']['NAME'] ? $arShop['NAME'] : '');
					$imageID = (($arShop['FIELDS']['PREVIEW_PICTURE'] && $arShop["PREVIEW_PICTURE"]['ID']) ? $arShop["PREVIEW_PICTURE"]['ID'] : (($arShop['FIELD_CODE']['DETAIL_PICTURE'] && $arShop["DETAIL_PICTURE"]['ID']) ? $arShop["DETAIL_PICTURE"]['ID'] : false));
					$arShop['IMAGE'] = ($imageID ? CFile::ResizeImageGet($imageID, array('width' => 100, 'height' => 69), BX_RESIZE_IMAGE_EXACT) : '');
					$arShop['ADDRESS'] = $arShop['DISPLAY_PROPERTIES']['ADDRESS']['VALUE'];
					$arShop['ADDRESS'] = $arShop['TITLE'].((strlen($arShop['TITLE']) && strlen($arShop['ADDRESS'])) ? ', ' : '').$arShop['ADDRESS'];
					$arShop['PHONE'] = $arShop['DISPLAY_PROPERTIES']['PHONE']['VALUE'];
					$arShop['EMAIL'] = $arShop['DISPLAY_PROPERTIES']['EMAIL']['VALUE'];
					if($arShop['DISPLAY_PROPERTIES']['SCHEDULE']['VALUE']['TYPE'] == 'html'){
						$arShop['SCHEDULE'] = htmlspecialchars_decode($arShop['DISPLAY_PROPERTIES']['SCHEDULE']['~VALUE']['TEXT']);
					}
					else{
						$arShop['SCHEDULE'] = nl2br($arShop['DISPLAY_PROPERTIES']['SCHEDULE']['~VALUE']['TEXT']);
					}
					$arShop['URL'] = $arShop['DETAIL_PAGE_URL'];
					$arShop['METRO_PLACEMARK_HTML'] = '';
					if($arShop['METRO'] = $arShop['DISPLAY_PROPERTIES']['METRO']['VALUE']){
						if(!is_array($arShop['METRO'])){
							$arShop['METRO'] = array($arShop['METRO']);
						}
						foreach($arShop['METRO'] as $metro){
							$arShop['METRO_PLACEMARK_HTML'] .= '<div class="metro"><i></i>'.$metro.'</div>';
						}
					}
					$arShop['DESCRIPTION'] = $arShop['DETAIL_TEXT'];
					$arShop['GPS_S'] = false;
					$arShop['GPS_N'] = false;
					if($arStoreMap = explode(',', $arShop['DISPLAY_PROPERTIES']['MAP']['VALUE'])){
						$arShop['GPS_S'] = $arStoreMap[0];
						$arShop['GPS_N'] = $arStoreMap[1];
					}

					if($arShop['GPS_S'] && $arShop['GPS_N']){
						$mapLAT += $arShop['GPS_S'];
						$mapLON += $arShop['GPS_N'];
						$str_phones = '';
						if($arShop['PHONE'])
						{
							$str_phones .= '<div class="phone">';
							foreach($arShop['PHONE'] as $phone)
							{
								$str_phones .= '<br><a rel="nofollow" href="tel:'.str_replace(array(' ', ',', '-', '(', ')'), '', $phone).'">'.$phone.'</a>';
							}
							$str_phones .= '</div>';
						}
						$html = self::prepareItemMapHtml($arShop);

						$arPlacemarks[] = array(
							"ID" => $arShop["ID"],
							"LAT" => $arShop['GPS_S'],
							"LON" => $arShop['GPS_N'],
							"TEXT" => $arShop["TITLE"],
							"TEXT" => $html,
							"HTML" => '<div class="title">'.(strlen($arShop["URL"]) ? '<a href="'.$arShop["URL"].'">' : '').$arShop["ADDRESS"].(strlen($arShop["URL"]) ? '</a>' : '').'</div><div class="info-content">'.($arShop['METRO'] ? $arShop['METRO_PLACEMARK_HTML'] : '').(strlen($arShop['SCHEDULE']) ? '<div class="schedule">'.$arShop['SCHEDULE'].'</div>' : '').$str_phones.(strlen($arShop['EMAIL']) ? '<div class="email"><a rel="nofollow" href="mailto:'.$arShop['EMAIL'].'">'.$arShop['EMAIL'].'</a></div>' : '').'</div>'.(strlen($arShop['URL']) ? '<a rel="nofollow" class="button" href="'.$arShop["URL"].'"><span>'.GetMessage('DETAIL').'</span></a>' : '')
						);
					}
				}
				else{
					$str_phones = '';
					if($arShop['PHONE'])
					{
						$arShop['PHONE'] = explode(",", $arShop['PHONE']);
						$str_phones .= '<div class="phone">';
						foreach($arShop['PHONE'] as $phone)
						{
							$str_phones .= '<br><a rel="nofollow" href="tel:'.str_replace(array(' ', ',', '-', '(', ')'), '', $phone).'">'.$phone.'</a>';
						}
						$str_phones .= '</div>';
					}
					if($arShop['GPS_S'] && $arShop['GPS_N']){
						$mapLAT += $arShop['GPS_N'];
						$mapLON += $arShop['GPS_S'];

						$html = self::prepareItemMapHtml($arShop, "Y");
						$arPlacemarks[] = array(
							"ID" => $arShop["ID"],
							"LON" => $arShop['GPS_S'],
							"LAT" => $arShop['GPS_N'],
							"TEXT" => $html,
							"HTML" => $html
						);
					}
				}
				$arShops[$i] = $arShop;
			}
		}
		$arFormatShops["SHOPS"]=$arShops;
		$arFormatShops["PLACEMARKS"]=$arPlacemarks;
		$arFormatShops["POINTS"]=array(
			"LAT" => $mapLAT,
			"LON" => $mapLON,
		);

		return $arFormatShops;
	}

	public static function prepareShopDetailArray($arShop, $arParams){
		$mapLAT = $mapLON = 0;
		$arPlacemarks = array();
		$arPhotos = array();
		$arFormatShops=array();

		if(is_array($arShop)){
			if(isset($arShop['IBLOCK_ID'])){
				$arShop['LIST_URL'] = $arShop['LIST_PAGE_URL'];
				$arShop['TITLE'] = (in_array('NAME', $arParams['FIELD_CODE']) ? $arShop['NAME'] : '');
				$arShop['ADDRESS'] = $arShop['DISPLAY_PROPERTIES']['ADDRESS']['VALUE'];
				$arShop['ADDRESS'] = $arShop['TITLE'].((strlen($arShop['TITLE']) && strlen($arShop['ADDRESS'])) ? ', ' : '').$arShop['ADDRESS'];
				$arShop['PHONE'] = $arShop['DISPLAY_PROPERTIES']['PHONE']['VALUE'];
				$arShop['EMAIL'] = $arShop['DISPLAY_PROPERTIES']['EMAIL']['VALUE'];
				if($arShop['DISPLAY_PROPERTIES']['SCHEDULE']['VALUE']['TYPE'] == 'html'){
					$arShop['SCHEDULE'] = htmlspecialchars_decode($arShop['DISPLAY_PROPERTIES']['SCHEDULE']['~VALUE']['TEXT']);
				}
				else{
					$arShop['SCHEDULE'] = nl2br($arShop['DISPLAY_PROPERTIES']['SCHEDULE']['~VALUE']['TEXT']);
				}
				$arShop['URL'] = $arShop['DETAIL_PAGE_URL'];
				$arShop['METRO_PLACEMARK_HTML'] = '';
				if($arShop['METRO'] = $arShop['DISPLAY_PROPERTIES']['METRO']['VALUE']){
					if(!is_array($arShop['METRO'])){
						$arShop['METRO'] = array($arShop['METRO']);
					}
					foreach($arShop['METRO'] as $metro){
						$arShop['METRO_PLACEMARK_HTML'] .= '<div class="metro"><i></i>'.$metro.'</div>';
					}
				}
				$arShop['GPS_S'] = false;
				$arShop['GPS_N'] = false;
				if($arStoreMap = explode(',', $arShop['DISPLAY_PROPERTIES']['MAP']['VALUE'])){
					$arShop['GPS_S'] = $arStoreMap[0];
					$arShop['GPS_N'] = $arStoreMap[1];
				}

				if($arShop['GPS_S'] && $arShop['GPS_N']){
					$mapLAT += $arShop['GPS_S'];
					$mapLON += $arShop['GPS_N'];
					$str_phones = '';
					if($arShop['PHONE'])
					{
						$str_phones .= '<div class="phone">';
						foreach($arShop['PHONE'] as $phone)
						{
							$str_phones .= '<br><a rel="nofollow" href="tel:'.str_replace(array(' ', ',', '-', '(', ')'), '', $phone).'">'.$phone.'</a>';
						}
						$str_phones .= '</div>';
					}
					$arPlacemarks[] = array(
						"ID" => $arShop["ID"],
						"LAT" => $arShop['GPS_S'],
						"LON" => $arShop['GPS_N'],
						"TEXT" => $arShop["TITLE"],
						"HTML" => '<div class="title">'.(strlen($arShop["URL"]) ? '<a href="'.$arShop["URL"].'">' : '').$arShop["ADDRESS"].(strlen($arShop["URL"]) ? '</a>' : '').'</div><div class="info-content">'.($arShop['METRO'] ? $arShop['METRO_PLACEMARK_HTML'] : '').(strlen($arShop['SCHEDULE']) ? '<div class="schedule">'.$arShop['SCHEDULE'].'</div>' : '').$str_phones.(strlen($arShop['EMAIL']) ? '<div class="email"><a rel="nofollow" href="mailto:'.$arShop['EMAIL'].'">'.$arShop['EMAIL'].'</a></div>' : '').'</div>'.(strlen($arShop['URL']) ? '<a rel="nofollow" class="button" href="'.$arShop["URL"].'"><span>'.GetMessage('DETAIL').'</span></a>' : '')
					);
				}
			}
			else{
				$arShop["TITLE"] = htmlspecialchars_decode($arShop["TITLE"]);
				$arShop["ADDRESS"] = htmlspecialchars_decode($arShop["ADDRESS"]);
				$arShop["ADDRESS"] = (strlen($arShop["TITLE"]) ? $arShop["TITLE"].', ' : '').$arShop["ADDRESS"];
				$arShop["DESCRIPTION"] = htmlspecialchars_decode($arShop['DESCRIPTION']);
				$arShop['SCHEDULE'] = htmlspecialchars_decode($arShop['SCHEDULE']);

				$str_phones = '';
				if($arShop['PHONE'])
				{
					$arShop['PHONE'] = explode(",", $arShop['PHONE']);
					$str_phones .= '<div class="phone">';
					foreach($arShop['PHONE'] as $phone)
					{
						$str_phones .= '<br><a rel="nofollow" href="tel:'.str_replace(array(' ', ',', '-', '(', ')'), '', $phone).'">'.$phone.'</a>';
					}
					$str_phones .= '</div>';
				}
				if($arShop['GPS_S'] && $arShop['GPS_N']){
					$mapLAT += $arShop['GPS_N'];
					$mapLON += $arShop['GPS_S'];
					$arPlacemarks[] = array(
						"ID" => $arShop["ID"],
						"LON" => $arShop['GPS_S'],
						"LAT" => $arShop['GPS_N'],
						"TEXT" => $arShop["TITLE"],
						"HTML" => '<div class="title">'.(strlen($arShop["URL"]) ? '<a href="'.$arShop["URL"].'">' : '').$arShop["ADDRESS"].(strlen($arShop["URL"]) ? '</a>' : '').'</div><div class="info-content">'.($arShop['METRO'] ? $arShop['METRO_PLACEMARK_HTML'] : '').(strlen($arShop['SCHEDULE']) ? '<div class="schedule">'.$arShop['SCHEDULE'].'</div>' : '').$str_phones.(strlen($arShop['EMAIL']) ? '<div class="email"><a rel="nofollow" href="mailto:'.$arShop['EMAIL'].'">'.$arShop['EMAIL'].'</a></div>' : '').'</div>'.(strlen($arShop['URL']) ? '<a rel="nofollow" class="button" href="'.$arShop["URL"].'"><span>'.GetMessage('DETAIL').'</span></a>' : '')
					);
				}
			}
		}
		$arFormatShops["SHOP"]=$arShop;
		$arFormatShops["PLACEMARKS"]=$arPlacemarks;
		$arFormatShops["POINTS"]=array(
			"LAT" => $mapLAT,
			"LON" => $mapLON,
		);

		return $arFormatShops;

	}

	public static function drawShopsList($arShops, $arParams, $showMap="Y"){
		global $APPLICATION;
		$mapLAT = $mapLON = 0;
		$arPlacemarks = array();

		if(is_array($arShops)){
			foreach($arShops as $i => $arShop){
				if(isset($arShop['IBLOCK_ID'])){
					$arShop['TITLE'] = (in_array('NAME', $arParams['FIELD_CODE']) ? strip_tags($arShop['~NAME']) : '');

					$imageID = ((in_array('PREVIEW_PICTURE', $arParams['FIELD_CODE']) && $arShop["PREVIEW_PICTURE"]['ID']) ? $arShop["PREVIEW_PICTURE"]['ID'] : ((in_array('DETAIL_PICTURE', $arParams['FIELD_CODE']) && $arShop["DETAIL_PICTURE"]['ID']) ? $arShop["DETAIL_PICTURE"]['ID'] : false));
					$arShop['IMAGE'] = ($imageID ? CFile::ResizeImageGet($imageID, array('width' => 100, 'height' => 69), BX_RESIZE_IMAGE_EXACT) : '');
					$arShop['ADDRESS'] = $arShop['DISPLAY_PROPERTIES']['ADDRESS']['VALUE'];
					$arShop['ADDRESS'] = $arShop['TITLE'].((strlen($arShop['TITLE']) && strlen($arShop['ADDRESS'])) ? ', ' : '').$arShop['ADDRESS'];
					$arShop['PHONE'] =  $arShop['DISPLAY_PROPERTIES']['PHONE']['VALUE'];
					$arShop['EMAIL'] = $arShop['DISPLAY_PROPERTIES']['EMAIL']['VALUE'];

					if(strToLower($arShop['DISPLAY_PROPERTIES']['SCHEDULE']['VALUE']['TYPE']) == 'html'){
						$arShop['SCHEDULE'] = htmlspecialchars_decode($arShop['DISPLAY_PROPERTIES']['SCHEDULE']['~VALUE']['TEXT']);
					}
					else{
						$arShop['SCHEDULE'] = nl2br($arShop['DISPLAY_PROPERTIES']['SCHEDULE']['~VALUE']['TEXT']);
					}
					$arShop['URL'] = $arShop['DETAIL_PAGE_URL'];
					$arShop['METRO_PLACEMARK_HTML'] = '';
					if($arShop['METRO'] = $arShop['DISPLAY_PROPERTIES']['METRO']['VALUE']){
						if(!is_array($arShop['METRO'])){
							$arShop['METRO'] = array($arShop['METRO']);
						}
						foreach($arShop['METRO'] as $metro){
							$arShop['METRO_PLACEMARK_HTML'] .= '<div class="metro"><i></i>'.$metro.'</div>';
						}
					}
					$arShop['DESCRIPTION'] = $arShop['DETAIL_TEXT'];
					$arShop['GPS_S'] = false;
					$arShop['GPS_N'] = false;
					if($arStoreMap = explode(',', $arShop['DISPLAY_PROPERTIES']['MAP']['VALUE'])){
						$arShop['GPS_S'] = $arStoreMap[0];
						$arShop['GPS_N'] = $arStoreMap[1];
					}

					if($arShop['GPS_S'] && $arShop['GPS_N']){
						$mapLAT += $arShop['GPS_S'];
						$mapLON += $arShop['GPS_N'];
						$str_phones = '';
						if($arShop['PHONE'])
						{
							foreach($arShop['PHONE'] as $phone)
							{
								$str_phones .= '<div class="phone"><a rel="nofollow" href="tel:'.str_replace(array(' ', ',', '-', '(', ')'), '', $phone).'">'.$phone.'</a></div>';
							}
						}
						$html = self::prepareItemMapHtml($arShop);
						$arPlacemarks[] = array(
							"ID" => $arShop["ID"],
							"LAT" => $arShop['GPS_S'],
							"LON" => $arShop['GPS_N'],
							"TEXT" => $html,
							"HTML" => '<div class="title">'.(strlen($arShop["URL"]) ? '<a href="'.$arShop["URL"].'">' : '').$arShop["ADDRESS"].(strlen($arShop["URL"]) ? '</a>' : '').'</div><div class="info-content">'.($arShop['METRO'] ? $arShop['METRO_PLACEMARK_HTML'] : '').(strlen($arShop['SCHEDULE']) ? '<div class="schedule">'.$arShop['SCHEDULE'].'</div>' : '').$str_phones.(strlen($arShop['EMAIL']) ? '<div class="email"><a rel="nofollow" href="mailto:'.$arShop['EMAIL'].'">'.$arShop['EMAIL'].'</a></div>' : '').'</div>'.(strlen($arShop['URL']) ? '<a rel="nofollow" class="button" href="'.$arShop["URL"].'"><span>'.GetMessage('DETAIL').'</span></a>' : '')
						);
					}
				}
				else{
					$str_phones = '';
					if($arShop['PHONE'])
					{
						$arShop['PHONE'] = explode(",", $arShop['PHONE']);
						foreach($arShop['PHONE'] as $phone)
						{
							$str_phones .= '<div class="phone"><a rel="nofollow" href="tel:'.str_replace(array(' ', ',', '-', '(', ')'), '', $phone).'">'.$phone.'</a></div>';
						}
					}
					if($arShop['GPS_S'] && $arShop['GPS_N']){
						$mapLAT += $arShop['GPS_N'];
						$mapLON += $arShop['GPS_S'];
						$html = self::prepareItemMapHtml($arShop, "Y");
						$arPlacemarks[] = array(
							"ID" => $arShop["ID"],
							"LON" => $arShop['GPS_S'],
							"LAT" => $arShop['GPS_N'],
							"TEXT" => $html,
							"HTML" => '<div class="title">'.(strlen($arShop["URL"]) ? '<a href="'.$arShop["URL"].'">' : '').$arShop["ADDRESS"].(strlen($arShop["URL"]) ? '</a>' : '').'</div><div class="info-content">'.($arShop['METRO'] ? $arShop['METRO_PLACEMARK_HTML'] : '').(strlen($arShop['SCHEDULE']) ? '<div class="schedule">'.$arShop['SCHEDULE'].'</div>' : '').$str_phones.(strlen($arShop['EMAIL']) ? '<div class="email"><a rel="nofollow" href="mailto:'.$arShop['EMAIL'].'">'.$arShop['EMAIL'].'</a></div>' : '').'</div>'.(strlen($arShop['URL']) ? '<a rel="nofollow" class="button" href="'.$arShop["URL"].'"><span>'.GetMessage('DETAIL').'</span></a>' : '')
						);
					}
				}
				$arShops[$i] = $arShop;
			}
			?>
			<?if($arShops):?>
				<?if(abs($mapLAT) > 0 && abs($mapLON) > 0 && $showMap=="Y"):?>
					<?
					$mapLAT = floatval($mapLAT / count($arShops));
					$mapLON = floatval($mapLON / count($arShops));
					?>
					<div class="contacts_map">
						<?if($arParams["MAP_TYPE"] != "0"):?>
							<?$APPLICATION->IncludeComponent(
								"bitrix:map.google.view",
								"map",
								array(
									"INIT_MAP_TYPE" => "ROADMAP",
									"MAP_DATA" => serialize(array("google_lat" => $mapLAT, "google_lon" => $mapLON, "google_scale" => 15, "PLACEMARKS" => $arPlacemarks)),
									"MAP_WIDTH" => "100%",
									"MAP_HEIGHT" => "400",
									"CONTROLS" => array(
									),
									"OPTIONS" => array(
										0 => "ENABLE_DBLCLICK_ZOOM",
										1 => "ENABLE_DRAGGING",
									),
									"MAP_ID" => "",
									"ZOOM_BLOCK" => array(
										"POSITION" => "right center",
									),
									"API_KEY" => $arParams["GOOGLE_API_KEY"],
									"COMPOSITE_FRAME_MODE" => "A",
									"COMPOSITE_FRAME_TYPE" => "AUTO"
								),
								false, array("HIDE_ICONS" =>"Y")
							);?>
						<?else:?>
							<?$APPLICATION->IncludeComponent(
								"bitrix:map.yandex.view",
								"map",
								array(
									"INIT_MAP_TYPE" => "ROADMAP",
									"MAP_DATA" => serialize(array("yandex_lat" => $mapLAT, "yandex_lon" => $mapLON, "yandex_scale" => 4, "PLACEMARKS" => $arPlacemarks)),
									"MAP_WIDTH" => "100%",
									"MAP_HEIGHT" => "400",
									"CONTROLS" => array(
										0 => "ZOOM",
										1 => "SMALLZOOM",
										3 => "TYPECONTROL",
										4 => "SCALELINE",
									),
									"OPTIONS" => array(
										0 => "ENABLE_DBLCLICK_ZOOM",
										1 => "ENABLE_DRAGGING",
									),
									"MAP_ID" => "",
									"ZOOM_BLOCK" => array(
										"POSITION" => "right center",
									),
									"COMPONENT_TEMPLATE" => "map",
									"API_KEY" => $arParams["GOOGLE_API_KEY"],
									"COMPOSITE_FRAME_MODE" => "A",
									"COMPOSITE_FRAME_TYPE" => "AUTO"
								),
								false, array("HIDE_ICONS" =>"Y")
							);?>
						<?endif;?>
					</div>
				<?endif;?>
				<div class="wrapper_inner">
					<div class="shops list">
						<div class="items">
							<?foreach($arShops as $arShop):?>
								<div class="item <?=(strlen($arShop["IMAGE"]["src"]) ? '' : 'wi')?>" data-ID="<?=$arShop['ID']?>">
									<div class="image">
										<?if(strlen($arShop["IMAGE"]["src"])):?>
											<?if(strlen($arShop['URL'])):?>
												<a href="<?=$arShop['URL']?>"><img src="<?=$arShop["IMAGE"]["src"]?>" alt="<?=$arShop["ADDRESS"]?>" title="<?=$arShop["ADDRESS"]?>" /></a>
											<?else:?>
												<img src="<?=$arShop["IMAGE"]["src"]?>" alt="<?=$arShop["ADDRESS"]?>" title="<?=$arShop["ADDRESS"]?>" />
											<?endif;?>
										<?endif;?>
									</div>
									<div class="rubber">
										<div class="title_metro">
											<?if(strlen($arShop["ADDRESS"])):?>
												<?if(strlen($arShop['URL'])):?>
													<a href="<?=$arShop['URL']?>"><div class="title"><?=$arShop["ADDRESS"]?></div></a>
												<?else:?>
													<div class="title"><?=$arShop["ADDRESS"]?></div>
												<?endif;?>
											<?endif;?>
											<?if($arShop["METRO"]):?>
												<?foreach($arShop['METRO'] as $metro):?>
													<div class="metro"><i></i><?=$metro?></div>
												<?endforeach;?>
											<?endif;?>
										</div>
										<div class="schedule_phone_email">
											<div class="schedule"><?=$arShop["SCHEDULE"]?></div>
											<div class="phone_email">
												<?if($arShop["PHONE"]):?>
													<?foreach($arShop["PHONE"] as $phone):?>
														<div class="phone"><a rel="nofollow" href="tel:<?=str_replace(array(' ', ',', '-', '(', ')'), '', $phone);?>"><?=$phone;?></a></div>
													<?endforeach;?>
												<?endif;?>
												<?if(strlen($arShop["EMAIL"])):?>
													<div class="email"><a rel="nofollow" href="mailto:<?=$arShop["EMAIL"]?>"><?=$arShop["EMAIL"]?></a></div>
												<?endif;?>
											</div>
										</div>
									</div>
								</div>
							<?endforeach;?>
						</div>
					</div>
				</div>
				<div class="clearboth"></div>
			<?endif;?>
			<?
		}
		else{
			LocalRedirect(SITE_DIR.'contacts/');
		}
	}

	public static function drawShopDetail($arShop, $arParams, $showMap="Y"){
		global $APPLICATION;
		$mapLAT = $mapLON = 0;
		$arPlacemarks = array();
		$arPhotos = array();
		if(is_array($arShop)){
			if(isset($arShop['IBLOCK_ID'])){
				$arShop['LIST_URL'] = $arShop['LIST_PAGE_URL'];
				$arShop['TITLE'] = (in_array('NAME', $arParams['FIELD_CODE']) ? strip_tags($arShop['~NAME']) : '');
				$arShop['ADDRESS'] = $arShop['DISPLAY_PROPERTIES']['ADDRESS']['VALUE'];
				$arShop['ADDRESS'] = $arShop['TITLE'].((strlen($arShop['TITLE']) && strlen($arShop['ADDRESS'])) ? ', ' : '').$arShop['ADDRESS'];
				$arShop['PHONE'] = $arShop['DISPLAY_PROPERTIES']['PHONE']['VALUE'];
				$arShop['EMAIL'] = $arShop['DISPLAY_PROPERTIES']['EMAIL']['VALUE'];
				if(strToLower($arShop['DISPLAY_PROPERTIES']['SCHEDULE']['VALUE']['TYPE']) == 'html'){
					$arShop['SCHEDULE'] = htmlspecialchars_decode($arShop['DISPLAY_PROPERTIES']['SCHEDULE']['~VALUE']['TEXT']);
				}
				else{
					$arShop['SCHEDULE'] = nl2br($arShop['DISPLAY_PROPERTIES']['SCHEDULE']['~VALUE']['TEXT']);
				}
				$arShop['URL'] = $arShop['DETAIL_PAGE_URL'];
				$arShop['METRO_PLACEMARK_HTML'] = '';
				if($arShop['METRO'] = $arShop['DISPLAY_PROPERTIES']['METRO']['VALUE']){
					if(!is_array($arShop['METRO'])){
						$arShop['METRO'] = array($arShop['METRO']);
					}
					foreach($arShop['METRO'] as $metro){
						$arShop['METRO_PLACEMARK_HTML'] .= '<div class="metro"><i></i>'.$metro.'</div>';
					}
				}
				$arShop['DESCRIPTION'] = $arShop['DETAIL_TEXT'];
				$imageID = ((in_array('DETAIL_PICTURE', $arParams['FIELD_CODE']) && $arShop["DETAIL_PICTURE"]['ID']) ? $arShop["DETAIL_PICTURE"]['ID'] : false);
				if($imageID){
					$arShop['IMAGE'] = CFile::ResizeImageGet($imageID, array('width' => 210, 'height' => 143), BX_RESIZE_IMAGE_EXACT);
					$arPhotos[] = array(
						'ID' => $arShop["DETAIL_PICTURE"]['ID'],
						'ORIGINAL' => ($arShop["DETAIL_PICTURE"]['SRC'] ? $arShop["DETAIL_PICTURE"]['SRC'] : $arShop['IMAGE']),
						'PREVIEW' => $arShop['IMAGE'],
						'DESCRIPTION' => (strlen($arShop["DETAIL_PICTURE"]['DESCRIPTION']) ? $arShop["DETAIL_PICTURE"]['DESCRIPTION'] : $arShop['ADDRESS']),
					);
				}
				if(is_array($arShop['DISPLAY_PROPERTIES']['MORE_PHOTOS']['VALUE'])) {
					foreach($arShop['DISPLAY_PROPERTIES']['MORE_PHOTOS']['VALUE'] as $i => $photoID){
						$arPhotos[] = array(
							'ID' => $photoID,
							'ORIGINAL' => CFile::GetPath($photoID),
							'PREVIEW' => CFile::ResizeImageGet($photoID, array('width' => 210, 'height' => 143), BX_RESIZE_IMAGE_EXACT),
							'DESCRIPTION' => $arShop['DISPLAY_PROPERTIES']['MORE_PHOTOS']['DESCRIPTION'][$i],
						);
					}
				}

				$arShop['GPS_S'] = false;
				$arShop['GPS_N'] = false;
				if($arStoreMap = explode(',', $arShop['DISPLAY_PROPERTIES']['MAP']['VALUE'])){
					$arShop['GPS_S'] = $arStoreMap[0];
					$arShop['GPS_N'] = $arStoreMap[1];
				}

				if($arShop['GPS_S'] && $arShop['GPS_N']){
					$mapLAT += $arShop['GPS_S'];
					$mapLON += $arShop['GPS_N'];
					$str_phones = '';
					if($arShop['PHONE'])
					{
						foreach($arShop['PHONE'] as $phone)
						{
							$str_phones .= '<div class="phone"><a rel="nofollow" href="tel:'.str_replace(array(' ', ',', '-', '(', ')'), '', $phone).'">'.$phone.'</a></div>';
						}
					}
					$arPlacemarks[] = array(
						"ID" => $arShop["ID"],
						"LAT" => $arShop['GPS_S'],
						"LON" => $arShop['GPS_N'],
						"TEXT" => "",
						"HTML" => '<div class="title">'.(strlen($arShop["URL"]) ? '<a href="'.$arShop["URL"].'">' : '').$arShop["ADDRESS"].(strlen($arShop["URL"]) ? '</a>' : '').'</div><div class="info-content">'.($arShop['METRO'] ? $arShop['METRO_PLACEMARK_HTML'] : '').(strlen($arShop['SCHEDULE']) ? '<div class="schedule">'.$arShop['SCHEDULE'].'</div>' : '').$str_phones.(strlen($arShop['EMAIL']) ? '<div class="email"><a rel="nofollow" href="mailto:'.$arShop['EMAIL'].'">'.$arShop['EMAIL'].'</a></div>' : '').'</div>'.(strlen($arShop['URL']) ? '<a rel="nofollow" class="button" href="'.$arShop["URL"].'"><span>'.GetMessage('DETAIL').'</span></a>' : '')
					);
				}
			}
			else{
				$arShop["TITLE"] = strip_tags(htmlspecialchars_decode($arShop["TITLE"]));
				$arShop["ADDRESS"] = htmlspecialchars_decode($arShop["ADDRESS"]);
				$arShop["ADDRESS"] = (strlen($arShop["TITLE"]) ? $arShop["TITLE"].', ' : '').$arShop["ADDRESS"];
				$arShop["DESCRIPTION"] = htmlspecialchars_decode($arShop['DESCRIPTION']);
				$arShop['SCHEDULE'] = htmlspecialchars_decode($arShop['SCHEDULE']);
				if($arShop["IMAGE_ID"]  && $arShop["IMAGE_ID"] != "null"){
					$arShop['IMAGE'] = CFile::ResizeImageGet($arShop["IMAGE_ID"], array('width' => 210, 'height' => 143), BX_RESIZE_IMAGE_EXACT);
					$arPhotos[] = array(
						'ID' => $arShop["PREVIEW_PICTURE"]['ID'],
						'ORIGINAL' => CFile::GetPath($arShop["IMAGE_ID"]),
						'PREVIEW' => $arShop['IMAGE'],
						'DESCRIPTION' => (strlen($arShop["PREVIEW_PICTURE"]['DESCRIPTION']) ? $arShop["PREVIEW_PICTURE"]['DESCRIPTION'] : $arShop["ADDRESS"]),
					);
				}
				if(is_array($arShop['MORE_PHOTOS'])) {
					foreach($arShop['MORE_PHOTOS'] as $photoID){
						$arPhotos[] = array(
							'ID' => $photoID,
							'ORIGINAL' => CFile::GetPath($photoID),
							'PREVIEW' => CFile::ResizeImageGet($photoID, array('width' => 210, 'height' => 143), BX_RESIZE_IMAGE_EXACT),
							'DESCRIPTION' => $arShop["ADDRESS"],
						);
					}
				}

				$str_phones = '';
				if($arShop['PHONE'])
				{
					$arShop['PHONE'] = explode(",", $arShop['PHONE']);
					foreach($arShop['PHONE'] as $phone)
					{
						$str_phones .= '<div class="phone"><a rel="nofollow" href="tel:'.str_replace(array(' ', ',', '-', '(', ')'), '', $phone).'">'.$phone.'</a></div>';
					}
				}
				if($arShop['GPS_S'] && $arShop['GPS_N']){
					$mapLAT += $arShop['GPS_N'];
					$mapLON += $arShop['GPS_S'];
					$arPlacemarks[] = array(
						"ID" => $arShop["ID"],
						"LON" => $arShop['GPS_S'],
						"LAT" => $arShop['GPS_N'],
						"TEXT" => "",
						"HTML" => '<div class="title">'.(strlen($arShop["URL"]) ? '<a href="'.$arShop["URL"].'">' : '').$arShop["ADDRESS"].(strlen($arShop["URL"]) ? '</a>' : '').'</div><div class="info-content">'.($arShop['METRO'] ? $arShop['METRO_PLACEMARK_HTML'] : '').(strlen($arShop['SCHEDULE']) ? '<div class="schedule">'.$arShop['SCHEDULE'].'</div>' : '').$str_phones.(strlen($arShop['EMAIL']) ? '<div class="email"><a rel="nofollow" href="mailto:'.$arShop['EMAIL'].'">'.$arShop['EMAIL'].'</a></div>' : '').'</div>'.(strlen($arShop['URL']) ? '<a rel="nofollow" class="button" href="'.$arShop["URL"].'"><span>'.GetMessage('DETAIL').'</span></a>' : '')
					);
				}
			}
			?>
			<?if(abs($mapLAT) > 0 && abs($mapLON) > 0 && $showMap=="Y"):?>
				<div class="contacts_map">
					<?if($arParams["MAP_TYPE"] != "0"):?>
						<?$APPLICATION->IncludeComponent(
							"bitrix:map.google.view",
							"map",
							array(
								"INIT_MAP_TYPE" => "ROADMAP",
								"MAP_DATA" => serialize(array("google_lat" => $mapLAT, "google_lon" => $mapLON, "google_scale" => 16, "PLACEMARKS" => $arPlacemarks)),
								"MAP_WIDTH" => "100%",
								"MAP_HEIGHT" => "400",
								"CONTROLS" => array(
								),
								"OPTIONS" => array(
									0 => "ENABLE_DBLCLICK_ZOOM",
									1 => "ENABLE_DRAGGING",
								),
								"MAP_ID" => "",
								"ZOOM_BLOCK" => array(
									"POSITION" => "right center",
								),
								"COMPONENT_TEMPLATE" => "map",
								"API_KEY" => $arParams["GOOGLE_API_KEY"],
								"COMPOSITE_FRAME_MODE" => "A",
								"COMPOSITE_FRAME_TYPE" => "AUTO"
							),
							false, array("HIDE_ICONS" =>"Y")
						);?>
					<?else:?>
						<?$APPLICATION->IncludeComponent(
							"bitrix:map.yandex.view",
							"map",
							array(
								"INIT_MAP_TYPE" => "ROADMAP",
								"MAP_DATA" => serialize(array("yandex_lat" => $mapLAT, "yandex_lon" => $mapLON, "yandex_scale" => 17, "PLACEMARKS" => $arPlacemarks)),
								"MAP_WIDTH" => "100%",
								"MAP_HEIGHT" => "400",
								"CONTROLS" => array(
									0 => "ZOOM",
									1 => "SMALLZOOM",
									3 => "TYPECONTROL",
									4 => "SCALELINE",
								),
								"OPTIONS" => array(
									0 => "ENABLE_DBLCLICK_ZOOM",
									1 => "ENABLE_DRAGGING",
								),
								"MAP_ID" => "",
								"ZOOM_BLOCK" => array(
									"POSITION" => "right center",
								),
								"COMPONENT_TEMPLATE" => "map",
								"API_KEY" => $arParams["GOOGLE_API_KEY"],
								"COMPOSITE_FRAME_MODE" => "A",
								"COMPOSITE_FRAME_TYPE" => "AUTO"
							),
							false, array("HIDE_ICONS" =>"Y")
						);?>
					<?endif;?>
				</div>
			<?endif;?>
			<div class="wrapper_inner shop detail">
				<div class="contacts_left">
					<div class="store_description">
						<?if(strlen($arShop['ADDRESS'])):?>
							<div class="store_property">
								<div class="title"><?=GetMessage('ADDRESS')?></div>
								<div class="value"><?=$arShop['ADDRESS']?></div>
							</div>
						<?endif;?>
						<?if($arShop['METRO']):?>
							<div class="store_property metro">
								<div class="title"><?=GetMessage('METRO')?></div>
								<?foreach($arShop['METRO'] as $metro):?>
									<div class="value"><i></i><?=$metro?></div>
								<?endforeach;?>
							</div>
						<?endif;?>
						<?if($arShop['PHONE']):?>
							<div class="store_property">
								<div class="title"><?=GetMessage('PHONE')?></div>
								<div class="value">
									<?foreach($arShop["PHONE"] as $phone):?>
										<div class="phone"><a rel="nofollow" href="tel:<?=str_replace(array(' ', ',', '-', '(', ')'), '', $phone);?>"><?=$phone;?></a></div>
									<?endforeach;?>
								</div>
							</div>
						<?endif;?>
						<?if(strlen($arShop['EMAIL'])):?>
							<div class="store_property">
								<div class="title">Email</div>
								<div class="value"><a rel="nofollow" href="mailto:<?=$arShop['EMAIL']?>"><?=$arShop['EMAIL']?></a></div>
							</div>
						<?endif;?>
						<?if(strlen($arShop['SCHEDULE'])):?>
							<div class="store_property">
								<div class="title"><?=GetMessage('SCHEDULE')?></div>
								<div class="value"><?=$arShop['SCHEDULE']?></div>
							</div>
						<?endif;?>
					</div>
				</div>
				<div class="contacts_right">
					<?if($arShop['DESCRIPTION']):?>
						<blockquote><?=$arShop['DESCRIPTION']?></blockquote>
					<?endif;?>
					<?if($arPhotos):?>
						<!-- noindex-->
						<?foreach($arPhotos as $arPhoto):?>
							<a class="fancy" data-fancybox-group="item_slider" title="<?=$arPhoto['DESCRIPTION']?>" href="<?=$arPhoto['ORIGINAL']?>"><img title="<?=$arPhoto['DESCRIPTION']?>" alt="<?=$arPhoto['DESCRIPTION']?>" src="<?=$arPhoto['PREVIEW']['src']?>"></a>
						<?endforeach;?>
						<!-- /noindex-->
					<?endif;?>
				</div>
				<div class="clearboth"></div>
				<!-- noindex--><a rel="nofollow" href="javascript:history.back();" class="back-url url-block"><i class="fa fa-angle-left"></i><span><?=GetMessage('BACK_STORE_LIST')?></span></a><!-- /noindex-->
			</div>
			<div class="clearboth"></div>
			<?
		}
		else{
			LocalRedirect(SITE_DIR.'contacts/');
		}
	}

	static public function nlo($code, $attrs = ''){
		static $arAvailable, $isStarted, $arNlo;

		if(!isset($arAvailable)){
			$arAvailable = array(
				'menu-fixed' => $GLOBALS['arTheme']['NLO_MENU']['VALUE'] === 'Y',
				'menu-mobile' => $GLOBALS['arTheme']['NLO_MENU']['VALUE'] === 'Y',
			);

			$arNlo = array();
		}

		if($arAvailable[$code]){
			if(
				isset($_REQUEST['nlo']) &&
				$_REQUEST['nlo'] === $code
			){
				if(isset($isStarted)){
					die();
				}

				$isStarted = true;
				$GLOBALS['APPLICATION']->RestartBuffer();
				return true;
			}
			else{
				if($arNlo[$code]){
					echo '</div>';
				}
				else{
					$arNlo[$code] = true;
					echo '<div '.(strlen($attrs) ? $attrs : '').' data-nlo="'.$code.'">';
				}

				return false;
			}
		}

		return true;
	}

	public static function checkMask($mask_exc = ''){
		if($mask_exc)
		{
			$request = Application::getInstance()->getContext()->getServer();
			$arMaskTmp = explode(";", $mask_exc);

			// trim space in array items
			$arMask = array_map(function($n){
				return trim($n);
			}, $arMaskTmp);

			$reg = '%^' . implode('|', $arMask) . '%i'; // set reg

			if($request["REAL_FILE_PATH"]) // real page
				$url = $request["REAL_FILE_PATH"];
			else
				$url = $request->getScriptName();

			if(!preg_match($reg, $url))
				return false; // no page in mask
			else
				return true; // page in mask
		}
		return false;
	}

	public static function SortBySearchRank($searchQuery, $arElements, $arParams){
		$arResult = array();

		if($arElements){
			if(Loader::includeModule('search')){
				$aSort = array(
					'CUSTOM_RANK' => 'DESC',
					'TITLE_RANK' => 'DESC',
					'RANK' => 'DESC',
					'DATE_CHANGE' => 'DESC'
				);

				$exFILTER = CSearchParameters::ConvertParamsToFilter($arParams, 'arrFILTER');

				$arFilter = array(
					'SITE_ID' => SITE_ID,
					'QUERY' => trim($searchQuery),
					'ITEM_ID' => array_values($arElements),
				);

				$obSearch = new CSearch();

				$obSearch->SetOptions(array(
					'ERROR_ON_EMPTY_STEM' => $arParams['RESTART'] != 'Y',
					'NO_WORD_LOGIC' => $arParams['NO_WORD_LOGIC'] == 'Y',
				));

				$obSearch->Search($arFilter, $aSort, $exFILTER);

				//echo $obSearch->errorno;
				//echo $obSearch->error;

				if($obSearch->errorno == 0){
					$obSearch->NavStart(20, false);
					$ar = $obSearch->GetNext();

					if(!$ar && $obSearch->Query->bStemming){
						$exFILTER['STEMMING'] = false;
						$obSearch = new CSearch();
						$obSearch->Search($arFilter, $aSort, $exFILTER);

						//echo $obSearch->errorno;
						//echo $obSearch->error;

						if($obSearch->errorno == 0){
							$obSearch->NavStart($arParams['PAGE_RESULT_COUNT'], false);
							$ar = $obSearch->GetNext();
						}
					}

					while($ar){
						$arResult[] = $ar['ITEM_ID'];
						$ar = $obSearch->GetNext();
					}
				}

			}
		}

		return $arResult;
	}

	public static function SortBySearchOrder($arElementsIDsSorted, $arItemsToSort){
		$arResult = array();

		if($arItemsToSort){
			$arResult = array_column($arItemsToSort, 'ID');
			$arElementsIDsSorted = array_values($arElementsIDsSorted);

			usort($arResult, function($a, $b) use ($arElementsIDsSorted){
				$posA = array_search($a, $arElementsIDsSorted);
				$posB = array_search($b, $arElementsIDsSorted);

				if($posA !== false && $posB !== false){
					return $posA <=> $posB;
				}

				return $posA !== false ? -1 : ($posB !== false ? 1 : 0);
			});
		}

		return $arResult;
	}
}

