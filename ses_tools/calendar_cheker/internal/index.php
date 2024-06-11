<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use SES\CalendarManager\CalendarCourse;


Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/css/styles_form.css');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/js/script_form.js');
global $APPLICATION;
$APPLICATION->SetPageProperty("HIDE_LEFT_BLOCK", "Y");

if (!Loader::includeModule('ses.calendarmanager')) {
    die;
}
$formHash = isset($_GET['form_hash']) ? $_GET['form_hash'] : '';
    $course = null;
    
    if (!empty($formHash)) {
        $calendarCourse = new CalendarCourse();
        $course = $calendarCourse->getCourseByLink($formHash);
    }
?>
<?php if ($course && $course['UF_TICKETS'] > 0){ ?>
	<div class="form-container">
	        <form id="contactForm" action="#" method="post" novalidate>
	            <h2>Внутренняя форма заявки для курса <br>"<?=$course["UF_NAME"]?>"</h2>
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
	                <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" required>
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

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const formResult = document.getElementById('formResult');

    if (form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(form);

            fetch('handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text()) // Получаем текст ответа для отладки
            .then(data => {
                try {
                    const jsonData = JSON.parse(data);
                    console.log(jsonData);

                    formResult.textContent = jsonData.message;
                    if (jsonData.success) {
                        formResult.className = 'form-result success';
                        form.reset();
                    } else {
                        formResult.className = 'form-result error';
                    }
                } catch (error) {
                    console.error('Ошибка при разборе JSON:', error);
                    console.error('Ответ сервера:', data);
                    formResult.textContent = 'Ошибка при обработке ответа сервера: ' + error.message;
                    formResult.className = 'form-result error';
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                formResult.textContent = 'Ошибка при отправке формы: ' + error.message;
                formResult.className = 'form-result error';
            });
        });
    }
});

</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>