<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?
if (!empty($_GET['fdv'])) {
  $session = \Bitrix\Main\Application::getInstance()->getSession();
  $session->set('FDT_VALUE', $_GET['fdv']);
}
if ($GET["debug"] == "y")
  error_reporting(E_ERROR | E_PARSE);
IncludeTemplateLangFile(__FILE__);
global $APPLICATION, $arRegion, $arSite, $arTheme, $USER;
/*if(!$USER->IsAdmin() && SITE_ID == 's3')
	LocalRedirect('https://stionline.ru/');
*/
$arSite = CSite::GetByID(SITE_ID)->Fetch();
$htmlClass = ($_REQUEST && isset($_REQUEST['print']) ? 'print' : false);
$bIncludedModule = (\Bitrix\Main\Loader::includeModule("aspro.next")); ?>
  <!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= LANGUAGE_ID ?>"
      lang="<?= LANGUAGE_ID ?>" <?= ($htmlClass ? 'class="' . $htmlClass . '"' : '') ?>>
  <head>
    <title><? $APPLICATION->ShowTitle() ?></title>
    <link rel="canonical" href="https://stionline.ru<?= $APPLICATION->GetCurPage() ?>">
    <!-- Google Tag Manager -->
    <script data-skip-moving='true'>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-W45J7NC');</script>
    <!-- End Google Tag Manager -->
    <?php
    // function ShowCanonical()
    // {
    // 	global $APPLICATION;
    // 	if ($APPLICATION->GetProperty("canonical")!="" && $APPLICATION->GetProperty("canonical")!=$APPLICATION->sDirPath){
    //     	return '';
    //     } else {
    //     	return false;
    //     }
    // }
    // $APPLICATION->AddBufferContent('ShowCanonical');
    ?>
    <?
    $GLOBALS["PAGE"] = explode("/", $APPLICATION->GetCurPage());
    $APPLICATION->ShowMeta("viewport"); ?>
    <? $APPLICATION->ShowMeta("HandheldFriendly"); ?>
    <? $APPLICATION->ShowMeta("apple-mobile-web-app-capable", "yes"); ?>
    <? $APPLICATION->ShowMeta("apple-mobile-web-app-status-bar-style"); ?>
    <? $APPLICATION->ShowMeta("SKYPE_TOOLBAR"); ?>
    <? CJSCore::Init();?>
    <? $APPLICATION->ShowHead(); ?>
    <? $APPLICATION->AddHeadString('<script>BX.message(' . CUtil::PhpToJSObject($MESS, false) . ')</script>', true); ?>
    <? if ($bIncludedModule)
      CNext::Start(SITE_ID); ?>

<!--     <script type="text/javascript">!function () {
        var t = document.createElement("script");
        t.type = "text/javascript", t.async = !0, t.src = "https://vk.com/js/api/openapi.js?168", t.onload = function () {
          VK.Retargeting.Init("VK-RTRG-534876-d8wLO"), VK.Retargeting.Hit()
        }, document.head.appendChild(t)
      }();</script>
    <noscript><img src="https://vk.com/rtrg?p=VK-RTRG-534876-d8wLO" style="position:fixed; left:-999px;" alt=""/>
    </noscript> -->
  </head>
<? $bIndexBot = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Lighthouse') !== false); // is indexed yandex/google bot?>
<body
    class="site_<?= SITE_ID ?> <?= ($bIncludedModule ? "fill_bg_" . strtolower(CNext::GetFrontParametrValue("SHOW_BG_BLOCK")) : ""); ?> <?= ($bIndexBot ? "wbot" : ""); ?>"
    id="main">

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W45J7NC" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

  <script>
    window.addEventListener('onBitrixLiveChat', function (event) {
      var widget = event.detail.widget;
      widget.setOption('checkSameDomain', false);
    });
  </script>
<div class="wrapper_main">
  <div id="panel"><? $APPLICATION->ShowPanel(); ?></div>
<? if(!$bIncludedModule):?>
		<?$APPLICATION->SetTitle(GetMessage("ERROR_INCLUDE_MODULE_ASPRO_NEXT_TITLE"));?>
		<center><?$APPLICATION->IncludeFile(SITE_DIR."include/error_include_module.php");?></center></body></html><?die();?>
	<?endif; ?>

<? $arTheme = $APPLICATION->IncludeComponent("aspro:theme.next", ".default", array("COMPONENT_TEMPLATE" => ".default"), false, array("HIDE_ICONS" => "Y")); ?>
<? include_once('defines.php'); ?>
<? CNext::SetJSOptions(); ?>

<div
    class="wrapper1 <?= ($isIndex && $isShowIndexLeftBlock ? "with_left_block" : ""); ?> <?= CNext::getCurrentPageClass(); ?> <?= CNext::getCurrentThemeClasses(); ?>">
<? CNext::get_banners_position('TOP_HEADER'); ?>

  <div class="clio-header header_wrap <?= $arTheme["PAGE_TITLE"]["VALUE"]; ?><?= ($isIndex ? ' index' : '') ?>">
    <header id="header" class="<?= $isIndex ?>">
      <? CNext::ShowPageType('header');
      ?>
    </header>
  </div>
<? CNext::get_banners_position('TOP_UNDERHEADER'); ?>

<? if ($arTheme["TOP_MENU_FIXED"]["VALUE"] == 'Y'): ?>
  <div id="headerfixed">
    <? CNext::ShowPageType('header_fixed'); ?>
  </div>
<? endif; ?>

  <div id="mobileheader" class="clio-mobile-menu">
    <? CNext::ShowPageType('header_mobile'); ?>
    <div id="mobilemenu"
         class="clio-sidenav <? //=($arTheme["HEADER_MOBILE_MENU_OPEN"]["VALUE"] == '1' ? 'leftside':'dropdown')?> <? //=($arTheme['HEADER_MOBILE_MENU_COMPACT']['VALUE'] == 'Y' ? 'menu-compact':'')?>">
      <? CNext::ShowPageType('header_mobile_menu'); ?>
    </div>
  </div>

<? if ($arTheme['MOBILE_FILTER_COMPACT']['VALUE'] === 'Y'): ?>
  <div id="mobilefilter" class="visible-xs visible-sm scrollbar-filter"></div>
<? endif; ?>
  <div class="tg-banner">
    <a href="https://t.me/+tK7jZzvdJKk2ZDYy"><p class="tg-banner-text">Подпишись на наш Телеграм</p></a>
    <a href="https://t.me/+tK7jZzvdJKk2ZDYy">
      <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M14.0001 0.666504C6.64008 0.666504 0.666748 6.63984 0.666748 13.9998C0.666748 21.3598 6.64008 27.3332 14.0001 27.3332C21.3601 27.3332 27.3334 21.3598 27.3334 13.9998C27.3334 6.63984 21.3601 0.666504 14.0001 0.666504ZM20.1867 9.73317C19.9867 11.8398 19.1201 16.9598 18.6801 19.3198C18.4934 20.3198 18.1201 20.6532 17.7734 20.6932C17.0001 20.7598 16.4134 20.1865 15.6667 19.6932C14.4934 18.9198 13.8267 18.4398 12.6934 17.6932C11.3734 16.8265 12.2267 16.3465 12.9867 15.5732C13.1867 15.3732 16.6001 12.2665 16.6667 11.9865C16.676 11.9441 16.6748 11.9001 16.6632 11.8582C16.6515 11.8164 16.6299 11.7781 16.6001 11.7465C16.5201 11.6798 16.4134 11.7065 16.3201 11.7198C16.2001 11.7465 14.3334 12.9865 10.6934 15.4398C10.1601 15.7998 9.68008 15.9865 9.25341 15.9732C8.77341 15.9598 7.86675 15.7065 7.18675 15.4798C6.34675 15.2132 5.69341 15.0665 5.74675 14.5998C5.77341 14.3598 6.10675 14.1198 6.73341 13.8665C10.6267 12.1732 13.2134 11.0532 14.5067 10.5198C18.2134 8.97317 18.9734 8.7065 19.4801 8.7065C19.5867 8.7065 19.8401 8.73317 20.0001 8.8665C20.1334 8.97317 20.1734 9.11984 20.1867 9.2265C20.1734 9.3065 20.2001 9.5465 20.1867 9.73317Z"
            fill="#EC1E22"></path>
      </svg>
    </a>
  </div>
<? /*filter for contacts*/
if ($arRegion) {
  if ($arRegion['LIST_STORES'] && !in_array('component', $arRegion['LIST_STORES'])) {
    if ($arTheme['STORES_SOURCE']['VALUE'] != 'IBLOCK')
      $GLOBALS['arRegionality'] = array('ID' => $arRegion['LIST_STORES']);
    else
      $GLOBALS['arRegionality'] = array('PROPERTY_STORE_ID' => $arRegion['LIST_STORES']);
  }
}
if ($isIndex) {
  $GLOBALS['arrPopularSections'] = array('UF_POPULAR' => 1);
  $GLOBALS['arrFrontElements'] = array('PROPERTY_SHOW_ON_INDEX_PAGE_VALUE' => 'Y');
} ?>

<div class="wraps hover_<?= $arTheme["HOVER_TYPE_IMG"]["VALUE"]; ?>" id="content">
<? if (!$is404 && !$isForm && !$isIndex): ?>
  <? $APPLICATION->ShowViewContent('section_bnr_content'); ?>
  <? if ($APPLICATION->GetProperty("HIDETITLE") !== 'Y'): ?>
    <!--title_content-->
    <? CNext::ShowPageType('page_title'); ?>
    <!--end-title_content-->
  <? endif; ?>
  <? $APPLICATION->ShowViewContent('top_section_filter_content'); ?>
<? endif; ?>

<? if ($isIndex): ?>
  <div class="wrapper_inner front <?= ($isShowIndexLeftBlock ? "" : "wide_page"); ?>">
  <? elseif (!$isWidePage): ?>
  <div class="wrapper_inner <?= ($isHideLeftBlock ? "wide_page" : ""); ?>">
<? endif; ?>
<? if (($isIndex && $isShowIndexLeftBlock) || (!$isIndex && !$isHideLeftBlock) && !$isBlog && !$isContactAddForm): ?>
  <div
      class="right_block <?= (defined("ERROR_404") ? "error_page" : ""); ?> wide_<?= CNext::ShowPageProps("HIDE_LEFT_BLOCK"); ?>">
<? endif; ?>
<div class="middle <?= ($is404 ? 'error-page' : ''); ?>">

<?php if ($APPLICATION->GetCurPage() == SITE_DIR) { ?>
  <div class="announce-section">
    <div class="container">
      <!-- <div class="announce-section__inner">
        <div class="announce-section__content">
          <img class="announce-section__icon" src="https://stionline.ru/upload/announce-section/announce-section-img.svg"
               alt="иконка">
          <p class="announce-section__description">Дорогие посетители! Мы рады представить вам новый раздел нашего каталога <span>"Для домашнего использования"</span>! В
            нем вы найдете средства по уходу за полостью рта, не требующие предварительной консультации с врачом.</p>
        </div>
        <a class="announce-section__button" href="https://stionline.ru/catalog/dlya_domashnego_ispolzovaniya/">Перейти</a>
      </div> -->
    </div>
  </div>

<?php } ?>

<? CNext::get_banners_position('CONTENT_TOP'); ?>
<? if (!$isIndex): ?>
  <div class="container">
  <? //h1?>
  <? if ($isHideLeftBlock && !$isWidePage): ?>
  <div class="maxwidth-theme">
  <? endif; ?>
  <? if ($isBlog): ?>
  <div class="row">
  <div class="col-md-9 col-sm-12 col-xs-12 content-md <?= CNext::ShowPageProps("ERROR_404"); ?>">
<? endif; ?>
<? endif; ?>
<? CNext::checkRestartBuffer(); ?>