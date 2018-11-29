<?php

namespace Mmsbuilder\Pushnotification\Model\Config\Source;

class Orderlist implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $objectManager                 = \Magento\Framework\App\ObjectManager::getInstance();
        $this->statusCollectionFactory = $objectManager->get('\Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory');

        $options = $this->statusCollectionFactory->create()->toOptionArray();
        return $options;
    }
}
