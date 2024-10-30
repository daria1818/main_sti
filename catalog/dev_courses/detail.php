<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Loader;
use SES\CalendarManager\CalendarCourse;
use SES\CalendarManager\CalendarUsers;
use Bitrix\Main\Page\Asset;

global $APPLICATION;

$cssPath = "/local/components/ses/calendar.manager/templates/STIDent/css/";
$jsPath = "/local/components/ses/calendar.manager/templates/STIDent/js/detail/";

Asset::getInstance()->addCss($cssPath . "template_styles.css");
Asset::getInstance()->addCss($cssPath . "width-2.css");
Asset::getInstance()->addCss($cssPath . "font-6.css");
Asset::getInstance()->addCss($cssPath . "styles.css");
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/assets/plugins/nouislider/nouislider.min.css');
Asset::getInstance()->addCss($cssPath . "new_style.css");

 
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/assets/plugins/just-validate.min.js');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/assets/plugins/nouislider/nouislider.min.js');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/assets/plugins/inputmask.min.js');
Asset::getInstance()->addJs($jsPath . 'detail.js');

$APPLICATION->SetPageProperty("HIDE_LEFT_BLOCK", "Y");
$APPLICATION->SetTitle("Курсы учебного центра S.T.I. Dent для стоматологов");
$APPLICATION->AddChainItem("Курсы учебного центра S.T.I. Dent для стоматологов", "/catalog/dev_courses/");

if (Loader::includeModule('ses.calendarmanager')) {
	$currentUrl = $_SERVER['REQUEST_URI'];

	$parts = explode('/', trim($currentUrl, '/'));
	$courseId = (int)end($parts);

	try {
	    $calendarCourse = new CalendarCourse();
	    $courseInfo = $calendarCourse->getCourseById($courseId);

	    if ($courseInfo['success']) {
	    	//отложенные функции
	    	$APPLICATION->SetTitle($courseInfo['data']['UF_NAME']);
	    	$APPLICATION->AddChainItem($courseInfo['data']['UF_NAME']);

	    	$calendarUsers = new CalendarUsers();
	    	$users = $calendarUsers->getUsersByRoleName("Лектор");

	    	$startDate = $courseInfo['data']['UF_DATE'];
	        $endDate = $courseInfo['data']['UF_DATE_END'];

	        //подготовка массива дат
	        $dateArray = [];
			if ($startDate instanceof \Bitrix\Main\Type\DateTime && $endDate instanceof \Bitrix\Main\Type\DateTime) {
			    setlocale(LC_TIME, 'ru_RU.utf8');
			    $currentDate = clone $startDate;
			    while ($currentDate <= $endDate) {
			        $formattedDate = [
			            'data-date' => $currentDate->format('d.m.Y'),  // дата для атрибута
			            'display-date' => strftime("%d %B %Y", strtotime($currentDate->toString()))  // формат для отображения
			        ];
			        $dateArray[] = $formattedDate;
			        $currentDate = $currentDate->add("+1 day");
			    }
			}
	    	?>

<div class="training-courses__detail" data-id='<?=$courseId?>'>
	<?php if (!empty($dateArray)): ?>
	<div class="training-courses__dates">
		<?php foreach ($dateArray as $date): ?>
		<button data-date="<?= htmlspecialchars($date['data-date']) ?>"
			class="training-courses__date <?= $courseInfo['data']['UF_TICKETS'] > 0 ? 'modal-enroll__open' : ''?>">
			<?= htmlspecialchars($date['display-date']) ?>
		</button>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<div class="training-courses__person">
		<div class="training-courses__img">
			<img src="<?=!empty($users[$courseInfo['data']['UF_LECTOR']]["PHOTO"]) ? 
													$users[$courseInfo['data']['UF_LECTOR']]["PHOTO"] :
													"/local/components/ses/calendar.manager/templates/STIDent/img/no_photo.png"?>" alt="Лектор курса" />
		</div>
		<div class="training-courses__name">
			<?php echo htmlspecialchars($users[$courseInfo['data']['UF_LECTOR']]['UF_FIRST_NAME']) . " " .htmlspecialchars($users[$courseInfo['data']['UF_LECTOR']]['UF_LAST_NAME']); ?>
		</div>
	</div>

	<div class="training-courses__content">
		<div class="training-courses__desc">
			<p>
				<?php echo nl2br(htmlspecialchars($courseInfo['data']['UF_DESCRIPTION'])); ?>
			</p>
			<?/*<a class='training-courses__more'
							href="<?php echo htmlspecialchars($courseInfo['data']['UF_LINK_EXTERNAL']); ?>">узнать подробнее</a>*/?>
		</div>
		<div class="training-courses__price">
			<?php echo number_format($courseInfo['data']['UF_PRICE'], 0, ',', ' '); ?> руб.
		</div>
		<?php if (isset($courseInfo['data']['UF_TICKETS']) && $courseInfo['data']['UF_TICKETS'] > 0){ ?>
		<button class="training-courses__enroll modal-enroll__open">
			Записаться
		</button>
		<?php }else{?>
		<button class="training-courses__enroll" disabled>
			Билеты проданы
		</button>
		<?}?>

		<div class="training-courses__available">
			<span>Количество свободных мест:</span>
			<div class="training-courses__count">
				<span><?php echo (int)$courseInfo['data']['UF_TICKETS']; ?>/</span><?php echo (int)$courseInfo['data']['UF_TICKETS_BASE']; ?>
			</div>
		</div>
	</div>
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
				<?php if (!empty($dateArray)): ?>
				<select name="date" data-title="Выберите дату курса">
					<?php foreach ($dateArray as $date): ?>
					<option value="<?= htmlspecialchars($date['data-date']) ?>">
						<?= htmlspecialchars($date['data-date']) ?>
					</option>
					<?php endforeach; ?>
				</select>
				<?php endif; ?>
			</div>
			<input type="hidden" name="ID">
		</div>
		<div class="modal-enroll__personal">
			<input type="checkbox" name="" id="personal" required />
			<label for="personal">Я согласен на обработку персональных данных, а также соглашаюсь с
				<a href="/include/licenses_detail.php">политикой конфиденциальности</a>
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
<?} else {
	        echo "Ошибка: " . $courseInfo['error'];
	    }
	} catch (\Exception $e) {
	    echo "Произошла ошибка: " . $e->getMessage();
	}
}

?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>