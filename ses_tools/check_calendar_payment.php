<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
    use Bitrix\Main\Loader;
    use SES\CalendarManager\CalendarPayment;

    if (!Loader::includeModule('ses.calendarmanager')) {
        echo 'Ошибка: Модуль не инициализирован';
        return;
    }
    $data = [
        'amount' => 3300,  // Сумма в рублях
        'returnUrl' => 'https://example.com/return',  // URL возврата
        'description' => 'Оплата виртуального товара',  // Описание
    ];

    try {
        $paymentGateway = new CalendarPayment($data);
        $paymentLink = $paymentGateway->createOrder();  // Создаем заказ и получаем ссылку

        echo "Ссылка на оплату: $paymentLink";
        $paymentGateway->redirectToPayment();  // Редирект на страницу оплаты
    } catch (Exception $e) {
        echo 'Ошибка: ' . $e->getMessage();
    }
?>