<?php

namespace Yandex\Market\Api\Model\Order;

use Yandex\Market;

/** @property Subsidy[] $collection */
class SubsidyCollection extends Market\Api\Reference\Collection
{
	public static function getItemReference()
	{
		return Subsidy::class;
	}

	public function getSum()
	{
		$result = 0;

		foreach ($this->collection as $item)
		{
			$result += $item->getAmount();
		}

		return $result;
	}
}