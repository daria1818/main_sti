<?
namespace Aspro\Functions;

use \Bitrix\Main\Application,
	\Bitrix\Main\Web\DOM\Document,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Web\DOM\CssParser,
	\Bitrix\Main\Text\HtmlFilter,
	\Bitrix\Main\IO\File,
	\Bitrix\Main\IO\Directory,
	\Bitrix\Main\Config\Option,
	\Bitrix\Main\Web\Json,
	\Aspro\Functions\CAsproNextCRM;

Loc::loadMessages(__FILE__);
\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('catalog');

if(!class_exists("CAsproNext"))
{
	class CAsproNext{
		const MODULE_ID = \CNext::moduleID;

		/*function OnAsproShowPriceMatrixHandler($arItem, $arParams, $strMeasure, $arAddToBasketData, &$html){
			// ... some code
		}*/

		/*function OnAsproShowPriceRangeTopHandler($arItem, $arParams, $strMeasure, &$html){
			// ... some code
		}*/

		/*function OnAsproItemShowItemPricesHandler($arParams, $arPrices, $strMeasure, &$price_id, $bShort, &$html){
			// ... some code
		}*/

		/*function OnAsproSkuShowItemPricesHandler($arParams, $arItem, &$item_id, &$min_price_id, $arItemIDs, $bShort, &$html){
			//... some code
		}*/

		/*function OnAsproGetTotalQuantityHandler($arItem, $arParams, &$totalCount){
			//... some code
		}*/

		/*function OnAsproGetTotalQuantityBlockHandler($totalCount, &$arOptions){
			//... some code
		}*/

		/*function OnAsproGetBuyBlockElementHandler($arItem, $totalCount, $arParams, &$arOptions){
			//... some code
		}*/

		//log to file
		public static function set_log($type="log", $path="log_file", $arMess=array()){
			$root = $_SERVER['DOCUMENT_ROOT'].'/upload/logs/'.self::MODULE_ID.'/'.$type.'/';
			if(!file_exists($root) || !is_dir($root))
				mkdir( $root, 0700, true );

			$path_date = $root.date('Y-m').'/';
			if(!file_exists($path_date) || !is_dir($path_date))
				mkdir( $path_date, 0700 );

			file_put_contents($path_date.$path.'.log', date('d-m-Y H-i-s', time()+\CTimeZone::GetOffset())."\n".print_r($arMess, true)."\n", LOCK_EX | FILE_APPEND);
		}

		public static function getPricesID($arPricesID = array(), $bUsePriceCode = false){
			$arPriceIDs = array();
			if($arPricesID)
			{
				global $USER;
				$arUserGroups = $USER->GetUserGroupArray();

				 if (!is_array($arUserGroups) && (int)$arUserGroups.'|' == (string)$arUserGroups.'|')
					$arUserGroups = array((int)$arUserGroups);

				if (!is_array($arUserGroups))
					$arUserGroups = array();

				if (!in_array(2, $arUserGroups))
					$arUserGroups[] = 2;
				\Bitrix\Main\Type\Collection::normalizeArrayValuesByInt($arUserGroups);

				$cacheKey = 'U'.implode('_', $arUserGroups).implode('_', $arPricesID);
				if (!isset($priceTypeCache[$cacheKey]))
				{
					if($bUsePriceCode)
					{
						$dbPriceType = \CCatalogGroup::GetList(
							array("SORT" => "ASC"),
							array("NAME" => $arPricesID)
							);
						while($arPriceType = $dbPriceType->Fetch())
						{
							$arPricesID[] = $arPriceType["ID"];
						}
					}
					$priceTypeCache[$cacheKey] = array();
					$priceIterator = \Bitrix\Catalog\GroupAccessTable::getList(array(
						'select' => array('CATALOG_GROUP_ID'),
						'filter' => array('@GROUP_ID' => $arUserGroups, 'CATALOG_GROUP_ID' => $arPricesID, 'ACCESS' => array(\Bitrix\Catalog\GroupAccessTable::ACCESS_BUY, \Bitrix\Catalog\GroupAccessTable::ACCESS_VIEW)),
						'order' => array('CATALOG_GROUP_ID' => 'ASC')
					));
					while ($priceType = $priceIterator->fetch())
					{
						$priceTypeId = (int)$priceType['CATALOG_GROUP_ID'];
						$priceTypeCache[$cacheKey][$priceTypeId] = $priceTypeId;
						unset($priceTypeId);
					}
					unset($priceType, $priceIterator);
				}
				$arPriceIDs = $priceTypeCache[$cacheKey];
			}
			return $arPriceIDs;
		}

		protected static function _getAllFormFieldsHTML($WEB_FORM_ID, $RESULT_ID, $arAnswers)
		{
			global $APPLICATION;

			$strResult = "";

			$w = \CFormField::GetList($WEB_FORM_ID, "ALL", $by, $order, array("ACTIVE" => "Y"), $is_filtered);
			while ($wr=$w->Fetch())
			{
				$answer = "";
				$answer_raw = '';
				if (is_array($arAnswers[$wr["SID"]]))
				{
					$bHasDiffTypes = false;
					$lastType = '';
					foreach ($arAnswers[$wr['SID']] as $arrA)
					{
						if ($lastType == '') $lastType = $arrA['FIELD_TYPE'];
						elseif ($arrA['FIELD_TYPE'] != $lastType)
						{
							$bHasDiffTypes = true;
							break;
						}
					}

					foreach($arAnswers[$wr["SID"]] as $arrA)
					{
						if ($wr['ADDITIONAL'] == 'Y')
						{
							$arrA['FIELD_TYPE'] = $wr['FIELD_TYPE'];
						}

						$USER_TEXT_EXIST = (strlen(trim($arrA["USER_TEXT"]))>0);
						$ANSWER_TEXT_EXIST = (strlen(trim($arrA["ANSWER_TEXT"]))>0);
						$ANSWER_VALUE_EXIST = (strlen(trim($arrA["ANSWER_VALUE"]))>0);
						$USER_FILE_EXIST = (intval($arrA["USER_FILE_ID"])>0);

						if (
							$bHasDiffTypes
							&&
							!$USER_TEXT_EXIST
							&&
							(
								$arrA['FIELD_TYPE'] == 'text'
								||
								$arrA['FIELD_TYPE'] == 'textarea'
							)
						)
							continue;

						if (strlen(trim($answer))>0) $answer .= "<br />";
						if (strlen(trim($answer_raw))>0) $answer_raw .= ",";

						if ($ANSWER_TEXT_EXIST)
							$answer .= $arrA["ANSWER_TEXT"].': ';

						switch ($arrA['FIELD_TYPE'])
						{
							case 'text':
							case 'textarea':
							case 'hidden':
							case 'date':
							case 'password':

								if ($USER_TEXT_EXIST)
								{
									$answer .= htmlspecialcharsbx(trim($arrA["USER_TEXT"]));
									$answer_raw .= htmlspecialcharsbx(trim($arrA["USER_TEXT"]));
								}

							break;

							case 'email':
							case 'url':

								if ($USER_TEXT_EXIST)
								{
									$answer .= '<a href="'.($arrA['FIELD_TYPE'] == 'email' ? 'mailto:' : '').trim($arrA["USER_TEXT"]).'">'.htmlspecialcharsbx(trim($arrA["USER_TEXT"])).'</a>';
									$answer_raw .= htmlspecialcharsbx(trim($arrA["USER_TEXT"]));
								}

							break;

							case 'checkbox':
							case 'multiselect':
							case 'radio':
							case 'dropdown':

								if ($ANSWER_TEXT_EXIST)
								{
									$answer = htmlspecialcharsbx(substr($answer, 0, -2).' ');
									$answer_raw .= htmlspecialcharsbx($arrA['ANSWER_TEXT']);
								}

								if ($ANSWER_VALUE_EXIST)
								{
									$answer .= '('.htmlspecialcharsbx($arrA['ANSWER_VALUE']).') ';
									if (!$ANSWER_TEXT_EXIST)
										$answer_raw .= htmlspecialcharsbx($arrA['ANSWER_VALUE']);
								}

								if (!$ANSWER_VALUE_EXIST && !$ANSWER_TEXT_EXIST)
									$answer_raw .= $arrA['ANSWER_ID'];

								$answer .= '['.$arrA['ANSWER_ID'].']';

							break;

							case 'file':
							case 'image':

								if ($USER_FILE_EXIST)
								{
									$f = \CFile::GetByID($arrA["USER_FILE_ID"]);
									if ($fr = $f->Fetch())
									{
										$file_size = \CFile::FormatSize($fr["FILE_SIZE"]);
										$url = ($APPLICATION->IsHTTPS() ? "https://" : "http://").$_SERVER["HTTP_HOST"]. "/bitrix/tools/form_show_file.php?rid=".$RESULT_ID. "&hash=".$arrA["USER_FILE_HASH"]."&lang=".LANGUAGE_ID;

										if ($arrA["USER_FILE_IS_IMAGE"]=="Y")
										{
											$answer .= "<a href=\"$url\">".htmlspecialcharsbx($arrA["USER_FILE_NAME"])."</a> [".$fr["WIDTH"]." x ".$fr["HEIGHT"]."] (".$file_size.")";
										}
										else
										{
											$answer .= "<a href=\"$url&action=download\">".htmlspecialcharsbx($arrA["USER_FILE_NAME"])."</a> (".$file_size.")";
										}

										$answer_raw .= htmlspecialcharsbx($arrA['USER_FILE_NAME']);
									}
								}

							break;
						}
					}
				}

				$strResult .= $wr["TITLE"].":<br />".(strlen($answer)<=0 ? " " : $answer)."<br /><br />";
			}

			return $strResult;
		}

		protected static function _getAllFormFields($WEB_FORM_ID, $RESULT_ID, $arAnswers)
		{
			global $APPLICATION;

			$strResult = "";

			$w = \CFormField::GetList($WEB_FORM_ID, "ALL", $by, $order, array("ACTIVE" => "Y"), $is_filtered);
			while ($wr=$w->Fetch())
			{
				$answer = "";
				$answer_raw = '';
				if (is_array($arAnswers[$wr["SID"]]))
				{
					$bHasDiffTypes = false;
					$lastType = '';
					foreach ($arAnswers[$wr['SID']] as $arrA)
					{
						if ($lastType == '') $lastType = $arrA['FIELD_TYPE'];
						elseif ($arrA['FIELD_TYPE'] != $lastType)
						{
							$bHasDiffTypes = true;
							break;
						}
					}

					foreach($arAnswers[$wr["SID"]] as $arrA)
					{
						if ($wr['ADDITIONAL'] == 'Y')
						{
							$arrA['FIELD_TYPE'] = $wr['FIELD_TYPE'];
						}

						$USER_TEXT_EXIST = (strlen(trim($arrA["USER_TEXT"]))>0);
						$ANSWER_TEXT_EXIST = (strlen(trim($arrA["ANSWER_TEXT"]))>0);
						$ANSWER_VALUE_EXIST = (strlen(trim($arrA["ANSWER_VALUE"]))>0);
						$USER_FILE_EXIST = (intval($arrA["USER_FILE_ID"])>0);

						if (
							$bHasDiffTypes
							&& !$USER_TEXT_EXIST
							&& (
								$arrA['FIELD_TYPE'] == 'text'
								||
								$arrA['FIELD_TYPE'] == 'textarea'
							)
						)
						{
							continue;
						}

						if (strlen(trim($answer)) > 0)
							$answer .= "\n";
						if (strlen(trim($answer_raw)) > 0)
							$answer_raw .= ",";

						if ($ANSWER_TEXT_EXIST)
							$answer .= $arrA["ANSWER_TEXT"].': ';

						switch ($arrA['FIELD_TYPE'])
						{
							case 'text':
							case 'textarea':
							case 'email':
							case 'url':
							case 'hidden':
							case 'date':
							case 'password':

								if ($USER_TEXT_EXIST)
								{
									$answer .= trim($arrA["USER_TEXT"]);
									$answer_raw .= trim($arrA["USER_TEXT"]);
								}

							break;

							case 'checkbox':
							case 'multiselect':
							case 'radio':
							case 'dropdown':

								if ($ANSWER_TEXT_EXIST)
								{
									$answer = substr($answer, 0, -2).' ';
									$answer_raw .= $arrA['ANSWER_TEXT'];
								}

								if ($ANSWER_VALUE_EXIST)
								{
									$answer .= '('.$arrA['ANSWER_VALUE'].') ';
									if (!$ANSWER_TEXT_EXIST)
									{
										$answer_raw .= $arrA['ANSWER_VALUE'];
									}
								}

								if (!$ANSWER_VALUE_EXIST && !$ANSWER_TEXT_EXIST)
								{
									$answer_raw .= $arrA['ANSWER_ID'];
								}

								$answer .= '['.$arrA['ANSWER_ID'].']';

							break;

							case 'file':
							case 'image':

								if ($USER_FILE_EXIST)
								{
									$f = \CFile::GetByID($arrA["USER_FILE_ID"]);
									if ($fr = $f->Fetch())
									{
										$file_size = \CFile::FormatSize($fr["FILE_SIZE"]);
										$url = ($APPLICATION->IsHTTPS() ? "https://" : "http://").$_SERVER["HTTP_HOST"]. "/bitrix/tools/form_show_file.php?rid=".$RESULT_ID. "&hash=".$arrA["USER_FILE_HASH"]."&action=download&lang=".LANGUAGE_ID;

										if ($arrA["USER_FILE_IS_IMAGE"]=="Y")
										{
											$answer .= $arrA["USER_FILE_NAME"]." [".$fr["WIDTH"]." x ".$fr["HEIGHT"]."] (".$file_size.")\n".$url;
										}
										else
										{
											$answer .= $arrA["USER_FILE_NAME"]." (".$file_size.")\n".$url."&action=download";
										}
									}

									$answer_raw .= $arrA['USER_FILE_NAME'];
								}

							break;
						}
					}
				}

				$strResult .= $wr["TITLE"].":\r\n".(strlen($answer)<=0 ? " " : $answer)."\r\n\r\n";
			}

			return $strResult;
		}

		public static function prepareArray($arFields = array(), $arReplace = array(), $stamp = '_leads'){
			$arTmpFields = array();
			if($arFields && $arReplace)
			{
				foreach($arFields as $key => $value)
				{
					$key = str_replace($stamp, '', $key);
					if(in_array($key, $arReplace))
						$arTmpFields[$key] = $value;
				}
				// $arTmpFields = self::prepareArray($arFields, array('name', 'tags', 'budget'), '_leads');
			}
			return $arTmpFields;
		}

		public static function showComments(){
			global $BLOG_DATA;
			$arPosts = [];
			if ($BLOG_DATA['COMMENT_ID'] && \Bitrix\Main\Loader::includeModule('blog')) {
				$SORT = Array("DATE_PUBLISH" => "DESC", "NAME" => "ASC");
				$arFilter = Array(
				    "BLOG_ID" => $BLOG_DATA['BLOG_DATA']['BLOG_ID'],
				    "POST_ID" => $BLOG_DATA['COMMENT_ID']
				    );
				$dbPosts = \CBlogComment::GetList(
				        $SORT,
				        $arFilter,
				        false,
				        ['nTopCount' => 4],
				        ['POST_TEXT', 'AUTHOR_NAME', 'AUTHOR_ID', 'AUTHOR_EMAIL', 'USER_LOGIN', 'USER_EMAIL', 'USER_NAME', 'USER_LAST_NAME']
				    );

				while ($arPost = $dbPosts->Fetch())
				{
					if (!$arPost['AUTHOR_NAME']) {
						if ($arPost['USER_NAME']) {
							$arPost['AUTHOR_NAME'] = $arPost['USER_NAME'];
						}
						if ($arPost['USER_LAST_NAME']) {
							$arPost['AUTHOR_NAME'] .= ' '.$arPost['USER_LAST_NAME'];
						}
						if (!$arPost['AUTHOR_NAME'] && $arPost['AUTHOR_EMAIL']) {
							$arPost['AUTHOR_NAME'] = $arPost['AUTHOR_EMAIL'];
						}
						if (!$arPost['AUTHOR_NAME'] && $arPost['USER_EMAIL']) {
							$arPost['AUTHOR_NAME'] = $arPost['USER_EMAIL'];
						}
						if (!$arPost['AUTHOR_NAME'] && $arPost['USER_LOGIN']) {
							$arPost['AUTHOR_NAME'] = $arPost['USER_LOGIN'];
						}
					}
				    $arPosts[] = $arPost;
				}
			}?>
			<?if ($arPosts):?>
				<?foreach($arPosts as $arPost):?>
					<div class="hidden" itemprop="review" itemscope itemtype="http://schema.org/Review">
						<meta itemprop="author" content="<?=$arPost["AUTHOR_NAME"]?>">
						<span style="display:none" itemprop="itemReviewed" itemscope itemtype="http://schema.org/Thing">
							<meta itemprop="name" content="<?=strip_tags($arResult['NAME'])?>" />
						</span>
						<div class="hidden" itemprop="reviewBody">
							<?=$arPost['POST_TEXT']?>
						</div>
					</div>
				<?endforeach;?>
			<?endif;
		}

		public static function showCalculateDeliveryBlock($productId, $arParams, $bSkipPreview = false){
			?>
			<?if($productId > 0 && $arParams['CALCULATE_DELIVERY'] !== 'NOT'):?>
				<?
				$bIndexBot = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Lighthouse') !== false); // is indexed yandex/google bot
				$bWithPreview = $arParams['CALCULATE_DELIVERY'] === 'WITH_PREVIEW' && !$bSkipPreview && !$bIndexBot;
				?>
				<?ob_start();?>
					<div class="calculate-delivery text-form muted777 muted ncolor<?=($bWithPreview ? ' with_preview' : '')?>">
						<?=\CNext::showIconSvg('delivery_calc', SITE_TEMPLATE_PATH.'/images/svg/catalog/delivery_calc.svg', '', '', true, false, true, false);?>
						<span><span class="animate-load dotted font_sxs" data-event="jqm" data-param-form_id="delivery" data-name="delivery" data-param-product_id="<?=$productId?>" <?=(($arParams['USE_REGION'] === 'Y' && $arParams['STORES'] && is_array($arParams['STORES'])) ? 'data-param-region_stores_id="'.implode(',', $arParams['STORES']).'"' : '')?>><?=$arParams['EXPRESSION_FOR_CALCULATE_DELIVERY']?></span></span>
						<?if($bWithPreview):?><span class="calculate-delivery-preview"></span><?endif;?>
					</div>
				<?
				$html = ob_get_contents();
				ob_end_clean();

				foreach(GetModuleEvents(self::MODULE_ID, 'OnAsproShowCalculateDeliveryBlock', true) as $arEvent) // event for manipulation calculate delivery link
					ExecuteModuleEventEx($arEvent, array($productId, $arParams, &$html));

				echo $html;
				?>
			<?endif;?>
			<?
		}

		public static function declOfNum($number, $titles)
		{
			$cases = array (2, 0, 1, 1, 1, 2);
			return $number." ".$titles[ ($number%100>4 && $number%100<20)? 2 : $cases[min($number%10, 5)] ];
		}

		public static function formatUsageTime($time){
			$timeFormat = '';
			switch ($time) {
				case 'FEW_WEEKS':
					$timeFormat = Loc::getMessage('FEW_WEEKS_USE');
					break;
				case 'FEW_MONTHS':
					$timeFormat = Loc::getMessage('FEW_MONTHS_USE');
					break;
				case 'FEW_DAYS':
					$timeFormat = Loc::getMessage('FEW_DAYS_USE');
					break;
				default:
					$timeFormat = Loc::getMessage('FEW_YEAR_USE');
					break;
			}
			return $timeFormat;
		}
		public static function encode($arItem = array(), $options = null){
			if(class_exists('\Bitrix\Main\Web\Json'))
			{
				if(method_exists('\Bitrix\Main\Web\Json', 'encode'))
					echo \Bitrix\Main\Web\Json::encode($arItem, $options);
				else
					echo json_encode($arItem, $options);
			}
			else
			{
				echo json_encode($arItem, $options);
			}
		}

		public static function decode($arItem = array()){
			if(class_exists('\Bitrix\Main\Web\Json'))
			{
				if(method_exists('\Bitrix\Main\Web\Json', 'decode'))
					echo Json::decode($arItem);
				else
					echo json_decode($arItem, true);
			}
			else
			{
				echo json_decode($arItem, true);
			}
		}

		public static function sendResultToIBlock($WEB_FORM_ID, $RESULT_ID){
			$bAdminSection = (defined('ADMIN_SECTION') && ADMIN_SECTION === true);
			if(!$bAdminSection)
			{
				//check REVIEW form
				$rsForm = \CForm::GetByID($WEB_FORM_ID);
				$arForm = $rsForm->Fetch();
				if($arForm && $arForm['SID'] == 'REVIEW')
				{
					\CForm::GetResultAnswerArray(
							$WEB_FORM_ID,
							$arrColumns,
							$arrAnswers,
							$arrAnswersVarname,
							array("RESULT_ID" => $RESULT_ID)
						);
					\CFormResult::GetDataByID($RESULT_ID, array(), $arResultFields, $arAnswers);

					if($arrAnswersVarname)
					{
						$el = new \CIBlockElement;

						$PROP = array(
							'EMAIL' => $arrAnswersVarname[$RESULT_ID]['EMAIL'][0]['USER_TEXT'],
							'POST' => $arrAnswersVarname[$RESULT_ID]['POST'][0]['USER_TEXT'],
							'RATING' => $arrAnswersVarname[$RESULT_ID]['RATING'][0]['USER_TEXT'],
						);

						if ($arrAnswersVarname[$RESULT_ID]['FILE'][0]["USER_FILE_ID"]) {
							$arFiles = [];
							foreach ($arrAnswersVarname[$RESULT_ID]['FILE'] as $arFile) {
								$arFiles[] = \CFile::MakeFileArray($arFile['USER_FILE_ID']);
							}
							$PROP['FILE'] = $arFiles;
						}

						$arLoadProductArray = array(
							"IBLOCK_ID" => \CNextCache::$arIBlocks[SITE_ID]["aspro_next_content"]["aspro_next_add_review"][0],
	  						"PROPERTY_VALUES"=> $PROP,
	  						"ACTIVE"=> "N",
	  						"ACTIVE_FROM"=> date('d.m.Y'),
	  						"NAME"=> $arrAnswersVarname[$RESULT_ID]['NAME'][0]['USER_TEXT'],
	  						"PREVIEW_TEXT"=> $arrAnswersVarname[$RESULT_ID]['REVIEW_TEXT'][0]['USER_TEXT'],
	  						"PREVIEW_PICTURE"=> \CFile::MakeFileArray($arrAnswersVarname[$RESULT_ID]['FILE_AVATAR'][0]['USER_FILE_ID']),
						);

						$el->Add($arLoadProductArray);
					}
				}
			}
		}

		public static function sendLeadCrmFromForm(
			$WEB_FORM_ID,
			$RESULT_ID,
			$TYPE = 'ALL',
			$SITE_ID = SITE_ID,
			$CURL = false,
			$DECODE = false
		){
			$bIntegrationFlowlu = (Option::get(self::MODULE_ID, 'ACTIVE_LINK_FLOWLU', '', $SITE_ID) && (Option::get(self::MODULE_ID, 'ACTIVE_FLOWLU', 'N', $SITE_ID) == 'Y'));
			$bIntegrationAmoCrm = (Option::get(self::MODULE_ID, 'ACTIVE_LINK_AMO_CRM', '', $SITE_ID) && (Option::get(self::MODULE_ID, 'ACTIVE_AMO_CRM', 'N', $SITE_ID) == 'Y'));
			$result = "{'erorr':{'error_msg': 'error'}}";

			if($bIntegrationFlowlu || $bIntegrationAmoCrm)
			{
				$arAllMatchValues = array();

				$arMatchValuesFlowlu = unserialize(Option::get(self::MODULE_ID, 'FLOWLU_CRM_FIELDS_MATCH_'.$WEB_FORM_ID, '', $SITE_ID));
				$arMatchValuesAmoCrm = unserialize(Option::get(self::MODULE_ID, 'AMO_CRM_FIELDS_MATCH_'.$WEB_FORM_ID, '', $SITE_ID));

				//flowlu
				if($bIntegrationFlowlu && ($TYPE == 'ALL' || $TYPE == 'FLOWLU'))
					$arAllMatchValues['FLOWLU'] = $arMatchValuesFlowlu;
				//amocrm
				if($bIntegrationAmoCrm && ($TYPE == 'ALL' || $TYPE == 'AMO_CRM'))
					$arAllMatchValues['AMO_CRM'] = $arMatchValuesAmoCrm;

				if($arAllMatchValues)
				{
					//get fields
					\CForm::GetResultAnswerArray(
						$WEB_FORM_ID,
						$arrColumns,
						$arrAnswers,
						$arrAnswersVarname,
						array("RESULT_ID" => $RESULT_ID)
					);

					//get form
					\CFormResult::GetDataByID($RESULT_ID, array(), $arResultFields, $arAnswers);
				}

				if($arAllMatchValues)
				{
					$arPostFields = array();

					//fill main fieds
					foreach($arAllMatchValues as $crm => $arFields)
					{
						foreach($arFields as $key => $id)
						{
							switch($id)
							{
								case 'RESULT_ID':
									$arPostFields[$crm][$key] = $arResultFields['ID'];
								break;
								case 'FORM_SID':
									$arPostFields[$crm][$key] = $arResultFields['SID'];
								break;
								case 'FORM_NAME':
									$arPostFields[$crm][$key] = $arResultFields['NAME'];
								break;
								case 'SITE_ID':
									$arPostFields[$crm][$key] = $SITE_ID;
								break;
								case 'FORM_ALL':
									$arPostFields[$crm][$key] = self::_getAllFormFields($WEB_FORM_ID, $RESULT_ID, $arAnswers);
								break;
								case 'FORM_ALL_HTML':
									$arPostFields[$crm][$key] = self::_getAllFormFieldsHTML($WEB_FORM_ID, $RESULT_ID, $arAnswers);
								break;
							}
						}
					}

					//fill form fieds
					foreach($arAllMatchValues as $crm => $arFields)
					{
						foreach($arFields as $key => $id)
						{
							if($arrAnswers[$RESULT_ID][$id])
							{
								$bCanPushCrm = true;

								$arAnswer = reset($arrAnswers[$RESULT_ID][$id]);

								$arPostFields[$crm][$key] = (isset($arAnswer['USER_TEXT']) && $arAnswer['USER_TEXT'] ? $arAnswer['USER_TEXT'] : $arAnswer['ANSWER_TEXT']);
							}
						}
					}

					if($arPostFields)
					{
						$arHeaders = array();

						if($crm === 'AMO_CRM'){
							$arOAuth = array();
							$arConfig = array(
								'type' => 'AMO_CRM',
								'siteId' => $SITE_ID,
							);
							CAsproNextCRM::restore(
								$arOAuth,
								$arConfig
							);

							CAsproNextCRM::updateOAuth(
								$arOAuth,
								$arConfig
							);

							CAsproNextCRM::save(
								$arOAuth,
								$arConfig
							);

							$arHeaders = array(
								'Authorization' => 'Bearer '.$arOAuth['accessToken']
							);
						}

						foreach($arPostFields as $crm => $arFields)
						{
							if($crm == 'FLOWLU')
							{
								$url = str_replace('#DOMAIN#', Option::get(self::MODULE_ID, 'DOMAIN_'.$crm, '', $SITE_ID), CAsproNextCRM::FLOWLU_PATH);
								$arFields['api_key'] = Option::get(self::MODULE_ID, 'TOKEN_FLOWLU', '', $SITE_ID);
								$arFields['ref'] = 'form:aspro-next';
								$arFields['ref_id'] = $WEB_FORM_ID.'_'.$RESULT_ID;
								$name_field = 'name';
							}
							else
							{
								$name_field = 'name_leads';
								$url = str_replace('#DOMAIN#', Option::get(self::MODULE_ID, 'DOMAIN_'.$crm, '', $SITE_ID), CAsproNextCRM::AMO_CRM_PATH);
								if(!$arFields['tags_leads'])
									$arFields['tags_leads'] = Option::get(self::MODULE_ID, 'TAGS_AMO_CRM_TITLE', '', $SITE_ID);
							}

							if(!$arFields[$name_field])
								$arFields[$name_field] = Option::get(self::MODULE_ID, 'LEAD_NAME_'.$crm.'_TITLE', \Bitrix\Main\Localization\Loc::getMessage('ASPRO_NEXT_MODULE_LEAD_NAME_'.$crm), $SITE_ID);

							$smCrmName = strtolower(str_replace('_', '', $crm));
							//log to file form request
							if(Option::get(self::MODULE_ID, 'USE_LOG_'.$crm, 'N', $SITE_ID) == 'Y')
							{
								self::set_log('crm', $smCrmName.'_create_lead_request', $arFields);
							}

							//convert all to UTF8 encoding for send to flowlu
							// foreach($arFields as $key => $value)
							// {
							// 	$arFields[$key] = iconv(LANG_CHARSET, 'UTF-8', $value);
							// }

							$arFieldsLead = $arFields;

							if($crm == 'AMO_CRM')
							{
								$arFieldsLeadTmp = $arFields;
								$arCustomFields = unserialize(Option::get(self::MODULE_ID, 'CUSTOM_FIELD_AMO_CRM', '', $SITE_ID));
								//prepare array
								$arFieldsLeadTmp = self::prepareArray($arFields, array('name', 'tags', 'price', 'budget'), '_leads');
								if($arCustomFields && $arCustomFields['leads'])
								{
									foreach($arCustomFields['leads'] as $key => $arProp)
									{
										if($arFields[$key.'_leads'])
										{
											$arFieldsLeadTmp['custom_fields'][] = array(
												'id' => $key,
												'values' => array(
													array(
														'value' => $arFields[$key.'_leads']
													)
												)
											);
										}
										elseif(isset($arProp['ENUMS']) && $arProp['ENUMS'])
										{
											foreach($arProp['ENUMS'] as $key2 => $value)
											{
												if($arFields[$key.'_'.$key2.'_leads'])
												{
													$arFieldsLeadTmp['custom_fields'][] = array(
														'id' => $key,
														'values' => array(
															array(
																'value' => $arFields[$key.'_'.$key2.'_leads'],
																'enum' => $value
															)
														)
													);
												}
											}
										}
									}
								}

								$arFieldsLead = array(
									'request' => array(
										'leads' => array(
											'add' => array(
												$arFieldsLeadTmp
											)
										)
									)
								);
							}

							$result = CAsproNextCRM::query($url, CAsproNextCRM::$arCrmMethods[$crm]["CREATE_LEAD"], $arFieldsLead, $arHeaders, $CURL, $DECODE);
							$arCrmResult = Json::decode($result);
							unset($arFieldsLead);

							if(isset($arCrmResult['response']))
							{
								if($crm == 'AMO_CRM' && $arCrmResult['response']['leads']) // create contact and company for amocrm
								{
									$arLead = reset($arCrmResult['response']['leads']['add']);
									$leadID = $arLead['id'];

									//add notes
									if($arFields['notes_leads'])
									{
										$arFieldsNote = array(
											'request' => array(
												'notes' => array(
													'add' => array(
														array(
															'element_id' => $leadID,
															'element_type' => 2,
															'note_type' => 4,
															'text' => $arFields['notes_leads']
														),
													)
												)
											)
										);
										$resultNote = CAsproNextCRM::query($url, CAsproNextCRM::$arCrmMethods[$crm]["CREATE_NOTES"], $arFieldsNote, $arHeaders, $CURL, $DECODE);

										unset($arFieldsNote);
										unset($resultNote);
									}

									//add company
									$company_id = 0;
									if($arCustomFields && $arCustomFields['companies'])
									{
										//prepare array
										$arFieldsCompanyTmp = self::prepareArray($arFields, array('name', 'tags'), '_companies');
										$arFieldsCompanyTmp['linked_leads_id'] = array($leadID);

										foreach($arCustomFields['companies'] as $key => $arProp)
										{
											if($arFields[$key.'_companies'])
											{
												$arFieldsCompanyTmp['custom_fields'][] = array(
													'id' => $key,
													'values' => array(
														array(
															'value' => $arFields[$key.'_companies']
														)
													)
												);
											}
											elseif(isset($arProp['ENUMS']) && $arProp['ENUMS'])
											{
												foreach($arProp['ENUMS'] as $key2 => $value)
												{
													if($arFields[$key.'_'.$key2.'_companies'])
													{
														$arFieldsCompanyTmp['custom_fields'][] = array(
															'id' => $key,
															'values' => array(
																array(
																	'value' => $arFields[$key.'_'.$key2.'_companies'],
																	'enum' => $value
																)
															)
														);
													}
												}
											}
										}
										$arFieldsCompany = array(
											'request' => array(
												'contacts' => array(
													'add' => array(
														$arFieldsCompanyTmp
													)
												)
											)
										);

										$resultCompany = CAsproNextCRM::query($url, CAsproNextCRM::$arCrmMethods[$crm]["CREATE_COMPANY"], $arFieldsCompany, $arHeaders, $CURL, $DECODE);
										$resultCompany = Json::decode($resultCompany);

										if(isset($resultCompany['response']['contacts']['add'][0]['id']))
											$company_id = $resultCompany['response']['contacts']['add'][0]['id'];

										//log to file crm response
										if(Option::get(self::MODULE_ID, 'USE_LOG_'.$crm, 'N', $SITE_ID) == 'Y')
										{
											self::set_log('crm', $smCrmName.'_create_company_response', $resultCompany);
										}

										unset($arFieldsCompany);
										unset($resultCompany);
									}

									//add contact
									$arFieldsContactTmp = self::prepareArray($arFields, array('name', 'tags'), '_contacts');
									$arFieldsContactTmp['linked_leads_id'] = array($leadID);

									if($company_id)
										$arFieldsContactTmp['linked_company_id'] = $company_id;

									if($arCustomFields && $arCustomFields['contacts'])
									{
										foreach($arCustomFields['contacts'] as $key => $arProp)
										{
											if($arFields[$key.'_contacts'])
											{
												$arFieldsContactTmp['custom_fields'][] = array(
													'id' => $key,
													'values' => array(
														array(
															'value' => $arFields[$key.'_contacts']
														)
													)
												);
											}
											elseif(isset($arProp['ENUMS']) && $arProp['ENUMS'])
											{
												foreach($arProp['ENUMS'] as $key2 => $value)
												{
													if($arFields[$key.'_'.$key2.'_contacts'])
													{
														$arFieldsContactTmp['custom_fields'][] = array(
															'id' => $key,
															'values' => array(
																array(
																	'value' => $arFields[$key.'_'.$key2.'_contacts'],
																	'enum' => $value
																)
															)
														);
													}
												}
											}
										}
									}

									$arFieldsContact = array(
										'request' => array(
											'contacts' => array(
												'add' => array(
													$arFieldsContactTmp
												)
											)
										)
									);

									$resultContact = CAsproNextCRM::query($url, CAsproNextCRM::$arCrmMethods['AMO_CRM']['CREATE_CONTACT'], $arFieldsContact, $arHeaders, $CURL, $DECODE);

									//log to file crm response
									if(Option::get(self::MODULE_ID, 'USE_LOG_'.$crm, 'N', $SITE_ID) == 'Y')
									{
										self::set_log('crm', $smCrmName.'_create_contact_response', Json::decode($resultContact));
									}

									unset($arFieldsContact);
									unset($resultContact);

								}

								if((isset($arCrmResult['response']['id']) && $arCrmResult['response']['id']) || (isset($arCrmResult['response']['leads']) && $leadID))
								{
									$arFormResultOption = unserialize(Option::get(self::MODULE_ID, 'CRM_SEND_FORM_'.$RESULT_ID, '', $SITE_ID));
									if(!isset($arFormResultOption['FLOWLU']) && (isset($arCrmResult['response']['id']) && $arCrmResult['response']['id']))
										$arFormResultOption['FLOWLU'] = $arCrmResult['response']['id'];
									if(!isset($arFormResultOption['AMO_CRM']) && (isset($arCrmResult['response']['leads']) && $leadID))
										$arFormResultOption['AMO_CRM'] = $leadID;
									Option::set(self::MODULE_ID, 'CRM_SEND_FORM_'.$RESULT_ID, serialize($arFormResultOption), $SITE_ID);
								}
							}

							//log to file crm response
							if(Option::get(self::MODULE_ID, 'USE_LOG_'.$crm, 'N', $SITE_ID) == 'Y')
							{
								self::set_log('crm', $smCrmName.'_create_lead_response', $arCrmResult);
							}
						}
					}
				}
			}
			return $result;
		}

		public static function showImg($arParams = array(), $arItem = array(), $bShowFW = true, $bWrapLink = true, $dopClassImg = ''){
			if($arItem):?>
				<?ob_start();?>
				<?if($bWrapLink):?>
				<a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="thumb shine">
				<?endif;?>
					<?
					$a_alt = (is_array($arItem["PREVIEW_PICTURE"]) && strlen($arItem["PREVIEW_PICTURE"]['DESCRIPTION']) ? $arItem["PREVIEW_PICTURE"]['DESCRIPTION'] : ($arItem['SELECTED_SKU_IPROPERTY_VALUES'] ? ($arItem["SELECTED_SKU_IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"] ? $arItem["SELECTED_SKU_IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"] : $arItem["NAME"]) : ($arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"] ? $arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"] : $arItem["NAME"])));

					$a_title = (is_array($arItem["PREVIEW_PICTURE"]) && strlen($arItem["PREVIEW_PICTURE"]['DESCRIPTION']) ? $arItem["PREVIEW_PICTURE"]['DESCRIPTION'] : ($arItem['SELECTED_SKU_IPROPERTY_VALUES'] ? ($arItem["SELECTED_SKU_IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"] ? $arItem["SELECTED_SKU_IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"] : $arItem["NAME"]) : ($arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"] ? $arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"] : $arItem["NAME"])));

					$bNeedFindSkuPicture = empty($arItem["DETAIL_PICTURE"]) && empty($arItem["PREVIEW_PICTURE"]) && (\CNext::GetFrontParametrValue("SHOW_FIRST_SKU_PICTURE") == "Y") &&  isset($arItem['OFFERS']) && !empty($arItem['OFFERS']);
					$arFirstSkuPicture = array();

					if($bNeedFindSkuPicture){

						foreach ($arItem['OFFERS'] as $keyOffer => $arOffer)
						{
							if(!empty($arOffer['PREVIEW_PICTURE'])){
								$arFirstSkuPicture = $arOffer['PREVIEW_PICTURE'];
								if (!is_array($arFirstSkuPicture)){
									$arFirstSkuPicture = \CFile::GetFileArray($arOffer['PREVIEW_PICTURE']);
								}
							} elseif (!empty($arOffer['DETAIL_PICTURE'])){
								$arFirstSkuPicture = $arOffer['DETAIL_PICTURE'];
								if (!is_array($arFirstSkuPicture)){
									$arFirstSkuPicture = \CFile::GetFileArray($arOffer['DETAIL_PICTURE']);
								}
							}

							if(isset($arFirstSkuPicture["ID"])){
								$arFirstSkuPicture = \CFile::ResizeImageGet($arFirstSkuPicture["ID"], array( "width" => 350, "height" => 350 ), BX_RESIZE_IMAGE_PROPORTIONAL,true );
							}

							if(!empty( $arFirstSkuPicture )){
								break;
							}
						}
					}

					?>

					<?if( !empty($arItem["PREVIEW_PICTURE"]) ):?>
						<img class="img-responsive <?=$dopClassImg;?>" src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>" alt="<?=$a_alt;?>" title="<?=$a_title;?>" />
					<?elseif( !empty($arItem["DETAIL_PICTURE"])):?>
						<?if(isset($arItem["DETAIL_PICTURE"]["src"])):?>
							<?$img["src"] = $arItem["DETAIL_PICTURE"]["src"]?>
						<?else:?>
							<?$img = \CFile::ResizeImageGet($arItem["DETAIL_PICTURE"], array( "width" => 350, "height" => 350 ), BX_RESIZE_IMAGE_PROPORTIONAL,true );?>
						<?endif;?>
						<img class="img-responsive <?=$dopClassImg;?>" src="<?=$img["src"]?>" alt="<?=$a_alt;?>" title="<?=$a_title;?>" />
					<?elseif( $bNeedFindSkuPicture && !empty( $arFirstSkuPicture ) ):?>
						<img class="img-responsive <?=$dopClassImg;?>" src="<?=$arFirstSkuPicture["src"]?>" alt="<?=$a_alt;?>" title="<?=$a_title;?>" />
					<?else:?>
						<img class="img-responsive <?=$dopClassImg;?>" src="<?=SITE_TEMPLATE_PATH?>/images/no_photo_medium.png" alt="<?=$a_alt;?>" title="<?=$a_title;?>" />
					<?endif;?>
					<?if($fast_view_text_tmp = \CNext::GetFrontParametrValue('EXPRESSION_FOR_FAST_VIEW'))
						$fast_view_text = $fast_view_text_tmp;
					else
						$fast_view_text = Loc::getMessage('FAST_VIEW');?>
				<?if($bWrapLink):?>
				</a>
				<?endif;?>
				<?if($bShowFW):?>
					<div class="fast_view_block" data-event="jqm" data-param-form_id="fast_view" data-param-iblock_id="<?=$arParams["IBLOCK_ID"];?>" data-param-id="<?=$arItem["ID"];?>" data-param-fid="<?=$arItemIDs["strMainID"];?>" data-param-item_href="<?=urlencode($arItem["DETAIL_PAGE_URL"]);?>" data-name="fast_view"><?=$fast_view_text;?></div>
				<?endif;?>
				<?$html = ob_get_contents();
				ob_end_clean();

				foreach(GetModuleEvents(self::MODULE_ID, 'OnAsproShowImg', true) as $arEvent) // event for manipulation item img
					ExecuteModuleEventEx($arEvent, array($arParams, $arItem, $bShowFW, $bWrapLink, $dopClassImg, &$html));

				echo $html;?>
			<?endif;?>
		<?}

		public static function showSectionGallery( $params = array() ){
			$arItem = isset($params['ITEM']) ? $params['ITEM'] : array();
			$key = isset($params['GALLERY_KEY']) ? $params['GALLERY_KEY'] : 'GALLERY';
			$bReturn = isset($params['RETURN']) ? $params['RETURN'] : false;
			$arResize = isset($params['RESIZE']) ? $params['RESIZE'] : array('WIDTH' => 400, 'HEIGHT' => 400);

			if($arItem):?>
				<?ob_start();?>
					<?if($arItem[$key]):?>
						<?$count = count($arItem[$key]);?>
						<a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="thumb<?=($bReturn ? '' : ($count > 1 ? '' : ' shine'));?>">
							<span class="section-gallery-wrapper flexbox">
								<?foreach($arItem[$key] as $i => $arGalleryItem):?>
									<?
									if($arResize) {
										$resizeImage = \CFile::ResizeImageGet($arGalleryItem["ID"], array("width" => $arResize['WIDTH'], "height" => $arResize['HEIGHT']), BX_RESIZE_IMAGE_PROPORTIONAL, true, array());
										$arGalleryItem['SRC'] = $resizeImage['src'];
										$arGalleryItem['HEIGHT'] = $resizeImage['height'];
										$arGalleryItem['WIDTH'] = $resizeImage['width'];
									}?>
									<span class="section-gallery-wrapper__item<?=(!$i ? ' _active' : '');?>">
										<span class="section-gallery-wrapper__item-nav<?=($count > 1 ? ' ' : ' section-gallery-wrapper__item_hidden ');?>"></span>
										<img class="lazy img-responsive" src="<?=$arGalleryItem["SRC"]?>" alt="<?=$arGalleryItem["ALT"];?>" title="<?=$arGalleryItem["TITLE"];?>" />
									</span>
								<?endforeach;?>
							</span>
						</a>
					<?else:?>
						<a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="thumb"><img class="img-responsive " src="<?=SITE_TEMPLATE_PATH.'/images/no_photo_medium.png';?>" alt="<?=$arItem["NAME"]?>" title="<?=$arItem["NAME"]?>" /></a>
					<?endif;?>
				<?$html = ob_get_contents();
				ob_end_clean();

				foreach(GetModuleEvents(self::MODULE_ID, 'OnAsproShowSectionGallery', true) as $arEvent) // event for manipulation item img
					ExecuteModuleEventEx($arEvent, array($arItem, &$html));

				if(!$bReturn)
					echo $html;
				else
					return $html?>
			<?endif;?>
		<?}
	}
}?>