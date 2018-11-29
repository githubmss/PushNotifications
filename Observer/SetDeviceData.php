<?php
namespace Mmsbuilder\Pushnotification\Observer;

use Mmsbuilder\Pushnotification\Helper\Data;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

class SetDeviceData implements ObserverInterface
{

    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->jsonHelper = $jsonHelper;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {   
        if($observer->getData('order')){
            $result = array();
            $order = $observer->getData('order');
            $result['device_type'] = $observer->getData('device_type') ? $observer->getData('device_type') : 0;
            $result['device_registraton'] = $observer->getData('device_registraton') ? $observer->getData('device_registraton') : null;
            $finalResult = $this->jsonHelper->jsonEncode($result);
            $order->setDeviceData($finalResult);
            $order->addStatusToHistory($order->getStatus(), 'User is notified by his app');
            try {
                $order->save();
            } catch (Exception $e) {
                echo json_encode(array('status' => 'error', 'message' => __($e->getMessage())));
                exit;
            }
        }
    }
}
