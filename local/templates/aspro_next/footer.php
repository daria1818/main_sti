						<?CNext::checkRestartBuffer();?>
						<?IncludeTemplateLangFile(__FILE__);?>
							<?if(!$isIndex):?>
								<?if($isBlog):?>
									</div> <?// class=col-md-9 col-sm-9 col-xs-8 content-md?>
									<div class="col-md-3 col-sm-3 hidden-xs hidden-sm right-menu-md">
										<div class="sidearea">
											<?$APPLICATION->ShowViewContent('under_sidebar_content');?>
											<?CNext::get_banners_position('SIDE', 'Y');?>
											<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "sect", "AREA_FILE_SUFFIX" => "sidebar", "AREA_FILE_RECURSIVE" => "Y"), false);?>
										</div>
									</div>
								</div><?endif;?>
								<?if($isHideLeftBlock && !$isWidePage):?>
									</div> <?// .maxwidth-theme?>
								<?endif;?>
								</div> <?// .container?>
							<?else:?>
								<?
								if($isIndex){
									CNext::ShowPageType('indexblocks');
								}								
								?>
							<?endif;?>
							<?CNext::get_banners_position('CONTENT_BOTTOM');?>
						</div> <?// .middle?>
					<?//if(!$isHideLeftBlock && !$isBlog):?>
					<?if(($isIndex && $isShowIndexLeftBlock) || (!$isIndex && !$isHideLeftBlock) && !$isBlog && !$isContactAddForm):?>
						</div> <?// .right_block?>				
						<?if($APPLICATION->GetProperty("HIDE_LEFT_BLOCK") != "Y" && !defined("ERROR_404")):?>
							<div class="left_block">
								<?CNext::ShowPageType('left_block');?>
							</div>
						<?endif;?>
					<?endif;?>
				<?if($isIndex):?>
					</div>
				<?elseif(!$isWidePage):?>
					</div> <?// .wrapper_inner?>				
				<?endif;?>
			</div> <?// #content?>
			<?CNext::get_banners_position('FOOTER');?>
		</div><?// .wrapper?>
		<footer id="footer">
			<?if($APPLICATION->GetProperty("viewed_show") == "Y" || $is404):?>
				<?$APPLICATION->IncludeComponent(
					"bitrix:main.include", 
					"basket", 
					array(
						"COMPONENT_TEMPLATE" => "basket",
						"PATH" => SITE_DIR."include/footer/comp_viewed.php",
						"AREA_FILE_SHOW" => "file",
						"AREA_FILE_SUFFIX" => "",
						"AREA_FILE_RECURSIVE" => "Y",
						"EDIT_TEMPLATE" => "standard.php",
						"PRICE_CODE" => array(
							0 => "BASE",
						),
						"STORES" => array(
							0 => "",
							1 => "",
						),
						"BIG_DATA_RCM_TYPE" => "bestsell"
					),
					false
				);?>					
			<?endif;?>
			<?
			CNext::ShowPageType('footer');
			?>
		</footer>
		</div><?// .wrapper_main?>
		<div class="bx_areas">
			<?CNext::ShowPageType('bottom_counter');?>
		</div>
		<?CNext::ShowPageType('search_title_component');?>
		<?CNext::setFooterTitle();
		CNext::showFooterBasket();?>
        <?
        if(isset($_SESSION['CHECK_USERS_ORDERS']) && !empty($_SESSION['CHECK_USERS_ORDERS'])){
            ?>
            <script type="text/javascript">
                window.yaParams = { "Client": "<?=$_SESSION['CHECK_USERS_ORDERS']?>" };
                ym(65657677, 'params', window.yaParams||{});
            </script>
            <?
            unset($_SESSION['CHECK_USERS_ORDERS']);
        }
        ?>
<?/*<script>
    window.addEventListener('onBitrixLiveChat', function(event){
        var widget = event.detail.widget;
        widget.setOption('checkSameDomain', false);
    });
</script>*/?>     
<script>
        (function(w,d,u){
                var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
                var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
        })(window,document,'https://crm.stionline.ru/upload/crm/site_button/loader_1_m81983.js');
</script>

<?/*?><div class="popup_global">
	<div class="popup_bckg"></div>
	<div class="popup_warning">
		<div class="wrap_content">
			<div class="text_content">
				В связи с масштабным техническим обновлением наш магазин временно приостанавливает прием заказов.<br>
				Все заказы, оплаченные <b>до 08.03.2022</b> будут выполнены.<br>
				Магазин восстановит работу в ближайшее время.<br>
				Приносим извинения за предоставленные неудобства.<br>
				С уважением, команда STIOnline.
			</div>
			<div class="btn btn_accept">ОК</div>
		</div>
	</div>
</div><?*/?>
	</body>

<script src="https://use.fontawesome.com/2a622e8e60.js"></script>

</html>