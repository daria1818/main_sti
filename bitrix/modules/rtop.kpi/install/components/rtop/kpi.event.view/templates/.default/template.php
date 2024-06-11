<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

/**
 * @var CBitrixComponentTemplate $this
 * @var array $arParams
 * @var array $arResult
 */

$this->addExternalCss('/bitrix/js/crm/entity-editor/css/style.css');

\Bitrix\Main\UI\Extension::load('ui.buttons');

if (!empty($arResult['ERRORS']))
{
	foreach ($arResult['ERRORS'] as $error)
	{
		ShowError($error);
	}
}
else
{
	?>
	<form class='kpi-event-edit-wrapper' id="kpi-event-edit-wrapper">
		<input type="hidden" name="ID" value="<?=($arResult['ID'] ?: 0)?>">
		<div id="bx-crm-error" class="crm-property-edit-top-block"></div>
		<table class="crm-table">
			<tr>
				<td class="crm-block-inner-table">
					<div class="crm-entity-card-container" style="width: 100%">
						<div class="crm-entity-card-container-content">
							<div class="crm-entity-card-widget">
								<div class="crm-entity-card-widget-title">
								<span class="crm-entity-card-widget-title-text">
									Данные события
								</span>
								</div>
								<div class="crm-entity-widget-content">
									<?foreach ($arResult['FIELDS'] as $field){?>
										<div class="crm-entity-widget-content-block crm-entity-widget-content-block-field-text">
											<div class="crm-entity-widget-content-block-title">
												<span class="crm-entity-widget-content-block-title-text">
													<?=$field['NAME']?>
													<?=($field['REQUIRED'] ? '<span style="color: rgb(255, 0, 0);">*</span>' : '')?>
												</span>
											</div>
											<div class="crm-entity-widget-content-block-inner">
												<?if($field['TYPE'] == 'select' && isset($field['LIST'])){?>
													<select name="<?=($field['ID'] . ($field['MULTY'] == 'Y' ? '[]' : ''))?>" <?=($field['MULTY'] == 'Y' ? ' multiple' : '')?>>
														<option value="">не выбрано</option>
														<?foreach($field['LIST'] as $code => $item){?>
															<option value="<?=$code?>"<?=(!empty($field['VALUE']) && ($field['VALUE'] == $code || $field['MULTY'] == 'Y' && in_array($code, $field['VALUE'])) ? ' selected' : '')?>><?=$item?></option>
														<?}?>
													</select>
												<?}else{?>
													<input type="<?=$field['TYPE']?>" name="<?=$field['ID']?>" value="<?=$field['VALUE']?>">
												<?}?>
											</div>
										</div>
									<?}?>
								</div>
							</div>
						</div>
					</div>
				</td>
			</tr>
		</table>
		<?
		if ($arParams['IFRAME'])
		{
			?>
			<div class="crm-footer-container">
				<div class="crm-entity-section-control">
					<a id="KPI_EVENT_VIEW_APPLY_BUTTON" class="ui-btn ui-btn-success">
						Сохранить
					</a>
					<a id="KPI_EVENT_VIEW_CANCEL" class="ui-btn ui-btn-link">
						Отменить
					</a>
				</div>
			</div>
			<?
		}
		else
		{
			?>
			<div>
				<a id="KPI_EVENT_VIEW_SUBMIT_BUTTON" class="ui-btn ui-btn-success">
					Сохранить
				</a>
			</div>
			<?
		}
		?>
	</form>
	<?
	$signer = new \Bitrix\Main\Security\Sign\Signer;
	$signedParams = $signer->sign(base64_encode(serialize($arParams)), 'kpi.event.view');
	?>
	<script>
		var kpiEvent = {
			params: <?=CUtil::PhpToJSObject($arParams)?>,
			signedParameters: '<?=CUtil::JSEscape($this->getComponent()->getSignedParameters())?>',
			componentName: '<?=CUtil::JSEscape($this->getComponent()->getName())?>'
		};
	</script>
	<?
}