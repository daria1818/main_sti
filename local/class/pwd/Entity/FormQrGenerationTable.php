<?php

declare(strict_types=1);

namespace Pwd\Entity;

use spaceonfire\BitrixTools\ORM\BaseHighLoadBlockDataManager;

/**
 * Class FormQrGenerationTable
 *
 * Fields:
 * - ID
 * - UF_ID
 * - UF_DESC
 * - UF_TITLE
 * - UF_DATE
 * - UF_HASH
 * - UF_PARAMS
 * - UF_RESPONSIBLE
 * - UF_QR
 *
 * @package Pwd\Entity
 */
final class FormQrGenerationTable extends BaseHighLoadBlockDataManager
{
	/**
	 * @inheritDoc
	 */
	public static function getHLId()
	{
		return 'FormQrGeneration';
	}
}
