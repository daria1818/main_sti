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
<?

?>
<div class="training-courses__address">
	г. Москва, 123182, ул. Щукинская, 2, 10 подъезд, 2 этаж, Учебный
	центр S.T.I.Dent
</div>

<form class="courses-filter__form" action="">
	<div class="courses-filter">
		<div class="courses-filter__caption">
			<span>Месяц</span>
			<div class="courses-filter__icon">
				<i class="courses-filter__expand"></i>
				<i class="courses-filter__purge"></i>
			</div>
		</div>
		<div class="courses-filter__body">
			<button class="courses-filter__close"></button>
			<div class="courses-filter__wrapper">
				<?foreach ($arResult["sortMonth"] as $month) {?>
				<div class="courses-filter__checkbox">
					<label>
						<input type="checkbox" name='month' value="<?=trim($month)?>" />
						<div class="courses-filter__checkbox-checkmark"></div>
						<div class="courses-filter__checkbox-body"><?=getMonthName($month);?></div>
					</label>
				</div>
				<?}?>
			</div>
			<div class="courses-filter__btns">
				<button class="courses-filter__reset">Сбросить</button>
				<button class="courses-filter__submit" disabled="">
					Применить
				</button>
			</div>
		</div>
	</div>
	<div class="courses-filter">
		<div class="courses-filter__caption">
			<span>Лектор</span>
			<div class="courses-filter__icon">
				<i class="courses-filter__expand"></i>
				<i class="courses-filter__purge"></i>
			</div>
		</div>
		<div class="courses-filter__body">
			<button class="courses-filter__close"></button>
			<div class="courses-filter__wrapper">
				<?foreach($arResult["LECTOR"] as $user){?>
				<? /*if($user['ID'] == 3 || $user['ID'] == 2 || $user['ID'] == 1) continue;*/?>
				<div class="courses-filter__checkbox">
					<label>
						<input type="checkbox" name='lector' value="<?=$user['ID']?>" />
						<div class="courses-filter__checkbox-checkmark"></div>
						<div class="courses-filter__checkbox-body"><?=$user['UF_FIRST_NAME'] . ' ' . $user['UF_LAST_NAME']?></div>
					</label>
				</div>
				<?}?>
			</div>
			<div class="courses-filter__btns">
				<button class="courses-filter__reset">Сбросить</button>
				<button class="courses-filter__submit" disabled="">
					Применить
				</button>
			</div>
		</div>
	</div>
	<div class="courses-filter">
		<div class="courses-filter__caption">
			<span>Цена</span>
			<div class="courses-filter__icon">
				<i class="courses-filter__expand"></i>
				<i class="courses-filter__purge"></i>
			</div>
		</div>
		<div class="courses-filter__body">
			<button class="courses-filter__close"></button>
			<div class="courses-filter__wrapper">
				<div class="courses-filter__range">
					<div class="courses-filter__range-inner noUi-target noUi-ltr noUi-horizontal" data-max="100000"></div>
					<div class="courses-filter__range-input courses-filter__range-input--min">
						от
						<input type="number" name="price_min" id="" placeholder="12" min="0" max="100000" />
						<span>руб</span>
					</div>
					<div class="courses-filter__range-input courses-filter__range-input--max">
						до
						<input type="number" name="price_max" id="" placeholder="100000" min="0" max="100000" />
						<span>руб</span>
					</div>
				</div>
			</div>
			<div class="courses-filter__btns">
				<button class="courses-filter__reset">Сбросить</button>
				<button class="courses-filter__submit" disabled="">
					Применить
				</button>
			</div>
		</div>
	</div>
	<div class="courses-filter">
		<div class="courses-filter__caption">
			<span>Специализация</span>
			<div class="courses-filter__icon">
				<i class="courses-filter__expand"></i>
				<i class="courses-filter__purge"></i>
			</div>
		</div>
		<div class="courses-filter__body">
			<button class="courses-filter__close"></button>
			<div class="courses-filter__wrapper">
				<?foreach($arResult["descFieldList"]['UF_SPEC'] as $id => $name){?>
				<div class="courses-filter__checkbox">
					<label>
						<input type="checkbox" name='specialization' value="<?=$id?>" />
						<div class="courses-filter__checkbox-checkmark"></div>
						<div class="courses-filter__checkbox-body"><?=$name?></div>
					</label>
				</div>
				<?}?>
			</div>
			<div class="courses-filter__btns">
				<button class="courses-filter__reset">Сбросить</button>
				<button class="courses-filter__submit" disabled="">
					Применить
				</button>
			</div>
		</div>
	</div>
	<button class="courses-filter__clear">Очистить фильтры</button>
</form>

<div class="training-courses">

</div>


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
			<div class="modal-enroll__date-course">
				<select name="date" data-title="Выберите дату курса">
				</select>
			</div>
			<input type="hidden" name="ID">
		</div>
		<div class="modal-enroll__personal">
			<input type="checkbox" name="" id="personal" required />
			<label for="personal">Я согласен на обработку персональных данных, а также соглашаюсь с
				<a href="">политикой конфиденциальности</a>
			</label>
		</div>
		<button class="modal__submit">Записаться</button>
	</form>
	<div class="modal-enroll__success">
		<div class="modal-enroll__success-title">Успешно!</div>
		<div class="modal-enroll__success-desc">
			Сейчас вы будете перенаправлены на страницу оплату
		</div>
	</div>
</div>

<script>
var dataObj = <?=CUtil::PhpToJSObject($arResult)?>;
</script>