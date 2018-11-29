<?php

namespace Mmsbuilder\Pushnotification\Model\Config\Source;

class IospushNotificationmode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Sandbox')],
            ['value' => 2, 'label' => __('Production')],
        ];
    }
}
