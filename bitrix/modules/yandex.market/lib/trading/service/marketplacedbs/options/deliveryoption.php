<?php

namespace Yandex\Market\Trading\Service\MarketplaceDbs\Options;

use Bitrix\Main;
use Yandex\Market;
use Yandex\Market\Trading\Entity as TradingEntity;
use Yandex\Market\Trading\Service as TradingService;

class DeliveryOption extends TradingService\Reference\Options\Fieldset
{
	use Market\Reference\Concerns\HasLang;
	use Market\Reference\Concerns\HasMessage;

	const SHIPMENT_DATE_BEHAVIOR_ORDER_DAY = 'orderDay';
	const SHIPMENT_DATE_BEHAVIOR_DELIVERY_DAY = 'deliveryDay';
	const SHIPMENT_DATE_BEHAVIOR_ORDER_OFFSET = 'orderOffset';
	const SHIPMENT_DATE_BEHAVIOR_DELIVERY_OFFSET = 'deliveryOffset';

	/** @var TradingService\MarketplaceDbs\Provider */
	protected $provider;

	/** @return int */
	public function getServiceId()
	{
		return (int)$this->getRequiredValue('ID');
	}

	/** @return string */
	public function getName()
	{
		return trim($this->getValue('NAME'));
	}

	/** @return string */
	public function getType()
	{
		return (string)$this->getRequiredValue('TYPE');
	}

	/** @return int|null */
	public function getDaysFrom()
	{
		return $this->getDaysValue('FROM');
 	}

	/** @return int|null */
	public function getDaysTo()
	{
		return $this->getDaysValue('TO');
 	}

 	protected function getDaysValue($key)
    {
	    $days = $this->getValue('DAYS');

	    return isset($days[$key]) && (string)$days[$key] !== '' ? (int)$days[$key] : null;
    }

	/** @return string[]|null */
	public function getOutlets()
    {
    	$values = $this->getValue('OUTLET');

    	return $values !== null ? (array)$values : null;
    }

	/** @return ScheduleOptions */
	public function getSchedule()
    {
    	return $this->getFieldsetCollection('SCHEDULE');
    }

	/** @return string|null */
	public function getShipmentDelay()
    {
	    return $this->getValue('SHIPMENT_DELAY');
    }

	/** @return bool */
	public function increasePeriodOnWeekend()
    {
    	return (string)$this->getValue('INCREASE_PERIOD_ON_WEEKEND') === Market\Ui\UserField\BooleanType::VALUE_Y;
    }

	/** @return HolidayOption */
	public function getHoliday()
	{
		return $this->getFieldset('HOLIDAY');
	}

	/** @return string */
	public function getShipmentDateBehavior()
	{
		return $this->getValue('SHIPMENT_DATE_BEHAVIOR', static::SHIPMENT_DATE_BEHAVIOR_DELIVERY_DAY); // default used if settings filled for old version
	}

	/** @return bool */
	public function getShipmentDateDirection()
	{
		return !in_array($this->getShipmentDateBehavior(), [
			static::SHIPMENT_DATE_BEHAVIOR_DELIVERY_DAY,
			static::SHIPMENT_DATE_BEHAVIOR_DELIVERY_OFFSET,
		], true);
	}

	/** @return int|null */
	public function getShipmentDateOffset()
	{
		switch ($this->getShipmentDateBehavior())
		{
			case static::SHIPMENT_DATE_BEHAVIOR_DELIVERY_DAY:
			case static::SHIPMENT_DATE_BEHAVIOR_ORDER_DAY:
				$result = 0;
			break;

			case static::SHIPMENT_DATE_BEHAVIOR_DELIVERY_OFFSET:
			case static::SHIPMENT_DATE_BEHAVIOR_ORDER_OFFSET:
				$value = $this->getValue('SHIPMENT_DATE_OFFSET');
				$value = Market\Data\Number::normalize($value);

				$result = $value !== null ? (int)abs($value) : null;
			break;

			default:
				$result = null;
			break;
		}

		return $result;
	}

	public function getFieldDescription(TradingEntity\Reference\Environment $environment, $siteId)
	{
		return parent::getFieldDescription($environment, $siteId) + [
			'SETTINGS' => [
				'SUMMARY' => self::getMessage('SUMMARY', null, '#TYPE# &laquo;#ID#&raquo;, #DAYS#'),
				'LAYOUT' => 'summary',
				'MODAL_WIDTH' => 600,
				'MODAL_HEIGHT' => 450,
			],
		];
	}

	public function getFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		return
			$this->getSelfFields($environment, $siteId)
			+ $this->getHolidayFields($environment, $siteId);
	}

	protected function getSelfFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		return [
			'ID' => [
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'NAME' => self::getMessage('ID'),
				'VALUES' => $this->getDeliveryEnum($environment, $siteId),
				'SETTINGS' => [
					'STYLE' => 'max-width: 220px;',
				],
			],
			'NAME' => [
				'TYPE' => 'string',
				'NAME' => self::getMessage('NAME'),
				'SETTINGS' => [
					'MAX_LENGTH' => 50,
				],
			],
			'TYPE' => [
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'NAME' => self::getMessage('TYPE'),
				'HELP_MESSAGE' => self::getMessage('TYPE_HELP'),
				'VALUES' => $this->provider->getDelivery()->getTypeEnum(),
			],
			'DAYS' => [
				'TYPE' => 'numberRange',
				'NAME' => self::getMessage('DAYS'),
				'HELP_MESSAGE' => self::getMessage('DAYS_HELP'),
				'SETTINGS' => [
					'SUMMARY' => '#FROM#-#TO#',
					'UNIT' => array_filter([
						self::getMessage('DAYS_UNIT_1', null, ''),
						self::getMessage('DAYS_UNIT_2', null, ''),
						self::getMessage('DAYS_UNIT_5', null, ''),
					]),
				],
			],
			'OUTLET' => [
				'TYPE' => 'tradingOutlet',
				'NAME' => self::getMessage('OUTLET'),
				'MULTIPLE' => 'Y',
				'DEPEND' => [
					'TYPE' => [
						'RULE' => 'ANY',
						'VALUE' => TradingService\MarketplaceDbs\Delivery::TYPE_PICKUP,
					],
				],
				'SETTINGS' => [
					'SERVICE' => $this->provider->getCode(),
				],
			],
			'SHIPMENT_DATE_BEHAVIOR' => [
				'TYPE' => 'enumeration',
				'NAME' => self::getMessage('SHIPMENT_DATE_BEHAVIOR'),
				'HELP_MESSAGE' => self::getMessage('SHIPMENT_DATE_BEHAVIOR_HELP'),
				'VALUES' => [
					[
						'ID' => static::SHIPMENT_DATE_BEHAVIOR_DELIVERY_DAY,
						'VALUE' => self::getMessage('SHIPMENT_DATE_BEHAVIOR_OPTION_DELIVERY_DAY'),
					],
					[
						'ID' => static::SHIPMENT_DATE_BEHAVIOR_ORDER_DAY,
						'VALUE' => self::getMessage('SHIPMENT_DATE_BEHAVIOR_OPTION_ORDER_DAY'),
					],
					[
						'ID' => static::SHIPMENT_DATE_BEHAVIOR_DELIVERY_OFFSET,
						'VALUE' => self::getMessage('SHIPMENT_DATE_BEHAVIOR_OPTION_DELIVERY_OFFSET'),
					],
					[
						'ID' => static::SHIPMENT_DATE_BEHAVIOR_ORDER_OFFSET,
						'VALUE' => self::getMessage('SHIPMENT_DATE_BEHAVIOR_OPTION_ORDER_OFFSET'),
					],
				],
				'SETTINGS' => [
					'ALLOW_NO_VALUE' => 'N',
				],
			],
			'SHIPMENT_DATE_OFFSET' => [
				'TYPE' => 'number',
				'NAME' => self::getMessage('SHIPMENT_DATE_OFFSET'),
				'HELP_MESSAGE' => self::getMessage('SHIPMENT_DATE_OFFSET_HELP'),
				'DEPEND' => [
					'SHIPMENT_DATE_BEHAVIOR' => [
						'RULE' => 'ANY',
						'VALUE' => [
							static::SHIPMENT_DATE_BEHAVIOR_DELIVERY_OFFSET,
							static::SHIPMENT_DATE_BEHAVIOR_ORDER_OFFSET,
						],
					],
				],
			],
			'SCHEDULE' => $this->getSchedule()->getFieldDescription($environment, $siteId) + [
				'TYPE' => 'fieldset',
				'NAME' => self::getMessage('SCHEDULE'),
				'GROUP' => self::getMessage('SCHEDULE_GROUP'),
				'HELP_MESSAGE' => self::getMessage('SCHEDULE_HELP'),
				'DEPEND' => [
					'TYPE' => [
						'RULE' => 'ANY',
						'VALUE' => [
							TradingService\MarketplaceDbs\Delivery::TYPE_DELIVERY,
							TradingService\MarketplaceDbs\Delivery::TYPE_PICKUP,
						],
					],
				],
			],
			'SHIPMENT_DELAY' => [
				'TYPE' => 'time',
				'NAME' => self::getMessage('SHIPMENT_DELAY'),
				'HELP_MESSAGE' => self::getMessage('SHIPMENT_DELAY_HELP'),
				'DEPEND' => [
					'TYPE' => [
						'RULE' => 'ANY',
						'VALUE' => [
							TradingService\MarketplaceDbs\Delivery::TYPE_DELIVERY,
							TradingService\MarketplaceDbs\Delivery::TYPE_PICKUP,
						],
					],
				],
			],
			'INCREASE_PERIOD_ON_WEEKEND' => [
				'TYPE' => 'boolean',
				'NAME' => self::getMessage('INCREASE_PERIOD_ON_WEEKEND'),
				'HELP_MESSAGE' => self::getMessage('INCREASE_PERIOD_ON_WEEKEND_HELP'),
				'DEPEND' => [
					'TYPE' => [
						'RULE' => 'ANY',
						'VALUE' => [
							TradingService\MarketplaceDbs\Delivery::TYPE_DELIVERY,
							TradingService\MarketplaceDbs\Delivery::TYPE_PICKUP,
						],
					],
				],
			],
		];
	}

	protected function getHolidayFields(TradingEntity\Reference\Environment $environment, $siteId)
	{
		$result = [];
		$defaults = [
			'GROUP' => self::getMessage('HOLIDAY_GROUP'),
			'DEPEND' => [
				'TYPE' => [
					'RULE' => 'ANY',
					'VALUE' => [
						TradingService\MarketplaceDbs\Delivery::TYPE_DELIVERY,
						TradingService\MarketplaceDbs\Delivery::TYPE_PICKUP,
					],
				],
			],
		];

		foreach ($this->getHoliday()->getFields($environment, $siteId) as $name => $field)
		{
			$key = sprintf('HOLIDAY[%s]', $name);

			$result[$key] = $field + $defaults;
		}

		return $result;
	}

	protected function getDeliveryEnum(TradingEntity\Reference\Environment $environment, $siteId)
	{
		$delivery = $environment->getDelivery();

		return array_filter($delivery->getEnum($siteId), static function($option) {
			return $option['TYPE'] !== Market\Data\Trading\Delivery::EMPTY_DELIVERY;
		});
	}

	protected function getFieldsetCollectionMap()
	{
		return [
			'SCHEDULE' => ScheduleOptions::class,
		];
	}

	protected function getFieldsetMap()
	{
		return [
			'HOLIDAY' => HolidayOption::class,
		];
	}
}
