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
<section class="admin" id="admin">
  <div class="container">
    <div class="page-main">
      <div class="page-content">
        <div class="page-header">
          <div class="page-header__info">
            <h1 class="page-header__title">Личный кабинет</h1>
            <p class="page-header__account-name">
              <?=$arResult["USER_INFO"]["NAME"] . " ({$arResult["USER_INFO"]["ROLE"]})"?></p>
          </div>
          <div class="page-header__exit">
            <a class="page-header__exit-btn" href="./?logout=yes&login=yes">Выйти</a>
          </div>
        </div>
        <? if (!empty($arResult["USER_INFO"]['ROLE'])){?>
        <div class="page-filters page-filters--center">
          <form action="" method="_POST" name="city-form" id="city-form">

            <? if ($arResult["USER_INFO"]['ROLE'] == 'Администратор'){?>
            <select class="select-lektor" name="lektor" data-title="Выберите Лектора">
              <option value="all">Лекторы</option>
              <?
              foreach($arResult["LECTOR"] as $user){?>
              <? if($user['ID'] == 3 || $user['ID'] == 2 || $user['ID'] == 1) continue;?>
              <option value="<?=$user['ID']?>"><?=$user['UF_FIRST_NAME'] . ' ' . $user['UF_LAST_NAME']?>
              </option>
              <?}?>
            </select>
            <div class="search-city">
              <div class="search-city__input search-icon">
                <input type="input" id="city-input" name="city" placeholder="Поиск...">
              </div>
              <div class="search-city__result"></div>
            </div>
        </div>
        <?}?>
        <div class="page-option">
          <div class="page-option__month">
            <a class="page-option__btn-prev month-prev">
              <svg class="arrow-icon">
                <use href="./assets/svg/sprite.svg#arrow-icon"></use>
              </svg>
            </a>
            <input type="hidden" id="current-month" value="0"
              data-current-month="<?=$arResult['MONTH']['currentMonth']['number']?>"
              data-current-year="<?=$arResult['YEAR']?>">
            <div class="page-option__date js-date">
              <?=$arResult['MONTH']['currentMonth']["name"] . ' ' . $arResult['YEAR']?></div>
            <a class="page-option__btn-next month-next">
              <svg class="arrow-icon">
                <use href="./assets/svg/sprite.svg#arrow-icon"></use>
              </svg>
            </a>
          </div>
          <? if ($arResult["USER_INFO"]['ROLE'] == 'Лектор'){?>
          <div class="page-option__schedule">
            <button class="page-option__btn" data-modal-target="#modal-calendar">
              Заполнить график
            </button>
          </div>
          <?}?>
        </div>
        <?}?>
        </form>

      </div>

      <div class="page-calendar">
        <div class="page-calendar__header">
          <div class="page-calendar__week">
            <div class="page-calendar__week-desk">Понедельник</div>
            <div class="page-calendar__week-mb">пн</div>
          </div>
          <div class="page-calendar__week">
            <div class="page-calendar__week-desk">Вторник</div>
            <div class="page-calendar__week-mb">вт</div>
          </div>
          <div class="page-calendar__week">
            <div class="page-calendar__week-desk">Среда</div>
            <div class="page-calendar__week-mb">ср</div>
          </div>
          <div class="page-calendar__week">
            <div class="page-calendar__week-desk">Четверг</div>
            <div class="page-calendar__week-mb">чт</div>
          </div>
          <div class="page-calendar__week">
            <div class="page-calendar__week-desk">Пятница</div>
            <div class="page-calendar__week-mb">пт</div>
          </div>
          <div class="page-calendar__week">
            <div class="page-calendar__week-desk">Суббота</div>
            <div class="page-calendar__week-mb">сб</div>
          </div>
          <div class="page-calendar__week">
            <div class="page-calendar__week-desk">Воскресенье</div>
            <div class="page-calendar__week-mb">вс</div>
          </div>
        </div>
        <div class="page-calendar__days js-days"></div>
      </div>
    </div>
  </div>
</section>
<div class="modal modal-calendar" id="modal-calendar" role="dialog" aria-modal="true">
  <form class="modal-calendar__content" method="post">
    <div class="modal-calendar__header">
      <h2 class="modal__title">Заполнить график</h2>
      <div class="modal-calendar__month">
        <button class="modal-calendar__btn-prev" type="button">
          <svg class="arrow-icon">
            <use href="./assets/svg/sprite.svg#arrow-icon"></use>
          </svg>
        </button>
        <div class="modal-calendar__date js-modal-date">Январь 2024</div>
        <button class="modal-calendar__btn-next" type="button">
          <svg class="arrow-icon">
            <use href="./assets/svg/sprite.svg#arrow-icon"></use>
          </svg>
        </button>
      </div>
    </div>
    <div class="modal-calendar__option">
      <div class="modal-calendar__work-days">
        <h3 class="modal-calendar__subtitle">Выберете рабочие дни</h3>
        <div class="modal-calendar__box">
          <div class="modal-calendar__week">
            <p class="modal-calendar__week-day">пн</p>
            <p class="modal-calendar__week-day">вт</p>
            <p class="modal-calendar__week-day">ср</p>
            <p class="modal-calendar__week-day">чт</p>
            <p class="modal-calendar__week-day">пт</p>
            <p class="modal-calendar__week-day">сб</p>
            <p class="modal-calendar__week-day">вс</p>
          </div>
          <div class="modal-calendar__days">
            <div class="modal-calendar__input-schedule">
              <input class="button-checkbox" type="checkbox" id="modal-calendar__01" name="work_days[]" value="01.01.24"
                checked />
              <label class="button-label" for="modal-calendar__01"><span>01.01.24</span></label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-calendar__work-hours">
        <h3 class="modal-calendar__subtitle">Выберете график работы</h3>
        <div class="modal-calendar__block">
          <div class="modal-calendar__input-schedule">
            <input class="button-radio" type="radio" id="modal-calendar__two_in_two" name="schedule" value="two_in_two"
              checked />
            <label class="button-label" for="modal-calendar__two_in_two">
              <span>2/2</span>
            </label>
          </div>
          <div class="modal-calendar__input-schedule">
            <input class="button-radio" type="radio" id="modal-calendar__three_in_three" name="schedule"
              value="three_in_three" />
            <label class="button-label" for="modal-calendar__three_in_three">
              <span>3/3</span>
            </label>
          </div>
          <div class="modal-calendar__input-schedule">
            <input class="button-radio" type="radio" id="modal-calendar__days_week" name="schedule" value="days_week" />
            <label class="button-label" for="modal-calendar__days_week">
              <span>дни недели</span>
            </label>
          </div>
        </div>
      </div>
      <div class="modal-calendar__btn-wrap">
        <button class="modal-calendar__btn" type="submit">Заполнить график</button>
      </div>
    </div>
  </form>
</div>
<div class="modal modal-course">
  <form class="modal-course__content" action="" novalidate>
    <div class="modal__title">Добавить курс</div>
    <div class="modal-course__inputs">
      <div class="modal-course__name">
        <input type="text" name="UF_NAME" placeholder="Введите название курса" value="">
      </div>

      <?if ($arResult["USER_INFO"]['ROLE'] == 'Администратор'){?>
      <div class="modal-course__select modal-course__lector hide">
        <select data-title="Выберите Лектора" required>
          <?foreach($arResult["LECTOR"] as $user){?>
          <? if($user['ID'] == 3 || $user['ID'] == 2 || $user['ID'] == 1) continue;?>
          <option value="<?=$user['ID']?>"><?=$user['UF_FIRST_NAME'] . ' ' . $user['UF_LAST_NAME']?>
          </option>
          <?}?>
        </select>
      </div>
      <?}?>
      <div class="modal-course__select">
        <select name="UF_TYPE" id="" required>
          <option value="244">Индивидуальные</option>
          <option value="245">Групповой</option>
        </select>
      </div>

      <div class="modal-course__city">
        <div class="modal-course__city-input search-icon">
          <input type="text" name="UF_CITY_NAME" placeholder="Введите город" value="" required />
          <input type="hidden" name="UF_CITY" value="" required />
        </div>
        <div class="modal-course__city-result"></div>
      </div>

      <div class="modal-course__two-row">
        <div class="modal-course__datetime">
          <input type="datetime-local" name="UF_DATE" value="" require>
        </div>
        <div class="modal-course__datetime-end">
          <input type="datetime-local" name="UF_DATE_END" value="" require>
        </div>
        <!-- <div class="modal-course__date">
          <input type="text" name="course-date" value="" readonly>
        </div>
        <div class="modal-course__time">
          <input type="time" name="course-time" value="" required />
        </div> -->
      </div>
      <div class="modal-course__location">
        <input type="text" name="UF_LOCATION" placeholder="Локация" required />
      </div>
      <div class="modal-course__address">
        <input type="text" name="UF_ADDRESS" placeholder="Адрес" required />
      </div>
      <div class="modal-course__count">
        <input type="text" name="UF_TICKETS" placeholder="Количество мест" required />
      </div>
      <div class="modal-course__desc">
        <textarea name="UF_DESCRIPTION" id="" placeholder="Введите описание курса"></textarea>
      </div>
      <input type="hidden" name="ID">
      <input type="hidden" name="UF_LECTOR">
    </div>
    <button class="modal__submit">Добавить курс</button>
    <input type="hidden" />
  </form>
</div>
<div class="change-schedule js-modal-change-schedule" role="dialog" aria-modal="true">
  <button class="modal__close">
    <svg width="24" height="24">
      <use href="./assets/svg/sprite.svg#icon-close"></use>
    </svg>
  </button>
  <form class="change-schedule__form" action="">
    <div class="change-schedule__title">
      Изменить график на
      <span class="change-schedule__data">24.01.24</span>
    </div>
    <div class="change-schedule__box">
      <div class="change-schedule__checkbox-wrap">
        <input type="radio" class="change-schedule__checkbox" id="busy_day" name="schedule" />
        <label for="busy_day">Занят</label>
      </div>
      <div class="change-schedule__checkbox-wrap">
        <input type="radio" class="change-schedule__checkbox" id="free_day" name="schedule" />
        <label for="free_day">Свободен</label>
      </div>
    </div>
    <input type="hidden" />
  </form>
</div>
<div class="modal modal-lektors">
  <div class="modal-lektors__content">
    <div class="modal-lektors__title">
      Расписание на
      <br />
      <span>00.00.00</span>
    </div>
    <div class="modal-lektors__wrapper js-day"></div>
  </div>
</div>

<script>
var dataObj = <?=CUtil::PhpToJSObject($arResult)?>;
</script>
