<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Seo\SitemapRuntime;
use Sotbit\Seometa\SitemapTable;
use Sotbit\Seometa\ConditionTable;
use Sotbit\Seometa\SeometaUrlTable;
use Bitrix\Main\Config\Option;
use Sotbit\Seometa\Helper\XMLMethods;

Loader::includeModule('iblock');

class CSeoMetaSitemapLight
{
    private $seometaSitemapFile = 'sitemap_seometa_';
    private $maxCountLinksPerFile = '';
    private $requestData = [];

    public function __construct(
    ) {
    }

    private function setRequestData(
        $data
    ) {
        if (!empty($data['ID'])) {
            $this->requestData['ID'] = intval($data['ID']);
            $this->requestData['limit'] = intval($data['limit']);
            $this->requestData['offset'] = intval($data['offset']);
            $this->requestData['sitemap_index'] = intval($data['sitemap_index']);
            $this->requestData['count_chpu'] = intval($data['count_chpu']);
            $this->requestData['count_link_writed'] = intval($data['count_link_writed']);
        } else {
            if (!empty($data['data'])) {
                $this->requestData = Json::decode($data['data']);
            }
        }
    }

    function generateSitemap(
        $arrLinks,
        $siteDomain
    ) {
        $this->setRequestData($_REQUEST);

        $sitePaths = self::pathMainSitemap($this->requestData['ID']);
        if ($sitePaths['TYPE'] == 'ERROR') {
            return $sitePaths;
        }

        if ($this->requestData['count_link_writed'] >= $this->maxCountLinksPerFile) {
            $this->requestData['sitemap_index']++;
        }

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $this->seometaSitemapFile . $this->requestData['ID'] . '_' . $this->requestData['sitemap_index'] . '.xml')) {
            $xml = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $this->seometaSitemapFile . $this->requestData['ID'] . '_' . $this->requestData['sitemap_index'] . '.xml');
            $xmlCurrentSize = filesize($_SERVER['DOCUMENT_ROOT'] . '/' . $this->seometaSitemapFile . $this->requestData['ID'] . '_' . $this->requestData['sitemap_index'] . '.xml');
            $data = (new XMLMethods)->xml2ary($xml);
        }

        $arSitemap = self::getSettings($this->requestData['ID']);

        foreach ($arrLinks as $link) {
            $conditionParams = ConditionTable::getConditionById($link['CONDITION_ID']);
            SeometaUrlTable::update($link['ID'],
                ['IN_SITEMAP' => 'Y']);

            if (!isset($conditionParams['PRIORITY'])) {
                $conditionParams['PRIORITY'] = '0.0';
            } else {
                $conditionParams['PRIORITY'] = number_format($conditionParams['PRIORITY'],
                    1);
            }

            $item[] = [
                '_c' => [
                    'loc' => [
                        '_v' => $arSitemap['SETTINGS']['PROTO'] . $siteDomain . (!empty($link['NEW_URL']) ? $link['NEW_URL'] :
                                $link['REAL_URL'])
                    ],
                    'lastmod' => [
                        '_v' => $link['DATE_CHANGE']->format("Y-m-d\Th:m:sP")
                    ],
                    'changefreq' => [
                        '_v' => $conditionParams['CHANGEFREQ'] ?: 'always'
                    ],
                    'priority' => [
                        '_v' => $conditionParams['PRIORITY']
                    ]
                ]
            ];
        }

        if (!empty($data) &&
            (intval($xmlCurrentSize) + intval((new XMLMethods)->ary2xml($item)) / 1000) / 1000 >=
            intval(Option::get(CSeoMeta::MODULE_ID,
                'SEOMETA_SITEMAP_FILE_SIZE'))
        ) {
            unset($data);
            $this->requestData['sitemap_index']++;
        }

        if (!$data || $_REQUEST['action'] != 'sitemap_in_progress') {
            $data = (new XMLMethods)->createXml($_SERVER['DOCUMENT_ROOT'] . '/' . $this->seometaSitemapFile . $this->requestData['ID'] . '_' . $this->requestData['sitemap_index'] . '.xml');
            if (!empty($data['TYPE'])) {
                $result = $data;
            }
        }

        (new XMLMethods)->ins2ary($data['urlset']['_c']['url'],
            $item,
            count($data['urlset']['_c']['url']));

        if (empty($this->requestData['count_chpu'])) {
            $this->requestData['count_chpu'] = self::getCountChpuUrls($this->requestData['ID']);
        }

        $curPercent = intdiv(100 * (count($item) + ($this->requestData['limit'] * $this->requestData['offset'])),
            $this->requestData['count_chpu']);

        $result['limit'] = $this->requestData['limit'] ? $this->requestData['limit'] : 0;
        $result['offset'] = $this->requestData['offset'] ? intval($this->requestData['offset']) + 1 : 1;
        $result['count_link_writed'] = $this->requestData['count_link_writed'] + count($item);
        $result['sitemap_index'] = $this->requestData['sitemap_index'];
        $result['ID'] = $this->requestData['ID'];

        $xmlData = (new XMLMethods)->ary2xml($data);
        $writeStatus = (new XMLMethods)->writeSiteMap($_SERVER['DOCUMENT_ROOT'] . '/' . $this->seometaSitemapFile . $this->requestData['ID'] . '_' . $this->requestData['sitemap_index'] . '.xml',
            $xmlData);

        if (isset($writeStatus['TYPE'])) {
            $result = $writeStatus;
        }

        if (!empty($data['TYPE'])) {
            $result = $data;
        }

        $result['progressbar'] = SitemapRuntime::showProgress(Loc::getMessage('SEO_META_SITEMAP_GENERATING'),
            Loc::getMessage('SEO_META_SITEMAP_RUN_TITLE'),
            $curPercent);
        return \Bitrix\Main\Web\Json::encode($result,
            JSON_INVALID_UTF8_IGNORE | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }

    function getCountChpuUrls(
        $ID
    ) {
        $arSitemap = self::getSettings($ID);

        $filter = [];
        if ($arSitemap['SETTINGS']['EXCLUDE_NOT_SEF'] == 'Y') {
            $filter = ['ACTIVE' => 'Y'];
        }

        $count = SeometaUrlTable::getList(
            array(
                'select' => array('CNT'),
                'filter' => $filter,
                'runtime' => array(
                    new Bitrix\Main\Entity\ExpressionField('CNT',
                        'COUNT(*)')
                )
            )
        )->fetch();

        if (intval($count) || $count == 0) {
            $count = $count['CNT'];
        }
        return $count;
    }

    function generateSitemapFinish(
        $ID,
        $index
    ) {
        $sitePaths = self::pathMainSitemap($ID);

        if (isset($sitePaths['abs_path'])) {
            $xml = file_get_contents($sitePaths['abs_path'] . $sitePaths['file_name']);
            $data = (new XMLMethods)->xml2ary($xml);

            (new XMLMethods)->delSeometaFromMainSitemap($data['sitemapindex']['_c']['sitemap']);

            $item = (new XMLMethods)->seometaMainSitemapFiles($index,
                $ID,
                $sitePaths['url']);

            if (is_array($item) && !empty($item)) {
                (new XMLMethods)->ins2ary($data['sitemapindex']['_c']['sitemap'],
                    $item,
                    count($data['sitemapindex']['_c']['sitemap']));

                $xmlData = (new XMLMethods)->ary2xml($data);

                $writeStatus = (new XMLMethods)->writeSiteMap($sitePaths['abs_path'] . $sitePaths['file_name'],
                    $xmlData);
                if (isset($writeStatus['TYPE'])) {
                    $result = $writeStatus;
                }
            }

            $result['progressbar'] = SitemapRuntime::showProgress(Loc::getMessage('SEO_META_SITEMAP_FINISH'),
                Loc::getMessage('SEO_META_SITEMAP_RUN_TITLE'),
                100);
        } else {
            $result = $sitePaths;
        }

        SitemapTable::update($ID,
            array('DATE_RUN' => new Bitrix\Main\Type\DateTime()));

        $result['STATUS'] = 'finish';
        return \Bitrix\Main\Web\Json::encode($result);
    }

    public function pathMainSitemap(
        $ID
    ) {
        $arSitemap = $this->getSettings($ID);

        $arSite = CSite::GetById($arSitemap['SITE_ID'])->Fetch();
        $result['url'] = $arSitemap['SETTINGS']['PROTO'] . $arSitemap['SETTINGS']['DOMAIN'] . $arSite['DIR'];
        $result['abs_path'] = $arSite['ABS_DOC_ROOT'] . $arSite['DIR'];
        $result['domain_dir'] = $arSitemap['SETTINGS']['DOMAIN'] . mb_substr($arSite['DIR'], 0, -1);
        $result['file_name'] = $arSitemap['SETTINGS']['FILENAME_INDEX'];
        $result['site_id'] = $arSitemap['SITE_ID'];

        if (file_exists($result['abs_path'] . $arSitemap['SETTINGS']['FILENAME_INDEX'])) {
            return $result;
        } else {
            return [
                'TYPE' => 'ERROR',
                'MSG' => $arSitemap['SETTINGS']['FILENAME_INDEX'] . ' not found!'
            ];
        }
    }

    private function getSettings(
        $ID
    ) {
        $arSitemap = SitemapTable::getById($ID)->fetch();
        $this->maxCountLinksPerFile = Option::get(CSeoMeta::MODULE_ID,
            'SEOMETA_SITEMAP_COUNT_LINKS',
            '50000',
            $arSitemap['SITE_ID']);
        $arSitemap['SETTINGS'] = unserialize($arSitemap['SETTINGS']);

        if (isset($arSitemap['SETTINGS']['PROTO']) && $arSitemap['SETTINGS']['PROTO'] == 1) {
            $arSitemap['SETTINGS']['PROTO'] = 'https://';
        } elseif (isset($arSitemap['SETTINGS']['PROTO']) && $arSitemap['SETTINGS']['PROTO'] == 0) {
            $arSitemap['SETTINGS']['PROTO'] = 'http://';
        }

        return $arSitemap;
    }

    public function deleteOldSeometaSitemaps(
        $dir
    ) {
        $result = false;
        if (is_dir($dir)) {
            if (($res = opendir($dir))) {
                while (($item = readdir($res))) {
                    if ($item == '..' || $item == '.') {
                        continue;
                    }
                    if (mb_strpos($item,
                            $this->seometaSitemapFile) !== false) {
                        if (is_file($dir . $item)) {
                            unlink($dir . $item);
                        }
                    }
                }
                closedir($res);
                $result = true;
            }
        }

        return $result;
    }
}
