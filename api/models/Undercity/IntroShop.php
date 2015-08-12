<?php

namespace Undercity;

use Undercity\Base\IntroShop as BaseIntroShop;
use Propel\Runtime\Map\TableMap;

/**
 * Skeleton subclass for representing a row from the 'intro_shops' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class IntroShop extends BaseIntroShop
{
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false) {
        $arr = parent::toArray($keyType, $includeLazyLoadColumns, $alreadyDumpedObjects, $includeForeignObjects);

        $images = array();
        foreach ($this->getIntroShopImages() as $storeImage) {
            array_push($images, $storeImage->getImageId());
        }

        $arr['Images'] = $images;
        $arr['Replies'] = $this->getIntroShopReplies()->toArray();
        return $arr;
    }
}
