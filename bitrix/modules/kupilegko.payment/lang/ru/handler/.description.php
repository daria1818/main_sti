<?php
$MESS["ALFABANK_PAYMENT_MODULE_TITLE"] = 'Интернет-эквайринг Альфа-Банк';
$MESS["ALFABANK_PAYMENT_GROUP_GATE"] = 'Параметры подключения платежного шлюза';
$MESS["ALFABANK_PAYMENT_GROUP_HANDLER"] = 'Параметры платежного обработчика';
$MESS["ALFABANK_PAYMENT_GROUP_ORDER"] = 'Параметры заказа';
$MESS["ALFABANK_PAYMENT_GROUP_FFD"] = 'Настройки ФФД';
$MESS["ALFABANK_PAYMENT_GROUP_OFD"] = "Фискализация";

$MESS["ALFABANK_PAYMENT_API_LOGIN_NAME"] = 'Логин';
$MESS["ALFABANK_PAYMENT_API_LOGIN_DESCR"] = '';
$MESS["ALFABANK_PAYMENT_API_PASSWORD_NAME"] = 'Пароль';
$MESS["ALFABANK_PAYMENT_API_PASSWORD_DESCR"] = '';
$MESS["ALFABANK_PAYMENT_API_TEST_MODE_NAME"] = 'Тестовый режим';
$MESS["ALFABANK_PAYMENT_API_TEST_MODE_DESCR"] = 'Если отмечено, плагин будет работать в тестовом режиме. При пустом значении будет стандартный режим работы.';

$MESS["ALFABANK_PAYMENT_HANDLER_AUTO_REDIRECT_NAME"] = 'Автоматический редирект на форму оплаты';
$MESS["ALFABANK_PAYMENT_HANDLER_AUTO_REDIRECT_DESCR"] = 'Если отмечено, после оформления заказа, покупатель будет автоматически перенаправлен на страницу платежной формы.';
$MESS["ALFABANK_PAYMENT_HANDLER_LOGGING_NAME"] = 'Логирование';
$MESS["ALFABANK_PAYMENT_HANDLER_LOGGING_DESCR"] = 'Если отмечено, плагин будет логировать запросы в файл.';
$MESS["ALFABANK_PAYMENT_HANDLER_TWO_STAGE_NAME"] = 'Двухстадийные платежи';
$MESS["ALFABANK_PAYMENT_HANDLER_TWO_STAGE_DESCR"] = 'Если отмечено, будет производиться двухстадийный платеж. При пустом значении будет производиться одностадийный платеж.';
$MESS["ALFABANK_PAYMENT_HANDLER_SHIPMENT_NAME"] = 'Разрешить отгрузку';
$MESS["ALFABANK_PAYMENT_HANDLER_SHIPMENT_DESCR"] = 'Если отмечено, то после успешной оплаты будет автоматически разрешена отгрузка заказа.';

$MESS["ALFABANK_PAYMENT_ORDER_NUMBER_NAME"] = 'Уникальный идентификатор заказа в магазине';
$MESS["ALFABANK_PAYMENT_ORDER_NUMBER_DESCR"] = '';
$MESS["ALFABANK_PAYMENT_ORDER_AMOUNT_NAME"] = 'Сумма заказа';
$MESS["ALFABANK_PAYMENT_ORDER_AMOUNT_DESCR"] = '';
$MESS["ALFABANK_PAYMENT_ORDER_DESCRIPTION_NAME"] = 'Описание заказа';
$MESS["ALFABANK_PAYMENT_ORDER_DESCRIPTION_DESCR"] = 'Передаются только первые 24 символа этого поля.Текст может содержать метки: #PAYMENT_ID# - ID оплаты, #ORDER_ID# - ID заказа, #PAYMENT_NUMBER# - номер оплаты, #ORDER_NUMBER# - номер заказа, #USER_EMAIL# - Email покупателя';


$MESS["ALFABANK_PAYMENT_FFD_VERSION_NAME"] = 'Формат фискальных документов';
$MESS["ALFABANK_PAYMENT_FFD_VERSION_DESCR"] = 'Формат версии требуется указать в личном кабинете банка и в кабинете сервиса фискализации';
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_NAME"] = 'Тип оплаты';
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_DESCR"] = 'Для ФФД версии 1.05 и выше';
$MESS['ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_1'] = "Полная предварительная оплата до момента передачи предмета расчёта";
$MESS['ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_2'] = "Частичная предварительная оплата до момента передачи предмета расчёта";
$MESS['ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_3'] = "Аванс";
$MESS['ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_4'] = "Полная оплата в момент передачи предмета расчёта";
$MESS['ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_5'] = "Частичная оплата предмета расчёта в момент его передачи с последующей оплатой в кредит";
$MESS['ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_6'] = "Передача предмета расчёта без его оплаты в момент его передачи с последующей оплатой в кредит";
$MESS['ALFABANK_PAYMENT_FFD_PAYMENT_METHOD_VALUE_7'] = "Оплата предмета расчёта после его передачи с оплатой в кредит";

$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_NAME"] = 'Тип оплачиваемой позиции';
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_DELIVERY_NAME"] = 'Тип оплачиваемой позиции для доставки';
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_DESCR"] = 'Для ФФД версии 1.05 и выше';
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_1"]  = "Товар";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_2"]  = "Подакцизный товар";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_3"]  = "Работа";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_4"]  = "Услуга";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_5"]  = "Ставка азартной игры";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_6"]  = "Выигрыш азартной игры";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_7"]  = "Лотерейный билет";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_8"]  = "Выигрыш лотереи";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_9"]  = "Предоставление РИД";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_10"] = "Платёж";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_11"] = "Агентское вознаграждение";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_12"] = "Составной предмет расчёта";
$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_OBJECT_VALUE_13"] = "Иной предмет расчёта";


$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_NAME"] = "Чек выпускает банк";
$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_DESCR"] = "Если отмечено, то сформирует и отправит клиенту чек. Опция платная, за подключением обратитесь в сервисную службу банка. При использовании необходимо настроить НДС продаваемых товаров";

$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_0"] = "Общая";
$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_1"] = "Упрощённая, доход";
$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_2"] = "Упрощённая, доход минус расход";
$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_3"] = "Единый налог на вменённый доход";
$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_4"] = "Единый сельскохозяйственный налог";
$MESS["ALFABANK_PAYMENT_OFD_RECIEPT_VALUE_5"] = "Патентная система налогообложения";


$MESS["ALFABANK_PAYMENT_OFD_TAX_SYSTEM_NAME"] = "Система налогообложения";
$MESS["ALFABANK_PAYMENT_OFD_TAX_SYSTEM_DESCR"] = "";

$MESS["ALFABANK_PAYMENT_RETURN_URL_NAME"] = "Адрес, на который требуется перенаправить пользователя в случае успешной оплаты";
$MESS["ALFABANK_PAYMENT_RETURN_URL_DESCR"] = "Не обязательно для заполнения. Адрес должен быть указан полностью, включая используемый протокол";
$MESS["ALFABANK_PAYMENT_FAIL_URL_NAME"] = "Адрес, на который требуется перенаправить пользователя в случае неуспешной оплаты";
$MESS["ALFABANK_PAYMENT_FAIL_URL_DESCR"] = "Не обязательно для заполнения. Адрес должен быть указан полностью, включая используемый протокол";

$MESS["ALFABANK_PAYMENT_FFD_PAYMENT_DELIVERY_METHOD_NAME"] = 'Тип оплаты для доставки';