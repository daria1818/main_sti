<?
$session = \Bitrix\Main\Application::getInstance()->getSession();
if (!$session->has('message_anesteziya'))
{
    $session->set('message_anesteziya', 'Y');
    $popupWarningMessage = 'N';
}else{
	$popupWarningMessage = 'Y';
}
?>
<script>
	BX.ready(function(){
		var paramPopupWarningMessage = '<?=CUtil::JSEscape($popupWarningMessage)?>';
		function popupWarningMessage(){
			var popupWM = BX.PopupWindowManager.create('popup-wm', null, {
				autoHide: false,
				offsetLeft: 0,
				offsetTop: 0,
				overlay: true,
				closeByEsc: true,
				titleBar: true,
				closeIcon: true,
				contentColor: 'white',
			});
			popupWM.setTitleBar('Уважаемые клиенты!');
			popupWM.setContent(BX.create('DIV', {
				children: [
					BX.create('DIV', {
						html: 'Согласно действующему законодательству, инъекционная анестезия относится к лекарственным средствам, к которым применяется особый порядок обращения.<br/><br/>Приобрести анестетик могут только юридические дица или индивидуальные предприниматели, имеющие лицензию на медицинскую или фармацевтическую деятельность.<br/><br/>Ваш заказ будет направлен в компанию "ЭС. ТИ. АЙ. ДЕНТАЛ", менеджеры компании свяжутся для подтверждения заказа.<br/><br/>Подробную информацию можно получить по бесплатному телефону <a href="tel:8-800-555-46-07">8-800-555-46-07</a><br/>'
					}),
					BX.create('A', {
						attrs: {
							href: 'javascript:void(0)',
							class: 'btn btn-default'
						},
						text: 'ОК',
						events: {
							click: function(){
								popupWM.close();
							}
						}
					})
				]
				
			}));
			popupWM.show();
		}
		if(paramPopupWarningMessage == 'N')
			popupWarningMessage();
	})
</script>