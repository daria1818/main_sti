<?php /** @noinspection AutoloadingIssuesInspection */

declare(strict_types=1);

namespace Pwd\Components;

use Bitrix\Main\Engine\Contract\Controllerable;
use Narrowspark\HttpStatus\Exception\UnauthorizedException;
use Pwd\Entity\FormGenerationTable;
use spaceonfire\BitrixTools\CacheMap\UserGroupCacheMap;
use spaceonfire\BitrixTools\Components\BaseComponent;
use spaceonfire\BitrixTools\Components\Property\ComponentPropertiesTrait;
use Bitrix\Main\Type\DateTime;
use Throwable;
use Latte\Engine;

/**
 * Class FormGenerationComponent
 * @package Pwd\Components
 * @property-read bool $isModerator
 */
class FormGenerationComponent extends BaseComponent implements Controllerable
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
        $this->isModerator = \CSite::InGroup([$eventAdminGroup]);
    }

    /**
     * @inheritDoc
     */
    protected function executeMain(): void
    {
        $this->setResultCacheKeys([]);
    }

    /**
     * @inheritDoc
     */
    public function configureActions(): array
    {
        return [
            'add' => [
                'prefilters' => [
                    new \Bitrix\Main\Engine\ActionFilter\Authentication(),
                    new \Bitrix\Main\Engine\ActionFilter\HttpMethod([
                        \Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_POST
                    ])
                ],
            ],
        ];
    }

    public function addFile($dir, $html)
    {
        $page = '';
        if (@file_put_contents(explode('local', __DIR__)[0] . '/' . $dir . '.php', $html)) $page = '/' . $dir . '.php';
        return $page;
    }

    public function addAction()
    {
        try {
            $this->includeModules();
            $this->init();
            global $USER;
        } catch (Throwable $e) {
            return [];
        }

        $request = $this->request->getPostList()->toArray();
        $title = $request['title'];
        $html = $request['html'];
        $idCamp = $request['id'];
        $titleTag = $request['title'] ?? "stionline.ru - интернет магазин для стоматологов и зубных техников";

        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $addr = 'form-'.substr(str_shuffle($permitted_chars), 0, 5);
        $date = new DateTime();

        $form = '<?php
                require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
                $APPLICATION->SetPageProperty("keywords", "интернет-магазин, стоматологические материалы, заказать, купить");
                $APPLICATION->SetPageProperty("title", "' . $titleTag . '");
                $APPLICATION->SetPageProperty("viewed_show", "Y");
                $APPLICATION->SetTitle("' . $titleTag . '");
                ?>
                <!--noindex-->
                <div class="maxwidth-theme">
                    <div id="permalink" class="form inline" style="padding-top: 0 !important;">
                        <div>
                            ' . htmlspecialchars_decode($html) . '
                        </div>
                        <form action="https://sticoin.ru:4000/parse" method="POST">
                            <input type="hidden" name="campaign_uid" value="' . $idCamp . '">
                            <div class="form_body">
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-control">
                                            <label><span>Ссылка на пост для анализа текста&nbsp;<span class="star">*</span></span></label>
                                            <input type="text" required class="inputtext" name="permalink" value="" data-sid="LINK" aria-required="true"></div>
                                        <div class="form-control">
                                            <label><span>E-mail</span></label>
                                            <input type="email" required placeholder="mail@domen.com" class="inputtext" name="email" value="" data-sid="EMAIL" aria-required="true"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form_footer">
                                <input type="submit" class="btn btn-default" value="Отправить">
                            </div>
                        </form>
                    </div>
                </div>
                <!--/noindex-->
                <?php
                require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
                ';

        $page = $this->addFile(\mb_strtolower($addr), $form);

        if (strlen($page)) {
            $url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
            $url = explode('?', $url);
            $url = $url[0];
            $file = $page;
            $page = $url . $page;
            FormGenerationTable::add([
                'UF_TITLE' => $title,
                'UF_DESC' => $html,
                'UF_ID' => $idCamp,
                'UF_DATE' => $date,
                'UF_PAGE' => $page
            ]);
            @file_put_contents(explode('local', __DIR__)[0].'/'.'.gitignore', PHP_EOL.$file, FILE_APPEND);
            @file_put_contents(explode('local', __DIR__)[0].'/'.'robots.txt', PHP_EOL.'Disallow: '.$file, FILE_APPEND);
        }
        return $page ?? '';
    }
}
