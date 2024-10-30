<?php

namespace SES\CalendarManager;

use Bitrix\Main\Web;
use Bitrix\Main\Config\Option;
use Exception;

class CalendarPayment
{
    private $options = [];
    private $data = [];
    private $paymentLink;

    public function __construct($data)
    {
        $this->options = [
            'userName' => 'stionline-api',  // Ваш логин для API Альфа-Банка
            'password' => '123ZXCdsa#@!',  // Ваш пароль для API
            'language' => 'ru',
            'gate_url_prod' => 'https://pay.alfabank.ru/payment/rest/',
            'gate_url_test' => 'https://alfa.rbsuat.com/ab/rest/',
            'test_mode' => false,  // Установите false для боевого режима
        ];
        $this->data = $data;
    }

    /**
     * Основной метод для создания заказа и получения ссылки на оплату.
     */
    public function createOrder()
    {
        $orderId = $this->generateOrderId();
        $amount = $this->data['amount'] * 100;  // Переводим в копейки

        $requestData = [
            'orderNumber' => $orderId,
            'amount' => $amount,
            'userName' => $this->options['userName'],
            'password' => $this->options['password'],
            'returnUrl' => $this->data['returnUrl'],
            'description' => $this->data['description'] ?? 'Оплата виртуального товара',
        ];

        $response = $this->sendRequest('register.do', $requestData);

        if (isset($response['formUrl'])) {
            $this->paymentLink = $response['formUrl'];
            return $response['formUrl'];
        }

        throw new Exception('Ошибка при регистрации заказа: ' . $response['errorMessage']);
    }

    /**
     * Генерация уникального идентификатора заказа.
     */
    private function generateOrderId()
    {
        return date('YmdHis') . '_' . mt_rand(1000, 9999);
    }

    /**
     * Отправка запроса в API Альфа-Банка.
     */
    private function sendRequest($method, $data)
    {
        $url = ($this->options['test_mode'] ? $this->options['gate_url_test'] : $this->options['gate_url_prod']) . $method;

        $httpClient = new Web\HttpClient();
        $httpClient->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $httpClient->disableSslVerification();

        $response = $httpClient->post($url, http_build_query($data));

        if ($this->isJson($response)) {
            return Web\Json::decode($response);
        }

        return [
            'errorCode' => 999,
            'errorMessage' => 'Не удалось получить корректный ответ от сервера',
        ];
    }

    /**
     * Проверка, является ли строка JSON.
     */
    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Редирект пользователя на страницу оплаты.
     */
    public function redirectToPayment()
    {
        if ($this->paymentLink) {
            header('Location: ' . $this->paymentLink);
            exit;
        }

        throw new Exception('Ссылка на оплату не была создана.');
    }
}

