<?php

namespace Undercity;

use Undercity\Base\Image as BaseImage;
use Propel\Runtime\Connection\ConnectionInterface;
/**
 * Skeleton subclass for representing a row from the 'images' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class Image extends BaseImage
{
    public function removeResources() {
        if (file_exists(RESOURCE_PATH . parent::getSource())) {
            unlink(RESOURCE_PATH . parent::getSource());
        }

        if (file_exists(RESOURCE_PATH . parent::getSourceThumb())) {
            unlink(RESOURCE_PATH . parent::getSourceThumb());
        }
    }
    
    public function delete(ConnectionInterface $con = null) {
        $this->removeResources();
        parent::delete($con);
    }
}