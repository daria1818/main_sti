<?php

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\Engine\Contract\Controllerable;
use StreamTelecom\Main;
use Bitrix\Main\Config\Option;

class SmsConfirmation extends CBitrixComponent implements Controllerable
{
  public const POPUP_CONFIRMATION = 'sms_confirmation';
  public const POPUP_CONFIRMATION_EMAIL = 'sms_confirmationEmail';

  /**
   * Отправляет sms с кодом для подтверждения
   * @param string $email
   * @param string $phoneNumber
   * @return array
   */
  public function sendSmsAction($email, $phoneNumber)
  {
    global $USER;

    if (!Loader::includeModule('streamtelecom.sms')) {
      return array('success' => false);
    }

    if (empty($email) || empty($phoneNumber)) {
      return array('success' => false);
    }

    $user = $this->getUser($phoneNumber);

    if (!$user) {
      return array('success' => false);
    }

    $userSmsInfo = unserialize($user['UF_SMS']);

    if ($userSmsInfo && $userSmsInfo['TIME'] + 60 > time()) {
      return array(
        'success' => false,
        'msg' => 'Сообщение уже было отправлено. Отправка повторного сообщения возможна через минуту.'
      );
    }

    $smsCode = $this->createSmsCode();

    $service = new Main();

    $serviceResult = $service->send(array($phoneNumber), $smsCode);

    if ($serviceResult) {
      $userSmsInfo = array(
        'SMS_CODE' => $smsCode,
        'TIME' => time()
      );

      $updated = $USER->Update($user['ID'], array(
        'UF_SMS' => serialize($userSmsInfo)
      ));

      return array('success' => $updated);
    }

    return array('success' => false);
  }

  /**
   * Проверяет совпадает ли код с отправленным
   * @param string $email
   * @param string $phoneNumber
   * @return array
   */
  public function checkSmsAction($phoneNumber, $email, $code, $newEmail)
  {
    global $USER;

    if (empty($email) || empty($phoneNumber)) {
      return array('success' => false);
    }

    $user = $this->getUser($phoneNumber);

    if (!$user) {
      return array('success' => false);
    }

    $userSmsInfo = unserialize($user['UF_SMS']);

    if ($userSmsInfo && $userSmsInfo['SMS_CODE'] == $code) {

      if ($newEmail === 'Y') {
        $USER->Update($user['ID'], array('EMAIL' => $email));
        file_put_contents(__DIR__ . '/log.log', print_r($USER, true));
      }

      $USER->Authorize($user['ID']);

      return array('success' => true);
    }

    return array('success' => false);
  }

  /**
   * Возвращает имя попапа который нужно показать
   * @param string $email
   * @param string $phoneNumber
   * @return array
   */
  public function getPopupAction($email, $phoneNumber)
  {
    $user = $this->getUser($phoneNumber);

    if (!$user) {
      return array('success' => false);
    }

    if ($user['EMAIL'] === $email && $user['PERSONAL_PHONE'] === $phoneNumber) {
      return array('success' => true, 'popup' => self::POPUP_CONFIRMATION);
    }

    if ($user['EMAIL'] !== $email && $user['PERSONAL_PHONE'] === $phoneNumber) {
      return array('success' => true, 'popup' => self::POPUP_CONFIRMATION_EMAIL, 'email' => $user['EMAIL']);
    }

    return array('success' => false);
  }

  /**
   * Возвращает пользователя по номеру телефона
   * @param string $email
   * @param string $phoneNumber
   * @return array|null
   */
  private function getUser($phoneNumber)
  {
    $user = UserTable::query()
      ->setSelect(array('ID', 'UF_SMS', 'EMAIL', 'PERSONAL_PHONE'))
      ->setFilter(array(
        '=PERSONAL_PHONE' => $phoneNumber,
      ))
      ->setLimit(1)
      ->exec()
      ->fetch();

    return $user;
  }

  /**
   * Создает смс код
   * @param int $length - длинна кода
   * @return string
   */
  private function createSmsCode()
  {
    $count = Option::get( "askaron.settings", "UF_SMS_SYMB_COUNT");

    if (intval($count) <= 0) {
      $count = 4;
    }
    $start = pow(10, $count - 1);
    $end = ($start * 10) - 1;

    $code = random_int($start, $end);
    if (Option::get( "askaron.settings", "UF_POPUP_NUM_ONLY") === '1') {
      return $code;
    } else {
      return strtoupper(substr(md5($code), 0, $count));
    }
  }

  public function configureActions()
  {
    return array(
      'sendSms' => array(
        'prefilters' => array(),
        'postfilters' => array()
      ),
      'checkSms' => array(
        'prefilters' => array(),
        'postfilters' => array()
      ),
      'getPopup' => array(
        'prefilters' => array(),
        'postfilters' => array()
      )
    );
  }
}
