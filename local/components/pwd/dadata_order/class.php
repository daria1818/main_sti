<?php /** @noinspection AutoloadingIssuesInspection */

declare(strict_types=1);

namespace Pwd\Components;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Contract\Controllerable;
use Narrowspark\HttpStatus\Exception\UnauthorizedException;
use spaceonfire\BitrixTools\Components\BaseComponent;
use spaceonfire\BitrixTools\Components\Property\ComponentPropertiesTrait;
use Throwable;
use Pwd\Tools\DaDataTools;

/**
 * Class DaDataComponent
 * @package Pwd\Components
 * @property-read bool $isAuth
 * @property-read bool $client
 * @property-read float $apPercent
 */
class DaDataComponent extends BaseComponent implements Controllerable
{
    use ComponentPropertiesTrait;

    /**
     * @var string[]
     */
    protected $needModules = ['iblock'];

    public $isAuth;


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
    public function init(): void
    {
        parent::init();

        global $USER;
        $this->isAuth = $USER->IsAuthorized();
    }

    /**
     * @inheritDoc
     */
    protected function executeProlog(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function configureActions(): array
    {
        return [
            'getByInn' => [
                '-prefilters' => [
                    ActionFilter\Authentication::class
                ],
            ],
            'getByPostalCode' => [
                '-prefilters' => [
                    ActionFilter\Authentication::class
                ],
            ],
            'getByAddress' => [
                '-prefilters' => [
                    ActionFilter\Authentication::class
                ],
            ],
        ];
    }

    public function getByInnAction(): array
    {
        try {
            $this->includeModules();
            $this->init();
        } catch (Throwable $e) {
            return [];
        }
        $request = $this->request->getPostList()->toArray();
        $dadata = new DaDataTools;
        $info = $dadata->getByInn($request['inn']);
        return ['info' => $info, 'request' => $dadata];
    }

    public function getByPostalCodeAction(): array
    {
        try {
            $this->includeModules();
            $this->init();
        } catch (Throwable $e) {
            return [];
        }
        $request = $this->request->getPostList()->toArray();
        $dadata = new DaDataTools;
        $info = $dadata->getByPostalCode($request['postalCode']);
        return ['info' => $info, 'request' => $dadata];
    }

    public function getByAddressAction(): array
    {
        try {
            $this->includeModules();
            $this->init();
        } catch (Throwable $e) {
            return [];
        }
        $request = $this->request->getPostList()->toArray();
        $dadata = new DaDataTools;
        $info = $dadata->getByAddress($request['address']);
        return ['info' => $info, 'request' => $dadata];
    }

}
