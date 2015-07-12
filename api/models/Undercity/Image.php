<?php

namespace Undercity;

use Propel\Runtime\Connection\ConnectionInterface;
use Undercity\Base\Image as BaseImage;

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
    public function delete(ConnectionInterface $con = null) {
        $fileName = RESOURCE_PATH.parent::getSource();

        if (file_exists($fileName))
        {
            unlink($fileName);
        }

        parent::delete($con);
    }
}
