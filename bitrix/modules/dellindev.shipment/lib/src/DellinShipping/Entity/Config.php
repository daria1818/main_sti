<?php

declare(strict_types=1);

namespace DellinShipping\Entity;

use DellinShipping\Entity\Cargo;
use DellinShipping\Kernel;
use Bitrix\Main\Localization\Loc;
use Sale\Handlers\Delivery\DellinBlockAdmin;

/**
 * Class Config
 * Dependency Injection для конфига и приведение данных к единому интерфейсу ввода.
 * Используется для унификации и централизации точки входа для объектов.
 * @package DellinDev\Shipping\Interfaces
 */


class Config
{

    public bool $isErrors = false;
    public array $errors = [];

    /**
     * Параметр имени профиля.
     * @var string
     */

    public string $name;
    /**
     * Ключ приложения. Для получения ключа необходимо пройти регистрацию на сайте dellin.ru
     * @var string
     */
    public ?string $appkey;
    /**
     * Логин от Личного кабинета.
     * В качестве логина можно использовать как email, так и номер телефона.
     * Формат номера телефона: "+7XXXXXXXXXX" - 12 символов, начиная с "+7"
     * @var string
     */
    public ?string $login;
    /**
     * Пароль от Личного кабинета на dellin.ru
     * @var string
     */
    public ?string $password;
    /**
     *
     * @var string
     */
    public string $email;
    public ?string $couteragent;
    public ?int $deliveryDelay;
    public string $kladrCodeDeliveryFrom;
    public ?string $opfCountry;
    public ?string $senderForm;
    public ?string $senderName;
    public ?int $senderInn;
    public ?string $senderContactName;
    public ?int $senderContactPhone;
    public ?string $senderContactEmail;
    public ?string $senderJuridicalAddress;
    public bool $isSmallGoods;
    public bool $isInsuranceGoodsWithDeclarePrice;
    public ?string $workStart;
    public ?string $workBreakStart;
    public ?string $workBreakEnd;
    public ?string $workEnd;
    public int $terminal_id;
    public bool $isGoodsLoading;
    public ?string $loadingAddress;


    public ?object $requirementsLoading;

    public bool $isGoodsUnloading;


    public ?object $requirementsUnloading;

    public string $loadingGroupingOfGoods;

    public bool  $isUseDefaultDemension;
    public ?float $defaultLenght;
    public ?float $defaultWidth;
    public ?float $defaultHeight;
    public ?float $defaultWeight;

    public bool $isDebug;
    public bool $isWarning;


    public ?string $frieghtTypeUID;

    //Деревянная обрешётка
    public bool $packingForGoodsCrate = false;
    //Жёсткий короб
    public bool $packingForGoodsCratePlus = false;
    //Картонные коробки
    public bool $packingForGoodsBox = false;
    public int  $packingBoxCount = 1;
    //Дополнительная упаковка
    public bool $packingForGoodsType = false;
    //Деревянная обрешётка + амортизация
    public bool $packingForGoodsCrateWithBuble = false;
    //спец. упаковка для автостёкл
    public bool $packingForGoodsCarGlass = false;
    public int  $packingCarGlass = 1;
    //спец. упаковка для автозапчастей
    public bool $packingForGoodsCarParts = false;
    public int  $packingCarParts = 1;
    //Палетный борт + амортизация
    public bool $packingForGoodsPalletWithBubble = false;
    //Мешок
    public bool $packingForGoodsBag = false;
    public int  $packingBagCount = 1;
    //Воздушно-пузырьковая плёнка
    public bool $packingForGoodsBubble = false;
    //Палетный борт
    public bool $packingForGoodsPallet = false;

    /**
     * Config constructor.
     * @param string $name
     */
//    public function __construct(string $name)
//    {
//        $this->name = $name;
//    }

    public function setFrieghtTypeUID($frieghtTypeUID)
    {
        $this->frieghtTypeUID = $frieghtTypeUID;
    }
    /**
     * ����� ��� ��������� �������� ��� ������������
     * @param string $appkey
     * @param string $login
     * @param string $password
     * @param string $email
     * @param string $counteragent
     */

    public function setLoginData(?string $appkey, ?string $login, ?string $password,
                                 /*string $email,*/ ?string $counteragent): void
    {
        $this->setAppkey($appkey);
        $this->setLogin($login);
        $this->setPassword($password);
//        $this->setEmail($email);
        $this->setCounteragent($counteragent);
    }


    public function getLoginData(): object
    {
        $obj = new \stdClass();

        $obj->appkey = $this->getAppkey();
        $obj->login = $this->getLogin();
        $obj->password = $this->getPassword();
       /* $obj->email = $this->getEmail();*/
        $obj->couteragent = $this->getCouteragent();

        return $obj;
    }

    /**
     * Метод задающий параметры отправителя.
     * @param int|null $deliveryDelay - отложенная доставка дней. По-умолчанию присваивается 1, если значение null
     * @param string|null $kladrCodeDeliveryFrom - поле КЛАДР города отправления.
     * @param string $opfCountry - поле ОПФ
     * @param string $senderForm - поле форма отправления( Юридическая информация)
     * @param string $senderName - Имя отправителя(Юридическая информация)
     * @param int $senderInn - ИНН юридического лица отправителя
     * @param string $senderContactName - Контактное лицо для связи
     * @param int $senderContactPhone - Телефон контактного лица
     * @param string $senderContactEmail - Почта контактного лица
     * @param string $senderJuridicalAddress - Юридический адрес отправителя в формате:
     *                                                                  "Россия, Москва, Кутузовский просп., д.18"
     */

    public function setSenderData(?int $deliveryDelay, string $kladrCodeDeliveryFrom,
                                  ?string $opfCountry, ?string $senderForm, ?string $senderName, ?int $senderInn,
                                  ?string $senderContactName, ?int $senderContactPhone, string $senderContactEmail,
                                  string $senderJuridicalAddress): void
    {
        $this->setDeliveryDelay($deliveryDelay);
        $this->setKladrCodeDeliveryFrom($kladrCodeDeliveryFrom);
        $this->setOpfCountry($opfCountry);
        $this->setSenderForm($senderForm);
        $this->setSenderName($senderName);
        $this->setSenderInn($senderInn);
        $this->setSenderContactName($senderContactName);
        $this->setSenderContactPhone($senderContactPhone);
        $this->setSenderContactEmail($senderContactEmail);
        $this->setSenderJuridicalAddress($senderJuridicalAddress);
    }




    public function getSenderData(): object
    {
        $obj = new \stdClass();

        $obj->deliveryDelay = $this->getDeliveryDelay();
        $obj->kladrCodeDeliveryFrom = $this->getKladrCodeDeliveryFrom();
        $obj->opfCountry = $this->getOpfCountry();
        $obj->senderForm = $this->getSenderForm();
        $obj->senderName = $this->getSenderName();
        $obj->senderInn = $this->getSenderInn();
        $obj->senderContactName =$this->getSenderContactName();
        $obj->senderContactPhone = $this->getSenderContactPhone();
        $obj->senderContactEmail = $this->getSenderContactEmail();
        $obj->senderJuridicalAddress = $this->getSenderJuridicalAddress();

        return $obj;
    }


    /**
     * Метод задающий параметры груза (предпочтительные параметры для забора).
     * @param bool $isSmallGoods - малогабаритный груз
     * @param bool $isInsuranceGoodsWithDeclarePrice - страховать груз с заявленной стоимостью
     * @param string|null $workStart - начало работы вашего склада/магазина
     * @param string|null $workBreakStart - начало перерыва на обед вашего склада/магазина
     * @param string|null $workBreakEnd - конец перерыва на обед вашего склада/магазина
     * @param string|null $workEnd - конец работы вашего склада/магазина
     */
    public function setCargoParams(bool $isSmallGoods, bool $isInsuranceGoodsWithDeclarePrice,
                                   ?string $workStart,  ?string $workBreakStart,
                                   ?string $workBreakEnd, ?string $workEnd, string $loadingGroupingOfGoods):void
    {

        $this->setIsSmallGoods($isSmallGoods);
        $this->setIsInsuranceGoodsWithDeclarePrice($isInsuranceGoodsWithDeclarePrice);
        $this->setWorkStart($workStart);
        $this->setWorkBreakStart($workBreakStart);
        $this->setWorkBreakEnd($workBreakEnd);
        $this->setWorkEnd($workEnd);
        $this->setLoadingGroupingOfGoods($loadingGroupingOfGoods);
    }

    public function getCargoParams(): object
    {
        $obj = new \stdClass();

        $obj->isSmallGoods = $this->isSmallGoods();
        $obj->isInsuranceGoodsWithDeclarePrice = $this->isInsuranceGoodsWithDeclarePrice();
        $obj->workStart = $this->getWorkStart();
        $obj->workBreakStart = $this->getWorkBreakStart();
        $obj->workBreakEnd = $this->getWorkBreakEnd();
        $obj->workEnd = $this->getWorkEnd();
        $obj->loadingGroupingOfGoods = $this->getLoadingGroupingOfGoods();

        return $obj;

    }


    public function setLoadingData( ?int $terminal_id, bool $isGoodsLoading ,
                                    ?string $loadingAddress ): void
    {
        $this->setIsGoodsLoading($isGoodsLoading);
//        $this->setLoadingType($loadingType);
        $this->setTerminalId($terminal_id);
        $this->setLoadingAddress($loadingAddress);
//        $this->setLoadingTransportRequirements($loadingTransportRequirements);
//        $this->setLoadingTransportEquipments($loadingTransportEquipments);

    }


    public function getLoadingData(): object
    {
        $obj = new \stdClass();
        $obj->loadingType = $this->getLoadingType();
        $obj->terminal_id = $this->getTerminalId();
        $obj->isGoodsLoading = $this->isGoodsLoading();
        $obj->loadingAddress = $this->getLoadingAddress();
        $obj->loadingTransportRequirements = $this->getLoadingTransportRequirements();
        $obj->loadingTransportEquipments = $this->getLoadingTransportEquipments();

        return $obj;
    }


    public function setRequirementsLoading(?object $requirements){
        $this->requirementsLoading = $requirements;
    }

    public function setRequirementsUnloading(?object $requirements){
        $this->requirementsUnloading = $requirements;
    }

    public function getRequirementsLoading(){
        return $this->requirementsLoading;
    }

    public function getRequirementsUnloading(){
        return $this->requirementsUnloading;
    }

    public function setUnloadingData( bool $isGoodsUnloading /*, ?string $unloading_type,
                                      ?string $unloadingTransportRequirements, ?string $unloadingTransportEquipments*/ ): void
    {
        $this->setIsGoodsUnloading($isGoodsUnloading);
//        $this->setUnloadingType($unloading_type);
//        $this->setUnloadingTransportRequirements($unloadingTransportRequirements);
//        $this->setUnloadingTransportEquipments($unloadingTransportEquipments);

    }


    public function getUnloadingData(): object
    {
        $obj = new \stdClass();
        $obj->loadingType = $this->getUnloadingType();
        $obj->isGoodsUnloading = $this->isGoodsUnloading();
        $obj->loadingTransportRequirements = $this->getUnloadingTransportRequirements();
        $obj->loadingTransportEquipments = $this->getUnloadingTransportEquipments();

        return $obj;
    }

    public function setLWHAndWeight(bool $isUseDefaultDemension, ?float $defaultLenght, ?float $defaultWidth,
                                   ?float $defaultHeight, ?float $defaultWeight)
    {
        $this->setIsUseDefaultDemension($isUseDefaultDemension);
        $this->setDefaultLenght($defaultLenght);
        $this->setDefaultWidth($defaultWidth);
        $this->setDefaultHeight($defaultHeight);
        $this->setDefaultWeight($defaultWeight);
    }

    public function setLoggerSettings( bool $isDebug, bool $isWarning): void
    {
        $this->isDebug = $isDebug;
        $this->isWarning = $isWarning;
    }


    /**
     * Да простят меня коллеги за такую реализацию.
     * Этот метод сеттит упаковки и должен быть по идее частью заказа, а в частности производной от товара, который
     * собираются отправлять, но мир малых коммерческих решений не готов к пониманию коэффициентов перевода (удельных значений)
     * количественной упаковки. Поэтому реализация упаковок остаётся на уровне сущности конфига.
     * Не ругайтесь, CLI для сущности пока будет здесь.
     * @param bool $packingForGoodsCrate
     * @param bool $packingForGoodsCratePlus
     * @param bool $packingForGoodsBox
     * @param bool $packingForGoodsType
     * @param bool $packingForGoodsCrateWithBuble
     * @param bool $packingForGoodsCarGlass
     * @param bool $packingForGoodsCarParts
     * @param bool $packingForGoodsPalletWithBubble
     * @param bool $packingForGoodsBag
     * @param bool $packingForGoodsBubble
     * @param bool $packingForGoodsPallet
     */
    public function setAdditionalServicePacking(bool $packingForGoodsCrate, bool $packingForGoodsCratePlus,
                                                bool $packingForGoodsBox,  bool $packingForGoodsType,
                                                bool $packingForGoodsCrateWithBuble, bool $packingForGoodsCarGlass,
                                                bool $packingForGoodsCarParts, bool $packingForGoodsPalletWithBubble,
                                                bool $packingForGoodsBag, bool $packingForGoodsBubble,
                                                bool $packingForGoodsPallet): void
    {

        $this->setPackingForGoodsCrate($packingForGoodsCrate);
        $this->setPackingForGoodsCratePlus($packingForGoodsCratePlus);
        $this->setPackingForGoodsBox($packingForGoodsBox);
        $this->setPackingForGoodsType($packingForGoodsType);
        $this->setPackingForGoodsCrateWithBuble($packingForGoodsCrateWithBuble);
        $this->setPackingForGoodsCarGlass($packingForGoodsCarGlass);
        $this->setPackingForGoodsCarParts($packingForGoodsCarParts);
        $this->setPackingForGoodsPalletWithBubble($packingForGoodsPalletWithBubble);
        $this->setPackingForGoodsBag($packingForGoodsBag);
        $this->setPackingForGoodsBubble($packingForGoodsBubble);
        $this->setPackingForGoodsPallet($packingForGoodsPallet);

    }

    public function getArrayAdditionalServicePacking(): array
    {
        $result = [];
        $arrPackage = [
                        'crate' => $this->isPackingForGoodsCrate(),
                        'cratePlus' => $this->isPackingForGoodsCratePlus(),
                        'box' => $this->isPackingForGoodsBox(),
                        'type' => $this->isPackingForGoodsType(),
                        'crateWithBubble' => $this->isPackingForGoodsCrateWithBuble(),
                        'carGlass' => $this->isPackingForGoodsCarGlass(),
                        'carParts' => $this->isPackingForGoodsCarParts(),
                        'palletWithBubble' => $this->isPackingForGoodsPalletWithBubble(),
                        'bag' => $this->isPackingForGoodsBag(),
                        'bubble' => $this->isPackingForGoodsBubble(),
                        'pallet' => $this->isPackingForGoodsPallet()
        ];


        foreach ($arrPackage as $type => $state){
            $this->addInArray($type, $state, $result);
        }

        return $result;

    }

    private function addInArray($type, $isAdd, &$array){
        if($isAdd){
                $array[] = $type;
            }
    }

    public function isDebug()
    {
        return $this->isDebug;
    }

    public function isWarning()
    {
        return $this->isWarning;
    }



    /**
     * @return bool
     */
    public function isUseDefaultDemension(): bool
    {
        return $this->isUseDefaultDemension;
    }

    /**
     * @param bool $isUseDefaultDemension
     */
    public function setIsUseDefaultDemension(bool $isUseDefaultDemension): void
    {
        $this->isUseDefaultDemension = $isUseDefaultDemension;
    }

    /**
     * @return float|null
     */
    public function getDefaultLenght(): ?float
    {
        return $this->defaultLenght;
    }

    /**
     * @param float|null $defaultLenght
     */
    public function setDefaultLenght(?float $defaultLenght): void
    {
        $this->defaultLenght = $defaultLenght;
    }

    /**
     * @return float|null
     */
    public function getDefaultWidth(): ?float
    {
        return $this->defaultWidth;
    }

    /**
     * @param float|null $defaultWidth
     */
    public function setDefaultWidth(?float $defaultWidth): void
    {
        $this->defaultWidth = $defaultWidth;
    }

    /**
     * @return float|null
     */
    public function getDefaultHeight(): ?float
    {
        return $this->defaultHeight;
    }

    /**
     * @param float|null $defaultHeight
     */
    public function setDefaultHeight(?float $defaultHeight): void
    {
        $this->defaultHeight = $defaultHeight;
    }

    /**
     * @return float|null
     */
    public function getDefaultWeight(): ?float
    {
        return $this->defaultWeight;
    }

    /**
     * @param float|null $defaultWeight
     */
    public function setDefaultWeight(?float $defaultWeight): void
    {
        $this->defaultWeight = $defaultWeight;
    }



    /**
     * Получаем параметр профиля доставки.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Устанавливаем параметр профиля доставки.
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getAppkey(): ?string
    {

        return $this->appkey;
    }

    /**
     * @param string $appkey
     */
    public function setAppkey(?string $appkey): void
    {
        $appkey = str_replace([' ', ':', ], '', $appkey);
        $this->appkey = $appkey;
    }

    /**
     * @return string
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin(?string $login): void
    {
        $this->login = $login;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getCouteragent(): ?string
    {
        return $this->couteragent;
    }

    /**
     * @param string $couteragent
     */
    public function setCounteragent(?string $couteragent): void
    {
        $this->couteragent = $couteragent;
    }

    /**
     * @return string
     */
    public function getDeliveryDelay(): ?int
    {
        return $this->deliveryDelay;
    }

    /**
     * @param int $deliveryDelay
     */
    public function setDeliveryDelay(?int $deliveryDelay): void
    {
        $deliveryDelay = ($deliveryDelay == null)? 1 : $deliveryDelay;
        $this->deliveryDelay = $deliveryDelay;
    }

    /**
     * @return int
     */
    public function getKladrCodeDeliveryFrom(): string
    {
        return $this->kladrCodeDeliveryFrom;
    }

    /**
     * @param int $kladrCodeDeliveryFrom
     */
    public function setKladrCodeDeliveryFrom(string $kladrCodeDeliveryFrom): void
    {
        $this->kladrCodeDeliveryFrom = $kladrCodeDeliveryFrom;
    }

    /**
     * @return string
     */
    public function getOpfCountry(): string
    {
        return $this->opfCountry;
    }

    /**
     * @param string $opfCountry
     */
    public function setOpfCountry(string $opfCountry): void
    {
        $this->opfCountry = $opfCountry;
    }

    /**
     * @return string
     */
    public function getSenderForm(): string
    {
        return $this->senderForm;
    }

    /**
     * @param string $senderForm
     */
    public function setSenderForm(string $senderForm): void
    {
        $this->senderForm = $senderForm;
    }

    /**
     * @return string
     */
    public function getSenderName(): string
    {
        return $this->senderName;
    }

    /**
     * @param string $senderName
     */
    public function setSenderName(string $senderName): void
    {
        $this->senderName = $senderName;
    }

    /**
     * @return int
     */
    public function getSenderInn(): int
    {
        return $this->senderInn;
    }

    /**
     * @param int $senderInn
     */
    public function setSenderInn(int $senderInn): void
    {
        $this->senderInn = $senderInn;
    }

    /**
     * @return string
     */
    public function getSenderContactName(): string
    {
        return $this->senderContactName;
    }

    /**
     * @param string $senderContactName
     */
    public function setSenderContactName(?string $senderContactName): void
    {
        $this->senderContactName = $senderContactName;
    }

    /**
     * @return int
     */
    public function getSenderContactPhone(): int
    {
        $phone = DellinBlockAdmin::changeFormatPhone($this->senderContactPhone);
        return  (int) $phone;
    }

    /**
     * @param int $senderContactPhone
     */
    public function setSenderContactPhone(?int $senderContactPhone): void
    {
        $this->senderContactPhone = $senderContactPhone;
    }

    /**
     * @return string
     */
    public function getSenderContactEmail(): string
    {
        return $this->senderContactEmail;
    }

    /**
     * @param string $senderContactEmail
     */
    public function setSenderContactEmail(?string $senderContactEmail): void
    {
        $this->senderContactEmail = $senderContactEmail;
    }

    /**
     * @return string
     */
    public function getSenderJuridicalAddress(): string
    {
        return $this->senderJuridicalAddress;
    }

    /**
     * @param string $senderJuridicalAddress
     */
    public function setSenderJuridicalAddress(?string $senderJuridicalAddress): void
    {
        $this->senderJuridicalAddress = $senderJuridicalAddress;
    }

    /**
     * @return bool
     */
    public function isSmallGoods(): bool
    {
        return $this->isSmallGoods;
    }

    /**
     * @param bool $isSmallGoods
     */
    public function setIsSmallGoods(?bool $isSmallGoods): void
    {
        if($isSmallGoods == null){
            $this->isSmallGoods = false;
        }
        $this->isSmallGoods = $isSmallGoods;
    }

    /**
     * @return bool
     */
    public function isInsuranceGoodsWithDeclarePrice(): bool
    {
        return $this->isInsuranceGoodsWithDeclarePrice;
    }

    /**
     * @param bool $isInsuranceGoodsWithDeclarePrice
     */
    public function setIsInsuranceGoodsWithDeclarePrice(bool $isInsuranceGoodsWithDeclarePrice): void
    {
        $this->isInsuranceGoodsWithDeclarePrice = $isInsuranceGoodsWithDeclarePrice;
    }

    /**
     * @return string
     */
    public function getWorkStart(): ?string
    {
        return $this->workStart;
    }

    /**
     * @param string $workStart
     */
    public function setWorkStart(?string $workStart): void
    {
        $this->workStart = $workStart;
    }

    /**
     * @return string
     */
    public function getWorkBreakStart(): ?string
    {
        return $this->workBreakStart;
    }

    /**
     * @param string $workBreakStart
     */
    public function setWorkBreakStart(?string $workBreakStart): void
    {
        $this->workBreakStart = $workBreakStart;
    }

    /**
     * @return string
     */
    public function getWorkBreakEnd(): ?string
    {
        return $this->workBreakEnd;
    }

    /**
     * @param string $workBreakEnd
     */
    public function setWorkBreakEnd(?string $workBreakEnd): void
    {
        $this->workBreakEnd = $workBreakEnd;
    }

    /**
     * @return string
     */
    public function getWorkEnd(): ?string
    {
        return $this->workEnd;
    }

    /**
     * @param string $workEnd
     */
    public function setWorkEnd(?string $workEnd): void
    {
        $this->workEnd = $workEnd;
    }

    /**
     * @return int
     */
    public function getTerminalId(): int
    {
        return $this->terminal_id;
    }

    /**
     * @param int $terminal_id
     */
    public function setTerminalId(?int $terminal_id): void
    {
        $this->terminal_id = $terminal_id;
    }

    /**
     * @return bool
     */
    public function isGoodsLoading(): bool
    {
        return $this->isGoodsLoading;
    }

    /**
     * @param bool $isGoodsLoading
     */
    public function setIsGoodsLoading(bool $isGoodsLoading): void
    {
        $this->isGoodsLoading = $isGoodsLoading;
    }

    /**
     * @return string
     */
    public function getLoadingAddress(): ?string
    {
        return $this->loadingAddress;
    }

    /**
     * @param string $loadingAddress
     */
    public function setLoadingAddress(?string $loadingAddress): void
    {
        $this->loadingAddress = $loadingAddress;
    }

    /**
     * @return string
     */
    public function getLoadingType(): ?string
    {
        return $this->loadingType;
    }

    /**
     * @param string $loadingType
     */
    public function setLoadingType(?string $loadingType): void
    {
        $this->loadingType = $loadingType;
    }

    /**
     * @return string
     */
    public function getLoadingTransportRequirements(): ?string
    {
        return $this->loadingTransportRequirements;
    }

    /**
     * @param string $loadingTransportRequirements
     */
    public function setLoadingTransportRequirements(?string $loadingTransportRequirements): void
    {
        $this->loadingTransportRequirements = $loadingTransportRequirements;
    }

    /**
     * @return string
     */
    public function getLoadingTransportEquipments(): ?string
    {
        return $this->loadingTransportEquipments;
    }

    /**
     * @param string $loadingTransportEquipments
     */
    public function setLoadingTransportEquipments(?string $loadingTransportEquipments): void
    {
        $this->loadingTransportEquipments = $loadingTransportEquipments;
    }

    /**
     * @return bool
     */
    public function isGoodsUnloading(): bool
    {
        return $this->isGoodsUnloading;
    }

    /**
     * @param bool $isGoodsUnloading
     */
    public function setIsGoodsUnloading(bool $isGoodsUnloading): void
    {
        $this->isGoodsUnloading = $isGoodsUnloading;
    }

    /**
     * @return string
     */
    public function getUnloadingType(): ?string
    {
        return $this->unloadingType;
    }

    /**
     * @param string $unloadingType
     */
    public function setUnloadingType(?string $unloadingType): void
    {
        $this->unloadingType = $unloadingType;
    }

    /**
     * @return string
     */
    public function getUnloadingTransportRequirements(): ?string
    {
        return $this->unloadingTransportRequirements;
    }

    /**
     * @param string $unloadingTransportRequirements
     */
    public function setUnloadingTransportRequirements(?string $unloadingTransportRequirements): void
    {
        $this->unloadingTransportRequirements = $unloadingTransportRequirements;
    }

    /**
     * @return string
     */
    public function getUnloadingTransportEquipments(): ?string
    {
        return $this->unloadingTransportEquipments;
    }

    /**
     * @param string $unloadingTransportEquipments
     */
    public function setUnloadingTransportEquipments(?string $unloadingTransportEquipments): void
    {
        $this->unloadingTransportEquipments = $unloadingTransportEquipments;
    }

    /**
     * @return string
     */
    public function getLoadingGroupingOfGoods(): ?string
    {
        return $this->loadingGroupingOfGoods;
    }

    /**
     * @param string $loadingGroupingOfGoods
     */
    public function setLoadingGroupingOfGoods(string $loadingGroupingOfGoods): void
    {
        $this->loadingGroupingOfGoods = $loadingGroupingOfGoods;
    }

    /**
     * @param string|null $couteragent
     */
    public function setCouteragent(?string $couteragent): void
    {
        $this->couteragent = $couteragent;
    }

    /**
     * @param string|null $loadingAdditional
     */
    public function setLoadingAdditional(?string $loadingAdditional): void
    {
        $this->loadingAdditional = $loadingAdditional;
    }

    /**
     * @param string|null $unloadingAdditional
     */
    public function setUnloadingAdditional(?string $unloadingAdditional): void
    {
        $this->unloadingAdditional = $unloadingAdditional;
    }



    /**
     * @return bool
     */
    public function isPackingForGoodsCrate(): bool
    {
        return $this->packingForGoodsCrate;
    }

    /**
     * @param bool $packingForGoodsCrate
     */
    public function setPackingForGoodsCrate(bool $packingForGoodsCrate): void
    {
        $this->packingForGoodsCrate = $packingForGoodsCrate;
    }

    /**
     * @return bool
     */
    public function isPackingForGoodsCratePlus(): bool
    {
        return $this->packingForGoodsCratePlus;
    }

    /**
     * @param bool $packingForGoodsCratePlus
     */
    public function setPackingForGoodsCratePlus(bool $packingForGoodsCratePlus): void
    {
        $this->packingForGoodsCratePlus = $packingForGoodsCratePlus;
    }

    /**
     * @return bool
     */
    public function isPackingForGoodsBox(): bool
    {
        return $this->packingForGoodsBox;
    }

    /**
     * @param bool $packingForGoodsBox
     */
    public function setPackingForGoodsBox(bool $packingForGoodsBox): void
    {
        $this->packingForGoodsBox = $packingForGoodsBox;
    }

    /**
     * @return bool
     */
    public function isPackingForGoodsType(): bool
    {
        return $this->packingForGoodsType;
    }

    /**
     * @param bool $packingForGoodsType
     */
    public function setPackingForGoodsType(bool $packingForGoodsType): void
    {
        $this->packingForGoodsType = $packingForGoodsType;
    }

    /**
     * @return bool
     */
    public function isPackingForGoodsCrateWithBuble(): bool
    {
        return $this->packingForGoodsCrateWithBuble;
    }

    /**
     * @param bool $packingForGoodsCrateWithBuble
     */
    public function setPackingForGoodsCrateWithBuble(bool $packingForGoodsCrateWithBuble): void
    {
        $this->packingForGoodsCrateWithBuble = $packingForGoodsCrateWithBuble;
    }

    /**
     * @return bool
     */
    public function isPackingForGoodsCarGlass(): bool
    {
        return $this->packingForGoodsCarGlass;
    }

    /**
     * @param bool $packingForGoodsCarGlass
     */
    public function setPackingForGoodsCarGlass(bool $packingForGoodsCarGlass): void
    {
        $this->packingForGoodsCarGlass = $packingForGoodsCarGlass;
    }

    /**
     * @return bool
     */
    public function isPackingForGoodsCarParts(): bool
    {
        return $this->packingForGoodsCarParts;
    }

    /**
     * @param bool $packingForGoodsCarParts
     */
    public function setPackingForGoodsCarParts(bool $packingForGoodsCarParts): void
    {
        $this->packingForGoodsCarParts = $packingForGoodsCarParts;
    }

    /**
     * @return bool
     */
    public function isPackingForGoodsPalletWithBubble(): bool
    {
        return $this->packingForGoodsPalletWithBubble;
    }

    /**
     * @param bool $packingForGoodsPalletWithBubble
     */
    public function setPackingForGoodsPalletWithBubble(bool $packingForGoodsPalletWithBubble): void
    {
        $this->packingForGoodsPalletWithBubble = $packingForGoodsPalletWithBubble;
    }

    /**
     * @return bool
     */
    public function isPackingForGoodsBag(): bool
    {
        return $this->packingForGoodsBag;
    }

    /**
     * @param bool $packingForGoodsBag
     */
    public function setPackingForGoodsBag(bool $packingForGoodsBag): void
    {
        $this->packingForGoodsBag = $packingForGoodsBag;
    }

    /**
     * @return bool
     */
    public function isPackingForGoodsBubble(): bool
    {
        return $this->packingForGoodsBubble;
    }

    /**
     * @param bool $packingForGoodsBubble
     */
    public function setPackingForGoodsBubble(bool $packingForGoodsBubble): void
    {
        $this->packingForGoodsBubble = $packingForGoodsBubble;
    }

    /**
     * @return bool
     */
    public function isPackingForGoodsPallet(): bool
    {
        return $this->packingForGoodsPallet;
    }

    /**
     * @param bool $packingForGoodsPallet
     */
    public function setPackingForGoodsPallet(bool $packingForGoodsPallet): void
    {
        $this->packingForGoodsPallet = $packingForGoodsPallet;
    }

    /**
     * @return int
     */
    public function getPackingBoxCount(): int
    {
        return $this->packingBoxCount;
    }

    /**
     * @param int $packingBoxCount
     */
    public function setPackingBoxCount(int $packingBoxCount): void
    {
        $this->packingBoxCount = $packingBoxCount;
    }

    /**
     * @return int
     */
    public function getPackingCarGlass(): int
    {
        return $this->packingCarGlass;
    }

    /**
     * @param int $packingCarGlass
     */
    public function setPackingCarGlass(int $packingCarGlass): void
    {
        $this->packingCarGlass = $packingCarGlass;
    }

    /**
     * @return int
     */
    public function getPackingCarParts(): int
    {
        return $this->packingCarParts;
    }

    /**
     * @param int $packingCarParts
     */
    public function setPackingCarParts(int $packingCarParts): void
    {
        $this->packingCarParts = $packingCarParts;
    }

    /**
     * @return int
     */
    public function getPackingBagCount(): int
    {
        return $this->packingBagCount;
    }

    /**
     * @param int $packingBagCount
     */
    public function setPackingBagCount(int $packingBagCount): void
    {
        $this->packingBagCount = $packingBagCount;
    }


    /**
     * В первой итерации содержится минимальный пул проверок.
     * Требуется глубокий анализ использования.
     */

    public function validation(){

        $poolErrors = [];
       //логин выглядит как телефон? Валидируем его как телефон)
      //  $loginIsPhone = preg_match("/^(?:).[0-9]{10,12}+$/", $this->getLogin());
        $loginIsPhone = Kernel::validPhone($this->getLogin());
        $loginIsEmail = Kernel::validEmail($this->getLogin());

        if(empty($this->getAppkey())){

            $this->setIsErrors(true);
            $poolErrors = array_merge($poolErrors, ['Appkey is not set. ']) ;

        }

        //Логин не телефон и не почта
        if(!$loginIsPhone && !$loginIsEmail){

            $this->setIsErrors(true);
            $messageError = [Loc::getMessage("LOGIN_IS_NOT_PHONE_OR_EMAIL")];

            $poolErrors = array_merge($poolErrors, $messageError);

        }

        //Если схема доставки от терминала и терминал не указан.
        if(!$this->isGoodsLoading()  && $this->getTerminalId() < 0 ){
            $this->setIsErrors(true);

            $messageError = [Loc::getMessage("INPUT_TERMINAL")];
            $poolErrors = array_merge($poolErrors, $messageError);
        }

        if(!empty($this->getSenderContactEmail()) && !Kernel::validEmail($this->getSenderContactEmail())){
            $this->setIsErrors(true);
            $messageError = [Loc::getMessage("EMAIL_IS_NOT_VALID")];
            $poolErrors = array_merge($poolErrors, $messageError);
        }

        if(empty($this->getSenderContactPhone()) && !Kernel::validPhone($this->getSenderContactPhone())){

            $this->setIsErrors(true);
            $messageError = [Loc::getMessage("PHONE_IS_NOT_VALID")];
            $poolErrors = array_merge($messageError, $messageError);
        }


        //Если схема доставки от адреса и адрес не указан.
        if($this->isGoodsLoading() && empty($this->getLoadingAddress())){
            $this->setIsErrors(true);
            $messageError = [Loc::getMessage("ADDRESS_IS_NOT_VALID")];
            $poolErrors = array_merge($poolErrors, $messageError);
        }


        if($this->isErrors()){
            $this->setErrors($poolErrors);
        }
    }

    /**
     * @return bool
     */
    public function isErrors(): bool
    {
        return $this->isErrors;
    }

    /**
     * @param bool $isErrors
     */
    public function setIsErrors(bool $isErrors): void
    {
        $this->isErrors = $isErrors;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }





}