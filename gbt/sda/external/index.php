<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use SES\CalendarManager\CalendarCourse;
use SES\CalendarManager\CalendarUsers;
use SES\CalendarManager\Geo\CityTable;

Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/css/styles_form.css');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/js/inputmask.min.js');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/js/just-validate.min.js');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/js/script_form.js');
global $APPLICATION;
$APPLICATION->SetPageProperty("HIDE_LEFT_BLOCK", "Y");

if (!Loader::includeModule('ses.calendarmanager')) {
    die;
}
    function findCityByID($cityId)
    {
        if (!is_numeric($cityId) || $cityId <= 0) {
            return 'Некорректный ID города';
        }

        try {
            $cityQuery = CityTable::getList([
                'filter' => ['ID' => $cityId],
                'select' => ['ID', 'NAME', 'REGION_ID', 'REGION_NAME' => 'REGION.NAME'],
            ]);

            if ($city = $cityQuery->fetch()) {
                $cityData = [
                    'ID' => $city['ID'],
                    'NAME' => $city['NAME'],
                    'REGION_ID' => $city['REGION_ID'],
                    'REGION_NAME' => $city['REGION_NAME'],
                ];
                return $cityData;
            } else {
               return 'Город не найден';
            }
        } catch (Exception $e) {
            return 'Ошибка при поиске города: ' . $e->getMessage();
        }
    }

    $formHash = isset($_GET['form_hash']) ? $_GET['form_hash'] : '';
    $course = null;
    if (!empty($formHash)) {

        $calendarCourse = new CalendarCourse();
        $course = $calendarCourse->getCourseByLink($formHash,'external');
        if($course){
          $CalendarUsers = new CalendarUsers();
          $lectorInfo =  $CalendarUsers->getCurUserModuleAr($course["UF_LECTOR"]);
          $fullName = $lectorInfo['UF_LAST_NAME'] . ' ' .$lectorInfo['UF_FIRST_NAME'];
          $city = findCityByID($course["UF_CITY"]);
          $fullCityName = $city["REGION_NAME"] . ', ' . $city["NAME"];
        }
    }
?>

<?php if ($course && $course['UF_TICKETS'] > 0){ ?>
<div class="form-container">
  <form id="contactForm" action="/gbt/sda/external/handler.php" method="post" novalidate>
    <h2>Запись на курс SDA <br><?=$fullCityName . ' <br> ' . $course["UF_DATE"] . ' <br> ' . $fullName?></h2>
    <div class="form-group">
      <label for="surname">Фамилия</label>
      <input type="text" id="surname" name="surname" required>
    </div>
    <div class="form-group">
      <label for="name">Имя</label>
      <input type="text" id="name" name="name" required>
    </div>
    <div class="form-group">
      <label for="phone">Телефон</label>
      <input data-telinput type="tel" id="phone" name="phone" pattern="[0-9]{10}" required>
    </div>
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>
    </div>
    <div class="form-group">
      <label for="clinic">Клиника</label>
      <input type="text" id="clinic" name="clinic" required>
    </div>
    <input type="hidden" name="form_hash" value="<?=$formHash?>">
    <button type="submit" class="submit-button">Отправить</button>
    <div id="formResult" class="form-result"></div>
  </form>
</div>
<?php }else if ($course['UF_TICKETS'] <= 0){ ?>
<div class="error-message">
  Билеты закончились.<br> <a href='/' id="back-link"> НА ГЛАВНУЮ</a>
</div>
<?php }else{?>
<div class="error-message">
  Нужен код формы.<br> <a href='/' id="back-link"> НА ГЛАВНУЮ</a>
</div>
<?} ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
