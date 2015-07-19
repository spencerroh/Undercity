<?php

namespace Undercity;

use Undercity\Base\Sale as BaseSale;

/**
 * Skeleton subclass for representing a row from the 'sales' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class Sale extends BaseSale
{
    public function getEventFrom($format = NULL)
    {
        if ($format === null)
        {
            return parent::getEventFrom('Y-m-d');
        }
        else
        {
            return parent::getEventFrom($format);
        }
    }

    public function getEventTo($format = NULL)
    {
        if ($format === null)
        {
            return parent::getEventTo('Y-m-d');
        }
        else
        {
            return parent::getEventTo($format);
        }
    }
}
