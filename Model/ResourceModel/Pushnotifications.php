<?php
 
namespace Mmsbuilder\Pushnotification\Model\ResourceModel;
 
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
class Pushnotifications extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('Mmsbuilder_Pushnotification', 'id');
    }
}
