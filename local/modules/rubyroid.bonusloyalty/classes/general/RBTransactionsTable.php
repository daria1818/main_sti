<?php
namespace Rubyroid\Loyality;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\StringField,
	Bitrix\Main\ORM\Fields\TextField,
	Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class TransactionsMoreTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ORDER_ID string(255) mandatory
 * <li> COIN text mandatory
 * <li> BALANCE text mandatory
 * </ul>
 *
 * @package Bitrix\History
 **/

class RBTransactionsTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'rb_history_transactions_more';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => ""
				]
			),
			new StringField(
				'ORDER_ID',
				[
					'required' => false,
					'validation' => [__CLASS__, 'validateOrderId'],
					'title' => ""
				]
			),
			new StringField(
				'TYPE_OPERATION',
				[
					'required' => true,
					'title' => ""
				]
			),
			new StringField(
				'TYPE_EVENT',
				[
					'required' => true,
					'title' => ""
				]
			),
			new TextField(
				'COIN',
				[
					'required' => true,
					'title' => ""
				]
			),
			new TextField(
				'BALANCE',
				[
					'required' => false,
					'title' => ""
				]
			),
			new TextField(
				'AFTER_BALANCE',
				[
					'required' => false,
					'title' => ""
				]
			),
			new IntegerField(
				'USER_ID',
				[
					'required' => false,
					'title' => ""
				]
			),
			new IntegerField(
				'EVENT_DATE',
				[
					'required' => false,
					'title' => ""
				]
			),
		];
	}

	/**
	 * Returns validators for ORDER_ID field.
	 *
	 * @return array
	 */
	public static function validateOrderId()
	{
		return [
			new LengthValidator(null, 255),
		];
	}
}