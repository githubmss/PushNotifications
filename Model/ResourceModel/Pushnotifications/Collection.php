<?php
 
namespace Mmsbuilder\Pushnotification\Model\ResourceModel\Pushnotifications;
 
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Mmsbuilder\Pushnotification\Model\Pushnotifications',
            'Mmsbuilder\Pushnotification\Model\ResourceModel\Pushnotifications'
        );
    }
}