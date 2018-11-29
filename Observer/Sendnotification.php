<?php
namespace Mmsbuilder\Pushnotification\Observer;

use Mmsbuilder\Pushnotification\Helper\Data;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

class Sendnotification implements ObserverInterface
{

    public function __construct(
        Data $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helper      = $helper;
        $this->jsonHelper = $jsonHelper;

    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        if ($this->scopeConfig->getValue(
            'mmsbuilder_pushnotification/user_config/user_config_status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            $order = $observer->getEvent()->getOrder();

            $orderStatus = $this->scopeConfig->getValue(
               'mmsbuilder_pushnotification/user_config/user_config_order_status',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $message = $this->scopeConfig->getValue(
                'mmsbuilder_pushnotification/user_config/user_config_order_message',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $FinalOrderStatus = explode(',', $orderStatus);
            if ($order instanceof \Magento\Framework\Model\AbstractModel) {
                if (in_array($order->getState(), $FinalOrderStatus)) {
                    try{
                        $orderMessage = str_replace(array('{orderid}','{status}'), array($order->getIncrementId(), $order->getStatus()), $message);
                        $varDeviceData = json_decode($order->getData('device_data'),1);
                        $decive_type = $varDeviceData['device_type'] ? $varDeviceData['device_type']+1 : 1;
                        $this->helper->sendPushNotificationsByOrder($decive_type, $orderMessage, $varDeviceData['device_registraton']);
                    } catch (Exception $e) {
                        echo 'Caught exception: '.  $e->getMessage(). "\n";
                    }
                }
            }
        }
        return $this;
    }
}
