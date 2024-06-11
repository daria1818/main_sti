<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<?
if(!empty($arResult["ERROR"])){
  foreach ($arResult["ERROR"] as $error) {
    echo $error;
  }
  echo "<br/><a href='/'> На главную </a>";
  die();
}
?>
<section class="courses">
  <div class="container">
    <div class="page-main">
      <div class="page-content">
        <div class="page-header">
          <div class="page-header__info">
            <h1 class="page-header__title">
              <?$APPLICATION->ShowTitle();?>
            </h1>
          </div>
        </div>
        <div class="page-filters">
          <form action="" method="_POST" name="city-form" id="city-form">
            <select class="select-lektor" name="lektor" data-title="Выберите Лектора">
              <option value="all">Все лекторы</option>
              <?foreach($arResult["LECTOR"] as $user){?>
                <? if($user['ID'] == 3 || $user['ID'] == 2 || $user['ID'] == 1) continue;?>
                <option value="<?=$user['ID']?>">
                  <?=$user['UF_FIRST_NAME'] . ' ' . $user['UF_LAST_NAME']?>
                </option>
              <?}?>
            </select>
            <div class="search-city">
              <div class="search-city__input search-icon">
                <input type="input" id="city-input" name="city" placeholder="Поиск...">
              </div>
              <div class="search-city__result"></div>
            </div>
          </form>
        </div>
      </div>
      <div class="page-courses">

      </div>
    </div>
  </div>
</section>
<div class="modal modal-enroll">
  <form class="modal-enroll__content" action="" novalidate>
    <div class="modal__title">Записаться на курс</div>
    <div class="modal-enroll__inputs">
      <div class="modal-enroll__surname">
        <input type="text" name="surname" placeholder="Фамилия*" required />
      </div>
      <div class="modal-enroll__firstname">
        <input type="text" name="name" placeholder="Имя*" required />
      </div>
      <div class="modal-enroll__tel">
        <input data-telinput type="text" name="phone" placeholder="+7 (999) 999-99-99*" required />
      </div>
      <div class="modal-enroll__email">
        <input type="email" name="email" placeholder="Email" required />
      </div>
      <div class="modal-enroll__clinic">
        <input type="text" name="clinic" placeholder="Наименование клиники*" required />
      </div>
    </div>
    <div class="modal-enroll__personal">
      <input type="checkbox" name="personal-data" id="personal" required />
      <label for="personal">Я согласен на обработку персональных данных, а также соглашаюсь с <a href="">политикой
          конфиденциальности</a> </label>
    </div>
    <input type="hidden" name="ID">
    <button class="modal__submit">Записаться</button>
  </form>
  <div class="modal-enroll__success">
    <div class="modal-enroll__success-title">Успешно!</div>
    <div class="modal-enroll__success-desc">
      Благодарим за заявку.
      <br />
      Мы свяжемся с вами в ближайшее время.
    </div>
  </div>
</div>
<!-- <pre>
<?print_r($arResult);?>
</pre> -->
<script>
var dataObj = <?=CUtil::PhpToJSObject($arResult)?>;
</script>
