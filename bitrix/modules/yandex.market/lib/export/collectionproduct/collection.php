<?php
namespace Yandex\Market\Export\CollectionProduct;

use Yandex\Market;

class Collection extends Market\Reference\Storage\Collection
{
    public static function getItemReference()
    {
        return Model::class;
    }
}