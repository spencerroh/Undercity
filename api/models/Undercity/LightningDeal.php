<?php

namespace Undercity;

use Undercity\Base\LightningDeal as BaseLightningDeal;
use Propel\Runtime\Map\TableMap;

/**
 * Skeleton subclass for representing a row from the 'lightning_deals' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class LightningDeal extends BaseLightningDeal
{
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false) {
        $arr = parent::toArray($keyType, $includeLazyLoadColumns, $alreadyDumpedObjects, $includeForeignObjects);

        $images = array();
        foreach ($this->getLightningDealImages() as $image) {
            array_push($images, $image->getImageId());
        }

        $arr['Images'] = $images;
        $arr['Replies'] = $this->getLightningDealReplies()->toArray();
        return $arr;
    }
}
