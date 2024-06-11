<?php

namespace Sale\Handlers\PaySystem;


use Bitrix\Main\Request;
use Bitrix\Sale\Order;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PriceMaths;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Result;
use Bitrix\Sale\Internals\UserBudgetPool;
use Bitrix\Main\Entity\EntityError;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\ResultError;

Loc::loadMessages(__FILE__);

class InnerBonusHandler extends PaySystem\BaseServiceHandler implements PaySystem\IRefund
{
	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		$result = new PaySystem\ServiceResult();

		/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
		$paymentCollection = $payment->getCollection();

		if ($paymentCollection)
		{
			/** @var \Bitrix\Sale\Order $order */
			$order = $paymentCollection->getOrder();
			if ($order)
			{
				$res = $payment->setPaid('Y');
				if ($res->isSuccess())
				{
					$res = $order->save();
					if ($res)
						$result->addErrors($res->getErrors());
				}
				else
				{
					$result->addErrors($res->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return ['RUB'];
	}

	public function refund(Payment $payment, $refundableSum)
	{
		return false;
	}
}