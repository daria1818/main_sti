<?php /** @noinspection AutoloadingIssuesInspection */

declare(strict_types=1);

namespace Pwd\Components;

use Narrowspark\HttpStatus\Exception\UnauthorizedException;
use Pwd\Entity\FormQrGenerationTable;
use spaceonfire\BitrixTools\CacheMap\UserGroupCacheMap;
use spaceonfire\BitrixTools\Components\BaseComponent;
use spaceonfire\BitrixTools\Components\Property\ComponentPropertiesTrait;
use Throwable;
use Pwd\Helpers\UserHelper;

/**
 * Class QrGenerationListComponent
 * @package Pwd\Components
 * @property-read bool $isModerator
 * @property-read int $creator
 * @property-read int $contactId
 * @property-read int $task
 * @property-read bool $generationMode
 * @property-read array $rows
 * @property-read array $params
 */
class QrGenerationListComponent extends BaseComponent
{
    use ComponentPropertiesTrait;

    /**
     * @var string[]
     */
    protected $needModules = ['iblock'];

    /**
     * @var bool
     */
    public $isModerator;

    public $creator;

    public $rows;

    /**
     * @inheritDoc
     */
    protected function getParamsTypes(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function canShowExceptionMessage(Throwable $exception): bool
    {
        if ($exception instanceof UnauthorizedException) {
            return true;
        }

        return parent::canShowExceptionMessage($exception);
    }

    /**
     * @inheritDoc
     */
    protected function init(): void
    {
        parent::init();
    }

    /**
     * @inheritDoc
     */
    protected function executeProlog(): void
    {
        $eventAdminGroup = UserGroupCacheMap::getId('MODERATOR_OF_FORM');
        $this->isModerator = UserHelper::isModeratorOfForms();
        global $USER;
        $this->creator = $USER->GetID();
    }

    /**
     * @inheritDoc
     */
    protected function executeMain(): void
    {
        $this->getQrList();
        $this->setResultCacheKeys([]);
    }

    public function getQrList()
    {
        try {
            $this->includeModules();
            $this->init();
            global $USER;
        } catch (Throwable $e) {
            return [];
        }

        if($this->creator && $this->isModerator){
            $this->rows = FormQrGenerationTable::getList([
                'select' => ['*'],
                'filter' => [
                    'UF_CREATOR' => $this->creator
                ]
            ])->fetchAll();
        }
    }
}
