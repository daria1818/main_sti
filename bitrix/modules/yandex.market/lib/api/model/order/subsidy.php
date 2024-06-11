<?php

namespace Yandex\Market\Api\Model\Order;

use Yandex\Market;

class Subsidy extends Market\Api\Model\Cart\Item
{
	public function getAmount()
	{
		return (float)$this->getField('amount');
	}
}