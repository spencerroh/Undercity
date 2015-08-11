<?php

namespace Undercity;

use Undercity\Base\Store as BaseStore;
use Propel\Runtime\Map\TableMap;

/**
 * Skeleton subclass for representing a row from the 'stores' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class Store extends BaseStore
{
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false) {
        $arr = parent::toArray($keyType, $includeLazyLoadColumns, $alreadyDumpedObjects, $includeForeignObjects);

        $images = array();
        foreach ($this->getStoreImages() as $storeImage) {
            array_push($images, $storeImage->getImageId());
        }

        $arr['Images'] = $images;
        return $arr;
    }
}
